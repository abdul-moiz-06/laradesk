<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;

final class IssueApiToken
{
    public function __construct(private readonly Hasher $hasher) {}

    /**
     * Verify the credentials and mint a personal access token for the user.
     *
     * @throws InvalidCredentialsException
     */
    public function __invoke(string $email, #[\SensitiveParameter] string $password, string $deviceName): string
    {
        $user = User::query()->where('email', Str::lower(trim($email)))->first();

        if ($user === null || ! $this->hasher->check($password, $user->password)) {
            throw new InvalidCredentialsException;
        }

        return $user->createToken($deviceName)->plainTextToken;
    }
}
