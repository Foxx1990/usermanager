<?php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Firebase\JWT\JWT;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherInterface;

class ApiKeyService
{
    private $userRepository;
    private $encoder;
    private $secretKey;

    public function __construct(PasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function generateNewApiKey(User $user): string
    {
        $token = JWT::encode([
            'id' => $user->getId(),
            'roles' => $user->getRoles(),
            'exp' => time() + 3600 // 1 hour expiration
        ], $this->secretKey);

        // Save old token as invalidated (in a real app, you'd use a database or cache)
        // Example: $this->invalidateOldToken($user);

        return $token;
    }

    public function validateApiKey(string $token): ?User
    {
        try {
            $decoded = JWT::decode($token, $this->secretKey, ['HS256']);
            return $this->userRepository->find($decoded->id);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid API key');
        }
    }

    // Method to invalidate old API keys (depends on your implementation)
    private function invalidateOldToken(User $user): void
    {
        // Implementation to mark old tokens as invalid
    }
}
