<?php

namespace VivienLN\Pilot\Console;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use VivienLN\Pilot\Pilot;
use VivienLN\Pilot\PilotRole;
use VivienLN\Pilot\PilotServiceProvider;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class PilotGrantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pilot:grant 
                                {role : The slug of the role to give the user(s)} 
                                {user : The user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant permissions to a user through a role';

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
    public function handle()
    {
        $argRole = $this->argument('role');
        $role = PilotRole::where('slug', $argRole)->first();
        if(empty($role)) {
            $this->error(sprintf('Role not found: %s', $argRole));
            return;
        }
        $argUser = $this->argument('user');
        $user = User::find($argUser);
        if(empty($user)) {
            $this->error(sprintf('User not found: %s', $argUser));
            return;
        }
        // grant role to user
        if(PilotRole::contains($user)) {
            $this->error(sprintf('User %s already has the %s role', $argUser, $argRole));
            return;
        }
        $role->users()->attach($user);
        $this->info(sprintf('Granted %s permissions to user %s', $argRole, $argUser));
    }
}
