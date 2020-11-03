<?php


namespace App\Model;


class MessageModel
{
    /** @var string */
    private $userName;

    /** @var string */
    private $timeStamp;

    /** @var string */
    private $text;

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return MessageModel
     */
    public function setUserName(string $userName): MessageModel
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeStamp(): string
    {
        return $this->timeStamp;
    }

    /**
     * @param string $timeStamp
     * @return MessageModel
     */
    public function setTimeStamp(string $timeStamp): MessageModel
    {
        $this->timeStamp = $timeStamp;
        return $this;
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
     * @return MessageModel
     */
    public function setText(string $text): MessageModel
    {
        $this->text = $text;
        return $this;
    }


}