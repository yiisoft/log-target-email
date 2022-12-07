<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii Logging Library - Email Target</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/log-target-email/v/stable.png)](https://packagist.org/packages/yiisoft/log-target-email)
[![Total Downloads](https://poser.pugx.org/yiisoft/log-target-email/downloads.png)](https://packagist.org/packages/yiisoft/log-target-email)
[![Build status](https://github.com/yiisoft/log-target-email/workflows/build/badge.svg)](https://github.com/yiisoft/log-target-email/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/log-target-email/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/log-target-email/?branch=master)
[![Code Coverage](https://codecov.io/gh/yiisoft/log-target-email/branch/master/graph/badge.svg)](https://codecov.io/gh/yiisoft/log-target-email)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Flog-target-email%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/log-target-email/master)
[![static analysis](https://github.com/yiisoft/log-target-email/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/log-target-email/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/log-target-email/coverage.svg)](https://shepherd.dev/github/yiisoft/log-target-email)

This package provides the Email target for the [yiisoft/log](https://github.com/yiisoft/log) library.

## Requirements

- PHP 8.0 or higher.

## Installation

The package could be installed with composer:

```
composer require yiisoft/log-target-email --prefer-dist
```

## General usage

Creating a target:

```php
$emailTarget = new \Yiisoft\Log\Target\Email\EmailTarget($mailer, $emailTo, $subjectEmail);
```

- `$mailer (\Yiisoft\Mailer\MailerInterface)` - The mailer instance that sends email and should be already configured.
- `$emailTo (strig|array)` - The receiver email address.
  You may pass an array of addresses if multiple recipients should receive this message.
  You may also specify receiver name in addition to email address using format: `[email => name]`.
- `$subjectEmail (string)` - The email message subject. Defaults to `Application Log`.

Creating a logger:

```php
$logger = new \Yiisoft\Log\Logger([$emailTarget]);
```

For a description of using the logger, see the [yiisoft/log](https://github.com/yiisoft/log) package.

For use in the [Yii framework](http://www.yiiframework.com/), see the configuration files:

- [`config/common.php`](https://github.com/yiisoft/log-target-email/blob/master/config/common.php)
- [`config/params.php`](https://github.com/yiisoft/log-target-email/blob/master/config/params.php)

See [Yii guide to logging](https://github.com/yiisoft/docs/blob/master/guide/en/runtime/logging.md) for more info.

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework with
[Infection Static Analysis Plugin](https://github.com/Roave/infection-static-analysis-plugin). To run it:

```shell
./vendor/bin/roave-infection-static-analysis-plugin
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

## License

The Yii Logging Library - Email Target is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
