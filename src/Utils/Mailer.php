<?php

declare(strict_types=1);

namespace Afterchange\Template\Utils;

use Symfony\Component\Mailer\Mailer as M;
use Symfony\Component\Mime\Email;

/**
 * Thin wrapper around the Symfony Mailer for sending email messages.
 */
final class Mailer
{
    private M $mailer;

    /**
     * Initializes the mailer with the underlying Symfony transport.
     *
     * @param M $mailer The configured Symfony Mailer instance.
     */
    public function __construct(M $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Dispatches an email message through the configured transport.
     *
     * @param Email $email The email message to send.
     * @return void
     */
    public function send(Email $email): void
    {
        $this->mailer->send($email);
    }
}