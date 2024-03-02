<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Tests\Services;

use PHPUnit\Framework\TestCase;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Somnambulist\Bundles\FormRequestBundle\Services\FormRequestArgumentResolver;
use Somnambulist\Components\Validation\Factory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ControllerArgumentResolverTest extends TestCase
{

    public function testResolveRequest()
    {
        $container = new Container();
        $container->set('security.token_storage', new TokenStorage());

        $resolver = new FormRequestArgumentResolver(
            new Factory(), new Security($container)
        );
        $form = $resolver
            ->resolve(
                Request::create('https://example.org/example?min=0&max=10&limit=100'),
                new ArgumentMetadata('test', TestFormRequest::class, false, false, null)
            )
            ->current()
        ;

        $this->assertInstanceOf(TestFormRequest::class, $form);
        $this->assertTrue($form->has('min'));
        $this->assertTrue($form->has('max'));
        $this->assertTrue($form->has('limit'));
        $this->assertFalse($form->has('bob'));
    }
}

class TestFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [

        ];
    }
}
