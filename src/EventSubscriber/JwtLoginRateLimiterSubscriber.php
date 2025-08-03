<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'kernel.request', priority: 10)]
class JwtLoginRateLimiterListener
{
    private RateLimiterFactory $limiter;

    public function __construct(RateLimiterFactory $jwtLoginLimiter)
    {
        $this->limiter = $jwtLoginLimiter;
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->getPathInfo() !== '/api/login_check' || !$request->isMethod('POST')) {
            return;
        }

        $ip = $request->getClientIp();
        $limiter = $this->limiter->create($ip);
        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();
            $response = new JsonResponse([
                'error' => 'Too many login attempts. Try again in ' . $retryAfter . ' seconds.'
            ], 429);
            $event->setResponse($response);
        }
    }
}
