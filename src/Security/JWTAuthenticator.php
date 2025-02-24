<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator as BaseJWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JWTAuthenticator extends BaseJWTAuthenticator
{
    private $userProvider;

    public function __construct(
        CustomUserProvider $userProvider,
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $eventDispatcherInterface,
        TokenExtractorInterface $tokenExtractorInterface
    ) {
        // Pass the JWTTokenManagerInterface to the parent constructor
        parent::__construct($jwtManager, $eventDispatcherInterface, $tokenExtractorInterface, $userProvider);
        $this->userProvider = $userProvider;
    }

    public function getCredentials(Request $request)
    {
        // Use the parent's getCredentials method to extract the token
        $token = parent::getCredentials($request);

        if (!$token) {
            throw new CustomUserMessageAuthenticationException('No JWT token found in request.');
        }

        return $token;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // Extract the email from the token and load the user
        $email = $this->extractEmailFromToken($credentials); // Assuming the token stores the email
        return $this->userProvider->loadUserByIdentifier($email);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // You can leave this empty because the token itself is already validated
        return true;
    }

    private function extractEmailFromToken($credentials)
    {
        // Extract the email from the JWT token payload.
        // The payload typically contains user data.
        $data = $this->parseJwt($credentials); // Parsing the JWT to extract user info
        return $data['email']; // Assuming the token contains an 'email' field
    }

    private function parseJwt($jwt)
    {
        // Decode the JWT token (this is a simple example, you'd want to use a proper JWT library)
        list($header, $payload, $signature) = explode('.', $jwt);
        $decodedPayload = base64_decode($payload);
        return json_decode($decodedPayload, true);
    }
}
