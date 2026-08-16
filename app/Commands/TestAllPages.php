<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestAllPages extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:pages';
    protected $description = 'Test rendering of all web controller pages';

    public function run(array $params)
    {
        // Mock session
        session()->set('user_id', 1);
        session()->set('user_name', 'Test Admin');
        session()->set('user_email', 'admin@example.com');

        $controllers = [
            'HomeController'     => fn() => (new \App\Controllers\HomeController())->index(),
            'SettingsController' => fn() => (new \App\Controllers\SettingsController())->index(),
            'ActivityController' => fn() => (new \App\Controllers\ActivityController())->index(),
            'FeaturesController' => fn() => (new \App\Controllers\FeaturesController())->index(),
            'PosController'      => fn() => (new \App\Controllers\PosController())->index(),
            'VehicleController'  => fn() => (new \App\Controllers\VehicleController())->index(),
            'DebtsController'    => fn() => (new \App\Controllers\DebtsController())->index(),
            'WalletsController'  => fn() => (new \App\Controllers\WalletsController())->index(),
            'BelanjaController'  => fn() => (new \App\Controllers\BelanjaController())->index(),
            'SearchController'   => fn() => (new \App\Controllers\SearchController())->index(),
            'BackupController'   => fn() => (new \App\Controllers\BackupController())->index(),
        ];

        CLI::write('Testing all web pages...', 'yellow');

        foreach ($controllers as $name => $fn) {
            try {
                $out = $fn();
                $len = is_string($out) ? strlen($out) : (is_object($out) ? strlen($out->getBody()) : 0);
                CLI::write("[$name] SUCCESS ($len bytes)", 'green');
            } catch (\Throwable $e) {
                CLI::write("[$name] ERROR: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine(), 'red');
                CLI::write($e->getTraceAsString(), 'light_red');
            }
        }
    }
}
