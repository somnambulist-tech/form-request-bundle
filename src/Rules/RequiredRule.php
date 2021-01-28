<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Rakit\Validation\Rule;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function count;
use function is_array;
use function is_null;
use function is_string;
use function trim;

/**
 * Class RequiredRule
 *
 * A re-implementation of the UploadedFile rule to work with Symfony file arrays.
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Rules\RequiredRule
 */
class RequiredRule extends Rule
{

    /** @var bool */
    protected $implicit = true;

    /** @var string */
    protected $message = "The :attribute is required";

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function check($value): bool
    {
        $this->setAttributeAsRequired();

        if ($this->attribute and $this->attribute->hasRule('uploaded_file') && $value instanceof UploadedFile) {
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

    /**
     * Set attribute is required if $this->attribute is set
     *
     * @return void
     */
    protected function setAttributeAsRequired()
    {
        if ($this->attribute) {
            $this->attribute->setRequired(true);
        }
    }
}
