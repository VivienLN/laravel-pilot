<?php

namespace VivienLN\Pilot\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use VivienLN\Pilot\PilotServiceProvider;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class PilotPoliciesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pilot:policies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate policy and copy config to Providers\AuthServiceProvider';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $path = $appDir ?? $this->anticipate('Path to your model directory', ['app/']);

        $excludedDirectories = ['Console','Exceptions','Http','Providers','Policies'];
        $policiesDirectory = 'app/Policies/';
        $files = [];


        if (is_dir($path)) {
            $iterator = new \RecursiveDirectoryIterator($path);
            foreach ( new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST) as $file ) {

                if ($file->isFile() && !in_array($iterator,$excludedDirectories)) {
                    $finalFile = explode('/',$file);
                    $finalFile = $finalFile[count($finalFile)-1];
                    $finalFile = str_replace('.php','',$finalFile);

                    $files[] = $finalFile;
                    $this->info('[found] ' . $finalFile);
                }
            }
        } else {
            $this->error($path . ' is not a directory');
        }


        if(empty($files) || count($files) <= 2) {
            $this->error('No models was found');
            return false;
        }

        foreach($files as $ind => $model) {
            $parts = explode('\\',$model);
            $model = $parts[count($parts) - 1];

            $policyName = $model . 'Policy';

            if(!file_exists($policiesDirectory . $policyName . '.php')) {

                Artisan::call('make:policy', [
                    '--model' => $model,
                    'name' => $policyName
                ]);

                $this->info('[created] ' . $policyName);

                continue;
            }

            $this->error('[error] ' . $policyName . ' already exists.');
        }

        return true;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->fire();
    }
}
