<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Http;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\UserFormRequest;
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

    public function testOnly()
    {
        $form = new UserFormRequest($r = Request::create('https://www.example.org/path/to/resource?min=0&max=100&foo=bar'));

        $this->assertInstanceOf(ParameterBag::class, $form->only('foo'));
        $this->assertCount(1, $form->only('foo'));
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
}
