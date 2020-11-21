<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Question
 * @package App\Entity
 * @ORM\Entity
 * @ORM\Table(name="questions")
 */
class Question
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $qu_id;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $qu_text;

    /**
     * @var ArrayCollection|Choice[]
     * @ORM\OneToMany(targetEntity="App\Entity\Choice", mappedBy="cho_question")
     */
    private $qu_choices;

    public function __construct(string $text)
    {
        $this->qu_text = $text;
        $this->qu_choices = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->qu_text;
    }

    // AUTO GENERATE: getters and setters
    // ! Remove setter for the ID

    /**
     * @return int
     */
    public function getQuId(): int
    {
        return $this->qu_id;
    }

    /**
     * @return string|null
     */
    public function getQuText(): ?string
    {
        return $this->qu_text;
    }

    /**
     * @param string|null $qu_text
     * @return Question
     */
    public function setQuText(?string $qu_text): Question
    {
        $this->qu_text = $qu_text;
        return $this;
    }

    /**
     * @return Choice[]|ArrayCollection
     */
    public function getQuChoices()
    {
        return $this->qu_choices;
    }

    /**
     * @param Choice[]|ArrayCollection $qu_choices
     * @return Question
     */
    public function setQuChoices($qu_choices)
    {
        $this->qu_choices = $qu_choices;
        return $this;
    }
}