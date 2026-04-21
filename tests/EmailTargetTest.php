<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Email\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionObject;
use RuntimeException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Email\EmailTarget;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\StubMailer;

use function wordwrap;

final class EmailTargetTest extends TestCase
{
    public function testExportWithSubject(): void
    {
        $mailer = new StubMailer();
        $target = $this->createEmailTarget($mailer, 'developer@example.com', 'Hello world');

        $target->collect([
            new Message(
                LogLevel::INFO,
                'A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 1',
            ),
            new Message(
                LogLevel::INFO,
                'A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 2',
            ),
        ], false);

        $textBody = $this->invokeFormatMessagesMethod($target);
        $target->collect([], true);

        $messages = $mailer->getMessages();
        $this->assertCount(1, $messages);
        $this->assertSame('Hello world', $messages[0]->getSubject());
        $this->assertSame('developer@example.com', $messages[0]->getTo());
        $this->assertSame($textBody, $messages[0]->getTextBody());
    }

    public function testExportWithoutSubject(): void
    {
        $mailer = new StubMailer();
        $target = $this->createEmailTarget($mailer, ['developer1@example.com', 'developer2@example.com']);

        $target->collect([
            new Message(
                LogLevel::INFO,
                'A veeeeery looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 3',
            ),
            new Message(
                LogLevel::INFO,
                'Message 4',
            ),
        ], false);

        $textBody = $this->invokeFormatMessagesMethod($target);
        $target->collect([], true);

        $messages = $mailer->getMessages();
        $this->assertCount(1, $messages);
        $this->assertSame('Application Log', $messages[0]->getSubject());
        $this->assertSame(['developer1@example.com', 'developer2@example.com'], $messages[0]->getTo());
        $this->assertSame($textBody, $messages[0]->getTextBody());
    }

    public function testExportWithCheckWidthLine(): void
    {
        $mailer = new StubMailer();
        $target = $this->createEmailTarget($mailer, ['developer1@example.com', 'developer2@example.com']);

        $target->collect([
            new Message(
                LogLevel::INFO,
                'A looooooooooooooooooooooooooooooooooooooooooooooooooooooooooong',
            ),
        ], false);

        $target->collect([], true);

        $messages = $mailer->getMessages();
        $this->assertCount(1, $messages);
        $this->assertSame(
            "[info] A\nlooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong\n",
            $messages[0]->getTextBody(),
        );
    }

    public function testExportWithSendFailure(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willThrowException(new RuntimeException());

        $target = new EmailTarget($mailer, ['developer@example.com']);
        $target->setFormat(fn(Message $message) => "[{$message->level()}] {$message->message()}");

        $message = new Message(LogLevel::INFO, 'Message');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to export log through email.');
        $this->expectExceptionCode(0);
        $target->collect([$message], true);
    }

    public function invalidEmailToDataProvider(): array
    {
        return [
            'empty-array' => [[]],
            'empty-string' => [''],
        ];
    }

    /**
     * @dataProvider invalidEmailToDataProvider
     */
    public function testConstructThrownExceptionForInvalidEmailTo(mixed $emailTo): void
    {
        $mailer = new StubMailer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "to" argument must be an array or string and must not be empty.');
        new EmailTarget($mailer, $emailTo);
    }

    public function testSetLevelsViaConstructor(): void
    {
        $mailer = new StubMailer();
        $target = new EmailTarget(
            $mailer,
            'developer@example.com',
            'Test Subject',
            [LogLevel::ERROR, LogLevel::INFO],
        );
        $target->setFormat(fn(Message $message) => "[{$message->level()}] {$message->message()}");

        $target->collect([
            new Message(LogLevel::INFO, 'message-1'),
            new Message(LogLevel::DEBUG, 'message-2'),
            new Message(LogLevel::ERROR, 'message-3'),
        ], false);

        $textBody = $this->invokeFormatMessagesMethod($target);
        $target->collect([], true);

        $messages = $mailer->getMessages();
        $this->assertCount(1, $messages);
        $this->assertSame($textBody, $messages[0]->getTextBody());
        $this->assertStringContainsString('message-1', $textBody);
        $this->assertStringContainsString('message-3', $textBody);
        $this->assertStringNotContainsString('message-2', $textBody);
    }

    public function testSetLevelsViaConstructorWithEmptyArray(): void
    {
        $mailer = new StubMailer();
        $target = new EmailTarget(
            $mailer,
            'developer@example.com',
            'Test Subject',
            [],
        );
        $target->setFormat(fn(Message $message) => "[{$message->level()}] {$message->message()}");

        $target->collect([
            new Message(LogLevel::INFO, 'message-1'),
            new Message(LogLevel::DEBUG, 'message-2'),
            new Message(LogLevel::ERROR, 'message-3'),
        ], false);

        $textBody = $this->invokeFormatMessagesMethod($target);
        $target->collect([], true);

        $messages = $mailer->getMessages();
        $this->assertCount(1, $messages);
        $this->assertSame($textBody, $messages[0]->getTextBody());
        $this->assertStringContainsString('message-1', $textBody);
        $this->assertStringContainsString('message-2', $textBody);
        $this->assertStringContainsString('message-3', $textBody);
    }

    private function createEmailTarget(StubMailer $mailer, mixed $emailTo, string $subjectEmail = ''): EmailTarget
    {
        $target = new EmailTarget($mailer, $emailTo, $subjectEmail);
        $target->setFormat(fn(Message $message) => "[{$message->level()}] {$message->message()}");
        return $target;
    }

    /**
     * Invokes the `EmailTarget::formatMessages()` protected method.
     *
     * @throws ReflectionException
     */
    private function invokeFormatMessagesMethod(EmailTarget $target): string
    {
        $reflection = new ReflectionObject($target);
        $method = $reflection->getMethod('formatMessages');
        return wordwrap($method->invokeArgs($target, ["\n"]), 70);
    }
}
