<?php
/**
 * Copyright (C) 2017-2024 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @copyright 2017-2024 thirty bees
 * @license   Academic Free License (AFL 3.0)
 */

namespace NoCaptchaRecaptchaModule;

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class RecaptchaGroup
 */
class RecaptchaGroup extends \ObjectModel
{
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'ncrc_group',
        'primary'   => 'id_ncrc_group',
        'fields'    => [
            'id_group'         => ['type' => self::TYPE_INT,  'validate' => 'isUnsignedId', 'required' => true, 'default' => '0', 'db_type' => 'INT(11) UNSIGNED'],
            'captcha_disabled' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool',       'required' => true, 'default' => '0', 'db_type' => 'TINYINT(1)'],
        ],
    ];
    /** @var int $id_group */
    public $id_group;
    /** @var bool $captchadisabled */
    public $captchadisabled;

    /**
     * Enable recaptcha for the given range
     *
     * @param array $range RecaptchaGroup IDs
     *
     * @return bool Indicates whether the captchas have been successfully enabled
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
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
     * @param array $range RecaptchaGroup IDs
     *
     * @return bool Indicates whether the captchas have been successfully disabled
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
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
     * Toggle captcha enabled status
     *
     * @param int $idRecaptchaGroup
     *
     * @return bool Indicates whether the captcha status has been successfully toggled
     * @throws \PrestaShopException
     */
    public static function toggle($idRecaptchaGroup)
    {
        return \Db::getInstance()->update(
            bqSQL(static::$definition['table']),
            [
                'captcha_disabled' => ['type' => 'sql', 'value' => 'IF(captcha_disabled=1, 0, 1)'],
            ],
            '`'.bqSQL(static::$definition['primary']).'` = '.(int) $idRecaptchaGroup
        );
    }
}
