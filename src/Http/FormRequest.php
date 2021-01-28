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
 * @property ParameterBag    $request
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
        $this->source          = $request;
        $this->source->request = new ParameterBag($request->request->all());
        $this->data            = new ParameterBag();
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
     * @internal
     */
    public static function appendValidationData(FormRequest $form, array $data): void
    {
        $form->data = new ParameterBag($data);
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
    public function all(): array
    {
        $input = $this->source->request->all() + $this->source->query->all();

        return array_replace_recursive($input, $this->source->files->all());
    }

    /**
     * Returns only the validated data
     *
     * @return ParameterBag
     */
    public function data(): ParameterBag
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
    public function has(string $key): bool
    {
        return $this->source->attributes->has($key) || $this->source->query->has($key) || $this->source->request->has($key);
    }

    /**
     * Gets the key value from: attributes, query or request data
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->source->get($key, $default);
    }

    /**
     * Get only the specified keys in a new ParameterBag from attributes, query or request
     *
     * @param string ...$key
     *
     * @return ParameterBag
     */
    public function only(string ...$key): ParameterBag
    {
        $bag = new ParameterBag();

        foreach ($key as $test) {
            $bag->set($test, $this->get($test));
        }

        return $bag;
    }

    /**
     * The original Symfony Request
     *
     * @return Request
     */
    public function source(): Request
    {
        return $this->source;
    }
}
