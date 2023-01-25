<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

final class EntityGeneratorCommand extends Command
{
    private const NS_HTTP_DEFAULT = 'Http';
    private const NS_HTTP_EXTERNAL = 'HttpExternal';

    private const ROUTES_PREFIX_DEFAULT = 'app';
    private const ROUTES_PREFIX_EXTERNAL = 'external';

    private const ARG_ENTITY = 'entity';

    private const OPTION_IS_EXTERNAL = 'external';

    private const OPTION_WITH_CRUD = 'with-crud-actions';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "make:entry-point ".
    "{".self::ARG_ENTITY."? : Entity name to generate} ".
    "{--".self::OPTION_IS_EXTERNAL." : Generates entity classes under the 'HttpExternal' directory} ".
    "{--".self::OPTION_WITH_CRUD." : Generates CRUD action classes and methods inside entity controller}";


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
            $withCrud = $this->option(self::OPTION_WITH_CRUD);

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

            $destinationPath = $this->composeFullDestination($destinationPath, $entityInput);
            $routesDestination = $this->composeFullDestination($routesDestination, $entityInput, $entityName);
            $namespace = $this->composeFullNamespace($namespace, $entityInput);

            $files = $withCrud
                ? $this->createEntityActionFiles(
                    $destinationPath,
                    $namespace,
                    $entityName,
                    $this->getSnippetsPath('actions'),
                )
                : $this->createEntityActionFolders($destinationPath);

            $files[] = $this->createEntityController(
                $destinationPath,
                $namespace,
                $entityName,
                $this->getSnippetsPath('controllers'),
                $withCrud
            );

            $files[] = $this->createRoutesFile(
                $routesDestination,
                $entityName,
                $this->getSnippetsPath('routes'),
                $entityInput,
                $withCrud
            );

