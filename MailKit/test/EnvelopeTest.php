<?php

use PHPUnit\Framework\TestCase;

use MailKit\Mail;
use MailKit\Envelope;

class MailKit_EnvelopeTest extends TestCase
{
    protected $mail;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->mail = new Mail('The subject', 'The message');
    }

    public function testHeaders()
    {
        $envelope = new Envelope($this->mail);
        $headers = $envelope->getHeaders();
        $this->assertInternalType('array', $headers);
        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('Subject', $headers);
        $this->assertEquals('The subject', $headers['Subject']);

        $envelope->setFrom('wubbo@wubbobos.nl');
        $headers = $envelope->getHeaders();
        $this->assertCount(2, $headers);
        $this->assertArrayHasKey('From', $headers);
        $this->assertEquals('wubbo@wubbobos.nl', $headers['From']);
    }

    public function testFrom()
    {
        $envelope = new Envelope($this->mail);
        $envelope->setFrom('wubbo@wubbobos.nl', 'Wubbo Bos');
        $headers = $envelope->getHeaders();
        $this->assertCount(2, $headers);
        $this->assertArrayHasKey('From', $headers);
        $this->assertEquals('Wubbo Bos <wubbo@wubbobos.nl>', $headers['From']);

        $envelope->setFrom([ 'wubbo@wubbobos.nl' => 'Wubbo Bos' ]);
        $headers = $envelope->getHeaders();
        $this->assertCount(2, $headers);
        $this->assertArrayHasKey('From', $headers);
        $this->assertEquals('Wubbo Bos <wubbo@wubbobos.nl>', $headers['From']);

        $envelope = new Envelope($this->mail, 'wubbo@wubbobos.nl');
        $headers = $envelope->getHeaders();
        $this->assertCount(2, $headers);
        $this->assertArrayHasKey('From', $headers);
        $this->assertEquals('wubbo@wubbobos.nl', $headers['From']);
    }

    public function testTo()
    {
        $envelope = new Envelope($this->mail);
        $envelope->addTo('wubbo@wubbobos.nl', 'Wubbo Bos');
        $headers = $envelope->getHeaders();
        $this->assertCount(2, $headers);
        $this->assertArrayHasKey('To', $headers);
        $this->assertEquals('Wubbo Bos <wubbo@wubbobos.nl>', $headers['To']);

        $envelope->addTo([ 'wubbo@wubbobos.nl' => 'Wubbo Bos' ]);
        $headers = $envelope->getHeaders();
        $this->assertCount(2, $headers);
        $this->assertArrayHasKey('To', $headers);
        $this->assertEquals('Wubbo Bos <wubbo@wubbobos.nl>', $headers['To']);

        $envelope = new Envelope($this->mail, 'wubbo@wubbobos.nl', 'info@domain.com');
        $headers = $envelope->getHeaders();
        $this->assertCount(3, $headers);
        $this->assertArrayHasKey('From', $headers);
        $this->assertEquals('wubbo@wubbobos.nl', $headers['From']);
        $this->assertArrayHasKey('To', $headers);
        $this->assertEquals('info@domain.com', $headers['To']);
    }
}
