<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\EventSubscribers;

use Somnambulist\Bundles\FormRequestBundle\Exceptions\FormValidationException;
use Somnambulist\Components\Validation\ErrorMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use function str_replace;

/**
 * Class FormValidationExceptionSubscriber
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\EventSubscribers
 * @subpackage Somnambulist\Bundles\FormRequestBundle\EventSubscribers\FormValidationExceptionSubscriber
 */
class FormValidationExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 15],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $exception = $event->getThrowable();

        if (!$exception instanceof FormValidationException) {
            return;
        }

        $event->setResponse($this->prepareResponse($exception));
    }

    private function prepareResponse(FormValidationException $exception): JsonResponse
    {
        return new JsonResponse([
            'message' => $exception->getMessage(),
            'errors'  => $this->processMessages($exception),
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    private function processMessages(FormValidationException $exception): array
    {
        $ret = [];

        foreach ($exception->getErrors()->toArray() as $field => $errors) {
            /** @var ErrorMessage $msg */
            foreach ($errors as $msg) {
                $ret[$field][str_replace('rule.', '', $msg->key())] = $msg->toString();
            }
        }

        return $ret;
    }
}
