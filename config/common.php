<?php

declare(strict_types=1);

use Yiisoft\Log\Target\Email\EmailTarget;
use Yiisoft\Mailer\MailerInterface;

/* @var $params array */

return [
    EmailTarget::class => static function (MailerInterface $mailer) use ($params) {
        $emailTarget = new EmailTarget(
            $mailer,
            $params['yiisoft/log-target-email']['emailTarget']['emailTo'],
            $params['yiisoft/log-target-email']['emailTarget']['subjectEmail'],
        );

        $emailTarget->setLevels($params['yiisoft/log-target-email']['emailTarget']['levels']);

        return $emailTarget;
    },
];
