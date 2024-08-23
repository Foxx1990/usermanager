<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordResetService
{
    private $mailer;
    private $encoder;

    public function __construct(MailerInterface $mailer, UserPasswordEncoderInterface $encoder)
    {
        $this->mailer = $mailer;
        $this->encoder = $encoder;
    }

    public function resetPassword(User $user): void
    {
        $newPassword = bin2hex(random_bytes(8)); // Generate a random password
        $encodedPassword = $this->encoder->encodePassword($user, $newPassword);
        $user->setPassword($encodedPassword);

        // Send email
        $email = (new Email())
            ->from('no-reply@yourapp.com')
            ->to($user->getEmail())
            ->subject('Password Reset')
            ->text('Your new password is: ' . $newPassword);

        $this->mailer->send($email);

        // Save user with new password
        // Assuming you have a UserRepository service to save changes
        $userRepository->save($user);
    }
}
