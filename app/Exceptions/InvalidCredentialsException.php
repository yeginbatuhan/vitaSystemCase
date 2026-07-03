<?php

namespace App\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    protected $message = 'E-posta veya şifre hatalı.';
}
