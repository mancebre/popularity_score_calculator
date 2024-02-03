<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class ExceptionListener
{
    public function __construct(
        private LoggerInterface $loggerInterface
    ) {
    }

    /**
     * Handle an exception and set a JSON response.
     *
     * @param ExceptionEvent $event The exception event
     * @throws \Exception description of exception
     * @return JsonResponse
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $this->loggerInterface->error('Exception of type ' . get_class($exception) . ' occurred: ' . $exception->getMessage());

        $errorMessage = 'An unexpected error occurred.';

        if ($exception instanceof \Exception) {
            $errorMessage = $exception->getMessage();
        }

        $response = new JsonResponse(['error' => $errorMessage], 500);
        $event->setResponse($response);
    }

}