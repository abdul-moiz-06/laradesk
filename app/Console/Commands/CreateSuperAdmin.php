<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateSuperAdmin extends Command
{
    /**
     * @var string
     */
    protected $signature = 'superadmin:create {name} {email}';

    /**
     * @var string
     */
    protected $description = 'Create a platform SuperAdmin and send a password set-up invite.';

    public function handle(Hasher $hasher): int
    {
        $name = $this->argument('name');
        $email = $this->argument('email');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hasher->make(Str::random(64)),
            'tenant_id' => null,
        ]);

        $user->assignRole(Role::SuperAdmin->value);

        $token = Password::createToken($user);
        $notification = new ResetPassword($token);
        $notification->url = Filament::getPanel('super')->getResetPasswordUrl($token, $user);
        $user->notify($notification);

        $this->info("SuperAdmin [{$user->email}] created. A password set-up link has been sent.");

        return self::SUCCESS;
    }
}
