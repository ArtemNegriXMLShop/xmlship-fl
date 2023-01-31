<?php

namespace App\EntryPoints\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeDataEntity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:data-entity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make migration and model';

    private const SCHEMAS_PATH = '/storage/generation/sql-tables/';
    private const MODELS_PATH = '/app/Data/Models/';
    /**
     * Name schema
     *
     * @var string
     */
    private string $schema;

    /**
     * Model classname
     *
     * @var string
     */
    private string $classname;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //input params
        $this->inputParams();

        //create migration
        $this->createMigration();

        //create model
        $this->createModel();

        return self::SUCCESS;
    }

    /**
     * Input params
     *
     * @return void
     */
    private function inputParams()
    {
        while (true) {
            $this->schema = $this->ask('Name schema from '.self::SCHEMAS_PATH);

            if (empty($this->schema)) {
                $this->error('Enter name schema');
                continue;
            } elseif (!file_exists(base_path(self::SCHEMAS_PATH. $this->schema . '.php'))) {
                $this->error(
                    sprintf(
                        "Schema file '%s' does not exist",
                        $this->schema
                    )
                );
                continue;
            }

            break;
        }

        while (true) {
            $this->classname = Str::ucfirst($this->ask("Class name model"));

            if (empty($this->classname)) {
                $this->error('Enter class name model');
                continue;
            } elseif (file_exists(base_path(self::MODELS_PATH. $this->classname . '.php'))) {
                $this->error(
                    sprintf(
                        "Model '%s' exist",
                        $this->classname
                    )
                );
                continue;
            }

            break;
        }
    }

    /**
     * Create migration
     *
     * @return void
     */
    private function createMigration()
    {
        $configs = include(base_path(self::SCHEMAS_PATH. $this->schema . '.php'));

        if (empty($configs)) {
            throw new \Exception('Empty configs');
        }

        if (!array_key_exists('table', $configs)) {
            throw new \Exception('Empty table name');
        }

        if (!array_key_exists('fields', $configs) || empty($configs['fields'])) {
            throw new \Exception('Empty fields');
        }

        $snippetContent = file_get_contents(base_path(self::SCHEMAS_PATH . '/snippets/migration.snippet'));

        //table fields
        $table_fields = [];
        $i = 0;
        foreach ($configs['fields'] as $name => $params) {
            $str = '';
            if ($i > 0) {
                $str = '            ';
            }
            $str .= '$table->'.$params['type'].'(\''.$name.'\')';
            if (array_key_exists('unique', $params)) {
                $str .= '->unique()';
            }
            if (array_key_exists('nullable', $params)) {
                $str .= '->nullable('.(boolval($params['nullable']) ? '' : 'false').')';
            }
            if (array_key_exists('default', $params)) {
                if ($params['type'] === 'boolean') {
                    $str .= '->default('.(boolval($params['default']) ? 'true' : 'false').')';
                }
                else {
                    $str .= '->default(\''.$params['default'].'\')';
                }
            }
            $table_fields[] = $str.';';

            $i++;
        }

        //replacements
        $replacements = [
            'table_name' => $configs['table'],
            'table_fields' => implode("\n", $table_fields),
        ];

        //placeholders
        $placeholders = array_map(
            fn (string $placeholder) => "{".$placeholder."}",
            array_keys($replacements)
        );

        //save to file
        $snippetContentReady = str_replace($placeholders, $replacements, $snippetContent);

        $filepath = database_path('migrations/'. date('Y_m_d_His') . '_create_' . $this->schema . '_' . $configs['table'] .  '_table.php');

        if (false === file_put_contents($filepath, $snippetContentReady)) {
            throw new \Exception(sprintf("Cannot create file [%s]", $filepath));
        }
    }

    /**
     * Create model
     *
     * @return void
     */
    private function createModel()
    {
        $configs = include(base_path(self::SCHEMAS_PATH. $this->schema . '.php'));

        $snippetContent = file_get_contents(base_path(self::SCHEMAS_PATH . '/snippets/model.snippet'));

        //property, fillable
        $property = [];
        $fillable = [];
        foreach ($configs['fields'] as $name => $params) {
            //get type param by type fields
            switch ($params['type']) {
                case 'bigIncrements':
                case 'bigInteger':
                case 'id':
                case 'increments':
                case 'integer':
                case 'smallIncrements':
                case 'smallInteger':
                case 'tinyIncrements':
                case 'tinyInteger':
                case 'unsignedInteger':
                case 'unsignedMediumInteger':
                case 'unsignedSmallInteger':
                case 'unsignedTinyInteger':
                    $type = 'int';
                    break;
                case 'decimal':
                case 'double':
                case 'float':
                case 'unsignedDecimal':
                    $type = 'float';
                    break;
                case 'boolean':
                    $type = 'bool';
                    break;
                case 'date':
                case 'dateTime':
                case 'dateTimeTz':
                case 'time':
                case 'timestamp':
                case 'timestampsTz':
                    $type = 'Carbon';
                    break;
                default:
                    $type = 'string';
            }
            $property[] = ' * @property '.$type.' $'.$name;

            $fillable[] = '        \''.$name.'\',';
        }

        //guarded
        $guarded = '';
        if (array_key_exists('id', $configs['fields'])) {
            $guarded = 'protected $guarded = [\'id\'];';
        }

        //replacements
        $replacements = [
            'classname' => $this->classname,
            'table_name' => $configs['table'],
            'property' => implode("\n", $property),
            'guarded' => $guarded,
            'fillable' => 'protected $fillable = ['."\n".implode("\n", $fillable)."\n".'    ];',
        ];

        //placeholders
        $placeholders = array_map(
            fn (string $placeholder) => "{".$placeholder."}",
            array_keys($replacements)
        );

        //save to file
        $snippetContentReady = str_replace($placeholders, $replacements, $snippetContent);

        $filepath = base_path(self::MODELS_PATH . $this->classname . '.php');

        if (false === file_put_contents($filepath, $snippetContentReady)) {
            throw new \Exception(sprintf("Cannot create file [%s]", $filepath));
        }
    }
}
