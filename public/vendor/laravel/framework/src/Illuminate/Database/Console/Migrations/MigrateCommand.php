<?php

namespace Illuminate\Database\Console\Migrations;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\SchemaLoaded;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\SqlServerConnection;

class MigrateCommand extends BaseCommand
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate {--database= : The database connection to use}
                {--force : Force the operation to run when in production}
                {--path=* : The path(s) to the migrations files to be executed}
                {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                {--schema-path= : The path to a schema dump file}
                {--pretend : Dump the SQL queries that would be run}
                {--seed : Indicates if the seed task should be re-run}
                {--step : Force the migrations to be run so they can be rolled back individually}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations';

    /**
     * The migrator instance.
     *
     * @var \Illuminate\Database\Migrations\Migrator
     */
    protected $migrator;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a new migration command instance.
     *
     * @param  \Illuminate\Database\Migrations\Migrator  $migrator
     * @param  \Illuminate\Contracts\Events\Dispatcher  $dispatcher
     * @return void
     */
    public function __construct(Migrator $migrator, Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->migrator = $migrator;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $this->migrator->usingConnection($this->option('database'), function () {
            $this->prepareDatabase();
            $this->migrator->setOutput($this->output)
                    ->run($this->getMigrationPaths(), [
                        'pretend' => $this->option('pretend'),
                        'step' => $this->option('step'),
                    ]);
            if ($this->option('seed') && ! $this->option('pretend')) {
                $this->call('db:seed', ['--force' => true]);
            }
        });

        return 0;
    }

    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        if (! $this->migrator->repositoryExists()) {
            $this->call('migrate:install', array_filter([
                '--database' => $this->option('database'),
            ]));
        }

        if (! $this->migrator->hasRunAnyMigrations() && ! $this->option('pretend')) {
            $this->loadSchemaState();
        }
    }

    /**
     * Load the schema state to seed the initial database schema structure.
     *
     * @return void
     */
    protected function loadSchemaState()
    {
        $connection = $this->migrator->resolveConnection($this->option('database'));
        if ($connection instanceof SqlServerConnection ||
            ! is_file($path = $this->schemaPath($connection))) {
            return;
        }

        $this->line('<info>Loading stored database schema:</info> '.$path);

        $startTime = microtime(true);
        $this->migrator->deleteRepository();

        $connection->getSchemaState()->handleOutputUsing(function ($type, $buffer) {
            $this->output->write($buffer);
        })->load($path);

        $runTime = number_format((microtime(true) - $startTime) * 1000, 2);
        $this->dispatcher->dispatch(
            new SchemaLoaded($connection, $path)
        );

        $this->line('<info>Loaded stored database schema.</info> ('.$runTime.'ms)');
    }

    /**
     * Get the path to the stored schema for the given connection.
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return string
     */
    protected function schemaPath($connection)
    {
        if ($this->option('schema-path')) {
            return $this->option('schema-path');
        }

        if (file_exists($path = database_path('schema/'.$connection->getName().'-schema.dump'))) {
            return $path;
        }

        return database_path('schema/'.$connection->getName().'-schema.sql');
    }
}
