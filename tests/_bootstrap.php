<?php
/**
 * Copyright (C) 2017-2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @copyright 2017-2018 thirty bees
 * @license   Academic Free License (AFL 3.0)
 */

// This is global bootstrap for autoloading
if ($workspace = getenv('WORKSPACE')) {
    require_once($workspace.'/config/config.inc.php');
    require_once($workspace.'/init.php');
}

// Install module
$module = Module::getInstanceByName('NoCaptchaRecaptcha');
$module->install();

// Install default customer
$customer = new Customer();
$customer->firstname = 'Test';
$customer->lastname = 'Test';
$customer->email = 'test@test.test';
$customer->passwd = md5(_COOKIE_KEY_.'testtest');
$customer->save();

Configuration::updateValue('NCRC_PUBLIC_KEY', 'kashdfkasdf');
Configuration::updateValue('NCRC_PRIVATE_KEY', 'kashdfkasdf');
Configuration::updateValue('NCRC_LOGIN', true);
