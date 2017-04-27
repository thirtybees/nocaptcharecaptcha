<?php
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
