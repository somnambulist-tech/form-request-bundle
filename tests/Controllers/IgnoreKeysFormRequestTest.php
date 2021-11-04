<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Controllers;

use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\BootTestClient;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\MakeJsonRequestTo;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class IgnoreKeysFormRequestTest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Controllers
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Controllers\IgnoreKeysFormRequestTest
 */
class IgnoreKeysFormRequestTest extends WebTestCase
{
    use BootTestClient;
    use MakeJsonRequestTo;

    public function testCanIgnoreKeysInValidatedData()
    {
        $ret = $this->makeJsonRequestToNamedRoute('test.ignore_keys', [
            'name'     => 'bob%',
            'page'     => 2,
            'per_page' => 10,
        ]);

        $this->assertEquals(['name' => 'bob%'], $ret);

        $headers = $this->__kernelBrowserClient->getResponse()->headers;

        $this->assertEquals(2, $headers->get('X-Page'));
        $this->assertEquals(10, $headers->get('X-PerPage'));
    }
}
