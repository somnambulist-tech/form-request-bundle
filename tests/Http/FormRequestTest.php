<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Http;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\UserFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\ExternalIdentity;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\ValueObjectWithNulls;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FormRequestTest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Http
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Http\FormRequestTest
 */
class FormRequestTest extends TestCase
{

    public function testMagicPassThrough()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertSame($r->query, $form->query);
        $this->assertSame($r->request, $form->request);
        $this->assertSame($r->attributes, $form->attributes);
        $this->assertSame($r->files, $form->files);
        $this->assertSame($r->server, $form->server);
    }

    public function testMagicContentPassThrough()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar', content: 'this=that'));

        $this->assertSame($r->getContent(), $form->content);
    }

    public function testSource()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertSame($r, $form->source());
    }

    public function testHas()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertTrue($form->has('foo'));
        $this->assertFalse($form->has('this'));
    }

    public function testGet()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertEquals('bar', $form->get('foo'));
        $this->assertEquals('0', $form->get('min'));
        $this->assertEquals(120, $form->get('this', 120));
        $this->assertEquals([], $form->get('this', []));
    }

    public function testReturningQueryArrayElements()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/?param[]=1&param[]=100&param[]=bar'));

        $this->assertEquals(['1', '100', 'bar'], $form->get('param'));

        $this->assertEquals([], $form->get('bar', []));
    }

    public function testReturningRequestArrayElements()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/', 'POST', ['param' => ['1', '100', 'bar']]));

        $this->assertEquals(['1', '100', 'bar'], $form->get('param'));
        $this->assertEquals([], $form->get('params', []));
    }

    public function testOnly()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertInstanceOf(ParameterBag::class, $form->only('foo'));
        $this->assertCount(1, $form->only('foo'));
    }

    public function testWithout()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertInstanceOf(ParameterBag::class, $form->without('foo', 'min'));
        $this->assertCount(1, $p = $form->without('foo', 'min'));
        $this->assertTrue($p->has('max'));
    }

    public function testAll()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar', parameters: [
            'this' => 'that', '_route' => 'some.route',
        ]));

        $this->assertIsArray($a = $form->all());
        $this->assertCount(5, $a);
        $this->assertArrayHasKey('this', $a);
        $this->assertArrayHasKey('_route', $a);
    }

    public function testData()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org'));

        FormRequest::appendValidationData($form, $e = ['this' => 'that']);

        $this->assertEquals($e, $form->data()->all());
    }

    public function testMagicFailsForUnsupportedProperties()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org'));

        $this->expectException(InvalidArgumentException::class);

        $form->bob;
    }

    public function testNullOrValue()
    {
        $form = new UserFormRequest(Request::create('/', 'GET', ['provider' => 'bob', 'identity' => 'foo']));
        $var = $form->nullOrValue('query', ['provider']);

        $this->assertEquals('bob', $var);
    }

    public function testNullOrValueFailsOnInvalidBag()
    {
        $form = new UserFormRequest(Request::create('/', 'GET', ['provider' => 'bob', 'identity' => 'foo']));

        $this->expectException(InvalidArgumentException::class);

        $form->nullOrValue('content', ['provider']);
    }

    public function testNullOrValueReturnsAllFields()
    {
        $form = new UserFormRequest(Request::create('/', 'GET', ['provider' => 'bob', 'identity' => 'foo']));

        $var = $form->nullOrValue('query', ['provider', 'identity']);

        $this->assertIsArray($var);
        $this->assertEquals(['provider' => 'bob', 'identity' => 'foo'], $var);
    }

    public function testNullOrValueReturnsNullIfMissingField()
    {
        $form = new UserFormRequest(Request::create('/', 'GET', ['provider' => 'bob']));

        $var = $form->nullOrValue('query', ['provider', 'identity']);

        $this->assertNull($var);
    }

    public function testNullOrValueIntoClass()
    {
        $form = new UserFormRequest(Request::create('/', 'GET', ['provider' => 'bob', 'identity' => 'foo']));

        $var = $form->nullOrValue('query', ['provider', 'identity'], ExternalIdentity::class);

        $this->assertInstanceOf(ExternalIdentity::class, $var);
        $this->assertEquals('bob', $var->provider());
        $this->assertEquals('foo', $var->identity());
    }

    public function testNullOrValueCanHydrateOptionalParametersWithNull()
    {
        $form = new UserFormRequest(Request::create('/', 'GET', ['name' => 'bob', 'phone' => '12345678990']));

        $var = $form->nullOrValue('query', ['name', 'email', 'phone'], ValueObjectWithNulls::class, true);

        $this->assertInstanceOf(ValueObjectWithNulls::class, $var);
        $this->assertEquals('bob', $var->getName());
        $this->assertNull($var->getEmail());
        $this->assertEquals('12345678990', $var->getPhone());
    }

    public function testNullOrValueReturnsNullInArrayOfFields()
    {
        $form = new UserFormRequest(Request::create('/', 'GET', ['name' => 'bob', 'phone' => '12345678990']));

        $var = $form->nullOrValue('query', ['name', 'email', 'phone'], subNull: true);

        $this->assertIsArray($var);
        $this->assertEquals('bob', $var['name']);
        $this->assertNull($var['email']);
        $this->assertEquals('12345678990', $var['phone']);
    }
}
