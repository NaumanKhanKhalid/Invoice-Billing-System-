<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateSuperAdmin extends Command
{
    protected $signature   = 'admin:create {--email=} {--password=} {--name=Super Admin}';
    protected $description = 'Create or update the super admin user on the central database';

    public function handle()
    {
        $email    = $this->option('email')    ?: $this->ask('Super admin email');
        $password = $this->option('password') ?: $this->secret('Password');
        $name     = $this->option('name');

        User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt($password)]
        );

        $this->info("Super admin created: {$email}");
        $this->line("Set SUPER_ADMIN_EMAIL={$email} in your .env file.");
    }
}
