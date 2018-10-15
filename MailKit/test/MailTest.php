<?php

use PHPUnit\Framework\TestCase;

use MailKit\Mail;

class MailKit_MailTest extends TestCase
{
    public function testHeaders()
    {
        $mail = new Mail();

        $headers = $mail->getHeaders();
        $this->assertInternalType('array', $headers);
        $this->assertCount(0, $headers);

        $mail->setSubject('This is a subject');

        $headers = $mail->getHeaders();

        $this->assertInternalType('array', $headers);
        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('Subject', $headers);
        $this->assertEquals('This is a subject', $headers['Subject']);

    }

    public function testBody()
    {
        $mail = new Mail();

        $headers = $mail->getHeaders();
        $this->assertEquals('', $mail->getRawMessage());

        $mail->setMessage('This is the message');
        $this->assertEquals('This is the message', $mail->getRawMessage());

    }

    public function testShorthand()
    {
        $mail = new Mail('Subject', 'Message');

        $headers = $mail->getHeaders();
        $this->assertInternalType('array', $headers);
        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('Subject', $headers);
        $this->assertEquals('Subject', $headers['Subject']);
        $this->assertEquals('Message', $mail->getRawMessage());
    }
}
