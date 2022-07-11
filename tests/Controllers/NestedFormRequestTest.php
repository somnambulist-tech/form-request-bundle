<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Controllers;

use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\BootTestClient;
use Somnambulist\Bundles\FormRequestBundle\Tests\Support\Behaviours\MakeJsonRequestTo;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NestedFormRequestTest extends WebTestCase
{

    use BootTestClient;
    use MakeJsonRequestTo;

    public function testResolveAndValidateNestedRules()
    {
        $ret = $this->makeJsonRequestToNamedRoute(route: 'test.nested_form', method: 'POST', payload: $e = [
            'id'         => 'ccddf1a9-8854-4a9f-83f7-d7101058ce62',
            'name'       => 'test product',
            'properties' => [
                [
                    'key'   => 'xpccode',
                    'value' => 'XDD121231',
                ],
                [
                    'key'   => 'category',
                    'value' => 'testers',
                ],
            ],
        ]);

        $this->assertEquals($e, $ret);
    }

    public function testResolveAndValidateNestedRulesInvalidData()
    {
        $ret = $this->makeJsonRequestToNamedRoute(route: 'test.nested_form', method: 'POST', expectedStatusCode: 400, payload: [
            'id'         => 'ccddf1a9-8854-4a9f-83f7-d7101058ce62',
            'name'       => 'test',
            'properties' => [
                [
                    'key'   => 'xpc_code',
                    'value' => 'XDD121231',
                ],
                [
                    'key'   => 'category',
                    'value' => 'testers',
                ],
            ],
        ]);

        $this->assertArrayHasKey('message', $ret);
        $this->assertArrayHasKey('errors', $ret);
        $this->assertArrayHasKey('properties.0.key', $ret['errors']);
    }
}
