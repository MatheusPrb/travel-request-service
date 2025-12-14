<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidTravelDatesException extends HttpException
{
    public function __construct()
    {
        parent::__construct(
            422,
            'Data de volta não pode ser antes da ida.'
        );
    }
}
