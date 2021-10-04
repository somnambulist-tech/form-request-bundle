<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Rakit\Validation\Rule;
use Ramsey\Uuid\Uuid;
use function is_null;

/**
 * Class UuidRule
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Rules\UuidRule
 */
class UuidRule extends Rule
{
    protected $message = ':attribute is not a valid UUID or is NIL';
    protected $implicit = true;

    public function check($value): bool
    {
        return !is_null($value) && Uuid::isValid($value) && $value !== Uuid::NIL;
    }
}
