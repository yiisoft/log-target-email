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
use stdClass;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Email\EmailTarget;
use Yiisoft\Mailer\BaseMailer;
use Yiisoft\Mailer\BaseMessage;

use function wordwrap;

final class EmailTargetTest extends TestCase
{
    /**
     * @var BaseMailer|MockObject
     */
    private $mailer;

    /**
     * @var BaseMessage|MockObject
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

    public function testExportWithSubject(): void
    {
        $target = $this->createEmailTarget('developer@example.com', 'Hello world');

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
        $target = $this->createEmailTarget(['developer1@example.com', 'developer2@example.com']);

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
        $target = $this->createEmailTarget(['developer@example.com']);

        $this->message->method('send')->willThrowException(new RuntimeException());

        $this->expectException(RuntimeException::class);
        $target->collect([new Message(LogLevel::INFO, 'Message')], true);
    }

    public function invalidEmailToDataProvider(): array
    {
        return [
            'int' => [1],
            'float' => [1.1],
            'null' => [null],
            'object' => [new stdClass()],
            'callable' => [fn () => 'admin@example.com'],
            'empty-array' => [[]],
            'empty-string' => [''],
        ];
    }

    /**
     * @dataProvider invalidEmailToDataProvider
     *
     * @param mixed $emailTo
     */
    public function testConstructThrownExceptionForInvalidEmailTo($emailTo): void
    {
        $this->expectException(InvalidArgumentException::class);
        new EmailTarget($this->mailer, []);
    }

    /**
     * @param mixed $emailTo
     * @param string $subjectEmail
     *
     * @return EmailTarget
     */
    private function createEmailTarget($emailTo, string $subjectEmail = ''): EmailTarget
    {
        $target = new EmailTarget($this->mailer, $emailTo, $subjectEmail);
        $target->setFormat(fn (Message $message) => "[{$message->level()}] {$message->message()}");
        return $target;
    }

    /**
     * Invokes the `EmailTarget::formatMessages()` protected method.
     *
     * @param EmailTarget $target
     *
     * @throws ReflectionException
     *
     * @return string
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
