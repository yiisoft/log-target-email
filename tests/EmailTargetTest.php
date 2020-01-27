<?php

namespace Yiisoft\Log\Target\Email\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Log\LogRuntimeException;
use Yiisoft\Log\Target\Email\EmailTarget;
use Yiisoft\Mailer\BaseMailer;
use Yiisoft\Mailer\BaseMessage;

/**
 * Class EmailTargetTest.
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
        $message1 = ['A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 1'];
        $message2 = ['A very looooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 2'];
        $messages = [$message1, $message2];
        $textBody = wordwrap(implode("\n", [$message1[0], $message2[0]]), 70);

        $message = $this->getMockBuilder(BaseMessage::class)
            ->setMethods(['setTextBody', 'setMailer', 'send', 'setSubject'])
            ->getMockForAbstractClass();

        $this->mailer->expects($this->once())->method('compose')->willReturn($message);

        $message->expects($this->once())->method('setTextBody')->with($this->equalTo($textBody));
        $message->expects($this->once())->method('setMailer')->with($this->equalTo($this->mailer));
        $message->expects($this->once())->method('setSubject')->with($this->equalTo('Hello world'));

        $mailTarget = $this->getMockBuilder(EmailTarget::class)
            ->setMethods(['formatMessage'])
            ->setConstructorArgs([
                'mailer' => $this->mailer,
                'message' => [
                    'to' => 'developer@example.com',
                    'subject' => 'Hello world',
                ],
            ])
            ->getMock();

        $mailTarget->setMessages($messages);
        $mailTarget->expects($this->exactly(2))->method('formatMessage')->willReturnMap(
            [
                [$message1, $message1[0]],
                [$message2, $message2[0]],
            ]
        );
        $mailTarget->export();
    }

    /**
     * @covers \Yiisoft\Log\Target\Email\EmailTarget::export()
     * @covers \Yiisoft\Log\Target\Email\EmailTarget::composeMessage()
     */
    public function testExportWithoutSubject(): void
    {
        $message1 = ['A veeeeery loooooooooooooooooooooooooooooooooooooooooooooooooooooooong message 3'];
        $message2 = ['Message 4'];
        $messages = [$message1, $message2];
        $textBody = wordwrap(implode("\n", [$message1[0], $message2[0]]), 70);

        $message = $this->getMockBuilder(BaseMessage::class)
            ->setMethods(['setTextBody', 'setMailer', 'send', 'setSubject'])
            ->getMockForAbstractClass();

        $this->mailer->expects($this->once())->method('compose')->willReturn($message);

        $message->expects($this->once())->method('setTextBody')->with($this->equalTo($textBody));
        $message->expects($this->once())->method('setMailer')->with($this->equalTo($this->mailer));
        $message->expects($this->once())->method('setSubject')->with($this->equalTo('Application Log'));

        $mailTarget = $this->getMockBuilder(EmailTarget::class)
            ->setMethods(['formatMessage'])
            ->setConstructorArgs([
                'mailer' => $this->mailer,
                'message' => [
                    'to' => 'developer@example.com',
                ],
            ])
            ->getMock();

        $mailTarget->setMessages($messages);
        $mailTarget->expects($this->exactly(2))->method('formatMessage')->willReturnMap(
            [
                [$message1, $message1[0]],
                [$message2, $message2[0]],
            ]
        );
        $mailTarget->export();
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
        $message->method('send')->willThrowException(new LogRuntimeException());
        $this->mailer->expects($this->once())->method('compose')->willReturn($message);
        $mailTarget = $this->getMockBuilder(EmailTarget::class)
            ->setMethods(['formatMessage'])
            ->setConstructorArgs([
                'mailer' => $this->mailer,
                'message' => [
                    'to' => 'developer@example.com',
                ],
            ])
            ->getMock();
        $this->expectException(LogRuntimeException::class);
        $mailTarget->export();
    }
}
