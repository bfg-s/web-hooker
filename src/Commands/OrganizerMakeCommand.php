<?php

namespace Bfg\WebHooker\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:organizer')]
class OrganizerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:organizer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new organizer WebHook class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'WebHook organizer';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        $postfix = "";

        if ($this->option('client')) {

            $postfix = "_client";
        }

        return $this->resolveStubPath("/stubs/webhook_organizer{$postfix}.stub");
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
                        ? $customPath
                        : __DIR__.$stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\WebHook\Organizers';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['client', null, InputOption::VALUE_NONE, 'Create a client WebHook organizer'],
        ];
    }
}
