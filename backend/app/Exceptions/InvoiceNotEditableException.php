<?php

namespace App\Exceptions;

use Exception;

class InvoiceNotEditableException extends Exception
{
    public function __construct()
    {
        parent::__construct('This invoice can no longer be edited because it is not in pending status.');
    }
}
