<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Email\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionObject;
use RuntimeException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Email\EmailTarget;
use Yiisoft\Mailer\BaseMailer;
use Yiisoft\Mailer\BaseMessage;

use function wordwrap;

final class EmailTargetTest extends TestCase
{
    /**
     * @var MockObject|BaseMailer
     */
    private $mailer;

    /**
     * @var MockObject|BaseMessage
     */
    private $message;

    /**
     * Set up mailer.
     */
    protected function setUp(): void
    {
        $this->message = $this->getMockBuilder(BaseMessage::class)
            ->onlyMethods(['setTextBody', 'setMailer', 'send', 'setSubject', 'setTo'])
            ->getMockForAbstractClass()
        ;

        $this->mailer = $this->getMockBuilder(BaseMailer::class)
            ->onlyMethods(['compose'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $this->message->method('setTextBody')->willReturnSelf();
        $this->message->method('setMailer')->willReturnSelf();
        $this->message->method('setSubject')->willReturnSelf();
        $this->message->method('setTo')->willReturnSelf();
        $this->mailer->method('compose')->willReturn($this->message);
    }

    public function testConstructThrownExceptionForWithoutOptionTo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "to" option must be set for Yiisoft\Log\Target\Email\EmailTarget::message.');
        new EmailTarget($this->mailer, []);
    }

    public function testExportWithSubject(): void
    {
        $target = $this->createEmailTarget([
            'to' => 'developer@example.com',
            'subject' => 'Hello world',
        ]);

        $target->collect([
            new Message(
                LogLevel::INFO,
                'A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 1'
            ),
            new Message(
                LogLevel::INFO,
                'A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 2'
            ),
        ], false);

        $textBody = $this->invokeFormatMessagesMethod($target);

        $this->message->expects($this->once())->method('setTextBody')->with($this->equalTo($textBody));
        $this->message->expects($this->once())->method('setMailer')->with($this->equalTo($this->mailer));
        $this->message->expects($this->once())->method('setSubject')->with($this->equalTo('Hello world'));
        $this->mailer->expects($this->once())->method('compose')->with($this->equalTo(null), $this->equalTo([]));

        $target->collect([], true);
    }

    public function testExportWithoutSubject(): void
    {
        $target = $this->createEmailTarget([
            'to' => 'developer@example.com',
        ]);

        $target->collect([
            new Message(
                LogLevel::INFO,
                'A veeeeery loooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 3',
            ),
            new Message(
                LogLevel::INFO,
                'Message 4'
            ),
        ], false);

        $textBody = $this->invokeFormatMessagesMethod($target);

        $this->message->expects($this->once())->method('setTextBody')->with($this->equalTo($textBody));
        $this->message->expects($this->once())->method('setMailer')->with($this->equalTo($this->mailer));
        $this->message->expects($this->once())->method('setSubject')->with($this->equalTo('Application Log'));
        $this->mailer->expects($this->once())->method('compose')->with($this->equalTo(null), $this->equalTo([]));

        $target->collect([], true);
    }

    public function testExportWithSendFailure(): void
    {
        $target = $this->createEmailTarget([
            'to' => 'developer@example.com',
        ]);

        $this->message->method('send')->willThrowException(new RuntimeException());

        $this->expectException(RuntimeException::class);
        $target->collect([new Message(LogLevel::INFO, 'Message')], true);
    }

    private function createEmailTarget(array $messageOptions): EmailTarget
    {
        $target = new EmailTarget($this->mailer, $messageOptions);
        $target->setFormat(fn (Message $message) => "[{$message->level()}] {$message->message()}");
        return $target;
    }

    /**
     * Invokes the `EmailTarget::formatMessages()` protected method.
     *
     * @param EmailTarget $target
     *
     * @return string
     *
     * @throws ReflectionException
     */
    private function invokeFormatMessagesMethod(EmailTarget $target): string
    {
        $reflection = new ReflectionObject($target);
        $method = $reflection->getMethod('formatMessages');
        $method->setAccessible(true);
        $result = wordwrap($method->invokeArgs($target, ["\n"]), 70);
        $method->setAccessible(false);
        return $result;
    }
}
