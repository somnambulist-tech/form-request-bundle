<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Rakit\Validation\Rule;
use function gettype;

/**
 * Class TypeFloatRule
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Rules\TypeFloatRule
 */
class TypeFloatRule extends Rule
{

    protected $message = ':attribute must be a floating point number / double';

    public function check($value): bool
    {
        // https://www.php.net/manual/en/function.is-float.php#117304
        if (!is_scalar($value)) {
            return false;
        }

        if ('double' === gettype($value)) {
            return true;
        } else {
            return preg_match('/^\\d+\\.\\d+$/', $value) === 1;
        }
    }
}
