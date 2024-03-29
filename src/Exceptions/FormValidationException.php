<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Exceptions;

use Exception;
use Somnambulist\Components\Validation\ErrorBag;

class FormValidationException extends Exception
{
    private ErrorBag $errors;

    public function __construct(ErrorBag $errors, string $message = 'The request data failed the validation rules')
    {
        $this->errors = $errors;

        parent::__construct($message);
    }

    public function getErrors(): ErrorBag
    {
        return $this->errors;
    }
}
