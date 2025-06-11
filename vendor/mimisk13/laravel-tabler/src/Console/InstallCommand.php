<?php

namespace MimisK13\LaravelTabler\Console;

use RuntimeException;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class InstallCommand extends Command implements PromptsForMissingInput
{
    //use InstallsApiStack, InstallsBladeStack, InstallsInertiaStacks, InstallsLivewireStack;
    use InstallBlade;

    //protected $signature = 'tabler:install { stack : (blade, livewire) }';

    protected $signature = 'tabler:install';

    protected $description = "Tabler UI";

    public function handle()
    {
//        if ($this->argument('stack') === 'blade') {
//
//            return $this->installBlade();
//
//
//        } elseif ($this->argument('stack') === 'livewire') {
//
//            //return $this->installInertiaReactStack();
//            return $this->components->info('Not yet...');
//        }
//
//        $this->components->error('Invalid option. Supported options are [blade], [livewire]');
//
//        return 1;


        return $this->installBlade();
    }

    /**
     * Update the "package.json" file.
     *
     * @param  callable  $callback
     * @param  bool  $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true): void
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    /**
     * Run the given commands.
     *
     * @param  array  $commands
     * @return void
     */
    protected function runCommands($commands)
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });
    }
}
