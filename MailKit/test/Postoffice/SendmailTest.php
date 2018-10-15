<?php

use PHPUnit\Framework\TestCase;

use MailKit\Mail;
use MailKit\Envelope;
use MailKit\Postoffice\Sendmail;

class MailKit_Postoffice_SendmailTest extends TestCase
{
    public function testSend()
    {
        $postOffice = new Sendmail();

        $postOffice->send(
            new Envelope(
                new Mail('The subject', 'The message'),
                'wubbo@addnoise.nl',
                'wubbotest@addnoise.nl'
            )
        );
    }
}
