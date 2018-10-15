<?php

namespace MailKit\Postoffice;

use MailKit\Envelope;

class Sendmail implements PostofficeInterface
{
    public function send(Envelope $envelope)
    {
        $headers = $envelope->getHeaders();
        if (!isset($headers['To'])) {
            throw new \Exception("Missing 'To' header");
        }
        $to = $headers['To'];
        unset($headers['To']);

        $subject = 'Mail';
        if (isset($headers['Subject'])) {
            $subject = $headers['Subject'];
            unset($headers['Subject']);
        }

        mail($to, $subject, $envelope->getBody(), $headers, isset($headers['From']) ? '-f'.$headers['From'] : '');
    }
}
