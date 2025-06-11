<?php

namespace MimisK13\LaravelTabler\Console;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

trait InstallBlade
{
    protected function installBlade()
    {
        $this->updateNodePackages(function ($packages) {
            return [
                'vite-plugin-static-copy' => '^0.17.0',
                '@tabler/core' => '^1.0.0-beta20'
            ] + $packages;
        });

        // Views...
        (new Filesystem)->ensureDirectoryExists(resource_path('views'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/default/resources/views', resource_path('views'));

        // Routes...
        copy(__DIR__.'/../../stubs/default/routes/web.php', base_path('routes/web.php'));

        // Vite
        copy(__DIR__.'/../../stubs/default/vite.config.js', base_path('vite.config.js'));

        $this->components->info('Installing and building Node dependencies.');

        if (file_exists(base_path('pnpm-lock.yaml')))
        {
            $this->runCommands(['pnpm install', 'pnpm run build']);

        } elseif (file_exists(base_path('yarn.lock'))) {

            $this->runCommands(['yarn install', 'yarn run build']);

        } else {

            $this->runCommands(['npm install', 'npm run build']);
        }

        $this->line('');
        $this->components->info('Tabler scaffolding installed successfully.');
    }
}
