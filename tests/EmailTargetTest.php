<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Email\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RuntimeException;
use Yiisoft\Log\Message;
use Yiisoft\Log\Target\Email\EmailTarget;
use Yiisoft\Mailer\BaseMailer;
use Yiisoft\Mailer\BaseMessage;

/**
 * Class EmailTargetTest.
 *
 * @group log
 */
class EmailTargetTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Yiisoft\Mailer\BaseMailer
     */
    protected $mailer;

    /**
     * Set up mailer.
     */
    protected function setUp(): void
    {
        $this->mailer = $this->getMockBuilder(BaseMailer::class)
            ->setMethods(['compose'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @covers \Yiisoft\Log\Target\Email\EmailTarget::__construct()
     */
    public function testConstructWithOptionTo(): void
    {
        $target = new EmailTarget($this->mailer, ['to' => 'developer1@example.com']);
        $this->assertIsObject($target);
    }

    /**
     * @covers \Yiisoft\Log\Target\Email\EmailTarget::__construct()
     */
    public function testConstructWithoutOptionTo(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The "to" option must be set for EmailTarget::message.');
        new EmailTarget($this->mailer, []);
    }

    /**
     * @covers \Yiisoft\Log\Target\Email\EmailTarget::export()
     * @covers \Yiisoft\Log\Target\Email\EmailTarget::composeMessage()
     */
    public function testExportWithSubject()
    {
        $message1 = new Message(
            LogLevel::INFO,
            'A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 1'
        );
        $message2 = new Message(
            LogLevel::INFO,
            'A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 2'
        );
        $messages = [$message1, $message2];
        $textBody = wordwrap(implode("\n", [$message1->message(), $message2->message()]), 70);

        $message = $this->getMockBuilder(BaseMessage::class)
            ->setMethods(['setTextBody', 'setMailer', 'send', 'setSubject'])
            ->getMockForAbstractClass();

        $this->mailer->expects($this->once())->method('compose')->willReturn($message);

        $message->expects($this->once())->method('setTextBody')->with($this->equalTo($textBody));
        $message->expects($this->once())->method('setMailer')->with($this->equalTo($this->mailer));
        $message->expects($this->once())->method('setSubject')->with($this->equalTo('Hello world'));

        $mailTarget = $this->getMockBuilder(EmailTarget::class)
            ->setMethods(['formatMessages'])
            ->setConstructorArgs([
                'mailer' => $this->mailer,
                'message' => [
                    'to' => 'developer@example.com',
                    'subject' => 'Hello world',
                ],
            ])
            ->getMock();

        $mailTarget
            ->expects($this->once())
            ->method('formatMessages')
            ->willReturn(
                "A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooong\nmessage 1\n"
                . "A very\nlooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong\nmessage 2"
            )
        ;
        $mailTarget->collect($messages, true);
    }

    /**
     * @covers \Yiisoft\Log\Target\Email\EmailTarget::export()
     * @covers \Yiisoft\Log\Target\Email\EmailTarget::composeMessage()
     */
    public function testExportWithoutSubject(): void
    {
        $message1 = new Message(
            LogLevel::INFO,
            'A veeeeery loooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 3',
        );
        $message2 = new Message(
            LogLevel::INFO,
            'Message 4'
        );
        $messages = [$message1, $message2];
        $textBody = wordwrap(implode("\n", [$message1->message(), $message2->message()]), 70);

        $message = $this->getMockBuilder(BaseMessage::class)
            ->setMethods(['setTextBody', 'setMailer', 'send', 'setSubject'])
            ->getMockForAbstractClass();

        $this->mailer->expects($this->once())->method('compose')->willReturn($message);

        $message->expects($this->once())->method('setTextBody')->with($this->equalTo($textBody));
        $message->expects($this->once())->method('setMailer')->with($this->equalTo($this->mailer));
        $message->expects($this->once())->method('setSubject')->with($this->equalTo('Application Log'));

        $mailTarget = $this->getMockBuilder(EmailTarget::class)
            ->setMethods(['formatMessages'])
            ->setConstructorArgs([
                'mailer' => $this->mailer,
                'message' => [
                    'to' => 'developer@example.com',
                ],
            ])
            ->getMock();

        $mailTarget
            ->expects($this->once())
            ->method('formatMessages')
            ->willReturn(
                "A veeeeery loooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 3\n"
                . 'Message 4'
            )
        ;
        $mailTarget->collect($messages, true);
    }

    /**
     * @covers \Yiisoft\Log\Target\Email\EmailTarget::export()
     *
     * See https://github.com/yiisoft/yii2/issues/14296
     */
    public function testExportWithSendFailure(): void
    {
        $message = $this->getMockBuilder(BaseMessage::class)
            ->setMethods(['send'])
            ->getMockForAbstractClass();
        $message->method('send')->willThrowException(new RuntimeException());
        $this->mailer->expects($this->once())->method('compose')->willReturn($message);
        $mailTarget = $this->getMockBuilder(EmailTarget::class)
            ->setMethods(['formatMessages'])
            ->setConstructorArgs([
                'mailer' => $this->mailer,
                'message' => [
                    'to' => 'developer@example.com',
                ],
            ])
            ->getMock();
        $this->expectException(RuntimeException::class);
        $mailTarget->export();
    }
}
