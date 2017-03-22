<?php

namespace VivienLN\Pilot\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use VivienLN\Pilot\PilotServiceProvider;

class PilotInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pilot:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Pilot administration panel and copy config and public assets to common directory';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->info('Installing pilot administration panel...');

        // publish config
        Artisan::call('vendor:publish', [
            '--provider' => PilotServiceProvider::class,
            '--tag' => 'config'
        ]);

        $this->info('Configuration file copied to config/pilot.php.');

        // publish public assets
        Artisan::call('vendor:publish', [
            '--provider' => PilotServiceProvider::class,
            '--tag' => 'public'
        ]);
        $this->info('Public assets copied to public/vendor/.');

        // migrate new tables
        if ($this->confirm('Do you want to run migrations now?')) {
            Artisan::call('migrate');
            $this->info('Created pilot tables');
        } else {
            $this->line('Call artisan migrate later to create pilot tables.');
        }


        if($this->confirm('Auto-generate policies from exisiting models?')) {
            Artisan::call('pilot:policy');

        } else {
            $this->line('Without policies, users will not be able to see models. Don\'t forget to manually generate them.');
        }

        // success message
        $this->info('Pilot was successfully installed!');
    }
}
