<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Http;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\FormRequestBundle\Http\ValidatedDataBag;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\Address;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\ExternalIdentity;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\ValueObjectWithNulls;

/**
 * Class ValidatedDataBagTest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Http
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Http\ValidatedDataBagTest
 */
class ValidatedDataBagTest extends TestCase
{
    public function testGet()
    {
        $data = new ValidatedDataBag($a = [
            'address' => [
                'line_1'   => '123 street',
                'city'     => 'Some City',
                'state'    => 'State',
                'postcode' => 'H0H0H0',
            ],
            'phone'   => '12345678990',
        ]);

        $this->assertEquals('123 street', $data->get('address.line_1'));
        $this->assertEquals($a['address'], $data->get('address'));
    }

    public function testGetInt()
    {
        $data = new ValidatedDataBag($a = [
            'total' => '12390',
            'phone' => '12345678990',
            'num'   => '02',
        ]);

        $this->assertEquals(12390, $data->getInt('total'));
        $this->assertEquals(12345678990, $data->getInt('phone'));
        $this->assertEquals(2, $data->getInt('num'));
    }

    public function testGetIntWithDefault()
    {
        $data = new ValidatedDataBag($a = [
            'total' => '12390',
            'phone' => '12345678990',
        ]);

        $this->assertEquals(0, $data->getInt('num'));
        $this->assertEquals(10, $data->getInt('num', 10));
    }

    public function testGetFloat()
    {
        $data = new ValidatedDataBag([
            'total' => '123.78',
            'phone' => '9',
        ]);

        $this->assertIsFloat($data->getFloat('total'));
        $this->assertEquals(123.78, $data->getFloat('total'));
        $this->assertEquals(9.0, $data->getFloat('phone'));
    }

    public function testGetFloatWithDefault()
    {
        $data = new ValidatedDataBag([
            'phone' => '9',
        ]);

        $this->assertEquals(100.00, $data->getFloat('total', 100.00));
    }

    public function testHas()
    {
        $data = new ValidatedDataBag([
            'address' => [
                'line_1'   => '123 street',
                'city'     => 'Some City',
                'state'    => 'State',
                'postcode' => 'H0H0H0',
            ],
            'phone'   => '12345678990',
        ]);

        $this->assertTrue($data->has('address.line_1'));
        $this->assertFalse($data->has('foo'));
    }

    public function testFilter()
    {
        $data = new ValidatedDataBag($a = [
            'address' => [
                'line_1'   => '123 street',
                'city'     => 'Some City',
                'state'    => 'State',
                'postcode' => 'H0H0H0',
            ],
            'phone'   => '12345678990',
        ]);

        $res = $data->filter(fn($v, $k) => $k === 'phone');

        $this->assertCount(1, $res);
        $this->assertTrue($data->has('phone'));
    }

    public function testFilterNulls()
    {
        $data = new ValidatedDataBag($a = [
            'name'      => null,
            'is_active' => 0,
            'phone'     => '12345678990',
        ]);

        $res = $data->filterNulls();

        $this->assertCount(2, $res);
        $this->assertTrue($data->has('is_active'));
        $this->assertTrue($data->has('phone'));
    }

    public function testNullOrValue()
    {
        $data = new ValidatedDataBag(['provider' => 'bob', 'identity' => 'foo']);
        $var  = $data->nullOrValue(['provider']);

        $this->assertEquals('bob', $var);
    }

    public function testNullOrValueReturnsAllFields()
    {
        $data = new ValidatedDataBag(['provider' => 'bob', 'identity' => 'foo']);

        $var = $data->nullOrValue(['provider', 'identity']);

        $this->assertIsArray($var);
        $this->assertEquals(['provider' => 'bob', 'identity' => 'foo'], $var);
    }

    public function testNullOrValueReturnsNullIfMissingField()
    {
        $data = new ValidatedDataBag(['provider' => 'bob']);

        $var = $data->nullOrValue(['provider', 'identity']);

        $this->assertNull($var);
    }

    public function testNullOrValueIntoClass()
    {
        $data = new ValidatedDataBag(['provider' => 'bob', 'identity' => 'foo']);

        $var = $data->nullOrValue(['provider', 'identity'], ExternalIdentity::class);

        $this->assertInstanceOf(ExternalIdentity::class, $var);
        $this->assertEquals('bob', $var->provider());
        $this->assertEquals('foo', $var->identity());
    }

    public function testNullOrValueIntoClassReturnsNullWithEmptyValuesByDefault()
    {
        $data = new ValidatedDataBag(['provider' => 'bob', 'identity' => null]);

        $var = $data->nullOrValue(['provider', 'identity'], ExternalIdentity::class);

        $this->assertNull($var);
    }

    public function testNullOrValueCanHydrateOptionalParametersWithNull()
    {
        $data = new ValidatedDataBag(['name' => 'bob', 'phone' => '12345678990']);

        $var = $data->nullOrValue(['name', 'email', 'phone'], ValueObjectWithNulls::class, true);

        $this->assertInstanceOf(ValueObjectWithNulls::class, $var);
        $this->assertEquals('bob', $var->getName());
        $this->assertNull($var->getEmail());
        $this->assertEquals('12345678990', $var->getPhone());
    }

    public function testNullOrValueReturnsNullInArrayOfFields()
    {
        $data = new ValidatedDataBag(['name' => 'bob', 'phone' => '12345678990']);

        $var = $data->nullOrValue(['name', 'email', 'phone'], subNull: true);

        $this->assertIsArray($var);
        $this->assertEquals('bob', $var['name']);
        $this->assertNull($var['email']);
        $this->assertEquals('12345678990', $var['phone']);
    }

    public function testAccessNestedAssocDataWithNullOrValue()
    {
        $data = new ValidatedDataBag([
            'address' => [
                'line_1'   => '123 street',
                'city'     => 'Some City',
                'state'    => 'State',
                'postcode' => 'H0H0H0',
            ],
            'phone'   => '12345678990',
        ]);

        $var = $data->nullOrValue(['address.line_1', 'address.line_2', 'address.city', 'address.state', 'address.postcode'], subNull: true);

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
        $data = new ValidatedDataBag([
            'address' => [
                'line_1'   => '123 street',
                'city'     => 'Some City',
                'state'    => 'State',
                'postcode' => 'H0H0H0',
            ],
            'phone'   => '12345678990',
        ]);

        $var = $data->nullOrValue([
            'address.line_1', 'address.line_2', 'address.city', 'address.state', 'address.postcode',
        ], Address::class, subNull: true);

        $this->assertInstanceOf(Address::class, $var);
        $this->assertEquals('123 street', $var->line1);
        $this->assertNull($var->line2);
        $this->assertEquals('Some City', $var->city);
        $this->assertEquals('State', $var->state);
        $this->assertEquals('H0H0H0', $var->postcode);
    }
}
