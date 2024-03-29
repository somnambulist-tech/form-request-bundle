<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Services;

use Somnambulist\Bundles\FormRequestBundle\Exceptions\FormValidationException;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Somnambulist\Components\Validation\Factory;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use function is_a;
use function is_null;

class FormRequestArgumentResolver implements ValueResolverInterface
{
    public function __construct(private Factory $factory, private ?Security $security)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_a($argument->getType(), FormRequest::class, true);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!is_a($argument->getType(), FormRequest::class, true)) {
            return [];
        }

        $form = $this->createFormInstance($request, $argument);

        if (!is_null($this->security) && !$form->authorize($this->security)) {
            throw new AccessDeniedHttpException(sprintf('Access to "%s" denied for current user', $argument->getType()));
        }

        $validation = $this->factory->validate($form->all(), $form->rules());

        FormRequest::appendValidationData($form, $validation->getValidData());

        if ($validation->fails()) {
            throw new FormValidationException($validation->errors());
        }

        yield $form;
    }

    private function createFormInstance(Request $current, ArgumentMetadata $argument): FormRequest
    {
        $class = $argument->getType();

        return new $class($current);
    }
}
