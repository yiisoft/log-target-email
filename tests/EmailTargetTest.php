<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Email\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionException;
use ReflectionObject;
use RuntimeException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Email\EmailTarget;
use Yiisoft\Mailer\Mailer;
use Yiisoft\Mailer\MessageInterface;

use function wordwrap;

final class EmailTargetTest extends TestCase
{
    private Mailer|MockObject $mailer;
    private MessageInterface|MockObject $message;

    /**
     * Set up mailer.
     */
    protected function setUp(): void
    {
        $this->message = $this
            ->getMockBuilder(MessageInterface::class)
            ->onlyMethods(['withTextBody', 'withSubject', 'withTo'])
            ->getMockForAbstractClass()
        ;

        $this->mailer = $this
            ->getMockBuilder(Mailer::class)
            ->onlyMethods(['compose', 'send'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;

        $this->message
            ->method('withTextBody')
            ->willReturnSelf();
        $this->message
            ->method('withSubject')
            ->willReturnSelf();
        $this->message
            ->method('withTo')
            ->willReturnSelf();
        $this->mailer
            ->method('compose')
            ->willReturn($this->message);
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

        $this->message
            ->expects($this->once())
            ->method('withTextBody')
            ->with($this->equalTo($textBody));
        $this->message
            ->expects($this->once())
            ->method('withSubject')
            ->with($this->equalTo('Hello world'));
        $this->mailer
            ->expects($this->once())
            ->method('compose')
            ->with($this->equalTo(null), $this->equalTo([]));

        $target->collect([], true);
    }

    public function testExportWithoutSubject(): void
    {
        $target = $this->createEmailTarget(['developer1@example.com', 'developer2@example.com']);

        $target->collect([
            new Message(
                LogLevel::INFO,
                'A veeeeery looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 3',
            ),
            new Message(
                LogLevel::INFO,
                'Message 4'
            ),
        ], false);

        $textBody = $this->invokeFormatMessagesMethod($target);

        $this->message
            ->expects($this->once())
            ->method('withTextBody')
            ->with($this->equalTo($textBody));
        $this->message
            ->expects($this->once())
            ->method('withSubject')
            ->with($this->equalTo('Application Log'));
        $this->mailer
            ->expects($this->once())
            ->method('compose')
            ->with($this->equalTo(null), $this->equalTo([]));

        $target->collect([], true);
    }

    public function testExportWithCheckWidthLine(): void
    {
        $target = $this->createEmailTarget(['developer1@example.com', 'developer2@example.com']);

        $target->collect([
            new Message(
                LogLevel::INFO,
                'A looooooooooooooooooooooooooooooooooooooooooooooooooooooooooong',
            ),
        ], false);

        $this->message
            ->expects($this->once())
            ->method('withTextBody')
            ->with($this->equalTo(
                "[info] A\nlooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong\n",
            ));

        $target->collect([], true);
    }

    public function testExportWithSendFailure(): void
    {
        $target = $this->createEmailTarget(['developer@example.com']);

        $this->mailer
            ->method('send')
            ->willThrowException(new RuntimeException());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to export log through email.');
        $this->expectExceptionCode(0);

        $target->collect([new Message(LogLevel::INFO, 'Message')], true);
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "to" argument must be an array or string and must not be empty.');

        new EmailTarget($this->mailer, $emailTo);
    }

    private function createEmailTarget(mixed $emailTo, string $subjectEmail = ''): EmailTarget
    {
        $target = new EmailTarget($this->mailer, $emailTo, $subjectEmail);
        $target->setFormat(fn (Message $message) => "[{$message->level()}] {$message->message()}");
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
        $method->setAccessible(true);
        $result = wordwrap($method->invokeArgs($target, ["\n"]), 70);
        $method->setAccessible(false);
        return $result;
    }
}
