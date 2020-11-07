<?php


namespace App\DTO;


use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ChangePasswordDto extends DtoBase
{
    /** @var string */
    private $old_password = "";

    /** @var string */
    private $new_password = "";

    public function __construct(FormFactoryInterface $formFactory, Request $request)
    {
        parent::__construct($formFactory, $request);
    }

    /**
     * @return string
     */
    public function getOldPassword(): string
    {
        return $this->old_password;
    }

    /**
     * @param string $old_password
     * @return ChangePasswordDto
     */
    public function setOldPassword(string $old_password): ChangePasswordDto
    {
        $this->old_password = $old_password;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewPassword(): string
    {
        return $this->new_password;
    }

    /**
     * @param string $new_password
     * @return ChangePasswordDto
     */
    public function setNewPassword(string $new_password): ChangePasswordDto
    {
        $this->new_password = $new_password;
        return $this;
    }

    public function getForm(): FormInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class, $this);
        $builder
            ->add("oldPassword", PasswordType::class)
            ->add("newPassword", RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'invalid_message' => "The provided passwords don't match.",
                'first_options' => ['label' => 'New Password'],
                'second_options' => ['label' => 'Confirm New Password'],

            ])
            ->add("changePassword", SubmitType::class);
        return $builder->getForm();
    }
}