<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Controllers;

use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\BootTestClient;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\MakeJsonRequestTo;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class SimpleFormRequestTest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Controllers
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Controllers\SimpleFormRequestTest
 */
class SimpleFormRequestTest extends WebTestCase
{

    use BootTestClient;
    use MakeJsonRequestTo;

    public function testResolveAndValidate()
    {
        $ret = $this->makeJsonRequestToNamedRoute(route: 'test.simple_form', payload: $e = [
            'name'     => 'Tester McTester',
            'email'    => 'test@example.org',
            'password' => 'this-is-the-password',
        ]);

        $this->assertEquals($e, $ret);
    }

    public function testResolveAndValidateWithInvalidData()
    {
        $ret = $this->makeJsonRequestToNamedRoute(route: 'test.simple_form', payload: [
            'name'  => 'bob',
            'email' => 'test',
        ], expectedStatusCode: 400);

        $this->assertArrayHasKey('message', $ret);
        $this->assertArrayHasKey('errors', $ret);
        $this->assertArrayHasKey('name', $ret['errors']);
        $this->assertArrayHasKey('email', $ret['errors']);
        $this->assertArrayHasKey('password', $ret['errors']);
        $this->assertArrayHasKey('min', $ret['errors']['name']);
        $this->assertArrayHasKey('email', $ret['errors']['email']);
        $this->assertArrayHasKey('required', $ret['errors']['password']);
    }
}
