<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours;

use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Kernel;
use Symfony\Component\BrowserKit\AbstractBrowser;
use function method_exists;

/**
 * Trait BootTestClient
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\BootTestClient
 *
 * @method void setKernelClass()
 * @method void setUpTests()
 */
trait BootTestClient
{
    protected ?AbstractBrowser $__kernelBrowserClient = null;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        if (method_exists($this, 'setKernelClass')) {
            self::setKernelClass();
        }

        $this->__kernelBrowserClient = self::createClient();

        if (method_exists($this, 'setUpTests')) {
            $this->setUpTests();
        }
    }
}
