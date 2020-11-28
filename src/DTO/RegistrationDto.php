<?php


namespace App\DTO;


use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationDto extends DtoBase
{
    /** @var string */
    private $firstName = "";
    /** @var string */
    private $lastName = "";
    /** @var string */
    private $email = "";
    /** @var string */
    private $password = "";
    /** @var bool */
    private $gdprAgreed = false;

    public function __construct(FormFactoryInterface $formFactory, Request $request)
    {
        parent::__construct($formFactory, $request);
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return RegistrationDto
     */
    public function setFirstName(string $firstName): RegistrationDto
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return RegistrationDto
     */
    public function setLastName(string $lastName): RegistrationDto
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return RegistrationDto
     */
    public function setEmail(string $email): RegistrationDto
    {
        $this->email = $email;
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
     * @return RegistrationDto
     */
    public function setPassword(string $password): RegistrationDto
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return bool
     */
    public function isGdprAgreed(): bool
    {
        return $this->gdprAgreed;
    }

    /**
     * @param bool $gdprAgreed
     * @return RegistrationDto
     */
    public function setGdprAgreed(bool $gdprAgreed): RegistrationDto
    {
        $this->gdprAgreed = $gdprAgreed;
        return $this;
    }

    public function getForm(): FormInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class, $this);
        $builder
            ->add("firstName", TextType::class, ["required" => true])
            ->add("lastName", TextType::class, ["required" => true])
            ->add("email", EmailType::class, ["required" => true])
            ->add("firstName", TextType::class, ["required" => true])
            ->add("password", RepeatedType::class, [
                "type" => PasswordType::class,
                "invalid_message" => "The passwords must match!",
                "required" => true,
                "first_options" => ["label" => "Password"],
                "second_options" => ["label" => "Confirm Password"],
                "constraints" => [
                    new NotBlank(["message" => "Password cannot be empty!"]),
                    new Length([
                        "min" => 6,
                        "minMessage" => "The password must be at least {{ limit }} characters",
                        "max" => 4096
                    ])
                ]
            ])
            ->add("gdprAgreed", CheckboxType::class, ["constraints" => [
                new IsTrue(["message" => "You must agree to the GDPR rules!"])
            ]])
            ->add("Register", SubmitType::class);

        return $builder->getForm();
    }
}