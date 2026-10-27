<?php

namespace App\Libraries;

class PackageValidationException extends \RuntimeException
{
    public array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct(implode("\n", $errors));
    }
}
