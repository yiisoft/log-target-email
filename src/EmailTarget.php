<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Email;

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use Yiisoft\Log\Target;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\MessageInterface;

use function sprintf;
use function wordwrap;

/**
 * EmailTarget sends selected log messages to the specified email addresses.
 *
 * You may configure the email to be sent by setting the {@see EmailTarget::$messageOptions} property,
 * through which you can set the target email addresses, subject, etc.
 *
 * {@see EmailTarget::$mailer} is instance of {@see MailerInterface} that sends email and should be already configured.
 */
final class EmailTarget extends Target
{
    /**
     * @var MailerInterface The mailer instance.
     */
    private MailerInterface $mailer;

    /**
     * @var array The configuration array for creating a {@see MessageInterface} instance.
     * Note that the "to" option must be set, which specifies the destination email address(es).
     */
    private array $messageOptions;

    /**
     * @param MailerInterface $mailer The mailer instance.
     * @param array $messageOptions The configuration array for creating a {@see MessageInterface} instance.
     * Note that the "to" option must be set, which specifies the destination email address(es).
     *
     * @throws InvalidArgumentException If the "to" message option was not set.
     */
    public function __construct(MailerInterface $mailer, array $messageOptions)
    {
        $this->mailer = $mailer;
        $this->messageOptions = $messageOptions;
        parent::__construct();

        if (empty($this->messageOptions['to'])) {
            throw new InvalidArgumentException(sprintf(
                'The "to" option must be set for %s::message.',
                self::class,
            ));
        }

        if (empty($this->messageOptions['subject'])) {
            $this->messageOptions['subject'] = 'Application Log';
        }
    }

    /**
     * Sends log messages to specified email addresses.
     *
     * @throws RuntimeException If the log cannot be exported.
     */
    protected function export(): void
    {

        $message = $this->mailer->compose()
            ->setTo($this->messageOptions['to'])
            ->setSubject($this->messageOptions['subject'])
            ->setTextBody(wordwrap($this->formatMessages("\n"), 70))
        ;

        try {
            $message->setMailer($this->mailer)->send();
        } catch (Throwable $e) {
            throw new RuntimeException('Unable to export log through email.', 0, $e);
        }
    }
}
