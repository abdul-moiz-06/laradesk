<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class InvalidCredentialsException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'The provided credentials are incorrect.';
}
