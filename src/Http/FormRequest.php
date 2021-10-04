<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Http;

use BadMethodCallException;
use InvalidArgumentException;
use Somnambulist\Bundles\FormRequestBundle\Http\Behaviours\GetNullOrValue;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use function in_array;
use function Somnambulist\Bundles\FormRequestBundle\Resources\arrayGet;
use function Somnambulist\Bundles\FormRequestBundle\Resources\arrayHas;
use function Somnambulist\Bundles\FormRequestBundle\Resources\forget;

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
 *
 * @method ParameterBag    attributes()
 * @method InputBag        cookies()
 * @method FileBag         files()
 * @method HeaderBag       headers()
 * @method InputBag        query()
 * @method InputBag        request()
 * @method ServerBag       server()
 * @method Session|null    session()
 * @method string|resource content()
 */
abstract class FormRequest
{
    use GetNullOrValue;

    private Request $source;
    private ValidatedDataBag $data;
    private array $passThrough = ['attributes', 'cookies', 'files', 'headers', 'query', 'request', 'server'];

    public function __construct(Request $request)
    {
        $this->source = $request;
        $this->data   = new ValidatedDataBag();
    }

    public function __call(string $name, array $arguments)
    {
        if (in_array($name, $this->passThrough)) {
            return $this->source->$name;
        }
        if ('session' === $name) {
            return $this->source->getSession();
        }
        if ('content' === $name) {
            return $this->source->getContent();
        }

        throw new BadMethodCallException(sprintf('Method "%s" does not exist', $name));
    }

    public function __get(string $name)
    {
        if (in_array($name, $this->passThrough)) {
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
        $form->data = new ValidatedDataBag($data);
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
     * Returns all the query, request, and file data in this request
     *
     * @return array
     */
    final public function all(): array
    {
        $input = $this->source->query->all() + $this->source->request->all();

        return array_replace_recursive($input, $this->source->files->all());
    }

    /**
     * Returns only the validated data
     */
    final public function data(): ValidatedDataBag
    {
        return $this->data;
    }

    /**
     * Returns true if the key exists in: query, request, or file data
     */
    final public function has(string $key): bool
    {
        return
            arrayHas($this->source->query->all(), [$key])
            ||
            arrayHas($this->source->request->all(), [$key])
            ||
            arrayHas($this->source->files->all(), [$key])
        ;
    }

    /**
     * Gets the key value from: query, request, or file data
     */
    final public function get(string $key, mixed $default = null): mixed
    {
        return arrayGet($this->all(), $key, $default);
    }

    /**
     * Get only the specified keys in a new ParameterBag from query, request, or file data
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
     * Get all fields except those specified from query, request, or file data
     */
    final public function without(string ...$key): ParameterBag
    {
        $data = $this->all();

        forget($data, $key);

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
    public function nullOrValue(string $bag, array $fields, string $class = null, bool $subNull = false): mixed
    {
        if (!$this->$bag instanceof ParameterBag) {
            throw new InvalidArgumentException(sprintf('Bag "%s" is not a ParameterBag instance', $bag));
        }

        return $this->doGetNullOrValue($this->$bag->all(), $fields, $class, $subNull);
    }
}
