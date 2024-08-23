<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticatorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiKeyAuthenticator implements AuthenticatorInterface
{
    public function authenticate(Request $request): Passport
    {
        $apiKey = $request->headers->get('Authorization');

        if (!$apiKey) {
            throw new AuthenticationException('No API key provided');
        }

        // Validate and extract user and roles from the API key
        // For example, use JWT or similar token system

        return new Passport(
            new UserBadge($apiKey),
            new CsrfTokenBadge('authenticate')
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Authentication failed', Response::HTTP_FORBIDDEN);
    }
}
