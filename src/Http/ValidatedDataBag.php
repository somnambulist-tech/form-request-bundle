<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Http;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Somnambulist\Bundles\FormRequestBundle\Http\Behaviours\GetNullOrValue;
use Traversable;
use function array_keys;
use function array_values;
use function Somnambulist\Bundles\FormRequestBundle\Resources\arrayGet;
use function Somnambulist\Bundles\FormRequestBundle\Resources\arrayHas;
use function Somnambulist\Bundles\FormRequestBundle\Resources\forget;
use const ARRAY_FILTER_USE_BOTH;

/**
 * Class ValidatedDataBag
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Http
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Http\ValidatedDataBag
 */
class ValidatedDataBag implements Countable, IteratorAggregate
{
    use GetNullOrValue;

    public function __construct(private array $params = [])
    {
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->params);
    }

    public function count(): int
    {
        return count($this->params);
    }

    /**
     * Returns the parameters.
     */
    public function all(): array
    {
        return $this->params;
    }

    /**
     * Filter parameters based on a callback
     */
    public function filter(?callable $callback = null): self
    {
        return new self(array_filter($this->params, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Returns a parameter by name.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return arrayGet($this->params, $key, $default);
    }

    /**
     * Returns true if the parameter is defined.
     */
    public function has(string ...$key): bool
    {
        return arrayHas($this->params, $key);
    }

    /**
     * Returns the parameter keys.
     */
    public function keys(): self
    {
        return new self(array_keys($this->params));
    }

    /**
     * Return the specified value for the field(s) or null, or return a new class of just those fields
     */
    public function nullOrValue(array $fields, string $class = null, bool $subNull = false): mixed
    {
        return $this->doGetNullOrValue($this->params, $fields, $class, $subNull);
    }

    /**
     * Return only the specified keys
     */
    public function only(string ...$key): self
    {
        $values = [];

        foreach ($key as $test) {
            $values[$test] = $this->get($test);
        }

        return new self($values);
    }

    public function values(): self
    {
        return new self(array_values($this->params));
    }

    /**
     * Return everything but the specified keys
     */
    public function without(string ...$key): self
    {
        $data = $this->all();

        forget($data, $key);

        return new self($data);
    }
}
