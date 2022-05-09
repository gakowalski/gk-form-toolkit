<?php

namespace Kowalski\Laravel;

use Illuminate\Support\Facades\Blade;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot() {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        
        Blade::directive('html', function ($expression) {
          return "<?php echo \App\Html::$expression ?>";
        });
    }

    public function register() {

    }
}
