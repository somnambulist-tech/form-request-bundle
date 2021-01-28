<?php declare(strict_types=1);

namespace Somnambulist\Bundles\FormRequestBundle\EventSubscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AccessDeniedExceptionSubscriber
 *
 * @package    Somnambulist\Bundles\FormRequestBundle\EventSubscribers
 * @subpackage Somnambulist\Bundles\FormRequestBundle\EventSubscribers\AccessDeniedExceptionSubscriber
 */
class AccessDeniedExceptionSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException($event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $exception = $event->getThrowable();

        if (!$exception instanceof AccessDeniedHttpException) {
            return;
        }

        $event->setResponse($this->prepareResponse($exception));
    }

    private function prepareResponse(AccessDeniedHttpException $exception): JsonResponse
    {
        return new JsonResponse(['message' => $exception->getMessage()], JsonResponse::HTTP_FORBIDDEN);
    }
}