            $this->renderReport($entityInput, $files);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->output->error($e->getMessage());
        } catch (\Throwable $e) {
            $this->output->error(sprintf("Unhandled error: %s", $e->getMessage()));
        }

        if ($this->output->isVerbose()) {
            $this->output->write($e->getTraceAsString());
        }

        return self::FAILURE;
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
            throw new \Exception(
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

        $fullDestination = $this->composeFullDestination($destinationPath, $entityName);

        if (!file_exists($fullDestination)) {
            return $entityName;
        }

        if (!is_dir($fullDestination)) {
            throw new \Exception(
                sprintf(
                    "Cannot create entity '%s'. Destination path '%s' already exists and it is a file",
                    $entityName,
                    $fullDestination
                )
            );
        }

        if (!$this->isDirEmpty($fullDestination)) {
            throw new \Exception(
                sprintf(
                    "Cannot create entity '%s'. Destination path '%s' is a non-empty folder",
                    $entityName,
                    $fullDestination
                )
            );
        }

        return $entityName;
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

    private function createEntityController(
        string $path,
        string $namespace,
        string $entity,
        string $snippetPath,
        bool $withCrud
    ): string {
        $className = $entity.'Controller';
        $filePath = $path."/".$className.".php";
        $replacements = [
            'entity_ns' => $namespace,
            'entity_name' => $entity,
            'entity_name_singular' => Str::singular($entity),
            'classname' => $entity.'Controller',
        ];

        $snippet = $withCrud ? 'EntityController-with-crud.php.snippet' : 'EntityController-empty.php.snippet';
        return $this->createClassFromSnippet(
            $snippetPath.'/'.$snippet,
            $filePath,
            $replacements
        );
    }

    private function createRoutesFile(
        string $routesDestination,
        string $entityName,
        string $snippetPath,
        string $entityInput,
        bool $withCrud
    ): string {
        $routesFile = rtrim($routesDestination, '/').'/'.strtolower($entityName).'.php';

        $replacements = [
            'entity_name' => $entityName,
            'extra_prefix' => $this->formatPrefix($entityInput),
            'extra_namespace' => str_replace('/', '\\', $entityInput),
            'prefix_route_name' => implode('.', array_map(function ($item) {
                return Str::snake(Str::lcfirst($item), '-');
            }, explode('/', $entityInput))),
            'entity_name_singular' => Str::lower(Str::singular($entityName)),
            'entity_controller' => $entityName.'Controller',
        ];

        $snippet = $withCrud ? 'EntityRoutes-with-crud.php.snippet' : 'EntityRoutes-empty.php.snippet';
        return $this->createClassFromSnippet(
            $snippetPath.'/'.$snippet,
            $routesFile,
            $replacements
        );
    }

    private function createEntityActionFolders(string $destinationFolder): array
    {
        $folders = [
            $destinationFolder."/ActionsPresentations",
            $destinationFolder."/ActionsProcessors",
            $destinationFolder."/ActionsRequests",
        ];

        foreach ($folders as $folder) {
            if (mkdir(directory: $folder, recursive: true)) {
                continue;
            }

            throw new \Exception(sprintf("Cannot create folder [%s]", $folder));
        }

        return $folders;
    }

    private function createEntityActionFiles(
        string $path,
        string $namespace,
        string $entity,
        string $snippetPath,
    ): array {
        $files = [];
        $actions = ['index', 'store', 'show', 'update', 'destroy'];

        // Presentations
        foreach ($actions as $action) {
            $dir = 'ActionsPresentations';
            $classname = $entity.ucfirst($action).'Presentation';
            $replacements = [
                'entity_ns' => $namespace.'\\'.$dir,
                'classname' => $entity.ucfirst($action).'Presentation'
            ];

            $files[] = $this->createClassFromSnippet(
                $snippetPath.'/EntityActionPresentation.php.snippet',
                $path."/".$dir."/".$classname.'.php',
                $replacements
            );
        }

        // Processors
        $action_templates = [
            'index' => 'Action',
            'store' => 'Action',
            'show' => 'Action-with-id',
            'update' => 'Action-with-id',
            'destroy' => 'Action-with-id',
        ];
        foreach ($actions as $action) {
            $dir = 'ActionsProcessors';
            $classname = $entity.ucfirst($action).'Processor';
            $replacements = [
                'entity_ns' => $namespace.'\\'.$dir,
                'classname' => $entity.ucfirst($action).'Processor'
            ];

            $files[] = $this->createClassFromSnippet(
                $snippetPath.'/Entity'.$action_templates[$action].'Processor.php.snippet',
                $path."/".$dir."/".$classname.'.php',
                $replacements
            );
        }

        // Requests
        foreach ($actions as $action) {
            $dir = 'ActionsRequests';
            $classname = $entity.ucfirst($action).'Request';
            $replacements = [
                'entity_ns' => $namespace.'\\'.$dir,
                'classname' => $entity.ucfirst($action).'Request'
            ];

            $files[] = $this->createClassFromSnippet(
                $snippetPath.'/EntityActionRequest.php.snippet',
                $path."/".$dir."/".$classname.'.php',
                $replacements
            );
        }

        return $files;
    }

    private function isDirEmpty(string $dirPath): bool
    {
        $dir = opendir($dirPath);
        $entry = readdir($dir);

        while (false !== $entry) {
            if ($entry !== "." && $entry !== "..") {
                closedir($dir);

                return false;
            }

            $entry = readdir($dir);
        }

        closedir($dir);

        return true;
    }

    private function getSnippetsPath(string $snippetsSet): string
    {
        $snippetsPath = storage_path("generation/entry-point-snippets")."/".$snippetsSet;

        $snippetsPath = "/".trim($snippetsPath, "/");

        if (!is_dir($snippetsPath)) {
            throw new \Exception(
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
            throw new \Exception(
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
            throw new \Exception(
                sprintf("Cannot create entity directory '%s'", $path)
            );
        }

        if (false === file_put_contents($filepath, $content)) {
            throw new \Exception(sprintf("Cannot create file [%s]", $filepath));
        }
    }

    private function formatPrefix(string $entityInput): string
    {
        $segments = [];
        foreach (explode('/', $entityInput) as $segment) {
            $segments[] = Str::snake(Str::lcfirst($segment), '-');
        }

        return implode('/', $segments);
    }
}
