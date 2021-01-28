<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours;

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
