<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Http;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use function array_key_exists;
use function array_values;
use function count;
use function is_callable;

/**
 * Class FormRequest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Http
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Http\FormRequest
 *
 * @property ParameterBag    $attributes
 * @property InputBag        $cookies
 * @property FileBag         $files
 * @property HeaderBag       $headers
 * @property InputBag        $query
 * @property InputBag        $request
 * @property ServerBag       $server
 * @property Session|null    $session
 * @property string|resource $content
 */
abstract class FormRequest
{

    private Request      $source;
    private ParameterBag $data;

    public function __construct(Request $request)
    {
        $this->source = $request;
        $this->data   = new ParameterBag();
    }

    public function __get(string $name)
    {
        if (in_array($name, ['attributes', 'cookies', 'files', 'headers', 'query', 'request', 'server'])) {
            return $this->source->$name;
        }
        if ('session' === $name) {
            return $this->source->getSession();
        }
        if ('content' === $name) {
            return $this->source->getContent();
        }

        throw new InvalidArgumentException(sprintf('Property "%s" not defined', $name));
    }

    /**
     * @param FormRequest $form
     * @param array       $data
     *
     * @internal
     */
    final public static function appendValidationData(FormRequest $form, array $data): void
    {
        $form->data = new ParameterBag($data);
    }

    /**
     * The original Symfony Request
     *
     * @return Request
     */
    final public function source(): Request
    {
        return $this->source;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Security $security
     *
     * @return bool
     */
    public function authorize(Security $security): bool
    {
        return true;
    }

    /**
     * The array of fields -> rules for validation
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Returns all the request, query and file data in this request
     *
     * @return array
     */
    final public function all(): array
    {
        $input = $this->source->request->all() + $this->source->query->all();

        return array_replace_recursive($input, $this->source->files->all());
    }

    /**
     * Returns only the validated data
     *
     * @return ParameterBag
     */
    final public function data(): ParameterBag
    {
        return $this->data;
    }

    /**
     * Returns true if the key exists in: attributes, query or request data
     *
     * @param string $key
     *
     * @return bool
     */
    final public function has(string $key): bool
    {
        return
            $this->arrayHas($this->source->attributes->all(), [$key])
            ||
            $this->arrayHas($this->source->query->all(), [$key])
            ||
            $this->arrayHas($this->source->request->all(), [$key])
        ;
    }

    /**
     * Gets the key value from: attributes, query or request data
     *
     * @param string     $key
     * @param mixed|null $default Can be a callback to generate values
     *
     * @return mixed
     */
    final public function get(string $key, mixed $default = null): mixed
    {
        return $this->arrayGet($this->all(), $key, $default);
    }

    /**
     * Get only the specified keys in a new ParameterBag from attributes, query or request
     *
     * @param string ...$key
     *
     * @return ParameterBag
     */
    final public function only(string ...$key): ParameterBag
    {
        $bag = new ParameterBag();

        foreach ($key as $test) {
            $bag->set($test, $this->get($test));
        }

        return $bag;
    }

    /**
     * Get all fields except those specified
     *
     * @param string ...$key
     *
     * @return ParameterBag
     */
    final public function without(string ...$key): ParameterBag
    {
        $data = $this->all();

        $this->forget($data, $key);

        return new ParameterBag($data);
    }

    /**
     * Returns either null or, all the fields specified or the fields as an object
     *
     * Note: to use class, the fields must be in constructor order and the constructor must
     * be simple scalars only. This will not hydrate a nested object.
     *
     * @param string       $bag     One of the parameter bags to fetch the value(s) from
     * @param array        $fields  An array of fields required for this value
     * @param string|null  $class   An optional class to instantiate using the fields
     * @param bool         $subNull If true, substitutes null for missing fields
     *
     * @return mixed
     */
    final public function nullOrValue(string $bag, array $fields, string $class = null, bool $subNull = false): mixed
    {
        if (!$this->$bag instanceof ParameterBag) {
            throw new InvalidArgumentException(sprintf('Bag "%s" is not a ParameterBag instance', $bag));
        }

        $data = $this->$bag->all();

        if (count($fields) === 1 && !$class) {
            return $this->arrayGet($data, $fields[0]);
        }

        if (!$subNull and !$this->arrayHas($data, $fields)) {
            return null;
        }

        if ($class) {
            return new $class(...array_values($this->arrayAll($data, $fields)));
        }

        return $this->arrayAll($data, $fields);
    }

    private function arrayAll(array $array, array $keys): array
    {
        $ret = [];

        foreach ($keys as $key) {
            $ret[$key] = $this->arrayGet($array, $key);
        }

        return $ret;
    }

    /**
     * Based on Laravel Arr::forget
     * @link https://github.com/laravel/framework/blob/cf26e13fa45ac5d9e64ddd0c830ed78e56e3fd4d/src/Illuminate/Collections/Arr.php#L241
     *
     * @param array $array
     * @param array $keys
     *
     * @return void
     */
    private function forget(array &$array, array $keys): void
    {
        $original = &$array;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Based on Laravel Arr::get
     * @link https://github.com/laravel/framework/blob/cf26e13fa45ac5d9e64ddd0c830ed78e56e3fd4d/src/Illuminate/Collections/Arr.php#L286
     *
     * @param array      $array
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    private function arrayGet(array $array, string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return is_callable($default) ? $default() : $default;
            }
        }

        return $array;
    }

    /**
     * Based on Laravel Arr::has
     * @link https://github.com/laravel/framework/blob/cf26e13fa45ac5d9e64ddd0c830ed78e56e3fd4d/src/Illuminate/Collections/Arr.php#L322
     *
     * @param array $array
     * @param array $keys
     *
     * @return bool
     */
    private function arrayHas(array $array, array $keys): bool
    {
        if (!$array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $testArr = $array;

            if (array_key_exists($key, $array)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (is_array($testArr) && array_key_exists($segment, $testArr)) {
                    $testArr = $testArr[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}
