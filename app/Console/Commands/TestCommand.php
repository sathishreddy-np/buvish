<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // setPermissionsTeamId(1);
        // dd(getPermissionsTeamId());
        // $user = User::with('roles')->find(1);
        // dd($user);

        $user = User::find(1);
        $count = $user->companies()->branches()->count();

        dd($count);
    }
}
