<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Rules;

use Rakit\Validation\Rule;
use Ramsey\Uuid\Uuid;

/**
 * Class UuidRule
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Rules\UuidRule
 */
class UuidRule extends Rule
{

    protected $message = 'The :attribute is not a valid UUID or is NIL';

    public function check($value): bool
    {
        return Uuid::isValid($value) && $value !== Uuid::NIL;
    }
}
