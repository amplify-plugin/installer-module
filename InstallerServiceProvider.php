<?php

namespace Amplify\System\Installer;

use Amplify\System\Installer\Middleware\canInstall;
use Amplify\System\installer\Middleware\canUpdate;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class InstallerServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/installer.php', 'installer');

    }

    /**
     * Bootstrap the application events.
     */
    public function boot(Router $router)
    {
        $this->loadRoutesFrom(__DIR__ . '/route/web.php');

        $this->loadTranslationsFrom(__DIR__ . '/lang', 'installer');

        $this->loadViewsFrom(__DIR__.'/Views', 'installer');

        $router->middlewareGroup('install', [CanInstall::class]);

        $router->middlewareGroup('update', [CanUpdate::class]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Amplify\System\Installer\Commands\ConsoleInstallCommand::class,
            ]);
        }
    }
}
