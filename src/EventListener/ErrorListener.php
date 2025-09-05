<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ErrorListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        // Suppress deprecated warnings in production
        $exception = $event->getThrowable();
        
        // If it's a deprecated warning, don't show it
        if (strpos($exception->getMessage(), 'deprecated') !== false ||
            strpos($exception->getMessage(), 'Deprecated') !== false) {
            
            // Create a simple response without the deprecated warning
            $response = new Response('', Response::HTTP_OK);
            $event->setResponse($response);
            return;
        }
    }
}