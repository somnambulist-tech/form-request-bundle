<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Http;

use BadMethodCallException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Somnambulist\Bundles\FormRequestBundle\Http\ValidatedDataBag;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\UserFormRequest;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\Address;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\ExternalIdentity;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\ValueObjectWithNulls;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

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

        $this->assertSame($r->query, $form->query());
        $this->assertSame($r->request, $form->request());
        $this->assertSame($r->attributes, $form->attributes());
        $this->assertSame($r->files, $form->files());
        $this->assertSame($r->server, $form->server());
    }

    public function testMagicContentPassThrough()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar', content: 'this=that'));

        $this->assertSame($r->getContent(), $form->content);
        $this->assertSame($r->getContent(), $form->content());
    }

    public function testSource()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertSame($r, $form->source());
    }

    public function testHas()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertTrue($form->has('foo'));
        $this->assertFalse($form->has('this'));
    }

    public function testHasWithDotNotation()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/path/to/resource?address[line_1]=foo&address[city]=city'));

        $this->assertTrue($form->has('address.line_1'));
        $this->assertFalse($form->has('address.line_2'));
    }

    public function testGet()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertEquals('bar', $form->get('foo'));
        $this->assertEquals('0', $form->get('min'));
        $this->assertEquals(120, $form->get('this', 120));
        $this->assertEquals([], $form->get('this', []));
    }

    public function testGetWithDotNotation()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/path/to/resource?address[line_1]=foo&address[city]=city'));

        $this->assertEquals('foo', $form->get('address.line_1'));
        $this->assertEquals('city', $form->get('address.city'));
        $this->assertNull($form->get('address.line_2'));
    }

    public function testGetWithDotNotationWithCalculatedDefault()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/path/to/resource?address[line_1]=foo&address[city]=city'));

        $this->assertEquals('no line_2', $form->get('address.line_2', function () { return 'no line_2'; }));
    }

    public function testReturningQueryArrayElements()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/?param[]=1&param[]=100&param[]=bar'));

        $this->assertEquals(['1', '100', 'bar'], $form->get('param'));

        $this->assertEquals([], $form->get('bar', []));
    }

    public function testReturningRequestArrayElements()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/', 'POST', ['param' => ['1', '100', 'bar']]));

        $this->assertEquals(['1', '100', 'bar'], $form->get('param'));
        $this->assertEquals([], $form->get('params', []));
    }

    public function testOnly()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertInstanceOf(ParameterBag::class, $form->only('foo'));
        $this->assertCount(1, $form->only('foo'));
    }

    public function testOnlyWithDotNotation()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/path/to/resource?address[line_1]=foo&address[city]=city'));

        $this->assertInstanceOf(ParameterBag::class, $form->only('address.line_1'));
        $this->assertCount(1, $form->only('address.line_1'));
    }

    public function testWithout()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertInstanceOf(ParameterBag::class, $form->without('foo', 'min'));
        $this->assertCount(1, $p = $form->without('foo', 'min'));
        $this->assertTrue($p->has('max'));
    }

    public function testWithoutWithDotNotation()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/path/to/resource?address[line_1]=foo&address[city]=city'));

        $this->assertInstanceOf(ParameterBag::class, $p = $form->without('address.line_1'));
        $this->assertTrue($p->has('address'));

        $this->assertTrue(isset($p->get('address')['city']));
        $this->assertFalse(isset($p->get('address')['line_1']));
    }

    public function testAll()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar', parameters: [
            'this' => 'that', '_route' => 'some.route',
        ]));

        $this->assertIsArray($a = $form->all());
        $this->assertCount(5, $a);
        $this->assertArrayHasKey('this', $a);
        $this->assertArrayHasKey('_route', $a);
    }

    public function testData()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org'));

        FormRequest::appendValidationData($form, $e = ['this' => 'that']);

        $this->assertInstanceOf(ValidatedDataBag::class, $form->data());
        $this->assertEquals($e, $form->data()->all());
    }

    public function testMagicFailsForUnsupportedProperties()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org'));

        $this->expectException(InvalidArgumentException::class);

        $form->bob;
    }

    public function testMagicFailsForUnsupportedMethods()
    {
        $form = new UserFormRequest(Request::create('https://www.example.org'));

        $this->expectException(BadMethodCallException::class);

        $form->bob();
    }

    public function testNullOrValue()
    {
        $form = new UserFormRequest(Request::create('/', 'GET', ['provider' => 'bob', 'identity' => 'foo']));
        $var  = $form->nullOrValue('query', ['provider']);

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

    public function testAccessNestedAssocDataWithNullOrValue()
    {
        $form = new UserFormRequest(Request::create('/', 'GET', [
            'address' => [
                'line_1'   => '123 street',
                'city'     => 'Some City',
                'state'    => 'State',
                'postcode' => 'H0H0H0',
            ],
            'phone'   => '12345678990',
        ]));

        $var = $form->nullOrValue('query', ['address.line_1', 'address.line_2', 'address.city', 'address.state', 'address.postcode'], subNull: true);

        $this->assertIsArray($var);
        $this->assertArrayHasKey('address.line_1', $var);
        $this->assertEquals('123 street', $var['address.line_1']);
        $this->assertArrayHasKey('address.line_2', $var);
        $this->assertNull($var['address.line_2']);
        $this->assertArrayHasKey('address.city', $var);
        $this->assertEquals('Some City', $var['address.city']);
        $this->assertArrayHasKey('address.state', $var);
        $this->assertEquals('State', $var['address.state']);
        $this->assertArrayHasKey('address.postcode', $var);
        $this->assertEquals('H0H0H0', $var['address.postcode']);
    }

    public function testAccessNestedAssocDataToObjectWithNullOrValue()
    {
        $form = new UserFormRequest(Request::create('/', 'GET', [
            'address' => [
                'line_1'   => '123 street',
                'city'     => 'Some City',
                'state'    => 'State',
                'postcode' => 'H0H0H0',
            ],
            'phone'   => '12345678990',
        ]));

        $var = $form->nullOrValue('query', ['address.line_1', 'address.line_2', 'address.city', 'address.state', 'address.postcode'], Address::class, subNull: true);

        $this->assertInstanceOf(Address::class, $var);
        $this->assertEquals('123 street', $var->line1);
        $this->assertNull($var->line2);
        $this->assertEquals('Some City', $var->city);
        $this->assertEquals('State', $var->state);
        $this->assertEquals('H0H0H0', $var->postcode);
    }
}
