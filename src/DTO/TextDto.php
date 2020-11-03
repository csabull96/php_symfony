<?php


namespace App\DTO;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class TextDto extends DtoBase
{
    /** @var string */
    private $textContent = "";

    /**
     * @return string
     */
    public function getTextContent(): string
    {
        return $this->textContent;
    }

    /**
     * @param string $textContent
     * @return TextDto
     */
    public function setTextContent(string $textContent): TextDto
    {
        $this->textContent = $textContent;
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
            ->add("textContent", TextareaType::class)
            ->add("saveToFile", SubmitType::class)
            ->add("saveToSession", SubmitType::class);
        return $builder->getForm();
    }
}