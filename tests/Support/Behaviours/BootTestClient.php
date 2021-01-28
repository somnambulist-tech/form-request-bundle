<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours;

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

    /**
     * @var AbstractBrowser
     */
    protected $__kernelBrowserClient;

    /**
     * {@inheritDoc}
     */
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
