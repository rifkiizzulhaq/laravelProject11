<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;

class MigrationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['events']->listen('Illuminate\Database\Events\MigrationsStarting', function () {
            try {
                $response = Http::timeout(5)->get('http://127.0.0.1:5000');
                
                if (!$response->successful()) {
                    throw new \Exception('Python server returned error response');
                }
            } catch (\Exception $e) {
                die("\n\033[31mERROR: Python face recognition server is not running!\033[0m\n" .
                    "Please start the server by running: python Detection.py\n" .
                    "Error: " . $e->getMessage() . "\n");
            }
        });
    }

    public function register()
    {
        //
    }
} 