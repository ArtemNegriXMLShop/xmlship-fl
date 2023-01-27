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

    private bool $isExternal;
    private string $entityInput;
    private string $entity;
    private string $action;
    private string $path;
    private string $routesPath;
    private string $namespace;

    /**
     * @var string[]
     */
    private array $filesCreated;

    /**
     * @var string[]
     */
    private array $filesEdited;

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
            $this->initFromArguments();

            if (!$this->controllerExists()) {
                $this->warn(sprintf("Controller for %s doesn't exist.", $this->entity));
                if (!$this->confirm(sprintf("Create Controller for %s?", $this->entity))) {
                    $this->info("Stopped because Controller is required to continue.");
                    return self::SUCCESS;
                }

                // create Controller
                $returnCode = $this->call(
                    'make:entry-point',
                    [
                        self::ARG_ENTITY => $this->entityInput,
                        '--' . self::OPTION_IS_EXTERNAL => $this->isExternal,
                    ]
                );
                if ($returnCode !== self::SUCCESS) {
                    $this->error("Stopped because Controller creation failed.");
                    return $returnCode;
                }
            }

            $this->generateActionFiles();
            $this->addActionToController();
            $this->addActionRoute();

            $this->renderReport();

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

    private function initFromArguments()
    {
        $this->isExternal = $this->option(self::OPTION_IS_EXTERNAL);

        $namespace = $this->isExternal
            ? 'App\\EntryPoints\\'.self::NS_HTTP_EXTERNAL
            : 'App\\EntryPoints\\'.self::NS_HTTP_DEFAULT;
        $destinationPath = $this->isExternal
            ? app_path('EntryPoints/'.self::NS_HTTP_EXTERNAL)
            : app_path('EntryPoints/'.self::NS_HTTP_DEFAULT);
        $routesDestination = $this->isExternal
            ? app_path('EntryPoints/Routes/'.self::ROUTES_PREFIX_EXTERNAL)
            : app_path('EntryPoints/Routes/'.self::ROUTES_PREFIX_DEFAULT);

        $this->entityInput = $this->validatedEntityName();
        $this->entity = str_contains($this->entityInput, '/')
            ? basename($this->entityInput)
            : $this->entityInput;

        $this->action = $this->validateActionName($this->argument(self::ARG_ACTION));

        $this->path = $this->composeFullDestination($destinationPath, $this->entityInput);
        $this->routesPath = $this->composeFullDestination($routesDestination, $this->entityInput, $this->entity);
        $this->namespace = $this->composeFullNamespace($namespace, $this->entityInput);

        $this->filesCreated = [];
        $this->filesEdited = [];
    }

    private function controllerExists(): bool
    {
        return $this->files->isFile($this->getControllerFile());
    }

    private function getControllerFile(): string
    {
        return $this->path . '/' . $this->entity . 'Controller.php';
    }

    private function getRoutesFile(): string
    {
        return $this->routesPath . '/' . strtolower($this->entity) . '.php';
    }

    /**
     * @throws Exception
     */
    private function generateActionFiles(): void
    {
        $this->createActionPresentation();
        $this->createActionProcessor();
        $this->createActionRequest();
    }

    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    private function addActionToController(): void
    {
        $controller = $this->readController();

        $actionStub = $this->processSnippet(
            $this->getActionSnippet(),
            [
                'entity_ns' => $this->namespace,
                'entity_name' => $this->entity,
                'entity_name_singular' => Str::singular($this->entity),
                'action_name' => $this->action,
                'action_method_name' => lcfirst($this->action),
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

        $this->saveController($controller);
    }

    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    private function addActionRoute(): void
    {
        $actionRoute = $this->processSnippet(
            $this->getRoutesActionSnippet(),
            [
                'entity_singular_snake' => Str::snake(Str::singular($this->entity)),
                'action_kebab' => Str::kebab($this->action),
                'entity_controller' => $this->entity . 'Controller',
                'action_method' => Str::camel($this->action),
                'route_name' => implode('.', array_map(
                    fn ($item) => Str::snake($item, '-'),
                    explode('/', $this->entityInput . '/' . $this->action)
                )),
            ]
        );

        $routes = $this->readRoutes();

        // insert route before last curly brace in routes
        $lastBracePos = strrpos($routes, '}', -1);
        if ($lastBracePos === false) {
            throw new Exception('Closing curly brace not found in the routes');
        }
        $routes = substr_replace($routes, "    " . trim($actionRoute) . "\n", $lastBracePos, 0);

        $this->saveRoutes($routes);
    }

    /**
     * @throws FileNotFoundException
     */
    private function readController(): string
    {
        return $this->files->get($this->getControllerFile());
    }

    private function saveController($controller): void
    {
        $this->files->put($this->getControllerFile(), $controller);

        $this->filesEdited[] = $this->getControllerFile();
    }

    /**
     * @throws FileNotFoundException
     */
    private function readRoutes(): string
    {
        return $this->files->get($this->getRoutesFile());
    }

    private function saveRoutes($routes): void
    {
        $this->files->put($this->getRoutesFile(), $routes);

        $this->filesEdited[] = $this->getRoutesFile();
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

    private function renderReport(): void
    {
        $this->output->success(sprintf("The %s action created.", $this->action));

        $this->info(sprintf("%d files were successfully generated:", count($this->filesCreated)));
        $this->renderReportTable($this->filesCreated);

        $this->info(sprintf("%d files were edited:", count($this->filesEdited)));
        $this->renderReportTable($this->filesEdited);
    }

    private function renderReportTable(array $files): void
    {
        $tableHeader = ['Type', 'Path'];

        $fileTypes = [
            'Controller',
            'Presentation',
            'Processor',
            'Request',
            'Routes', // Default
        ];

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
     * @return string
     * @throws Exception
     */
    private function validatedEntityName(): string
    {
        $entityName = $this->argument(self::ARG_ENTITY);

        if (preg_match('/[^a-zA-Z\/]/', $entityName)) {
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

        $this->filesCreated[] = $filepath;
    }

    /**
     * @throws Exception
     */
    private function createActionPresentation(): void
    {
        $dir = 'ActionsPresentations';
        $classname = $this->entity . $this->action . 'Presentation';
        $replacements = [
            'entity_ns' => $this->namespace . '\\' . $dir,
            'classname' => $classname
        ];

        $snippetFile = $this->getSnippetsPath('actions') . '/EntityActionPresentation.php.snippet';
        $classFile = $this->path . '/' . $dir . '/' . $classname . '.php';
        $this->createClassFromSnippet($snippetFile, $classFile, $replacements);
    }

    /**
     * @throws Exception
     */
    private function createActionProcessor(string $snippetActionName = 'Action'): void
    {
        $dir = 'ActionsProcessors';
        $classname = $this->entity . $this->action . 'Processor';
        $replacements = [
            'entity_ns' => $this->namespace . '\\' . $dir,
            'classname' => $classname
        ];

        $snippetFile = $this->getSnippetsPath('actions') . '/Entity' . $snippetActionName . 'Processor.php.snippet';
        $classFile = $this->path . '/' . $dir . '/' . $classname . '.php';
        $this->createClassFromSnippet($snippetFile, $classFile, $replacements);
    }

    /**
     * @throws Exception
     */
    private function createActionRequest(): void
    {
        $dir = 'ActionsRequests';
        $classname = $this->entity . $this->action . 'Request';
        $replacements = [
            'entity_ns' => $this->namespace . '\\' . $dir,
            'classname' => $classname
        ];

        $snippetFile = $this->getSnippetsPath('actions') . '/EntityActionRequest.php.snippet';
        $classFile = $this->path . '/' . $dir . '/' . $classname . '.php';
        $this->createClassFromSnippet($snippetFile, $classFile, $replacements);
    }
}
