<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    private $userRepository;
    private $validator;
    private $passwordHasher;
    private $mailer;

    public function __construct(UserRepository $userRepository, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, Mailer $mailer)
    {
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
        $this->mailer = $mailer;
    }

     /**
     * @Route("/api/me", methods={"GET"})
     */
    public function getCurrentUser(Request $request): JsonResponse
    {
        $user = $this->getUser(); // Obiekt zalogowanego użytkownika

        if (!$user || !$user instanceof UserInterface) {
            return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(), // email jako identyfikator
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'isActive' => $user->isActive(),
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/api/users", methods={"GET"})
     */
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $sortBy = $request->query->get('sort_by', 'id');
        $sortOrder = $request->query->get('sort_order', 'asc');

        $users = $this->userRepository->findBy([], [$sortBy => $sortOrder], $limit, ($page - 1) * $limit);
        $totalUsers = $this->userRepository->count([]);

        $data = array_map(fn(User $user) => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'isActive' => $user->isActive(),
        ], $users);

        return new JsonResponse([
            'total' => $totalUsers,
            'page' => $page,
            'limit' => $limit,
            'users' => $data
        ]);
    }

    /**
     * @Route("/api/users/{id}", methods={"GET"})
     */
    public function get(User $user): JsonResponse
    {
        if (!$user->isActive()) {
            return new JsonResponse(['error' => 'User not active'], Response::HTTP_FORBIDDEN);
        }

        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'isActive' => $user->isActive(),
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/api/users", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setIsActive(true);

        // Walidacja danych
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        // Zapisanie użytkownika
        $this->userRepository->save($user);

        return new JsonResponse(['status' => 'User created'], Response::HTTP_CREATED);
    }


    /**
     * @Route("/api/users/{id}", methods={"PUT"})
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user->setEmail($data['email'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName']);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->userRepository->save($user);

        return new JsonResponse(['status' => 'User updated']);
    }

    /**
     * @Route("/api/users/{id}", methods={"DELETE"})
     */
    public function delete(User $user): JsonResponse
    {
        $this->userRepository->remove($user);

        return new JsonResponse(['status' => 'User deleted'], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/users/{id}/block", methods={"POST"})
     */
    public function blockUser(User $user): JsonResponse
    {
        $user->setIsActive(false);
        $this->userRepository->save($user);

        return new JsonResponse(['status' => 'User blocked']);
    }

    /**
     * @Route("/api/users/{id}/unblock", methods={"POST"})
     */
    public function unblockUser(User $user): JsonResponse
    {
        $user->setIsActive(true);
        $this->userRepository->save($user);

        return new JsonResponse(['status' => 'User unblocked']);
    }

    /**
     * @Route("/api/users/{id}/reset-password", methods={"POST"})
     */
    public function resetPassword(User $user): JsonResponse
    {
        $newPassword = bin2hex(random_bytes(8)); // Wygenerowanie nowego hasła
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        // Wyślij e-mail z nowym hasłem (przykład z użyciem Symfony Mailer)
        $email = (new Email())
            ->from('no-reply@yourapp.com')
            ->to($user->getEmail())
            ->subject('Password Reset')
            ->text('Your new password is: ' . $newPassword);

        $this->mailer->send($email);

        $this->userRepository->save($user);

        return new JsonResponse(['status' => 'Password reset successfully']);
    }
}
