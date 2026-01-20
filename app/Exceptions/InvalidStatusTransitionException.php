<?php

namespace App\Exceptions;

class InvalidStatusTransitionException extends DomainException
{
    protected int $statusCode = 409;
}
