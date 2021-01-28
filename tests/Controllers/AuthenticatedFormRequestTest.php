<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Controllers;

use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\BootTestClient;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\MakeJsonRequestTo;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Models\UserProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthenticatedFormRequestTest
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Tests\Controllers
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Tests\Controllers\AuthenticatedFormRequestTest
 *
 * @group auth
 */
class AuthenticatedFormRequestTest extends WebTestCase
{

    use BootTestClient;
    use MakeJsonRequestTo;

    public function testAuthorizeFormRequestFailsWith403()
    {
        $this->fakeLogin();

        $ret = $this->makeJsonRequestToNamedRoute(route: 'test.authenticated', expectedStatusCode: Response::HTTP_FORBIDDEN, payload: [
            'id'       => '358184a5-6edf-4a69-8d19-4dae04a32c30',
            'name'     => 'a test user',
            'email'    => 'a_test_user@example.org',
            'password' => 'password',
        ]);

        $this->assertArrayHasKey('message', $ret);
        $this->assertEquals(
            'Access to "Somnambulist\Bundles\FormRequestBundle\Tests\Support\Stubs\Forms\AuthenticatedFormRequest" denied for current user',
            $ret['message']
        );
    }

    public function testAuthorizeFormRequest()
    {
        $this->fakeLogin('admin');

        $ret = $this->makeJsonRequestToNamedRoute(route: 'test.authenticated', payload: $e = [
            'id'       => '358184a5-6edf-4a69-8d19-4dae04a32c30',
            'name'     => 'a test user',
            'email'    => 'a_test_user@example.org',
            'password' => 'password',
        ]);

        $this->assertEquals($e, $ret);
    }

    private function fakeLogin(string $user = 'tester'): void
    {
        $this->__kernelBrowserClient->loginUser(
            $this->__kernelBrowserClient->getContainer()->get(UserProvider::class)->loadUserByUsername($user)
        );
    }
}
