<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\EventSubscribers;

use Somnambulist\Bundles\FormRequestBundle\Exceptions\FormValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelEvents;

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
            KernelEvents::EXCEPTION => ['onKernelException']
        ];
    }

    public function onKernelException($event): void
    {
        if (!$event->isMasterRequest()) {
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
            'errors'  => $exception->getErrors()->toArray(),
        ], JsonResponse::HTTP_BAD_REQUEST);
    }
}
