<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Http\Behaviours;

use function array_values;
use function count;
use function Somnambulist\Bundles\FormRequestBundle\Resources\arrayAll;
use function Somnambulist\Bundles\FormRequestBundle\Resources\arrayGet;
use function Somnambulist\Bundles\FormRequestBundle\Resources\arrayHas;
use function Somnambulist\Bundles\FormRequestBundle\Resources\arrayHasWithValue;
use function sprintf;

/**
 * Trait GetNullOrValue
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Http\Behaviours
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Http\Behaviours\GetNullOrValue
 */
trait GetNullOrValue
{
    /**
     * Returns either null or, all the fields specified or the fields as an object
     *
     * Note: to use class, the fields must be in constructor order and the constructor must
     * be simple scalars only. This will not hydrate a nested object.
     *
     * @param array        $data    The array of data to use
     * @param array        $fields  An array of fields required for this value
     * @param string|null  $class   An optional class to instantiate using the fields
     * @param bool         $subNull If true, substitutes null for missing fields
     *
     * @return mixed
     */
    private function doGetNullOrValue(array $data, array $fields, ?string $class = null, bool $subNull = false): mixed
    {
        if (count($fields) === 1 && !$class) {
            return arrayGet($data, $fields[0]);
        }

        if (!$subNull && (!arrayHas($data, $fields) || !arrayHasWithValue($data, $fields))) {
            return null;
        }

        if ($class) {
            return new $class(...array_values(arrayAll($data, $fields)));
        }

        return arrayAll($data, $fields);
    }
}
