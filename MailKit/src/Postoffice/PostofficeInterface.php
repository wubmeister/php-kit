<?php

namespace MailKit\Postoffice;

use MailKit\Envelope;

interface PostofficeInterface
{
    public function send(Envelope $envelope);
}
