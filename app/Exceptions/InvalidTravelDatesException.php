<?php

namespace App\Exceptions;

class InvalidTravelDatesException extends DomainException
{
    protected int $statusCode = 422;
}
