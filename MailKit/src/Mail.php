<?php

namespace MailKit;

class Mail
{
    protected $subject;
    protected $message;

    public function __construct($subject = null, $message = null)
    {
        if ($subject) {
            $this->setSubject($subject);
        }

        if ($message) {
            $this->setMessage($message);
        }
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getRawMessage()
    {
        return $this->message;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function getHeaders()
    {
        $headers = [];

        if ($this->subject) {
            $headers['Subject'] = $this->subject;
        }

        return $headers;
    }
}
