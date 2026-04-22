# Yii Logging Library - Email Target Change Log

## 4.2.0 April 22, 2026

- Chg #65: Change PHP constraint in `composer.json` to `8.1 - 8.5` (@vjik)
- Chg #66: Raise the minimum `yiisoft/mailer` version to `^6.0` and adapt the code accordingly (@vjik)
- Enh #62: Explicitly import classes in "use" section (@mspirkov)

## 4.1.0 December 13, 2025

- New #60: Add optional `$levels` parameter to `EmailTarget` constructor for log level filtering at instantiation (@samdark)

## 4.0.0 February 17, 2023

- Chg #41: Adapt configuration group names to Yii conventions (@vjik)
- Enh #39: In `EmailTarget` move type hints from phpdoc to constructor signature (@vjik)
- Enh #39: Add support of `yiisoft/mailer` version `^4.0` (@vjik)
- Enh #40: Add support of `yiisoft/mailer` version `^5.0` (@vjik)

## 3.1.0 May 23, 2022

- Chg #29: Raise the minimum `yiisoft/log` version to `^2.0` and the minimum PHP version to 8.0 (@rustamwin)

## 3.0.1 August 26, 2021

- Bug #28: Remove `Psr\Log\LoggerInterface` definition from configuration for using multiple targets 
  to application (@devanych)

## 3.0.0 August 25, 2021

- Chg: Use `yiisoft/mailer` version `^3.0` (@samdark)

## 2.0.0 August 24, 2021

- Chg: Use `yiisoft/mailer` version `^2.0` (@samdark)

## 1.0.0 July 05, 2021

Initial release.
