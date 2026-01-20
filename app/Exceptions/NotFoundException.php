<?php

namespace App\Exceptions;

class NotFoundException extends DomainException
{
    protected int $statusCode = 404;
}
