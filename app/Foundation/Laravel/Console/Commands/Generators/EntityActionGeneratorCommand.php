<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

final class EntityActionGeneratorCommand extends Command
{
    private const NS_HTTP_DEFAULT = 'Http';
    private const NS_HTTP_EXTERNAL = 'HttpExternal';

    private const ROUTES_PREFIX_DEFAULT = 'app';
    private const ROUTES_PREFIX_EXTERNAL = 'external';

    private const ARG_ENTITY = 'entity';
    private const ARG_ACTION = 'action';

    private const OPTION_IS_EXTERNAL = 'external';

    protected Filesystem $files;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "make:entry-point-action " .
    "{" . self::ARG_ENTITY . " : Entity name to generate} " .
    "{" . self::ARG_ACTION . " : Action name to generate} " .
    "{--" . self::OPTION_IS_EXTERNAL . " : Generates entity classes under the 'HttpExternal' directory} ";


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate entity action';

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->files = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (!App::isLocal()) {
            $this->error('Only for local environment.');
            return self::INVALID;
        }

        try {
            $isExternal = $this->option(self::OPTION_IS_EXTERNAL);

            $namespace = $isExternal
                ? 'App\\EntryPoints\\'.self::NS_HTTP_EXTERNAL
                : 'App\\EntryPoints\\'.self::NS_HTTP_DEFAULT;
            $destinationPath = $isExternal
                ? app_path('EntryPoints/'.self::NS_HTTP_EXTERNAL)
                : app_path('EntryPoints/'.self::NS_HTTP_DEFAULT);
            $routesDestination = $isExternal
                ? app_path('EntryPoints/Routes/'.self::ROUTES_PREFIX_EXTERNAL)
                : app_path('EntryPoints/Routes/'.self::ROUTES_PREFIX_DEFAULT);

            $entityInput = $this->validatedEntityName($destinationPath);
            $entityName = str_contains($entityInput, '/')
                ? basename($entityInput)
                : $entityInput;

            $action = $this->validateActionName($this->argument(self::ARG_ACTION));

            $destinationPath = $this->composeFullDestination($destinationPath, $entityInput);
            $routesDestination = $this->composeFullDestination($routesDestination, $entityInput, $entityName);
            $namespace = $this->composeFullNamespace($namespace, $entityInput);

            if (!$this->controllerExists($destinationPath, $entityName)) {
                $this->warn(sprintf("Controller for %s doesn't exist.", $entityName));
                if (!$this->confirm(sprintf("Create controller for %s?", $entityName))) {
                    return self::SUCCESS;
                }

                // create Controller
                $returnCode = $this->call(
                    'make:entry-point',
                    [
                        self::ARG_ENTITY => $entityInput,
                        '--' . self::OPTION_IS_EXTERNAL => $isExternal,
                    ]
                );
                if ($returnCode) {
                    return $returnCode;
                }

                // double check
                if (!$this->controllerExists($destinationPath, $entityName)) {
                    $this->error("Something went wrong, controller wasn't created");
                    return self::FAILURE;
                }
            }

            $files = $this->generateActionFiles($entityName, $action, $namespace, $destinationPath);
            $this->addActionToController($entityName, $action, $namespace, $destinationPath);
            $this->addActionRoute($entityName, $action, $namespace, $routesDestination, $entityInput);

            $this->renderReport($entityInput, $files);

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->output->error($e->getMessage());
        } catch (\Throwable $e) {
            $this->output->error(sprintf("Unhandled error: %s", $e->getMessage()));
        }

        if ($this->output->isVerbose()) {
            $this->output->write($e->getTraceAsString());
        }

