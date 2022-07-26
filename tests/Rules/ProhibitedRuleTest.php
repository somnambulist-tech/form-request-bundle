<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Rules;

use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\BootKernel;
use Somnambulist\Components\Validation\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProhibitedRuleTest extends KernelTestCase
{

    use BootKernel;

    public function testProhibited()
    {
        $validator = self::getContainer()->get(Factory::class);

        $res = $validator->validate(
            [
                'foo' => 'bar',
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited',
            ],
        );

        $this->assertFalse($res->passes());
        $this->assertEquals('bar is not allowed', $res->errors()->get('bar')['prohibited']);
    }

    public function testProhibitedIf()
    {
        $validator = self::getContainer()->get(Factory::class);

        $res = $validator->validate(
            [
                'foo' => 'bar',
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited_if:foo,bar',
            ],
        );

        $this->assertFalse($res->passes());
        $this->assertEquals('bar is not allowed if foo has value(s) "bar"', $res->errors()->get('bar')['prohibited_if']);

        $res = $validator->validate(
            [
                'foo' => 'baz',
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited_if:foo,bar',
            ],
        );

        $this->assertTrue($res->passes());

        $res = $validator->validate(
            [
                'foo' => true,
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited_if:foo,true',
            ],
        );

        $this->assertFalse($res->passes());
        $this->assertEquals('bar is not allowed if foo has value(s) "true"', $res->errors()->get('bar')['prohibited_if']);

        $res = $validator->validate(
            [
                'foo' => false,
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited_if:foo,false',
            ],
        );

        $this->assertFalse($res->passes());
        $this->assertEquals('bar is not allowed if foo has value(s) "false"', $res->errors()->get('bar')['prohibited_if']);

        $res = $validator->validate(
            [
                'foo' => 'baz',
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited_if:foo,bar,baz,foo',
            ],
        );

        $this->assertFalse($res->passes());
        $this->assertEquals('bar is not allowed if foo has value(s) "bar", "baz", "foo"', $res->errors()->get('bar')['prohibited_if']);
    }

    public function testProhibitedUnless()
    {
        $validator = self::getContainer()->get(Factory::class);

        $res = $validator->validate(
            [
                'foo' => 'bar',
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited_unless:foo,bar',
            ],
        );

        $this->assertTrue($res->passes());

        $res = $validator->validate(
            [
                'foo' => 'baz',
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited_unless:foo,bar',
            ],
        );

        $this->assertFalse($res->passes());
        $this->assertEquals('bar is not allowed if foo does not have value(s) "bar"', $res->errors()->get('bar')['prohibited_unless']);

        $res = $validator->validate(
            [
                'foo' => true,
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited_unless:foo,true',
            ],
        );

        $this->assertTrue($res->passes());

        $res = $validator->validate(
            [
                'foo' => true,
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited_unless:foo,false',
            ],
        );

        $this->assertFalse($res->passes());
        $this->assertEquals('bar is not allowed if foo does not have value(s) "false"', $res->errors()->get('bar')['prohibited_unless']);

        $res = $validator->validate(
            [
                'foo' => 'bob',
                'bar' => 'this',
            ],
            [
                'bar' => 'prohibited_unless:foo,bar,baz,foo',
            ],
        );

        $this->assertFalse($res->passes());
        $this->assertEquals('bar is not allowed if foo does not have value(s) "bar", "baz", "foo"', $res->errors()->get('bar')['prohibited_unless']);
    }
}
