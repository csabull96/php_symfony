<?php


namespace App\DTO;


use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ForumDto extends DtoBase
{
    /** @var string */
    private $textContent = "";

    /** @var string */
    private $category;

    /**
     * @return string
     */
    public function getTextContent(): string
    {
        return $this->textContent;
    }

    /**
     * @param string $textContent
     * @return ForumDto
     */
    public function setTextContent(string $textContent): ForumDto
    {
        $this->textContent = $textContent;
        return $this;
    }

    public function __construct(FormFactoryInterface $formFactory, Request $request, string $category)
    {
        parent::__construct($formFactory, $request);
        $this->category = $category;
    }

    public function getForm(): FormInterface
    {
        $builder = $this->formFactory->createBuilder(FormType::class, $this);
        $builder
            ->add("textContent", TextType::class,
                ["required" => true, "label" => "Add {$this->category}"])
            ->add("save", SubmitType::class);
        return $builder->getForm();
    }
}