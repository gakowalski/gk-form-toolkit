<?php

namespace Kowalski\Laravel;

use Illuminate\Support\Facades\Blade;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot() {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        
        Blade::directive('html', function ($expression) {
          return "<?php echo \App\Html::$expression ?>";
        });

        if ($this->app->runningInConsole()) {
          $this->commands([
              \App\Console\Commands\gk_check::class,
          ]);

          // if debug mode is on
          if (config('app.debug') == true) {

            // output current path
            $this->info('Current path: '.__DIR__);
            
            // output config file path
            $this->info('Config file path: '.__DIR__.'/../../config/gk-form-toolkit.php');

            // output routes file path
            $this->info('Routes file path: '.__DIR__.'/../../routes/web.php');

            // output migrations file path
            $this->info('Migrations file path: '.__DIR__.'/../../database/migrations');

            // output views file path
            $this->info('Views file path: '.__DIR__.'/../../resources/views');

            // output controllers file path
            $this->info('Controllers file path: '.__DIR__.'/../../app/Http/Controllers');

            // output models file path
            $this->info('Models file path: '.__DIR__.'/../../app/Models');

            // output commands file path
            $this->info('Commands file path: '.__DIR__.'/../../app/Console/Commands');

            // output helpers file path
            $this->info('Helpers file path: '.__DIR__.'/../../app/Helpers');

            // output public file path
            $this->info('Public file path: '.__DIR__.'/../../public');

            // output tests file path
            $this->info('Tests file path: '.__DIR__.'/../../tests');

            // output factories file path
            $this->info('Factories file path: '.__DIR__.'/../../database/factories');

            // output seeds file path
            $this->info('Seeds file path: '.__DIR__.'/../../database/seeds');

            // output lang file path
            $this->info('Lang file path: '.__DIR__.'/../../resources/lang');

            // output mail file path
            $this->info('Mail file path: '.__DIR__.'/../../resources/mail');

            // output notifications file path
            $this->info('Notifications file path: '.__DIR__.'/../../app/Notifications');

            // output events file path
            $this->info('Events file path: '.__DIR__.'/../../app/Events');

            // output listeners file path
            $this->info('Listeners file path: '.__DIR__.'/../../app/Listeners');

            // output jobs file path
            $this->info('Jobs file path: '.__DIR__.'/../../app/Jobs');

            // output policies file path
            $this->info('Policies file path: '.__DIR__.'/../../app/Policies');

            // output exceptions file path
            $this->info('Exceptions file path: '.__DIR__.'/../../app/Exceptions');

            // output providers file path
            $this->info('Providers file path: '.__DIR__.'/../../app/Providers');

            // output rules file path
            $this->info('Rules file path: '.__DIR__.'/../../app/Rules');

            // output requests file path
            $this->info('Requests file path: '.__DIR__.'/../../app/Http/Requests');

            // output middleware file path
            $this->info('Middleware file path: '.__DIR__.'/../../app/Http/Middleware');

            // output public assets file path
            $this->info('Public assets file path: '.__DIR__.'/../../public');
            
          }
        }

        $this->publishes([
          __DIR__.'/../../config/gk-form-toolkit.php' => config_path('gk-form-toolkit.php'),
        ], 'config');

        $this->publishes([
          __DIR__.'/../../public' => public_path('vendor/gk-form-toolkit'),
        ], 'public');
    }

    public function register() {

    }
}
