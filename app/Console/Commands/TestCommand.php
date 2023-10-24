<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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

        // $user = User::find(1);
        // $count = $user->company()->count();
        $r = url('admin/logout');
                dd($r);
    }
}
