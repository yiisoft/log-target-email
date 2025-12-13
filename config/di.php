<?php

declare(strict_types=1);

use Yiisoft\Log\Target\Email\EmailTarget;
use Yiisoft\Mailer\MailerInterface;

/** @var array $params */

return [
    EmailTarget::class => static function (MailerInterface $mailer) use ($params) {
        return new EmailTarget(
            mailer: $mailer,
            emailTo: $params['yiisoft/log-target-email']['emailTarget']['emailTo'],
            subjectEmail: $params['yiisoft/log-target-email']['emailTarget']['subjectEmail'],
            levels: $params['yiisoft/log-target-email']['emailTarget']['levels'],
        );
    },
];
