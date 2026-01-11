<?php
namespace Exceptions;

class AccountBlockedException extends \Exception
{
    protected $message = "The account is blocked.";
}
