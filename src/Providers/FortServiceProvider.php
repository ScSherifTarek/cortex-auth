<?php

declare(strict_types=1);

namespace Cortex\Fort\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Rinvex\Fort\Contracts\RoleContract;
use Rinvex\Fort\Contracts\UserContract;
use Rinvex\Fort\Contracts\AbilityContract;
use Cortex\Fort\Console\Commands\SeedCommand;
use Cortex\Fort\Console\Commands\InstallCommand;
use Cortex\Fort\Console\Commands\MigrateCommand;
use Cortex\Fort\Console\Commands\PublishCommand;
use Cortex\Fort\Console\Commands\RollbackCommand;
use Illuminate\Database\Eloquent\Relations\Relation;

class FortServiceProvider extends ServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        SeedCommand::class => 'command.cortex.fort.seed',
        InstallCommand::class => 'command.cortex.fort.install',
        MigrateCommand::class => 'command.cortex.fort.migrate',
        PublishCommand::class => 'command.cortex.fort.publish',
        RollbackCommand::class => 'command.cortex.fort.rollback',
    ];

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cortex.fort.user.tabs', function ($app) {
            return collect();
        });

        // Register console commands
        ! $this->app->runningInConsole() || $this->registerCommands();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        // Bind route models and constrains
        $router->pattern('ability', '[0-9]+');
        $router->pattern('role', '[a-z0-9-]+');
        $router->pattern('user', '[a-zA-Z0-9_-]+');
        $router->model('role', RoleContract::class);
        $router->model('user', UserContract::class);
        $router->model('ability', AbilityContract::class);

        // Map relations
        Relation::morphMap([
            'role' => config('rinvex.fort.models.role'),
            'ability' => config('rinvex.fort.models.ability'),
            'user' => config('auth.providers.'.config('auth.guards.'.config('auth.defaults.guard').'.provider').'.model'),
        ]);

        // Load resources
        require __DIR__.'/../../routes/breadcrumbs.php';
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'cortex/fort');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'cortex/fort');
        $this->app->afterResolving('blade.compiler', function () {
            require __DIR__.'/../../routes/menus.php';
        });

        // Publish Resources
        ! $this->app->runningInConsole() || $this->publishResources();

        // Register attributes entities
        app('rinvex.attributes.entities')->push(UserContract::class);
    }

    /**
     * Publish resources.
     *
     * @return void
     */
    protected function publishResources()
    {
        $this->publishes([realpath(__DIR__.'/../../resources/lang') => resource_path('lang/vendor/cortex/fort')], 'cortex-fort-lang');
        $this->publishes([realpath(__DIR__.'/../../resources/views') => resource_path('views/vendor/cortex/fort')], 'cortex-fort-views');
    }

    /**
     * Register console commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        // Register artisan commands
        foreach ($this->commands as $key => $value) {
            $this->app->singleton($value, function ($app) use ($key) {
                return new $key();
            });
        }

        $this->commands(array_values($this->commands));
    }
}
