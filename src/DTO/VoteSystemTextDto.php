<?php


namespace App\DTO;


use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class VoteSystemTextDto extends DtoBase
{
    /** @var string */
    private $text = "";

    public function __construct(FormFactoryInterface $formFactory, Request $request)
    {
        parent::__construct($formFactory, $request);
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return VoteSystemTextDto
     */
    public function setText(string $text): VoteSystemTextDto
    {
        $this->text = $text;
        return $this;
    }

    public function getForm(): FormInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class, $this)
            ->add("text", TextareaType::class)
            ->add("submit", SubmitType::class);

        return $builder->getForm();
    }
}