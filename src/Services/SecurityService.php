<?php


namespace App\Services;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityService
{
    /** @var EntityManagerInterface  */
    private $em;

    /** @var UserPasswordEncoderInterface  */
    private $encoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $this->em = $em;
        $this->encoder = $encoder;
    }

    public function registerUser(string $email, string $password, string $firstName, string $lastName) : void
    {
        $user = new User();
        $user->setEmail($email)
            ->setPassword($this->encoder->encodePassword($user, $password))
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRoles(["ROLE_USER"]);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function checkPassword(string $email, string $password)
    {
        $user = $this->findUserByEmail($email);
        return $this->isPasswordValid($user, $password);
    }

    public function findUserByEmail(string $email) : ?User
    {
        /** @var EntityRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        return $userRepository->findOneBy(["email" => $email]);
    }

    public function isPasswordValid(?UserInterface $user, string $password)
    {
        return $user ?
            $this->encoder->isPasswordValid($user, $password) : false;
    }
}