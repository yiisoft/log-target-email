<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Email;

use Yiisoft\Log\LogRuntimeException;
use Yiisoft\Log\Target;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\MessageInterface;

/**
 * EmailTarget sends selected log messages to the specified email addresses.
 *
 * You may configure the email to be sent by setting the [[message]] property, through which
 * you can set the target email addresses, subject, etc.:
 *
 * ```php
 * 'components' => [
 *     'log' => [
 *          'targets' => [
 *              [
 *                  '__class' => \Yiisoft\Log\EmailTarget::class,
 *                  'mailer' => 'mailer',
 *                  'levels' => ['error', 'warning'],
 *                  'message' => [
 *                      'from' => ['log@example.com'],
 *                      'to' => ['developer1@example.com', 'developer2@example.com'],
 *                      'subject' => 'Log message',
 *                  ],
 *              ],
 *          ],
 *     ],
 * ],
 * ```
 *
 * In the above `mailer` is ID of the component that sends email and should be already configured.
 */
class EmailTarget extends Target
{
    /**
     * @var array the configuration array for creating a [[MessageInterface|message]] object.
     * Note that the "to" option must be set, which specifies the destination email address(es).
     */
    protected array $message = [];
    /**
     * @var \Yiisoft\Mailer\MailerInterface the mailer object.
     */
    protected MailerInterface $mailer;

    /**
     * EmailTarget constructor
     *
     * @param \Yiisoft\Mailer\MailerInterface $mailer
     * @param array $message
     * @throws \InvalidArgumentException
     */
    public function __construct(MailerInterface $mailer, array $message)
    {
        $this->mailer = $mailer;
        $this->message = $message;
        if (empty($this->message['to'])) {
            throw new \InvalidArgumentException('The "to" option must be set for EmailTarget::message.');
        }
    }

    /**
     * Sends log messages to specified email addresses.
     * Starting from version 2.0.14, this method throws LogRuntimeException in case the log can not be exported.
     * @throws \Yiisoft\Log\LogRuntimeException
     */
    public function export(): void
    {
        // moved initialization of subject here because of the following issue
        // https://github.com/yiisoft/yii2/issues/1446
        if (empty($this->message['subject'])) {
            $this->message['subject'] = 'Application Log';
        }
        $messages = array_map([$this, 'formatMessage'], $this->getMessages());
        $body = wordwrap(implode("\n", $messages), 70);
        $message = $this->composeMessage($body);

        try {
            $message->setMailer($this->mailer)->send();
        } catch (\Throwable $e) {
            throw new LogRuntimeException('Unable to export log through email!');
        }
    }

    /**
     * Composes a mail message with the given body content.
     * @param string $body the body content
     * @return \Yiisoft\Mailer\MessageInterface $message
     */
    protected function composeMessage(string $body): MessageInterface
    {
        $message = $this->mailer->compose();
        $message->setTo($this->message['to']);
        $message->setSubject($this->message['subject']);
        $message->setTextBody($body);

        return $message;
    }
}
