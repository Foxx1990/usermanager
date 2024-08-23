<?php
namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InstallCommand extends Command
{
    protected static $defaultName = 'app:install';

    private $entityManager;
    private $encoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
    }

    protected function configure()
    {
        $this
            ->setDescription('Initial install of the application including admin user creation.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $admin = new User();
        $admin->setEmail('admin@yourapp.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->encoder->encodePassword($admin, 'adminpassword'));
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setIsActive(true);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $output->writeln('Admin user created.');

        return Command::SUCCESS;
    }
}
