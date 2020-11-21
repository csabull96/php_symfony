<?php


namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Choice
 * @package App\Entity
 * @ORM\Entity
 * @ORM\Table(name="choices")
 * @ORM\HasLifecycleCallbacks
 */
class Choice
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $cho_id;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $cho_inserted;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $cho_modified;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cho_visible = true;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true, length=100)
     */
    private $cho_text = "";

    /**
     * @var int/null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cho_numvotes = 0;

    /**
     * @var Question|null
     * @ORM\JoinColumn(name="cho_question", referencedColumnName="qu_id")
     * @ORM\ManyToOne(targetEntity="App\Entity\Question", inversedBy="qu_choices")
     */
    private $cho_question;

    public function __toString()
    {
        $question = $this->cho_question ? $this->cho_question->getQuText() : "N/A";
        return "{$question} / {$this->cho_text} / {$this->cho_numvotes}";
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps()
    {
        $this->cho_modified = new DateTime();
        if ($this->cho_inserted == null)
        {
            $this->cho_inserted = new DateTime();
        }
    }

    /**
     * @return int
     */
    public function getChoId(): int
    {
        return $this->cho_id;
    }

    /**
     * @return DateTime|null
     */
    public function getChoInserted(): ?DateTime
    {
        return $this->cho_inserted;
    }

    /**
     * @param DateTime|null $cho_inserted
     * @return Choice
     */
    public function setChoInserted(?DateTime $cho_inserted): Choice
    {
        $this->cho_inserted = $cho_inserted;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getChoModified(): ?DateTime
    {
        return $this->cho_modified;
    }

    /**
     * @param DateTime|null $cho_modified
     * @return Choice
     */
    public function setChoModified(?DateTime $cho_modified): Choice
    {
        $this->cho_modified = $cho_modified;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getChoVisible(): ?bool
    {
        return $this->cho_visible;
    }

    /**
     * @param bool|null $cho_visible
     * @return Choice
     */
    public function setChoVisible(?bool $cho_visible): Choice
    {
        $this->cho_visible = $cho_visible;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getChoText(): ?string
    {
        return $this->cho_text;
    }

    /**
     * @param string|null $cho_text
     * @return Choice
     */
    public function setChoText(?string $cho_text): Choice
    {
        $this->cho_text = $cho_text;
        return $this;
    }

    /**
     * @return int
     */
    public function getChoNumvotes(): int
    {
        return $this->cho_numvotes;
    }

    /**
     * @param int $cho_numvotes
     * @return Choice
     */
    public function setChoNumvotes(int $cho_numvotes): Choice
    {
        $this->cho_numvotes = $cho_numvotes;
        return $this;
    }

    /**
     * @return Question|null
     */
    public function getChoQuestion(): ?Question
    {
        return $this->cho_question;
    }

    /**
     * @param Question|null $cho_question
     * @return Choice
     */
    public function setChoQuestion(?Question $cho_question): Choice
    {
        $this->cho_question = $cho_question;
        return $this;
    }


}