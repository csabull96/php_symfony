<?php


namespace App\DTO;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class LoginDto extends DtoBase
{
    /** @var string  */
    private $userName = "";

    /** @var string  */
    private $password = "";

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return LoginDto
     */
    public function setUserName(string $userName): LoginDto
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return LoginDto
     */
    public function setPassword(string $password): LoginDto
    {
        $this->password = $password;
        return $this;
    }

    public function __construct(FormFactoryInterface $formFactory, Request $request)
    {
        parent::__construct($formFactory, $request);
    }

    public function getForm(): FormInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class, $this);
        $builder
            ->add("userName", TextType::class)
            ->add("password", PasswordType::class)
            ->add("login", SubmitType::class);
        return $builder->getForm();
    }
}