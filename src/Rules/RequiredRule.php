<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Somnambulist\Components\Validation\Rule;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function count;
use function is_array;
use function is_null;
use function is_string;
use function trim;

/**
 * A re-implementation of the UploadedFile rule to work with Symfony file arrays.
 */
class RequiredRule extends Rule
{
    protected bool $implicit = true;
    protected string $message = 'rule.required';

    public function check($value): bool
    {
        $this->attribute?->makeRequired();

        if ($this->attribute?->rules()->has('uploaded_file') && $value instanceof UploadedFile) {
            return $value->isValid();
        }

        if (is_string($value)) {
            return mb_strlen(trim($value), 'UTF-8') > 0;
        }
        if (is_array($value)) {
            return count($value) > 0;
        }

        return !is_null($value);
    }
}
