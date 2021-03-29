<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Rules;

use Rakit\Validation\Helper;
use Rakit\Validation\Rule;
use Somnambulist\Bundles\FormRequestBundle\Rules\Behaviours\CanConvertValuesToBooleans;
use function array_shift;
use function in_array;
use function is_bool;

/**
 * Class ProhibitedUnlessRule
 *
 * Based on Laravel validators prohibited_unless
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Rules\ProhibitedUnlessRule
 */
class ProhibitedUnlessRule extends Rule
{

    use CanConvertValuesToBooleans;

    protected $message  = ':attribute is not allowed if :field does not have value(s) :values';
    protected $implicit = true;

    public function fillParameters(array $params): Rule
    {
        $this->params['field']  = array_shift($params);
        $this->params['values'] = $this->convertStringsToBoolean($params);

        return $this;
    }

    public function check($value): bool
    {
        $this->requireParameters(['field', 'values']);

        $anotherAttribute = $this->parameter('field');
        $definedValues    = $this->parameter('values');
        $anotherValue     = $this->getAttribute()->getValue($anotherAttribute);

        if ($definedValues) {
            $or = $this->validation ? $this->validation->getTranslation('or') : 'or';
            $this->setParameterText('values', Helper::join(Helper::wraps($this->convertBooleansToString($definedValues), "'"), ', ', ", {$or} "));
        }

        $validator         = $this->validation->getValidator();
        $requiredValidator = $validator('required');

        if (!in_array($anotherValue, $definedValues, is_bool($anotherValue))) {
            return !$requiredValidator->check($value);
        }

        return true;
    }
}
