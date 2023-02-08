<?php

declare(strict_types=1);

namespace Yiisoft\Log\Target\Email\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Log\Target\Email\EmailTarget;
use Yiisoft\Mailer\MailerInterface;

final class ConfigTest extends TestCase
{
    public function testBase(): void
    {
        $container = $this->createContainer();

        $emailTarget = $container->get(EmailTarget::class);

        $this->assertInstanceOf(EmailTarget::class, $emailTarget);
    }

    private function createContainer(?array $params = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions(
                $this->getDiConfig($params)
                +
                [
                    MailerInterface::class => $this->createMock(MailerInterface::class),
                ]
            )
        );
    }

    private function getDiConfig(?array $params = null): array
    {
        if ($params === null) {
            $params = $this->getParams();
        }
        return require dirname(__DIR__) . '/config/di.php';
    }

    private function getParams(): array
    {
        return require dirname(__DIR__) . '/config/params.php';
    }
}
