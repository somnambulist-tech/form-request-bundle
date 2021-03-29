<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Rakit\Validation\Rule;

/**
 * Class TypeStringRule
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Rules\TypeStringRule
 */
class TypeStringRule extends Rule
{

    protected $message = ':attribute must be a string';

    public function check($value): bool
    {
        return is_string($value);
    }
}
