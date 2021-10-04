<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Rakit\Validation\Rule;

/**
 * Class ProhibitedRule
 *
 * Based on Laravel validators prohibited
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Rules\ProhibitedRule
 */
class ProhibitedRule extends Rule
{
    protected $message = ':attribute is not allowed';

    public function check($value): bool
    {
        return false;
    }
}
