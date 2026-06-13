<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours;

use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Kernel;
use function method_exists;

/**
 * Trait BootKernel
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\BootKernel
 *
 * @method void setKernelClass()
 * @method void setUpTests()
 */
trait BootKernel
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        if (method_exists($this, 'setKernelClass')) {
            self::setKernelClass();
        }

        self::bootKernel();

        if (method_exists($this, 'setUpTests')) {
            $this->setUpTests();
        }
    }
}
