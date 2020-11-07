<?php


namespace App\DTO;


use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class RegisterDto extends DtoBase
{
    /** @var string */
    private $username = "";

    /** @var string */
    private $password = "";

    /**
     * RegisterDto constructor.
     * @param FormFactoryInterface $formFactory
     * @param Request $request
     */
    public function __construct(FormFactoryInterface $formFactory, Request $request)
    {
        parent::__construct($formFactory, $request);
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return RegisterDto
     */
    public function setUsername(string $username): RegisterDto
    {
        $this->username = $username;
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
     * @return RegisterDto
     */
    public function setPassword(string $password): RegisterDto
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class, $this);
        $builder
            ->add("username", TextType::class, [
                'required' => true])
            ->add("password", RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => "The provided passwords don't match.",
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password']])
            ->add("register", SubmitType::class);
        return $builder->getForm();
    }
}