        return self::FAILURE;
    }

    private function controllerExists(string $path, string $entityName): bool
    {
        return $this->files->isFile($this->getControllerFile($path, $entityName));
    }

    private function getControllerFile(string $path, string $entity): string
    {
        return $path . '/' . $entity . 'Controller.php';
    }

    private function getRoutesFile(string $path, string $entity): string
    {
        return $path . '/' . strtolower($entity) . '.php';
    }

    private function generateActionFiles(
        string $entity,
        string $action,
        string $namespace,
        string $path
    ): array {
        $files = [];

        $snippetPath = $this->getSnippetsPath('actions');
        $files[] = $this->createActionPresentation($entity, $action, $namespace, $snippetPath, $path);
        $files[] = $this->createActionProcessor($entity, $action, $namespace, $snippetPath, $path);
        $files[] = $this->createActionRequest($entity, $action, $namespace, $snippetPath, $path);

        return $files;
    }

    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    private function addActionToController(
        string $entity,
        string $action,
        string $namespace,
        string $path
    ): void {
        $controller = $this->readController($path, $entity);

        $actionStub = $this->fillSnippet(
            $this->getActionSnippet(),
            [
                'entity_ns' => $namespace,
                'entity_name' => $entity,
                'entity_name_singular' => Str::singular($entity),
                'action_name' => $action,
                'action_method_name' => lcfirst($action),
            ]
        );

        // split action imports and method code
        $parts = explode('{split}', $actionStub, 2);
        [$actionImports, $actionMethod] = count($parts) == 2 ? $parts : ['', $actionStub];

        // insert imports into controller
        if (!preg_match('/^(.*?(\s+use\b[^;]+;)+)/s', $controller, $match)) {
            throw new Exception('Imports not found in the controller');
        }
        $importsLen = strlen($match[1]);
        $controller = substr_replace($controller, "\n" . trim($actionImports), $importsLen, 0);

        // insert method into controller
        $classEndPos = strrpos($controller, '}', -1);
        if ($classEndPos === false) {
            throw new Exception('Class ending not found in the controller');
        }
        $controller = substr_replace($controller, "\n    " . trim($actionMethod) . "\n", $classEndPos, 0);

        $this->saveController($path, $entity, $controller);
    }

    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    private function addActionRoute(
        string $entity,
        string $action,
        string $namespace,
        string $path,
        string $entityInput
    ): void {
        $actionRoute = $this->fillSnippet(
            $this->getRoutesActionSnippet(),
            [
                'uri' => Str::snake($action, '-'),
                'entity_controller' => $entity . 'Controller',
                'action_method' => lcfirst($action),
                'route_name' => implode('.', array_map(
                    fn ($item) => Str::snake($item, '-'),
                    explode('/', $entityInput . '/' . $action)
                )),
            ]
        );

        $routes = $this->readRoutes($path, $entity);

        $insertAtPos = strrpos($routes, '}', -1);
        if ($insertAtPos === false) {
            throw new Exception('Closing curly brace not found in the routes');
        }
        $routes = substr_replace($routes, "\n    " . trim($actionRoute) . "\n", $insertAtPos, 0);

        $this->saveRoutes($path, $entity, $routes);
    }

    /**
     * @throws FileNotFoundException
     */
    private function readController(string $path, string $entity): string
    {
        return $this->files->get($this->getControllerFile($path, $entity));
    }

    private function saveController(string $path, string $entity, $controller): void
    {
        $this->files->put($this->getControllerFile($path, $entity), $controller);
    }

    /**
     * @throws FileNotFoundException
     */
    private function readRoutes($path, $entity): string
    {
        return $this->files->get($this->getRoutesFile($path, $entity));
    }

    private function saveRoutes($path, $entity, $routes): void
    {
        $this->files->put($this->getRoutesFile($path, $entity), $routes);
    }

    private function getActionSnippet(): string
    {
        $path = $this->getSnippetsPath('controllers');
        $file = $path . '/EntityControllerAction.php.snippet';
        return $this->files->get($file);
    }

    private function getRoutesActionSnippet(): string
    {
        $path = $this->getSnippetsPath('routes');
        $file = $path . '/EntityRoutesAction.php.snippet';
        return $this->files->get($file);
    }

    private function fillSnippet(string $snippet, array $replacements): string
    {
        $replacements = array_combine(
            array_map(fn($key) => '{' . $key . '}', array_keys($replacements)),
            $replacements
        );
        return strtr($snippet, $replacements);
    }

    private function renderReport(string $entity, array $files): void
    {
        $this->output->success(
            sprintf(
                "The %s entity created. %d files were successfully generated!",
                $entity,
                count($files)
            )
        );

        $fileTypes = [
            'Controller',
            'Presentation',
            'Processor',
            'Request',
            'Routes', // Default
        ];
        $tableHeader = ['Type', 'Path'];
        $rows = [];

        foreach ($files as $file) {
            $type = 'Routes';

            foreach ($fileTypes as $fType) {
                if (!str_contains($file, $fType)) {
                    continue;
                }

                $type = $fType;

                break;
            }

            $rows[] = [$type, $file];
        }

        $this->output->table($tableHeader, $rows);
    }

    /**
     * Parses entity name and returns if valid
     *
     * @param  string  $destinationPath
     * @return string
     */
    private function validatedEntityName(string $destinationPath): string
    {
        $entityName = ucfirst($this->argument(self::ARG_ENTITY))
            ?: ucfirst($this->ask("How to name entity?"));


        if (!preg_match('/[a-zA-Z\/]/', $entityName)) {
            throw new Exception(
                sprintf(
                    "Invalid entity name '%s'. Entity name can contain uppercase, lowercase characters and '/'",
                    $entityName
                )
            );
        }

        // Normalize entity name
        $entityName = collect(explode("/", $entityName))
            ->filter(fn(string $fragment) => !empty($fragment))
            ->map(fn(string $fragment) => ucfirst($fragment))
            ->join("/");

        return $entityName;
    }

    private function validateActionName(string $action): string
    {
        if (!preg_match('/^[a-z]+$/i', $action)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid action name '%s'. Action name can contain uppercase and lowercase characters only",
                    $action
                )
            );
        }
        return ucfirst($action);
    }

    /**
     * Composes full path to the entity files
     *
     * @param  string  $baseDestinationPath
     * @param  string  $entityInput
     * @return string
     */
    private function composeFullDestination(
        string $baseDestinationPath,
        string $entityInput,
        ?string $entityName = null
    ): string {
        if (null !== $entityName) {
            $tmp = array_filter(
                explode('/', $entityInput),
                function ($item) use ($entityName) {
                    return $entityName !== $item;
                }
            );

            return $baseDestinationPath.'/'.rtrim(implode('/', $tmp), '/');
        }

        return $baseDestinationPath.'/'.$entityInput;
    }

    private function composeFullNamespace(string $baseNamespace, string $entityInput): string
    {
        return $baseNamespace."\\".str_replace("/", "\\", $entityInput);
    }

    private function getSnippetsPath(string $snippetsSet): string
    {
        $snippetsPath = storage_path("generation/entry-point-snippets")."/".$snippetsSet;

        $snippetsPath = "/".trim($snippetsPath, "/");

        if (!is_dir($snippetsPath)) {
            throw new Exception(
                sprintf("Cannot find snippets in '%s'", $snippetsPath)
            );
        }

        return $snippetsPath;
    }

    private function createClassFromSnippet(
        string $snippetFile,
        string $classFile,
        array $replacements
    ): string {
        $snippetContent = $this->readSnippet($snippetFile);
        $controllerContent = $this->processSnippet($snippetContent, $replacements);
        $this->createFile($classFile, $controllerContent);

        return $classFile;
    }

    private function readSnippet(string $snippetFile): string
    {
        if (!file_exists($snippetFile)) {
            throw new Exception(
                sprintf("Entity snippet not found [%s]", $snippetFile)
            );
        }

        return file_get_contents($snippetFile);
    }

    private function processSnippet(string $snippetContent, array $replacements): string
    {
        $placeholders = array_map(
            fn(string $placeholder) => "{".$placeholder."}",
            array_keys($replacements)
        );

        return str_replace($placeholders, $replacements, $snippetContent);
    }

    private function createFile(string $filepath, string $content): void
    {
        $path = dirname($filepath);

        if (!file_exists($path) && !mkdir(directory: $path, recursive: true)) {
            throw new Exception(
                sprintf("Cannot create entity directory '%s'", $path)
            );
        }

        // don't overwrite existing file to not erase user code
        if ($this->files->isFile($filepath)) {
            throw new Exception(sprintf("File already exists [%s]", $filepath));
        }

        if (false === file_put_contents($filepath, $content)) {
            throw new Exception(sprintf("Cannot create file [%s]", $filepath));
        }
    }

    private function createActionPresentation(
        string $entity,
        string $action,
        string $namespace,
        string $snippetPath,
        string $path
    ): string {
        $dir = 'ActionsPresentations';
        $classname = $entity . ucfirst($action) . 'Presentation';
        $replacements = [
            'entity_ns' => $namespace . '\\' . $dir,
            'classname' => $entity . ucfirst($action) . 'Presentation'
        ];

        return $this->createClassFromSnippet(
            $snippetPath . '/EntityActionPresentation.php.snippet',
            $path . "/" . $dir . "/" . $classname . '.php',
            $replacements
        );
    }

    private function createActionProcessor(
        string $entity,
        string $action,
        string $namespace,
        string $snippetPath,
        string $path,
        string $snippetActionName = 'Action',
    ): string {
        $dir = 'ActionsProcessors';
        $classname = $entity.ucfirst($action).'Processor';
        $replacements = [
            'entity_ns' => $namespace.'\\'.$dir,
            'classname' => $entity.ucfirst($action).'Processor'
        ];

        return $this->createClassFromSnippet(
            $snippetPath.'/Entity'.$snippetActionName.'Processor.php.snippet',
            $path."/".$dir."/".$classname.'.php',
            $replacements
        );
    }

    private function createActionRequest(
        string $entity,
        string $action,
        string $namespace,
        string $snippetPath,
        string $path,
    ): string {
        $dir = 'ActionsRequests';
        $classname = $entity . ucfirst($action) . 'Request';
        $replacements = [
            'entity_ns' => $namespace . '\\' . $dir,
            'classname' => $entity . ucfirst($action) . 'Request'
        ];

        return $this->createClassFromSnippet(
            $snippetPath . '/EntityActionRequest.php.snippet',
            $path . "/" . $dir . "/" . $classname . '.php',
            $replacements
        );
    }
}
