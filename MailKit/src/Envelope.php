<?php

namespace MailKit;

class Envelope
{
    protected $mail;
    protected $from;
    protected $to = [];
    protected $cc = [];
    protected $bcc = [];

    public function __construct($mail, $from = null, $to = null, $cc = null, $bcc = null)
    {
        $this->mail = $mail;

        if ($from) {
            $this->setFrom($from);
        }
        if ($to) {
            $this->addTo($to);
        }
        if ($cc) {
            $this->addCc($cc);
        }
        if ($bcc) {
            $this->addBcc($bcc);
        }
    }

    protected function parseAddress($email, $name = null)
    {
        $result = [];

        if (is_string($email)) {
            $result['email'] = $email;
            if ($name) {
                $result['name'] = (string)$name;
            }
        } else if (is_array($email)) {
            if (isset($email['email'])) {
                $result['email'] = (string)$email['email'];
                if (isset($email['name'])) {
                    $result['name'] = (string)$email['name'];
                }
            } else {
                $e = current(array_keys($email));
                $result['email'] = $e;
                $result['name'] = (string)$email[$e];
            }
        } else {
            return null;
        }

        return $result['name']
            ? "{$result['name']} <{$result['email']}>"
            : $result['email'];
    }

    public function setFrom($from, $fromName = null)
    {
        $this->from = $this->parseAddress($from, $fromName);
        return $this;
    }

    protected function addAddress($key, $email, $name = null)
    {
        if (is_array($email) && !isset($email['email'])) {
            foreach ($email as $key => $value) {
                $addr = $this->parseAddress($key, $value);
                if ($addr) {
                    $this->$key[] = $addr;
                }
            }
        } else {
            $addr = $this->parseAddress($email, $name);
            if ($addr) {
                $this->$key[] = $addr;
            }
        }
        return $this;
    }

    public function addTo($to, $toName = null)
    {
        $this->addAddress('to', $to, $toName);
    }

    public function addCc($cc, $ccName = null)
    {
        $this->addAddress('cc', $cc, $toName);
    }

    public function addBcc($bcc, $bccName = null)
    {
        $this->addAddress('bcc', $bcc, $toName);
    }

    public function getHeaders()
    {
        $headers = $this->mail->getHeaders();

        if ($this->from) $headers['From'] = $this->from;
        if (count($this->to) > 0) $headers['To'] = implode(', ', $this->to);
        if (count($this->cc) > 0) $headers['Cc'] = implode(', ', $this->cc);
        if (count($this->bcc) > 0) $headers['Bcc'] = implode(', ', $this->bcc);

        return $headers;
    }

    public function getBody()
    {
        return $this->mail->getRawMessage();
    }
}
