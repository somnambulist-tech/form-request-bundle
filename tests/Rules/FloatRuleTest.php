<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Rules;

use Rakit\Validation\Validator;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\BootKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class FloatRuleTest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Rules\FloatRuleTest
 */
class FloatRuleTest extends KernelTestCase
{

    use BootKernel;

    public function testFloat()
    {
        /** @var Validator $validator */
        $validator = self::getContainer()->get(Validator::class);

        $res = $validator->validate(
            [
                'foo' => 'bar',
                'bar' => 1.121,
                'baz' => '1.121',
            ],
            [
                'foo' => 'float',
                'bar' => 'float',
                'baz' => 'float',
            ],
        );

        $this->assertFalse($res->passes());

        $this->assertArrayNotHasKey('bar', $res->errors()->toArray());
        $this->assertArrayNotHasKey('baz', $res->errors()->toArray());
    }

    public function testFloatWithLongFloats()
    {
        $validator = self::getContainer()->get(Validator::class);

        $res = $validator->validate(
            [
                'long'  => '3.1415926535897932384626433832795',
                'long2' => 3.1415926535897932384626433832795,
            ],
            [
                'long'  => 'float',
                'long2' => 'float',
            ],
        );

        $this->assertTrue($res->passes());
    }
}
