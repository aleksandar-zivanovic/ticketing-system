<?php
namespace Exceptions;

class AccountNotVerifiedException extends \Exception
{
    protected $message = "The email is not verified. Please verify your email before logging in.";
}
