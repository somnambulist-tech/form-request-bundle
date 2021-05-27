<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Rules;

use Rakit\Validation\Validator;
use Ramsey\Uuid\Uuid;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\BootKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class UuidRuleTest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Rules
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Rules\UuidRuleTest
 */
class UuidRuleTest extends KernelTestCase
{

    use BootKernel;

    public function testUuid()
    {
        /** @var Validator $validator */
        $validator = self::$container->get(Validator::class);

        $res = $validator->validate(
            [
                'foo' => '86e51afc-c626-4b28-999f-560a297d019f',
            ],
            [
                'foo' => 'uuid',
            ],
        );

        $this->assertTrue($res->passes());
    }

    public function testFailsOnNullOrNotString()
    {
        /** @var Validator $validator */
        $validator = self::$container->get(Validator::class);

        $res = $validator->validate(
            [
                'foo' => null,
            ],
            [
                'foo' => 'uuid',
            ],
        );

        $this->assertTrue($res->fails());

        $res = $validator->validate(
            [
                'bar' => '',
            ],
            [
                'bar' => 'uuid',
            ],
        );

        $this->assertTrue($res->fails());
    }

    public function testFailsForNIL()
    {
        /** @var Validator $validator */
        $validator = self::$container->get(Validator::class);

        $res = $validator->validate(
            [
                'foo' => Uuid::NIL,
            ],
            [
                'foo' => 'uuid',
            ],
        );

        $this->assertTrue($res->fails());
    }
}
