<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\Services;

use Rakit\Validation\Validator;
use Somnambulist\Bundles\FormRequestBundle\Exceptions\FormValidationException;
use Somnambulist\Bundles\FormRequestBundle\Http\FormRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;
use function is_a;

/**
 * Class ControllerArgumentResolver
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\Services
 * @subpackage Somnambulist\Bundles\FormRequestBundle\Services\ControllerArgumentResolver
 */
class ControllerArgumentResolver implements ArgumentValueResolverInterface
{

    private Validator $validator;
    private ?Security $security;

    public function __construct(Validator $validator, ?Security $security)
    {
        $this->validator = $validator;
        $this->security  = $security;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return is_a($argument->getType(), FormRequest::class, true);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $form = $this->createFormInstance($request, $argument);

        if (!is_null($this->security) && !$form->authorize($this->security)) {
            throw new AccessDeniedHttpException(sprintf('Access to "%s" denied for current user', $argument->getType()));
        }

        $validation = $this->validator->validate($form->all(), $form->rules());

        if ($validation->fails()) {
            throw new FormValidationException($validation->errors());
        }

        FormRequest::appendValidationData($form, $validation->getValidatedData());

        yield $form;
    }

    private function createFormInstance(Request $current, ArgumentMetadata $argument): FormRequest
    {
        $class = $argument->getType();

        return new $class($current);
    }
}
