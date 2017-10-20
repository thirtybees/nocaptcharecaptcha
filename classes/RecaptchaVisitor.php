<?php
/**
 * Copyright (C) 2017 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 *  @author    thirty bees <modules@thirtybees.com>
 *  @copyright 2017 thirty bees
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace NoCaptchaRecaptchaModule;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class RecaptchaVisitor
 */
class RecaptchaVisitor extends \ObjectModel
{
    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'ncrc_visitor',
        'primary' => 'id_ncrc_visitor',
        'fields'  => [
            'email'                  => ['type' => self::TYPE_STRING, 'validate' => 'isString',     'required' => true,                                     'db_type' => 'VARCHAR(255)'],
            'captcha_disabled'       => ['type' => self::TYPE_BOOL,   'validate' => 'isBool',       'required' => true, 'default' => '0',                   'db_type' => 'TINYINT(1)'],
            'captcha_failed_attempt' => ['type' => self::TYPE_DATE,   'validate' => 'isDateFormat', 'required' => true, 'default' => '1970-01-01 00:00:00', 'db_type' => 'DATETIME'],
            'captcha_attempts'       => ['type' => self::TYPE_INT,    'validate' => 'isInt',        'required' => true, 'default' => '0',                   'db_type' => 'INT(11) UNSIGNED'],
        ],
    ];
    /** @var string $email */
    public $email;
    /** @var bool $captcha_disabled */
    public $captcha_disabled;
    /** @var int $captcha_failed_attempt */
    public $captcha_failed_attempt;
    /** @var int $captcha_attempts */
    public $captcha_attempts;
    // @codingStandardsIgnoreEnd

    /**
     * Enable recaptcha for the given range
     *
     * @param array $range RecaptchaVisitor IDs
     *
     * @return bool Indicates whether the captchas have been successfully enabled
     */
    public static function enableCaptchas($range)
    {
        if (empty($range)) {
            return true;
        }

        foreach ($range as &$item) {
            $item = (int) $item;
        }

        return \Db::getInstance()->update(
            \bqSQL(self::$definition['table']),
            [
                'captcha_disabled' => false,
            ],
            '`'.\bqSQL(self::$definition['primary']).'` IN ('.implode(',', $range).')'
        );
    }

    /**
     * Disable recaptcha for the given range
     *
     * @param array $range RecaptchaVisitor IDs
     *
     * @return bool Indicates whether the captchas have been successfully disabled
     */
    public static function disableCaptchas($range)
    {
        if (empty($range)) {
            return true;
        }

        foreach ($range as &$item) {
            $item = (int) $item;
        }

        return \Db::getInstance()->update(
            \bqSQL(self::$definition['table']),
            [
                'captcha_disabled' => true,
            ],
            '`'.\bqSQL(self::$definition['primary']).'` IN ('.implode(',', $range).')'
        );
    }

    /**
     * Reset all recaptcha attempts with the number provided
     *
     * @param int $number Reset all attempts to this number
     *
     * @return bool Indicates whether the reset succeeded
     */
    public static function resetAllAttempts($number)
    {
        if (\Validate::isInt($number)) {
            return \Db::getInstance()->update(
                \bqSQL(self::$definition['table']),
                ['captcha_attempts' => (int) $number]
            );
        }

        return false;
    }

    /**
     * Reset attempts by email address
     *
     * @param string $email  Email address
     * @param int    $number Amount of attempts for reset
     *
     * @return bool Indicates whether the attempts have been reset
     */
    public static function resetAttemptsByEmail($email, $number)
    {
        if (!\Validate::isEmail($email) || !\Validate::isInt($number)) {
            return false;
        }

        $success = true;

        $sql = new \DbQuery();
        $sql->select('`captcha_attempts`, `id_ncrc_visitor`');
        $sql->from(\bqSQL(self::$definition['table']));
        $sql->where('`email` = \''.\pSQL($email).'\'');

        $visitor = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (empty($visitor)) {
            $success &= \Db::getInstance()->insert(
                \bqSQL(self::$definition['table']),
                [
                    'email'                  => \pSQL($email),
                    'captcha_attempts'       => (int) $number,
                    'captcha_disabled'       => false,
                    'captcha_failed_attempt' => '1970-01-01 00:00:00',
                ]
            );
        } else {
            $success &= \Db::getInstance()->update(
                \bqSQL(self::$definition['table']),
                [
                    'captcha_attempts' => (int) $number,
                ],
                '`id_ncrc_visitor` = '.(int) $visitor['id_ncrc_visitor']
            );
        }

        return $success;
    }

    /**
     * Set a failed attempt
     *
     * @param string $email Email address
     *
     * @return bool Indicates whether the failed attempt has been registered
     */
    public static function failedAttempt($email)
    {
        if (!\Validate::isEmail($email)) {
            return false;
        }

        $success = true;

        // Check if email exists in DB
        $sql = new \DbQuery();
        $sql->select('rc.`email`');
        $sql->from(\bqSQL(self::$definition['table']), 'rc');
        $sql->where('rc.`email` = \''.\pSQL($email).'\'');

        $result = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        if (!$result) {
            \Db::getInstance()->insert(
                \bqSQL(self::$definition['table']),
                [
                    'email'                  => \pSQL($email),
                    'captcha_failed_attempt' => date('Y-m-d H:i:s'),
                    'captcha_disabled'       => false,
                    'captcha_attempts'       => (int) \Configuration::get('NCRC_ATTEMPTS'),
                ]
            );
        } else {
            \Db::getInstance()->update(
                \bqSQL(self::$definition['table']),
                ['captcha_failed_attempt' => date('Y-m-d H:i:s')],
                '`email` = \''.\pSQL($email).'\''
            );
        }

        if (!self::decreaseAttempts($email)) {
            $success &= false;
        }

        return $success;
    }

    /**
     * Decrease amount of attempts left
     *
     * @param string $email Email address
     *
     * @return bool Indicates whether the amount has been successfully decreased
     */
    public static function decreaseAttempts($email)
    {
        if (!\Validate::isEmail($email)) {
            return false;
        }

        return \Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.\bqSQL(self::$definition['table']).'`
             SET `captcha_attempts` = `captcha_attempts` - 1
             WHERE `email` = \''.\pSQL($email).'\'
             AND `captcha_attempts` > 0'
        );
    }

    /**
     * Toggle captcha enabled status
     *
     * @param int $idRecaptchaVisitor
     *
     * @return bool Indicates whether the captcha status has been successfully toggled
     */
    public static function toggle($idRecaptchaVisitor)
    {
        return \Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.\bqSQL(static::$definition['table']).'
             SET captcha_disabled = IF(captcha_disabled=1, 0, 1)
             WHERE `'.\bqSQL(static::$definition['primary']).'` = '.(int) $idRecaptchaVisitor
        );
    }
}
