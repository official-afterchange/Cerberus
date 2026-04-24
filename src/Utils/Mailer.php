<?php

declare(strict_types=1);

namespace Afterchange\Template\Utils;

use Symfony\Component\Mailer\Mailer as M;
use Symfony\Component\Mime\Email;

class Mailer
{
    private M $mailer;

    public function __construct(M $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(Email $email): void
    {
        $this->mailer->send($email);
    }
}
