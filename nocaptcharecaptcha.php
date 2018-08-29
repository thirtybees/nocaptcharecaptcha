<?php
/**
 * Copyright (C) 2017-2018 thirty bees
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
 * @author    thirty bees <modules@thirtybees.com>
 * @copyright 2017-2018 thirty bees
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use NoCaptchaRecaptchaModule\RecaptchaGroup;
use NoCaptchaRecaptchaModule\RecaptchaLib;
use NoCaptchaRecaptchaModule\RecaptchaVisitor;

if (!defined('_TB_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/lib/autoload.php';
require_once dirname(__FILE__).'/classes/autoload.php';

/**
 * Class NoCaptchaRecaptcha
 */
class NoCaptchaRecaptcha extends Module
{
    const PUBLIC_KEY = 'NCRC_PUBLIC_KEY';
    const PRIVATE_KEY = 'NCRC_PRIVATE_KEY';
    const LOGIN = 'NCRC_LOGIN';
    const LOGIN_THEME = 'NCRC_LOGIN_THEME';
    const LOGINHTML = 'NCRC_LOGINHTML';
    const LOGINSELECT = 'NCRC_LOGINSELECT';
    const LOGINPOS = 'NCRC_LOGINPOS';

    const ADMINLOGIN = 'NCRC_ADMINLOGIN';
    const ADMINLOGIN_THEME = 'NCRC_ADMINLOGIN_THEME';

    const CREATE = 'NCRC_CREATE';
    const CREATE_THEME = 'NCRC_CREATE_THEME';
    const CREATEHTML = 'NCRC_CREATEHTML';
    const CREATESELECT = 'NCRC_CREATESELECT';
    const CREATEPOS = 'NCRC_CREATEPOS';

    const PASSWORD = 'NCRC_PASSWORD';
    const PASSWORD_THEME = 'NCRC_PASSWORD_THEME';
    const PASSWORDHTML = 'NCRC_PASSWORDHTML';
    const PASSWORDSELECT = 'NCRC_PASSWORDSELECT';
    const PASSWORDPOS = 'NCRC_PASSWORDPOS';

    const CONTACT = 'NCRC_CONTACT';
    const CONTACT_THEME = 'NCRC_CONTACT_THEME';
    const CONTACTHTML = 'NCRC_CONTACTHTML';
    const CONTACTSELECT = 'NCRC_CONTACTSELECT';
    const CONTACTPOS = 'NCRC_CONTACTPOS';

    const OPCLOGINHTML = 'NCRC_OPCLOGINHTML';
    const OPCLOGINSELECT = 'NCRC_OPCLOGINSELECT';
    const OPCLOGINPOS = 'NCRC_OPCLOGINPOS';

    const OPCCREATEHTML = 'NCRC_OPCCREATEHTML';
    const OPCCREATESELECT = 'NCRC_OPCCREATESELECT';
    const OPCCREATEPOS = 'NCRC_OPCCREATEPOS';

    const SENDTOAFRIEND = 'NCRC_SENDTOAFRIEND';

    const ATTEMPTS = 'NCRC_ATTEMPTS';
    const ATTEMPTS_MINS = 'NCRC_ATTEMPTS_MINS';
    const ATTEMPTS_HOURS = 'NCRC_ATTEMPTS_HOURS';
    const ATTEMPTS_DAYS = 'NCRC_ATTEMPTS_DAYS';
    const LOGGEDINDISABLE = 'NCRC_LOGGEDINDISABLE';
    const EXTRACSS = 'NCRC_EXTRACSS';
    const JQUERYOPTS = 'NCRC_JQUERYOPTS';
    const PS15COMPAT = 'NCRC_PS15COMPAT';
    const GOOGLEIGNORE = 'NCRC_GOOGLEIGNORE';

    const MENU_SETTINGS = 1;
    const MENU_ADVANCED_SETTINGS = 2;
    const MENU_CUSTOMERS = 3;
    const MENU_GROUPS = 4;

    /** @var string $moduleUrl */
    public $moduleUrl;

    /**
     * NoCaptchaRecaptcha constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'nocaptcharecaptcha';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'thirty bees';
        $this->need_instance = 1;

        // Only check from Back Office
        if (isset(Context::getContext()->employee->id) && Context::getContext()->employee->id) {
            if (Tools::getValue('controller') == 'AdminModules') {
                $this->warning = '';
                foreach ($this->detectBOSettings() as $warning) {
                    $this->warning .= $warning;
                }
            }

            $this->moduleUrl = Context::getContext()->link->getAdminLink('AdminModules', true).'&'.http_build_query([
                    'configure'   => $this->name,
                    'tab_module'  => $this->tab,
                    'module_name' => $this->name,
                ]);
        }

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('No Captcha reCaptcha Module');
        $this->description = $this->l('Protects your store from spambots and brute force attacks with the new reCAPTCHA by Google.');
    }

    /**
     * Install the module
     *
     * @return bool Whether the install succeeded
     * @throws PrestaShopException
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        foreach ($this->hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        RecaptchaVisitor::createDatabase();
        RecaptchaGroup::createDatabase();

        // Create tables for ObjectModels
        $sql = [];

        $sql[] = 'CREATE INDEX email ON `'._DB_PREFIX_.bqSQL(RecaptchaVisitor::$definition['table']).'` (email)';

        $sql[] = 'CREATE INDEX id_group ON `'._DB_PREFIX_.bqSQL(RecaptchaGroup::$definition['table']).'` (id_group)';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query, false)) {
                return false;
            }
        }

        $this->syncGroups();

        Configuration::updateGlobalValue(static::ATTEMPTS, 0);
        Configuration::updateGlobalValue(static::ATTEMPTS_MINS, 0);
        Configuration::updateGlobalValue(static::ATTEMPTS_HOURS, 0);
        Configuration::updateGlobalValue(static::ATTEMPTS_DAYS, 0);
        Configuration::updateGlobalValue(static::LOGGEDINDISABLE, true);
        $this->updateAllValue(static::PS15COMPAT, false);

        $this->installDefaultHtml();

        return true;
    }

    /**
     * Uninstall the module
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public function uninstall()
    {
        $this->uninstallOptionalOverrides();

        foreach ($this->hooks as $hook) {
            $this->unregisterHook($hook);
        }

        RecaptchaVisitor::dropDatabase();
        RecaptchaGroup::dropDatabase();

        Configuration::deleteByName(static::PRIVATE_KEY);
        Configuration::deleteByName(static::PUBLIC_KEY);
        Configuration::deleteByName(static::CONTACT);
        Configuration::deleteByName(static::CONTACT_THEME);
        Configuration::deleteByName(static::PASSWORD);
        Configuration::deleteByName(static::PASSWORD_THEME);
        Configuration::deleteByName(static::ATTEMPTS);
        Configuration::deleteByName(static::LOGIN);
        Configuration::deleteByName(static::LOGIN_THEME);
        Configuration::deleteByName(static::ADMINLOGIN_THEME);
        Configuration::deleteByName(static::CREATE);
        Configuration::deleteByName(static::CREATE_THEME);
        Configuration::deleteByName(static::ATTEMPTS);
        Configuration::deleteByName(static::ATTEMPTS_MINS);
        Configuration::deleteByName(static::ATTEMPTS_HOURS);
        Configuration::deleteByName(static::ATTEMPTS_DAYS);
        Configuration::deleteByName(static::PS15COMPAT);
        Configuration::deleteByName(static::GOOGLEIGNORE);
        Configuration::deleteByName(static::LOGGEDINDISABLE);
        Configuration::deleteByName(static::ADMINLOGIN);

        $this->uninstallDefaultHtml();
        $this->uninstallAdminLoginOverride();

        return parent::uninstall();
    }

    /**
     * @var array With hooks
     */
    protected $hooks = [
        'actionAuthentication',
        'displayBackOfficeHeader',
        'header',
    ];

    /**
     * Install the default HTML for all store contexts
     *
     * @return bool Whether the install succeeded
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function installDefaultHtml()
    {
        $defaultsJson = json_decode(
            file_get_contents(_PS_MODULE_DIR_.$this->name.'/views/json/admin/defaults.json')
        );
        $shortVersion = substr(_PS_VERSION_, 0, 3);
        $defaultsJson = $defaultsJson->{$shortVersion};

        $this->updateAllValue(static::LOGINSELECT, $defaultsJson->login->select);
        $this->updateAllValue(static::LOGINPOS, $defaultsJson->login->position);
        $this->updateAllValue(static::LOGINHTML, $this->unescapeJsonHtml($defaultsJson->login->content), true);
        $this->updateAllValue(static::CREATESELECT, $defaultsJson->create->select);
        $this->updateAllValue(static::CREATEPOS, $defaultsJson->create->position);
        $this->updateAllValue(static::CREATEHTML, $this->unescapeJsonHtml($defaultsJson->create->content), true);
        $this->updateAllValue(static::PASSWORDSELECT, $defaultsJson->password->select);
        $this->updateAllValue(static::PASSWORDPOS, $defaultsJson->password->position);
        $this->updateAllValue(static::PASSWORDHTML, $this->unescapeJsonHtml($defaultsJson->password->content), true);
        $this->updateAllValue(static::CONTACTSELECT, $defaultsJson->contact->select);
        $this->updateAllValue(static::CONTACTPOS, $defaultsJson->contact->position);
        $this->updateAllValue(static::CONTACTHTML, $this->unescapeJsonHtml($defaultsJson->contact->content), true);
        $this->updateAllValue(static::OPCLOGINSELECT, $defaultsJson->opclogin->select);
        $this->updateAllValue(static::OPCLOGINPOS, $defaultsJson->opclogin->position);
        $this->updateAllValue(static::OPCLOGINHTML, $this->unescapeJsonHtml($defaultsJson->opclogin->content), true);
        $this->updateAllValue(static::OPCCREATESELECT, $defaultsJson->opccreate->select);
        $this->updateAllValue(static::OPCCREATEPOS, $defaultsJson->opccreate->position);
        $this->updateAllValue(static::OPCCREATEHTML, $this->unescapeJsonHtml($defaultsJson->opccreate->content), true);

        file_put_contents($this->local_path.'views/css/extra.css', $defaultsJson->css->extra);

        return true;
    }

    /**
     * Uninstall all HTML fields
     *
     * @return bool Whether the removal succeeded
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstallDefaultHtml()
    {
        Configuration::deleteByName(static::LOGINSELECT);
        Configuration::deleteByName(static::LOGINPOS);
        Configuration::deleteByName(static::LOGINHTML);
        Configuration::deleteByName(static::CREATESELECT);
        Configuration::deleteByName(static::CREATEPOS);
        Configuration::deleteByName(static::CREATEHTML);
        Configuration::deleteByName(static::PASSWORDSELECT);
        Configuration::deleteByName(static::PASSWORDPOS);
        Configuration::deleteByName(static::PASSWORDHTML);
        Configuration::deleteByName(static::CONTACTSELECT);
        Configuration::deleteByName(static::CONTACTPOS);
        Configuration::deleteByName(static::CONTACTHTML);
        Configuration::deleteByName(static::OPCLOGINSELECT);
        Configuration::deleteByName(static::OPCLOGINPOS);
        Configuration::deleteByName(static::OPCLOGINHTML);
        Configuration::deleteByName(static::OPCCREATESELECT);
        Configuration::deleteByName(static::OPCCREATEPOS);
        Configuration::deleteByName(static::OPCCREATEHTML);

        return true;
    }

    /**
     * Install Admin Login override
     *
     * @return void
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function installAdminLoginOverride()
    {
        $targetTpl = _PS_OVERRIDE_DIR_.'controllers/admin/templates/login/content.tpl';

        $result = $this->addOptionalOverride(
            'AdminLoginController',
            _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'AdminLoginController.php'
        );

        if (!$result) {
            $this->updateAllValue(static::ADMINLOGIN, false);

            return;
        }
        $sourceTpl = dirname(__FILE__).'/optionaloverride/1610/content.tpl';
        $targetDir = _PS_OVERRIDE_DIR_.'controllers/admin';
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir)) {
                $this->updateAllValue(static::ADMINLOGIN, false);

                $this->addError($this->l(sprintf($this->l('Couldn\'t create directory: %s'), $targetDir)), true);

                return;
            }
        }
        $targetDir = _PS_OVERRIDE_DIR_.'controllers/admin/templates';
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir)) {
                $this->updateAllValue(static::ADMINLOGIN, false);

                $this->addError($this->l(sprintf($this->l('Couldn\'t create directory: %s'), $targetDir)), true);

                return;
            }
        }
        $targetDir = _PS_OVERRIDE_DIR_.'controllers/admin/templates/login';
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir)) {
                $this->updateAllValue(static::ADMINLOGIN, false);

                $this->addError($this->l(sprintf($this->l('Couldn\'t create directory: %s'), $targetDir)), true);

                return;
            }
        }
        if (!copy($sourceTpl, $targetTpl)) {
            $this->updateAllValue(static::ADMINLOGIN, false);

            $this->addError(
                sprintf(
                    $this->l('Couldn\'t install Admin template: %s to %s'),
                    $sourceTpl,
                    $targetTpl
                ),
                true
            );

            return;
        }
        $this->updateAllValue(static::ADMINLOGIN, true);

        return;
    }

    /**
     * Uninstall Admin Login override
     *
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public function uninstallAdminLoginOverride()
    {
        $sourceTpl = _PS_OVERRIDE_DIR_.'controllers/admin/templates/login/content.tpl';

        $result = $this->removeOptionalOverride(
            'AdminLoginController',
            _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'AdminLoginController.php'
        );

        if (!$result) {
            $this->updateAllValue(static::ADMINLOGIN, true);

            return;
        }

        if (@filemtime($sourceTpl)) {
            if (@!unlink($sourceTpl)) {
                $this->addError(sprintf($this->l('Couldn\'t remove Admin template: %s'), $sourceTpl), true);

                return;
            }
        }

        $this->updateAllValue(static::ADMINLOGIN, false);

        return;
    }

    /**
     * Manage the auth override
     * Automatically detects whether it should stay or should be removed
     *
     * @param bool $login  Whether the login captcha should be enabled for the current shop
     * @param bool $create Whether the register captcha should be enable for the current shop
     *
     * @return bool
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function manageAuthOverride($login, $create)
    {
        $output = true;
        if ($login || $create) {
            $output &= $this->addOptionalOverride(
                'AuthController',
                _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'AuthController.php'
            );
            if (!file_exists(_PS_OVERRIDE_DIR_.'classes/checkout')) {
                @mkdir(_PS_OVERRIDE_DIR_.'classes/checkout');
            }
            if ($output) {
                if ($login) {
                    Configuration::updateValue(static::LOGIN, $login);
                }
                if ($create) {
                    Configuration::updateValue(static::CREATE, $create);
                }
            } else {
                $this->updateAllValue(static::LOGIN, false);
                $this->updateAllValue(static::CREATE, false);
            }
        } else {
            Configuration::updateValue(static::LOGIN, false);
            Configuration::updateValue(static::CREATE, false);

            $active = false;
            foreach (Shop::getShops() as $shop) {
                if (Configuration::get(static::LOGIN, null, $shop['id_shop_group'], $shop['id_shop'])) {
                    $active = true;
                    break;
                }
                if (Configuration::get(static::CREATE, null, $shop['id_shop_group'], $shop['id_shop'])) {
                    $active = true;
                    break;
                }
            }
            if (!$active) {
                try {
                    $result = $this->removeOptionalOverride(
                        'AuthController',
                        _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'AuthController.php'
                    );
                    if (!$result) {
                        $this->updateAllValue(static::LOGIN, true);
                        $this->updateAllValue(static::CREATE, true);
                    }
                    $output &= $result;
                } catch (Exception $e) {
                    $msg = $e->getMessage();
                    if (!strpos($msg, 'does not exist')) {
                        $this->addError($msg, true);
                    }

                    return false;
                }
            }
        }

        return $output;
    }

    /**
     * Manage the password forgotten override
     * Automatically detects whether it should stay or should be removed
     *
     * @param string $password Whether the password captcha should be enabled for this shop
     *
     * @return bool
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function managePasswordOverride($password)
    {
        $output = true;
        if ($password) {
            $output &= $this->addOptionalOverride(
                'PasswordController',
                _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'PasswordController.php'
            );
            if ($output) {
                if ($password) {
                    Configuration::updateValue(static::PASSWORD, true);
                }
            } else {
                $this->updateAllValue(static::PASSWORD, false);
            }
        } else {
            Configuration::updateValue(static::PASSWORD, false);

            $active = false;
            foreach (Shop::getShops() as $shop) {
                if (Configuration::get(static::PASSWORD, null, $shop['id_shop_group'], $shop['id_shop'])) {
                    $active = true;
                    break;
                }
            }
            if (!$active) {
                try {
                    $output &= $this->removeOptionalOverride(
                        'PasswordController',
                        _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'PasswordController.php'
                    );
                    if (!$output) {
                        $this->updateAllValue(static::PASSWORD, true);
                    }
                } catch (Exception $e) {
                    $msg = $e->getMessage();
                    if (!strpos($msg, 'does not exist')) {
                        $this->addError($msg);

                        $output = false;
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Manage the contact override
     * Automatically detect whether it should stay or should be removed
     *
     * @param string $contact Whether the contact captcha should be enabled for this shop
     *
     * @return bool
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function manageContactOverride($contact)
    {
        $output = true;
        if ($contact) {
            $output &= $this->addOptionalOverride(
                'ContactController',
                _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'ContactController.php'
            );
            if ($output) {
                if ($contact) {
                    Configuration::updateValue(static::CONTACT, true);
                }
            } else {
                $this->updateAllValue(static::CONTACT, false);
            }
        } else {
            Configuration::updateValue(static::CONTACT, false);

            $active = false;
            foreach (Shop::getShops() as $shop) {
                if (Configuration::get(static::CONTACT, null, $shop['id_shop_group'], $shop['id_shop'])) {
                    $active = true;
                    break;
                }
            }
            if (!$active) {
                try {
                    $output &= $this->removeOptionalOverride(
                        'ContactController',
                        _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'ContactController.php'
                    );
                    if (!$output) {
                        $this->updateAllValue(static::CONTACT, true);
                    }
                } catch (Exception $e) {
                    $msg = $e->getMessage();
                    if (!strpos($msg, 'does not exist')) {
                        $this->addError($msg);

                        $output = false;
                    }
                }
            }
        }

        return $output;
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function uninstallOptionalOverrides()
    {
        // Uninstall overrides
        try {
            $this->uninstallAdminLoginOverride();
        } catch (Exception $e) {
        }

        try {
            if ($this->removeOptionalOverride(
                'AuthController',
                _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'AuthController.php'
            )) {
                return false;
            }
        } catch (Exception $e) {
        }

        try {
            if ($this->removeOptionalOverride(
                'PasswordController',
                _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'PasswordController.php'
            )) {
                return false;
            }
        } catch (Exception $e) {
        }

        try {
            if ($this->removeOptionalOverride(
                'ContactController',
                _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'optionaloverride'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'front'.DIRECTORY_SEPARATOR.'ContactController.php'
            )) {
                return false;
            }
        } catch (Exception $e) {
        }

        $this->updateAllValue(static::LOGIN, false);
        $this->updateAllValue(static::CREATE, false);
        $this->updateAllValue(static::PASSWORD, false);
        $this->updateAllValue(static::CONTACT, false);
    }

    /**
     * Load the configuration form
     *
     * @return string
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $this->checkHooks();

        foreach ($this->detectBOSettings() as $error) {
            $this->addError($error, true);
        }

        $this->initNavigation();

        $this->postProcess();

        $this->context->smarty->assign(
            [
                'menutabs' => $this->initNavigation(),
            ]
        );

        $output = $this->display(__FILE__, 'views/templates/admin/navbar.tpl');

        switch (Tools::getValue('menu')) {
            case static::MENU_ADVANCED_SETTINGS:
                return $output.$this->renderAdvancedSettingsPage();
            case static::MENU_CUSTOMERS:
                return $output.$this->renderCustomersPage();
            case static::MENU_GROUPS:
                return $output.$this->renderGroupsPage();
            default:
                $this->menu = static::MENU_SETTINGS;

                return $output.$this->renderSettingsPage();
        }
    }

    /**
     * Initialize navigation
     *
     * @return array Menu items
     */
    protected function initNavigation()
    {
        $menu = [
            static::MENU_SETTINGS          => [
                'short'  => $this->l('Settings'),
                'desc'   => $this->l('Module settings'),
                'href'   => $this->moduleUrl.'&menu='.static::MENU_SETTINGS,
                'active' => false,
                'icon'   => 'icon-cog',
            ],
            static::MENU_ADVANCED_SETTINGS => [
                'short'  => $this->l('Advanced settings'),
                'desc'   => $this->l('Advanced module setings'),
                'href'   => $this->moduleUrl.'&menu='.static::MENU_ADVANCED_SETTINGS,
                'active' => false,
                'icon'   => 'icon-cogs',
            ],
            static::MENU_CUSTOMERS         => [
                'short'  => $this->l('Customers'),
                'desc'   => $this->l('Customers'),
                'href'   => $this->moduleUrl.'&menu='.static::MENU_CUSTOMERS,
                'active' => false,
                'icon'   => 'icon-user',
            ],
            static::MENU_GROUPS            => [
                'short'  => $this->l('Groups'),
                'desc'   => $this->l('Groups'),
                'href'   => $this->moduleUrl.'&menu='.static::MENU_GROUPS,
                'active' => false,
                'icon'   => 'icon-users',
            ],
        ];

        switch (Tools::getValue('menu')) {
            case static::MENU_ADVANCED_SETTINGS:
                $this->menu = static::MENU_ADVANCED_SETTINGS;
                $menu[static::MENU_ADVANCED_SETTINGS]['active'] = true;
                break;
            case static::MENU_CUSTOMERS:
                $this->menu = static::MENU_CUSTOMERS;
                $menu[static::MENU_CUSTOMERS]['active'] = true;
                break;
            case static::MENU_GROUPS:
                $this->menu = static::MENU_GROUPS;
                $menu[static::MENU_GROUPS]['active'] = true;
                break;
            default:
                $this->menu = static::MENU_SETTINGS;
                $menu[static::MENU_SETTINGS]['active'] = true;
                break;
        }

        return $menu;
    }

    /**
     * Render settings page
     *
     * @return string HTML
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    protected function renderSettingsPage()
    {
        $output = '';

        $this->context->controller->addJS($this->_path.'views/js/sweetalert.min.js');
        $this->context->controller->addCSS($this->_path.'views/css/sweetalert.min.css');

        $domain1 = (string) Tools::getHttpHost(false);
        $shop = new Shop($this->getShopId());
        $domain2 = (string) $shop->domain;

        $this->context->smarty->assign(
            [
                'language_iso'                    => $this->context->language->iso_code,
                'site_key'                        => Configuration::get(static::PUBLIC_KEY),
                'secret_key'                      => Configuration::get(static::PRIVATE_KEY),
                'nocaptcharecaptcha_confirm_link' => $this->moduleUrl,
                'differentDomain'                 => $domain1 !== $domain2,
                'domain1'                         => $domain1,
                'domain2'                         => $domain2,
            ]
        );

        $output .= $this->display(__FILE__, 'views/templates/admin/configure.tpl');
        $output = $output.$this->displayForm();
        $output .= $this->display(__FILE__, 'views/templates/admin/confirmcaptcha.tpl');

        return $output;
    }

    /**
     * Render advanced settings page
     *
     * @return string HTML
     */
    protected function renderAdvancedSettingsPage()
    {
        $output = '';

        $this->context->controller->addJS($this->_path.'/views/js/libs/ace-min-noconflict/ace.js');
        $this->context->controller->addJS($this->_path.'/views/js/libs/ace-min-noconflict/ext-language_tools.js');
        $this->context->controller->addJS($this->_path.'/views/js/advanced.js');
        $this->context->controller->addCSS($this->_path.'/views/css/advanced16.css');

        // Add advanced form
        $output .= $this->displayAdvancedForm();

        return $output;
    }

    /**
     * Render customer page
     *
     * @return string HTML
     */
    protected function renderCustomersPage()
    {
        $output = '';

        if (Tools::isSubmit(RecaptchaVisitor::$definition['primary'])
            && Tools::isSubmit('update'.RecaptchaVisitor::$definition['table'])) {
            $output .= $this->renderCustomerEditForm();
        } else {
            $output .= $this->renderCustomerList();
        }

        return $output;
    }

    /**
     * Render group page
     *
     * @return string HTML
     */
    protected function renderGroupsPage()
    {
        $output = '';

        $this->syncGroups();

        if (Tools::isSubmit(RecaptchaGroup::$definition['primary'])
            && Tools::isSubmit('update'.RecaptchaGroup::$definition['table'])) {
            $output .= $this->renderGroupEditForm();
        } else {
            $output .= $this->renderGroupList();
        }

        return $output;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function displayForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitNocaptcharecaptchaModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of the form.
     */
    protected function getConfigForm()
    {
        $options = [
            [
                'id_option' => 1,
                'name'      => 'Light',
            ],
            [
                'id_option' => 2,
                'name'      => 'Dark',
            ],
        ];

        $input = [
            [
                'type'     => 'text',
                'label'    => $this->l('reCAPTCHA site key'),
                'name'     => static::PUBLIC_KEY,
                'size'     => 64,
                'desc'     => $this->l('Used in the Javascript files that are served to users.'),
                'required' => true,
            ],
            [
                'type'     => 'text',
                'label'    => $this->l('reCAPTCHA secret key'),
                'name'     => static::PRIVATE_KEY,
                'desc'     => $this->l('Used for communication between the store and Google. Be sure to keep this key a secret.'),
                'size'     => 64,
                'required' => true,
            ],
            [
                'type' => 'hr',
                'name' => '',
            ],
            [
                'type'    => 'switch',
                'label'   => $this->l('Customer login'),
                'name'    => static::LOGIN,
                'is_bool' => true,
                'values'  => [
                    [
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ],
                    [
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ],
                ],
            ],
            [
                'type'     => 'text',
                'label'    => $this->l('Login attempts'),
                'name'     => static::ATTEMPTS,
                'desc'     => $this->l('Amount of login attempts before captcha is shown. (0 = always)'),
                'size'     => 10,
                'class'    => 'fixed-width-xl',
                'required' => true,
            ],
            [
                'type'     => 'text',
                'label'    => $this->l('Reset attempts after'),
                'name'     => static::ATTEMPTS_MINS,
                'desc'     => ucfirst(Translate::getAdminTranslation('minutes', 'AdminEmployees')),
                'hint'     => $this->l('0 - 0 - 0 disables time limit'),
                'size'     => 2,
                'class'    => 'fixed-width-sm',
                'required' => true,
            ],
            [
                'type'     => 'text',
                'name'     => static::ATTEMPTS_HOURS,
                'desc'     => Translate::getAdminTranslation('Hours', 'AdminBackup'),
                'size'     => 2,
                'class'    => 'fixed-width-sm',
                'required' => true,
            ],
            [
                'type'     => 'text',
                'name'     => static::ATTEMPTS_DAYS,
                'desc'     => Translate::getAdminTranslation('Days', 'AdminBackup'),
                'size'     => 3,
                'class'    => 'fixed-width-sm',
                'required' => true,
            ],
            [
                'type'    => 'select',
                'lang'    => true,
                'label'   => $this->l('Theme'),
                'name'    => static::LOGIN_THEME,
                'desc'    => $this->l('Enable captcha and select theme for customer login'),
                'options' => [
                    'query' => $options,
                    'id'    => 'id_option',
                    'name'  => 'name',
                ],
            ],
            [
                'type'    => 'switch',
                'label'   => $this->l('Disable captcha when user is logged in'),
                'name'    => static::LOGGEDINDISABLE,
                'is_bool' => true,
                'values'  => [
                    [
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ],
                    [
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ],
                ],
            ],
            [
                'type' => 'hr',
                'name' => '',
            ],
            [
                'type'    => 'switch',
                'label'   => $this->l('Register account'),
                'name'    => static::CREATE,
                'is_bool' => true,
                'values'  => [
                    [
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ],
                    [
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ],
                ],
            ],
            [
                'type'    => 'select',
                'lang'    => true,
                'label'   => $this->l('Theme'),
                'name'    => static::CREATE_THEME,
                'desc'    => $this->l('Enable captcha and select theme for the customer registration page'),
                'options' => [
                    'query' => $options,
                    'id'    => 'id_option',
                    'name'  => 'name',
                ],
            ],
            [
                'type' => 'hr',
                'name' => '',
            ],
            [
                'type'    => 'switch',
                'label'   => $this->l('Contact form'),
                'name'    => static::CONTACT,
                'is_bool' => true,
                'values'  => [
                    [
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ],
                    [
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ],
                ],
            ],
            [
                'type'    => 'select',
                'lang'    => true,
                'label'   => $this->l('Theme'),
                'name'    => static::CONTACT_THEME,
                'desc'    => $this->l('Enable captcha and select theme for the contact form'),
                'options' => [
                    'query' => $options,
                    'id'    => 'id_option',
                    'name'  => 'name',
                ],
            ],
            [
                'type' => 'hr',
                'name' => '',
            ],
            [
                'type'    => 'switch',
                'label'   => $this->l('Password forgotten'),
                'name'    => static::PASSWORD,
                'is_bool' => true,
                'values'  => [
                    [
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ],
                    [
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ],
                ],
            ],
            [
                'type'    => 'select',
                'lang'    => true,
                'label'   => $this->l('Theme'),
                'name'    => static::PASSWORD_THEME,
                'desc'    => $this->l('Enable captcha and select theme for the password forgotten form'),
                'options' => [
                    'query' => $options,
                    'id'    => 'id_option',
                    'name'  => 'name',
                ],
            ],
            [
                'type' => 'hr',
                'name' => '',
            ],
            [
                'type'    => 'switch',
                'label'   => $this->l('Back Office login'),
                'name'    => static::ADMINLOGIN,
                'is_bool' => true,
                'values'  => [
                    [
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ],
                    [
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ],
                ],
            ],
            [
                'type'    => 'select',
                'lang'    => true,
                'label'   => $this->l('Theme'),
                'name'    => static::ADMINLOGIN_THEME,
                'options' => [
                    'query' => $options,
                    'id'    => 'id_option',
                    'name'  => 'name',
                ],
            ],
            [
                'type' => 'hr',
                'name' => '',
            ],
            [
                'type'        => 'switch',
                'label'       => $this->l('Ignore connection problems with Google'),
                'hint'        => $this->l('Do not check captcha if it is not possible to connect with Google'),
                'description' => $this->l('Check PrestaShop\'s logs if the server has connection problems'),
                'name'        => static::GOOGLEIGNORE,
                'is_bool'     => true,
                'values'      => [
                    [
                        'id'    => 'active_on',
                        'value' => true,
                        'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                    ],
                    [
                        'id'    => 'active_off',
                        'value' => false,
                        'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                    ],
                ],
            ],
        ];

        return [
            'form' => [
                'legend' => [
                    'title' => Translate::getAdminTranslation('Settings', 'AdminReferrers'),
                    'icon'  => 'icon-cogs',
                ],
                'input'  => $input,
                'submit' => [
                    'title' => Translate::getAdminTranslation('Save', 'AdminReferrers'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];
    }

    /**
     * Create the HTML for the advanced form
     *
     * @return string Advanced form HTML
     *
     * @throws Exception
     * @throws SmartyException
     */
    protected function displayAdvancedForm()
    {
        // jQuery position options
        $jqueryPosOptions = [
            1  => 'before',
            2  => 'after',
            3  => 'prepend',
            4  => 'append',
            5  => 'parent before',
            6  => 'parent after',
            7  => 'parent prepend',
            8  => 'parent append',
            9  => 'parent parent before',
            10 => 'parent parent after',
            11 => 'parent parent prepend',
            12 => 'parent parent append',
        ];

        // Assign Advanced Settings variables
        $this->context->smarty->assign(
            [
                static::LOGINPOS        => Configuration::get(static::LOGINPOS),
                static::LOGINSELECT     => Configuration::get(static::LOGINSELECT),
                static::LOGINHTML       => Configuration::get(static::LOGINHTML),
                static::CREATEPOS       => Configuration::get(static::CREATEPOS),
                static::CREATESELECT    => Configuration::get(static::CREATESELECT),
                static::CREATEHTML      => Configuration::get(static::CREATEHTML),
                static::PASSWORDPOS     => Configuration::get(static::PASSWORDPOS),
                static::PASSWORDSELECT  => Configuration::get(static::PASSWORDSELECT),
                static::PASSWORDHTML    => Configuration::get(static::PASSWORDHTML),
                static::CONTACTPOS      => Configuration::get(static::CONTACTPOS),
                static::CONTACTSELECT   => Configuration::get(static::CONTACTSELECT),
                static::CONTACTHTML     => Configuration::get(static::CONTACTHTML),
                static::OPCLOGINPOS     => Configuration::get(static::OPCLOGINPOS),
                static::OPCLOGINSELECT  => Configuration::get(static::OPCLOGINSELECT),
                static::OPCLOGINHTML    => Configuration::get(static::OPCLOGINHTML),
                static::OPCCREATEPOS    => Configuration::get(static::OPCCREATEPOS),
                static::OPCCREATESELECT => Configuration::get(static::OPCCREATESELECT),
                static::OPCCREATEHTML   => Configuration::get(static::OPCCREATEHTML),
                static::JQUERYOPTS      => $jqueryPosOptions,
                'advancedAction'        => $this->moduleUrl.'&token='.Tools::getAdminTokenLite('AdminModules').'&menu='.static::MENU_ADVANCED_SETTINGS,
            ]
        );

        if (file_exists($this->local_path.'views/css/extra.css')) {
            $this->context->smarty->assign(
                static::EXTRACSS,
                file_get_contents($this->local_path.'views/css/extra.css')
            );
        } else {
            $this->context->smarty->assign(static::EXTRACSS, '');
        }

        return $this->display(__FILE__, 'views/templates/admin/advanced.tpl');
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            static::ATTEMPTS       => Configuration::get(static::ATTEMPTS),
            static::ATTEMPTS_MINS  => Configuration::get(static::ATTEMPTS_MINS),
            static::ATTEMPTS_HOURS => Configuration::get(static::ATTEMPTS_HOURS),
            static::ATTEMPTS_DAYS  => Configuration::get(static::ATTEMPTS_DAYS),

            static::LOGIN       => Configuration::get(static::LOGIN),
            static::LOGIN_THEME => Configuration::get(static::LOGIN_THEME),

            static::ADMINLOGIN       => Configuration::get(static::ADMINLOGIN),
            static::ADMINLOGIN_THEME => Configuration::get(static::ADMINLOGIN_THEME),

            static::CREATE       => Configuration::get(static::CREATE),
            static::CREATE_THEME => Configuration::get(static::CREATE_THEME),

            static::CONTACT       => Configuration::get(static::CONTACT),
            static::CONTACT_THEME => Configuration::get(static::CONTACT_THEME),

            static::PASSWORD       => Configuration::get(static::PASSWORD),
            static::PASSWORD_THEME => Configuration::get(static::PASSWORD_THEME),

            static::SENDTOAFRIEND => Configuration::get(static::SENDTOAFRIEND),

            static::PRIVATE_KEY     => Configuration::get(static::PRIVATE_KEY),
            static::PUBLIC_KEY      => Configuration::get(static::PUBLIC_KEY),
            static::LOGGEDINDISABLE => Configuration::get(static::LOGGEDINDISABLE),
            static::GOOGLEIGNORE    => Configuration::get(static::GOOGLEIGNORE),
        ];
    }

    /**
     * Render customer export list
     *
     * @return string
     * @throws PrestaShopDatabaseException
     */
    protected function renderCustomerList()
    {
        $fieldsList = [
            RecaptchaVisitor::$definition['primary'] => ['title' => $this->l('ID'), 'width' => 40],
            'firstname'                              => ['title' => $this->l('First name'), 'width' => 'auto'],
            'lastname'                               => ['title' => $this->l('Last name'), 'width' => 'auto'],
            'email'                                  => ['title' => $this->l('Email'), 'width' => 'auto'],
            'captcha_disabled'                       => ['title' => $this->l('Captcha disabled'), 'type' => 'bool', 'width' => 'auto', 'active' => 'captcha_disabled', 'ajax' => true],
            'captcha_failed_attempt'                 => ['title' => $this->l('Last failed attempt'), 'type' => 'datetime', 'width' => 'auto'],
            'captcha_attempts'                       => ['title' => $this->l('Attempts left'), 'type' => 'int', 'width' => 'auto'],
        ];

        if (Tools::isSubmit('submitReset'.RecaptchaVisitor::$definition['table'])) {
            $cookie = $this->context->cookie;
            $filterKeys = array_keys($fieldsList);
            foreach ($fieldsList as $item) {
                if (isset($item['filter_key'])) {
                    $filterKeys[] = $item['filter_key'];
                }
            }
            foreach ($filterKeys as $fieldName) {
                unset($cookie->{RecaptchaVisitor::$definition['table'].'Filter_'.$fieldName});
                unset($_POST[RecaptchaVisitor::$definition['table'].'Filter_'.$fieldName]);
                unset($_GET[RecaptchaVisitor::$definition['table'].'Filter_'.$fieldName]);
            }
            unset($this->context->cookie->{RecaptchaVisitor::$definition['table'].'Orderby'});
            unset($this->context->cookie->{RecaptchaVisitor::$definition['table'].'OrderWay'});

            $cookie->write();
        }

        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from(bqSQL(RecaptchaVisitor::$definition['table']), 'rc');
        $sql->rightJoin('customer', 'c', 'c.`email` = rc.`email`');
        $sql->where('c.`id_shop` = '.(int) $this->getShopId());

        $listTotal = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        $pagination = (int) $this->getSelectedPagination(RecaptchaVisitor::$definition['table']);
        $currentPage = (int) $this->getSelectedPage(RecaptchaVisitor::$definition['table'], $listTotal);

        $helperList = new HelperList();
        $helperList->shopLinkType = false;

        $helperList->list_id = RecaptchaVisitor::$definition['table'];

        $helperList->bulk_actions = [
            'enable_recaptcha'  => [
                'text' => $this->l('Enable recaptcha'),
                'icon' => 'icon-check',
            ],
            'disable_recaptcha' => [
                'text' => $this->l('Disable recaptcha'),
                'icon' => 'icon-times',
            ],
        ];

        $helperList->actions = ['Edit'];

        $helperList->page = $currentPage;

        $helperList->_defaultOrderBy = RecaptchaVisitor::$definition['primary'];

        if (Tools::isSubmit(RecaptchaVisitor::$definition['table'].'Orderby')) {
            $helperList->orderBy = Tools::getValue(RecaptchaVisitor::$definition['table'].'Orderby');
            $this->context->cookie->{RecaptchaVisitor::$definition['table'].'Orderby'} = $helperList->orderBy;
        } elseif (!empty($this->context->cookie->{RecaptchaVisitor::$definition['table'].'Orderby'})) {
            $helperList->orderBy = $this->context->cookie->{RecaptchaVisitor::$definition['table'].'Orderby'};
        } else {
            $helperList->orderBy = bqSQL(RecaptchaVisitor::$definition['primary']);
        }

        if (Tools::isSubmit(RecaptchaVisitor::$definition['table'].'Orderway')) {
            $helperList->orderWay = Tools::strtoupper(Tools::getValue(RecaptchaVisitor::$definition['table'].'Orderway'));
            $this->context->cookie->{RecaptchaVisitor::$definition['table'].'Orderway'} = Tools::getValue(RecaptchaVisitor::$definition['table'].'Orderway');
        } elseif (!empty($this->context->cookie->{RecaptchaVisitor::$definition['table'].'Orderway'})) {
            $helperList->orderWay = Tools::strtoupper($this->context->cookie->{RecaptchaVisitor::$definition['table'].'Orderway'});
        } else {
            $helperList->orderWay = 'DESC';
        }

        $filterSql = $this->getSQLFilter($helperList, $fieldsList);

        $sql = new DbQuery();
        $sql->select('`'.RecaptchaVisitor::$definition['primary'].'`, c.`firstname`, c.`lastname`, c.`email`, rc.`captcha_disabled`, rc.`captcha_failed_attempt`, rc.`captcha_attempts`');
        $sql->from(RecaptchaVisitor::$definition['table'], 'rc');
        $sql->rightJoin('customer', 'c', 'rc.`email` = c.`email`');
        $sql->orderBy('`'.bqSQL($helperList->orderBy).'` '.pSQL($helperList->orderWay));
        $sql->where('1 '.$filterSql);
        $sql->limit($pagination, $currentPage - 1);

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        foreach ($results as &$result) {
            if (!$result[RecaptchaVisitor::$definition['primary']]) {
                $visitor = new RecaptchaVisitor();
                $visitor->email = $result['email'];
                $visitor->captcha_disabled = false;
                $visitor->captcha_failed_attempt = '1970-01-01 00:00:00';
                $visitor->captcha_attempts = (int) Configuration::get(static::ATTEMPTS);

                $visitor->add();

                $result[RecaptchaVisitor::$definition['primary']] = $visitor->id;
                $result['captcha_disabled'] = $visitor->captcha_disabled;
                $result['captcha_failed_attempt'] = $visitor->captcha_failed_attempt;
                $result['captcha_attempts'] = $visitor->captcha_attempts;
            }
        }

        $helperList->listTotal = $listTotal;

        $helperList->identifier = bqSQL(RecaptchaVisitor::$definition['primary']);
        $helperList->title = $this->l('Customers');
        $helperList->token = Tools::getAdminTokenLite('AdminModules');
        $helperList->currentIndex = $this->moduleUrl.'&menu='.static::MENU_CUSTOMERS;

        $helperList->table = bqSQL(RecaptchaVisitor::$definition['table']);

        return $helperList->generateList($results, $fieldsList);
    }

    /**
     * Render Customer export form
     *
     * @return string Form HTML
     */
    protected function renderCustomerEditForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = RecaptchaVisitor::$definition['table'];
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name.'editcustomer';
        $helper->currentIndex = AdminController::$currentIndex.'&'.http_build_query(
                [
                    'configure' => $this->name,
                    'menu'      => static::MENU_CUSTOMERS,
                ]
            );

        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getCustomerValues((int) Tools::getValue(RecaptchaVisitor::$definition['primary'])),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getCustomerEditForm()]);
    }

    /**
     * @return array
     */
    protected function getCustomerEditForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Edit'),
                    'icon'  => 'icon-globe',
                ],
                'input'  => [
                    [
                        'type' => 'hidden',
                        'name' => RecaptchaVisitor::$definition['primary'],
                    ],
                    [
                        'type'     => 'text',
                        'label'    => Translate::getAdminTranslation('Last name'),
                        'name'     => 'firstname',
                        'disabled' => true,
                    ],
                    [
                        'type'     => 'text',
                        'label'    => Translate::getAdminTranslation('Last name'),
                        'name'     => 'lastname',
                        'disabled' => true,
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => 'Recaptcha '.Translate::getAdminTranslation('Disabled'),
                        'name'    => 'captcha_disabled',
                        'is_bool' => true,
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ],
                        ],
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];
    }

    /**
     * Set values for the inputs of the configuration form
     *
     * @param int $idNoCaptchaRecaptchaVisitor NoCaptchaRecaptchaVisitor ID
     *
     * @return array Array with current values
     */
    protected function getCustomerValues($idNoCaptchaRecaptchaVisitor)
    {
        $sql = new DbQuery();
        $sql->select('`'.bqSQL(RecaptchaVisitor::$definition['primary']).'`, c.`firstname`, c.`lastname`, rc.`captcha_disabled`');
        $sql->from(bqSQL(RecaptchaVisitor::$definition['table']), 'rc');
        $sql->leftJoin('customer', 'c', 'rc.`email` = c.`email`');
        $sql->where('`'.bqSQL(RecaptchaVisitor::$definition['primary']).'` = '.(int) $idNoCaptchaRecaptchaVisitor);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    /**
     * Render group recaptcha list
     *
     * @return string
     * @throws PrestaShopDatabaseException
     */
    protected function renderGroupList()
    {
        $fieldsList = [
            RecaptchaGroup::$definition['primary'] => ['title' => $this->l('ID'), 'width' => 40],
            'id_group'                             => ['title' => $this->l('Group ID'), 'width' => 'auto', 'filter_key' => 'rg!id_group'],
            'name'                                 => ['title' => $this->l('Name'), 'width' => 'auto'],
            'captcha_disabled'                     => ['title' => $this->l('Captcha disabled'), 'type' => 'bool', 'width' => 'auto', 'active' => 'captcha_disabled', 'ajax' => true],
        ];

        if (Tools::isSubmit('submitReset'.RecaptchaGroup::$definition['table'])) {
            $cookie = $this->context->cookie;
            $filterKeys = array_keys($fieldsList);
            foreach ($fieldsList as $item) {
                if (isset($item['filter_key'])) {
                    $filterKeys[] = $item['filter_key'];
                }
            }
            foreach ($filterKeys as $fieldName) {
                unset($cookie->{RecaptchaGroup::$definition['table'].'Filter_'.$fieldName});
                unset($_POST[RecaptchaGroup::$definition['table'].'Filter_'.$fieldName]);
                unset($_GET[RecaptchaGroup::$definition['table'].'Filter_'.$fieldName]);
            }
            unset($this->context->cookie->{RecaptchaGroup::$definition['table'].'Orderby'});
            unset($this->context->cookie->{RecaptchaGroup::$definition['table'].'OrderWay'});

            $cookie->write();
        }

        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from(RecaptchaGroup::$definition['table'], 'rg');
        $sql->innerJoin('group', 'g', 'g.`id_group` = rg.`id_group`');
        $sql->innerJoin('group_shop', 'gs', 'gs.`id_group` = g.`id_group`');
        $sql->where('gs.`id_shop` = '.(int) $this->getShopId());

        $listTotal = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        $pagination = (int) $this->getSelectedPagination(RecaptchaGroup::$definition['table']);
        $currentPage = (int) $this->getSelectedPage(RecaptchaGroup::$definition['table'], $listTotal);

        $helperList = new HelperList();
        $helperList->shopLinkType = false;

        $helperList->list_id = RecaptchaGroup::$definition['table'];

        $helperList->bulk_actions = [
            'enable_recaptcha'  => [
                'text' => $this->l('Enable recaptcha'),
                'icon' => 'icon-check',
            ],
            'disable_recaptcha' => [
                'text' => $this->l('Disable recaptcha'),
                'icon' => 'icon-times',
            ],
        ];

        $helperList->actions = ['Edit'];

        $helperList->page = $currentPage;

        $helperList->_defaultOrderBy = RecaptchaGroup::$definition['primary'];

        if (Tools::isSubmit(RecaptchaGroup::$definition['table'].'Orderby')) {
            $helperList->orderBy = Tools::getValue(RecaptchaGroup::$definition['table'].'Orderby');
            $this->context->cookie->{RecaptchaGroup::$definition['table'].'Orderby'} = $helperList->orderBy;
        } elseif (!empty($this->context->cookie->{RecaptchaGroup::$definition['table'].'Orderby'})) {
            $helperList->orderBy = $this->context->cookie->{RecaptchaGroup::$definition['table'].'Orderby'};
        } else {
            $helperList->orderBy = bqSQL(RecaptchaGroup::$definition['primary']);
        }

        if (Tools::isSubmit(RecaptchaGroup::$definition['table'].'Orderway')) {
            $helperList->orderWay = Tools::strtoupper(Tools::getValue(RecaptchaGroup::$definition['table'].'Orderway'));
            $this->context->cookie->{RecaptchaGroup::$definition['table'].'Orderway'} = Tools::getValue(RecaptchaGroup::$definition['table'].'Orderway');
        } elseif (!empty($this->context->cookie->{RecaptchaGroup::$definition['table'].'Orderway'})) {
            $helperList->orderWay = Tools::strtoupper($this->context->cookie->{RecaptchaGroup::$definition['table'].'Orderway'});
        } else {
            $helperList->orderWay = 'DESC';
        }

        if (!Validate::isOrderBy($helperList->orderBy)) {
            $helperList->orderBy = bqSQL(RecaptchaGroup::$definition['primary']);
        }
        if (!Validate::isOrderWay($helperList->orderWay)) {
            $helperList->orderWay = 'DESC';
        }

        $filterSql = $this->getSQLFilter($helperList, $fieldsList);

        $sql = new DbQuery();
        $sql->select('`'.RecaptchaGroup::$definition['primary'].'`, g.`id_group`, gl.`name`, rg.`captcha_disabled`');
        $sql->from(bqSQL(RecaptchaGroup::$definition['table']), 'rg');
        $sql->innerJoin('group', 'g', 'rg.`id_group` = g.`id_group`');
        $sql->innerJoin('group_lang', 'gl', 'gl.`id_group` = g.`id_group`');
        $sql->innerJoin('group_shop', 'gs', 'g.`id_group` = gs.`id_group`');
        $sql->orderBy('`'.$helperList->orderBy.'` '.$helperList->orderWay);
        $sql->where('gl.`id_lang` = '.(int) $this->context->language->id);
        $sql->where('gs.`id_shop` = '.(int) $this->getShopId().' '.$filterSql);
        $sql->limit($pagination, $currentPage - 1);

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $helperList->listTotal = $listTotal;

        $helperList->identifier = bqSQL(RecaptchaGroup::$definition['primary']);
        $helperList->title = $this->l('Groups');
        $helperList->token = Tools::getAdminTokenLite('AdminModules');
        $helperList->currentIndex = $this->moduleUrl.'&menu='.static::MENU_GROUPS;

        $helperList->table = bqSQL(RecaptchaGroup::$definition['table']);

        return $helperList->generateList($results, $fieldsList);
    }

    /**
     * Render Group form
     *
     * @return string Form HTML
     */
    protected function renderGroupEditForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = RecaptchaGroup::$definition['table'];
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name.'editgroup';
        $helper->currentIndex = AdminController::$currentIndex.'&'.http_build_query(
                [
                    'configure' => $this->name,
                    'menu'      => static::MENU_GROUPS,
                ]
            );

        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getGroupValues((int) Tools::getValue(RecaptchaGroup::$definition['primary'])),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getGroupEditForm()]);
    }

    /**
     * @return array
     */
    protected function getGroupEditForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Edit'),
                    'icon'  => 'icon-globe',
                ],
                'input'  => [
                    [
                        'type' => 'hidden',
                        'name' => RecaptchaGroup::$definition['primary'],
                    ],
                    [
                        'type'     => 'text',
                        'label'    => Translate::getAdminTranslation('Name'),
                        'name'     => 'name',
                        'disabled' => true,
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => 'Recaptcha '.Translate::getAdminTranslation('Disabled'),
                        'name'    => 'captcha_disabled',
                        'is_bool' => true,
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ],
                        ],
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];
    }

    /**
     * Set values for the inputs of the configuration form
     *
     * @param int $idNoCaptchaRecaptchaGroup NoCaptchaRecaptchaGroup ID
     *
     * @return array Array with current values
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function getGroupValues($idNoCaptchaRecaptchaGroup)
    {
        $sql = new DbQuery();
        $sql->select('`'.bqSQL(RecaptchaGroup::$definition['primary']).'`, rg.`id_group`, gl.`name`, rg.`captcha_disabled`');
        $sql->from(bqSQL(RecaptchaGroup::$definition['table']), 'rg');
        $sql->innerJoin('group', 'g', 'rg.`id_group` = g.`id_group`');
        $sql->innerJoin('group_lang', 'gl', 'gl.`id_group` = g.`id_group`');
        $sql->where('`'.bqSQL(RecaptchaGroup::$definition['primary']).'` = '.(int) $idNoCaptchaRecaptchaGroup);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    /**
     * @throws PrestaShopException
     */
    protected function syncGroups()
    {
        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ncrc_group` (`id_group`, `captcha_disabled`)
         SELECT g.`id_group` AS `id_group`, 0 AS `captcha_disabled`  FROM `'._DB_PREFIX_.'group` g
         LEFT OUTER JOIN `'._DB_PREFIX_.'ncrc_group` AS rg ON rg.`id_group` = g.`id_group`
         WHERE rg.`id_group` IS NULL;');
    }

    /**
     * Save form data.
     *
     * @return void
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    protected function postProcess()
    {
        if (Tools::getValue('ajax')) {
            $this->ajaxProcess();

            return;
        }

        switch (Tools::getValue('menu')) {
            case static::MENU_ADVANCED_SETTINGS:
                $this->postProcessAdvancedSettings();

                return;
            case static::MENU_CUSTOMERS:
                $this->postProcessCustomers();

                return;
            case static::MENU_GROUPS:
                $this->postProcessGroups();

                return;
            default:
                $this->postProcessSettings();

                return;
        }
    }

    /**
     * method call when ajax request is made with the customer row action
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function ajaxProcess()
    {
        if (Tools::isSubmit('captcha_disabledncrc_visitor') && Tools::getValue('action') === 'captcha_disabledncrc_visitor') {
            $idNoCaptchaRecaptchaVisitor = (int) Tools::getValue(RecaptchaVisitor::$definition['primary']);
            if (RecaptchaVisitor::toggle($idNoCaptchaRecaptchaVisitor)) {
                echo json_encode(
                    [
                        'success' => true,
                        'text'    => Translate::getAdminTranslation('Update successful', 'AdminAccess'),
                        'message' => Db::getInstance()->getMsgError(),
                    ]
                );
            } else {
                echo json_encode(
                    [
                        'success' => false,
                        'text'    => Translate::getAdminTranslation('Update error', 'AdminAccess'),
                        'message' => Db::getInstance()->getMsgError(),
                    ]
                );
            }
            die();
        }

        if (Tools::isSubmit('captcha_disabledncrc_group') && Tools::getValue('action') === 'captcha_disabledncrc_group') {
            $idNoCaptchaRecaptchaGroup = (int) Tools::getValue(RecaptchaGroup::$definition['primary']);
            if (RecaptchaGroup::toggle($idNoCaptchaRecaptchaGroup)) {
                echo json_encode(
                    [
                        'success' => true,
                        'text'    => Translate::getAdminTranslation('Update successful', 'AdminAccess'),
                        'message' => Db::getInstance()->getMsgError(),
                    ]
                );
            } else {
                echo json_encode(
                    [
                        'success' => false,
                        'text'    => Translate::getAdminTranslation('Update error', 'AdminAccess'),
                        'message' => Db::getInstance()->getMsgError(),
                    ]
                );
            }
            die();
        }

        if (Tools::isSubmit('method') && Tools::getValue('method') == 'confirm') {
            header('Content-Type: application/json');
            $secretKey = Tools::getValue('secretKey');
            if (empty($secretKey)) {
                die(json_encode(
                    [
                        'data' => [
                            'confirmed' => false,
                            'message'   => $this->l('No secret key found. Please enter the secret key first.'),
                        ],
                    ]
                ));
            }
            $response = Tools::getValue('recaptchaToken');

            if (empty($response)) {
                die(json_encode(
                    [
                        'data' => [
                            'confirmed' => false,
                            'message'   => $this->l('No captcha token found. Please try again.'),
                        ],
                    ]
                ));
            }

            $recaptchalib = new RecaptchaLib($secretKey);
            $resp = $recaptchalib->verifyResponse(Tools::getRemoteAddr(), $response);

            if ($resp == null || !($resp->success)) {
                if ($resp->error_codes[0] === 'invalid-input-secret') {
                    die(json_encode(
                        [
                            'data' => [
                                'confirmed' => false,
                                'message'   => Translate::getModuleTranslation(
                                    'NoCaptchaRecaptcha',
                                    'The reCAPTCHA secret key is invalid. Please contact the site administrator.',
                                    'configure'
                                ),
                            ],
                        ]
                    ));
                } elseif ($resp->error_codes[0] === 'google-no-contact') {
                    die(json_encode(
                        [
                            'data' => [
                                'confirmed' => false,
                                'message'   => Translate::getModuleTranslation(
                                    'NoCaptchaRecaptcha',
                                    'Unable to connect to Google in order to verify the captcha. Please check your server settings or contact your hosting provider.',
                                    'configure'
                                ),
                            ],
                        ]
                    ));
                } else {
                    die(json_encode(
                        [
                            'data' => [
                                'confirmed' => false,
                                'message'   => Translate::getModuleTranslation(
                                    'NoCaptchaRecaptcha',
                                    'Your captcha was wrong. Please try again.',
                                    'configure'
                                ),
                            ],
                        ]
                    ));
                }
            }
            die(json_encode(
                [
                    'data' => [
                        'confirmed' => true,
                    ],
                ]
            ));
        }

        echo json_encode(
            [
                'success' => false,
                'text'    => Translate::getAdminTranslation('Update error', 'AdminAccess'),
            ]
        );
        die();
    }

    /**
     * Post process settings form
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    protected function postProcessSettings()
    {
        if (Tools::isSubmit('submitNocaptcharecaptchaModule')) {
            $formValues = $this->getConfigFormValues();

            foreach (array_keys($formValues) as $key) {
                if (Tools::isSubmit($key)) {
                    if (($key == static::PUBLIC_KEY) && !Tools::getValue($key) ||
                        ($key == static::PRIVATE_KEY) && !Tools::getValue($key)
                    ) {
                        $this->uninstallOptionalOverrides();

                        $this->addError($this->l('Site key and secret are required!'), true);

                        return;
                    } else {
                        if ($key == static::ATTEMPTS) {
                            if (Validate::isInt(Tools::getValue($key))) {
                                Configuration::updateValue(static::ATTEMPTS, (int) Tools::getValue($key));
                                RecaptchaVisitor::resetAllAttempts((int) Tools::getValue($key));
                            } else {
                                $this->addError(sprintf($this->l('The %s field is invalid.'), 'login attempts'), true);

                                return;
                            }
                        } elseif ($key == static::ADMINLOGIN) {
                            $adminLogin = (bool) Tools::getValue(static::ADMINLOGIN);
                            if ($adminLogin != Configuration::get(static::ADMINLOGIN)) {
                                if ($adminLogin) {
                                    $this->installAdminLoginOverride();
                                } else {
                                    $this->uninstallAdminLoginOverride();
                                }
                            }
                        } elseif ($key == static::LOGIN || $key == static::CREATE) {
                            $this->manageAuthOverride(Tools::getValue(static::LOGIN), Tools::getValue(static::CREATE));
                        } elseif ($key == static::PASSWORD) {
                            $this->managePasswordOverride(Tools::getValue(static::PASSWORD));
                        } elseif ($key == static::CONTACT) {
                            $this->manageContactOverride(Tools::getValue(static::CONTACT));
                        } else {
                            Configuration::updateValue($key, Tools::getValue($key));
                        }
                    }
                }
            }

            if (empty($this->context->controller->errors)) {
                $this->addConfirmation($this->l('Settings successfully updated'));
            }
        }
    }

    /**
     * Post process advanced settings form
     *
     * @throws PrestaShopException
     */
    protected function postProcessAdvancedSettings()
    {
        if (Tools::isSubmit('submitRecaptchaAdvanced')) {
            if (Tools::isSubmit(static::LOGINSELECT)) {
                $loginSelect = Tools::getValue(static::LOGINSELECT);
                if (!empty($loginSelect)) {
                    Configuration::updateValue(static::LOGINSELECT, $loginSelect);
                } else {
                    $this->addError(sprintf($this->l('The %s jQuery selector is invalid'), $this->l('Login')), true);
                }
            }
            if (Tools::isSubmit(static::LOGINPOS)) {
                Configuration::updateValue(static::LOGINPOS, Tools::getValue(static::LOGINPOS));
            }
            if (Tools::isSubmit(static::LOGINHTML)) {
                $this->updateHTMLValue('loginCaptcha', 'login', $this->l('Login'), static::LOGINHTML);
            }
            if (Tools::isSubmit(static::CREATESELECT)) {
                $createSelect = Tools::getValue(static::CREATESELECT);
                if (!empty($createSelect)) {
                    Configuration::updateValue(static::CREATESELECT, $createSelect);
                } else {
                    $this->addError(sprintf($this->l('The %s jQuery selector is invalid'), $this->l('Register')), true);
                }
            }
            if (Tools::isSubmit(static::CREATEPOS)) {
                Configuration::updateValue(static::CREATEPOS, Tools::getValue(static::CREATEPOS));
            }
            if (Tools::isSubmit(static::CREATEHTML)) {
                $this->updateHTMLValue('createCaptcha', 'create', $this->l('Register'), static::CREATEHTML);
            }
            if (Tools::isSubmit(static::PASSWORDSELECT)) {
                $passwordSelect = Tools::getValue(static::PASSWORDSELECT);
                if (!empty($passwordSelect)) {
                    Configuration::updateValue(static::PASSWORDSELECT, $passwordSelect);
                } else {
                    $this->addError(sprintf($this->l('The %s jQuery selector is invalid'), $this->l('Password')), true);
                }
            }
            if (Tools::isSubmit(static::PASSWORDPOS)) {
                Configuration::updateValue(static::PASSWORDPOS, Tools::getValue(static::PASSWORDPOS));
            }
            if (Tools::isSubmit(static::PASSWORDHTML)) {
                $this->updateHTMLValue('passwordCaptcha', 'password', $this->l('Password'), static::PASSWORDHTML);
            }
            if (Tools::isSubmit(static::CONTACTSELECT)) {
                $contactSelect = Tools::getValue(static::CONTACTSELECT);
                if (!empty($contactSelect)) {
                    Configuration::updateValue(static::CONTACTSELECT, $contactSelect);
                } else {
                    $this->addError(sprintf($this->l('The %s jQuery selector is invalid'), $this->l('Contact')), true);
                }
            }
            if (Tools::isSubmit(static::CONTACTPOS)) {
                Configuration::updateValue(static::CONTACTPOS, Tools::getValue(static::CONTACTPOS));
            }
            if (Tools::isSubmit(static::CONTACTHTML)) {
                $this->updateHTMLValue('contactCaptcha', 'contact', $this->l('Contact'), static::CONTACTHTML);
            }
            if (Tools::isSubmit(static::OPCLOGINSELECT)) {
                $opcLoginSelect = Tools::getValue(static::OPCLOGINSELECT);
                if (!empty($opcLoginSelect)) {
                    Configuration::updateValue(static::OPCLOGINSELECT, $opcLoginSelect);
                } else {
                    $this->addError(sprintf($this->l('The %s jQuery selector is invalid'), $this->l('OPC Login')), true);
                }
            }
            if (Tools::isSubmit(static::OPCLOGINPOS)) {
                Configuration::updateValue(static::OPCLOGINPOS, Tools::getValue(static::OPCLOGINPOS));
            }
            if (Tools::isSubmit(static::OPCLOGINHTML)) {
                $this->updateHTMLValue('loginCaptcha', 'login', $this->l('OPC Login'), static::OPCLOGINHTML);
            }
            if (Tools::isSubmit(static::OPCCREATESELECT)) {
                $opcCreateSelect = Tools::getValue(static::OPCCREATESELECT);
                if (!empty($opcCreateSelect)) {
                    Configuration::updateValue(static::OPCCREATESELECT, $opcCreateSelect);
                } else {
                    $this->addError(sprintf($this->l('The %s jQuery selector is invalid'), $this->l('OPC Register')), true);
                }
            }
            if (Tools::isSubmit(static::OPCCREATEPOS)) {
                Configuration::updateValue(static::OPCCREATEPOS, Tools::getValue(static::OPCCREATEPOS));
            }
            if (Tools::isSubmit(static::OPCCREATEHTML)) {
                $this->updateHTMLValue('createCaptcha', 'create', $this->l('OPC Register'), static::OPCCREATEHTML);
            }
            if (Tools::isSubmit(static::EXTRACSS)) {
                $extraCss = Tools::getValue(static::EXTRACSS);
                if (!empty($extraCss)) {
                    if (!file_put_contents($this->local_path.'views/css/extra.css', $extraCss)) {
                        $this->addError($this->l('Couldn\'t save CSS file'), true);
                    }
                } elseif (file_exists($this->local_path.'views/css/extra.css')) {
                    if (!@unlink($this->local_path.'views/css/extra.css')) {
                        $this->addError($this->l('Couldn\'t clear CSS file'), true);
                    };
                }
            }

            if (empty($this->context->controller->errors)) {
                $this->addConfirmation($this->l('Advanced settings successfully updated'));
            }
        }
    }

    /**
     * Post process customer settings
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function postProcessCustomers()
    {
        if (Tools::isSubmit('submit'.$this->name.'editcustomer')) {
            if (Tools::getValue('captcha_disabled')) {
                RecaptchaVisitor::disableCaptchas([(int) Tools::getValue(RecaptchaVisitor::$definition['primary'])]);
            } else {
                RecaptchaVisitor::enableCaptchas([(int) Tools::getValue(RecaptchaVisitor::$definition['primary'])]);
            }
            $this->addConfirmation($this->l('The customer settings have been updated'));
        } elseif (Tools::isSubmit('submitBulkdisable_recaptcha'.RecaptchaVisitor::$definition['table'])) {
            RecaptchaVisitor::disableCaptchas(Tools::getValue(RecaptchaVisitor::$definition['table'].'Box'));
            $this->addConfirmation($this->l('The customers have been updated'));
        } elseif (Tools::isSubmit('submitBulkenable_recaptcha'.RecaptchaVisitor::$definition['table'])) {
            RecaptchaVisitor::enableCaptchas(Tools::getValue(RecaptchaVisitor::$definition['table'].'Box'));
            $this->addConfirmation($this->l('The customers have been updated'));
        } elseif (Tools::isSubmit('captcha_disabledncrc_visitor')) {
            if (RecaptchaVisitor::toggle(Tools::getValue(RecaptchaVisitor::$definition['primary']))) {
                $this->addConfirmation($this->l('The captcha status has been successfully toggled'));
            } else {
                $this->addError($this->l('Unable to toggle captcha status'));
            }
        }
    }

    /**
     * Post process group settings
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function postProcessGroups()
    {
        if (Tools::isSubmit('submit'.$this->name.'editgroup')) {
            if (Tools::getValue('captcha_disabled')) {
                RecaptchaGroup::disableCaptchas([(int) Tools::getValue(RecaptchaGroup::$definition['primary'])]);
            } else {
                RecaptchaGroup::enableCaptchas([(int) Tools::getValue(RecaptchaGroup::$definition['primary'])]);
            }
            $this->addConfirmation($this->l('The group settings have been updated'));
        } elseif (Tools::isSubmit('submitBulkdisable_recaptcha'.RecaptchaGroup::$definition['table'])) {
            RecaptchaGroup::disableCaptchas(Tools::getValue(RecaptchaGroup::$definition['table'].'Box'));
            $this->addConfirmation($this->l('The groups have been updated'));
        } elseif (Tools::isSubmit('submitBulkenable_recaptcha'.RecaptchaGroup::$definition['table'])) {
            RecaptchaGroup::enableCaptchas(Tools::getValue(RecaptchaGroup::$definition['table'].'Box'));
            $this->addConfirmation($this->l('The groups have been updated'));
        } elseif (Tools::isSubmit('captcha_disabledncrc_group')) {
            if (RecaptchaGroup::toggle(Tools::getValue(RecaptchaGroup::$definition['primary']))) {
                $this->addConfirmation($this->l('The captcha status has been successfully toggled'));
            } else {
                $this->addError($this->l('Unable to toggle captcha status'));
            }
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     *
     * @return string
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookHeader()
    {
        $this->context->controller->addJQuery();
        $this->context->controller->addCSS($this->_path.'views/css/extra.css');

        if (Configuration::get(static::PRIVATE_KEY) && Configuration::get(static::PUBLIC_KEY) &&
            (Configuration::get(static::LOGIN) ||
                Configuration::get(static::CREATE) ||
                Configuration::get(static::CONTACT) ||
                Configuration::get(static::PASSWORD))
        ) {
            $this->context->smarty->assign(
                [
                    static::PUBLIC_KEY => Configuration::get(static::PUBLIC_KEY),

                    static::LOGIN       => Configuration::get(static::LOGIN),
                    static::LOGIN_THEME => Configuration::get(static::LOGIN_THEME),
                    static::LOGINHTML   => Configuration::get(static::LOGINHTML),
                    static::LOGINSELECT => Configuration::get(static::LOGINSELECT),
                    static::LOGINPOS    => Configuration::get(static::LOGINPOS),

                    static::CREATE       => Configuration::get(static::CREATE),
                    static::CREATE_THEME => Configuration::get(static::CREATE_THEME),
                    static::CREATEHTML   => Configuration::get(static::CREATEHTML),
                    static::CREATESELECT => Configuration::get(static::CREATESELECT),
                    static::CREATEPOS    => Configuration::get(static::CREATEPOS),

                    static::PASSWORD       => Configuration::get(static::PASSWORD),
                    static::PASSWORD_THEME => Configuration::get(static::PASSWORD_THEME),
                    static::PASSWORDHTML   => Configuration::get(static::PASSWORDHTML),
                    static::PASSWORDSELECT => Configuration::get(static::PASSWORDSELECT),
                    static::PASSWORDPOS    => Configuration::get(static::PASSWORDPOS),

                    static::OPCLOGINHTML   => Configuration::get(static::OPCLOGINHTML),
                    static::OPCLOGINSELECT => Configuration::get(static::OPCLOGINSELECT),
                    static::OPCLOGINPOS    => Configuration::get(static::OPCLOGINPOS),

                    static::OPCCREATEHTML   => Configuration::get(static::OPCCREATEHTML),
                    static::OPCCREATESELECT => Configuration::get(static::OPCCREATESELECT),
                    static::OPCCREATEPOS    => Configuration::get(static::OPCCREATEPOS),

                    static::CONTACT       => Configuration::get(static::CONTACT),
                    static::CONTACT_THEME => Configuration::get(static::CONTACT_THEME),
                    static::CONTACTHTML   => Configuration::get(static::CONTACTHTML),
                    static::CONTACTSELECT => Configuration::get(static::CONTACTSELECT),
                    static::CONTACTPOS    => Configuration::get(static::CONTACTPOS),

                    static::PS15COMPAT                          => Configuration::get(static::PS15COMPAT),
                    static::GOOGLEIGNORE                        => Configuration::get(static::GOOGLEIGNORE),
                    'nocaptcharecaptcha_module_link'            => $this->context->link->getModuleLink($this->name, 'captchaenabled', [], true),
                    'nocaptcharecaptcha_guest_checkout_enabled' => (bool) Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
                    'nocaptcharecaptcha_show_at_start'          => !empty(Context::getContext()->cookie->email),
                    'nocaptcharecaptcha_lang_iso'               => Tools::strtoupper(Tools::substr(Context::getContext()->language->language_code, 0, 2)),
                ]
            );

            $this->context->controller->addJquery();

            if ('authentication' == @$this->context->controller->php_self) {
                return $this->display(__FILE__, 'views/templates/front/authcaptcha.tpl');
            } elseif ('contact' == @$this->context->controller->php_self && Configuration::get(static::CONTACT)) {
                return $this->display(__FILE__, 'views/templates/front/contactcaptcha.tpl');
            } elseif ('password' == @$this->context->controller->php_self && Configuration::get(static::PASSWORD)) {
                return $this->display(__FILE__, 'views/templates/front/passwordcaptcha.tpl');
            } elseif ('order-opc' == @$this->context->controller->php_self && Configuration::get(static::LOGIN)) {
                return $this->display(__FILE__, 'views/templates/front/standardopccaptcha.tpl');
            }
        }

        return '';
    }

    /**
     * Add JS to back office
     *
     * @return string HTML
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayBackOfficeHeader()
    {
        $output = '';
        if (trim(Tools::getValue('controller')) === 'AdminModules' &&
            trim(Tools::getValue('configure')) === 'nocaptcharecaptcha'
        ) {
            $this->context->controller->addJquery();
            $this->context->controller->addCSS($this->_path.'views/css/hopscotch.css');
            $this->context->controller->addJS($this->_path.'views/js/libs/hopscotch.js');
            $this->context->controller->addJS('https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.0/js.cookie.min.js');
            if (!Tools::isSubmit('menu') || (int) Tools::isSubmit('menu') === static::MENU_SETTINGS) {
                $output .= $this->display(__FILE__, 'views/templates/admin/introtour.tpl');
            }

            $output .= $this->display(__FILE__, 'views/templates/admin/loaddefaults.tpl');
        }

        return $output;
    }

    /**
     * Hook after auth succeeded to reset attempts
     *
     * @param array $params Those mysterious parameters
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionAuthentication($params)
    {
        if (Validate::isEmail($this->context->customer->email)) {
            $this->resetAttempt($this->context->customer->email, Configuration::get(static::ATTEMPTS));
        }
    }

    /**
     * Set datetime of failed attempt
     * Call to decrease attempt
     *
     * @param string $email The user's email address
     *
     * @return bool Whether the decrease succeeded
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function failedAttempt($email)
    {
        return RecaptchaVisitor::failedAttempt($email);
    }

    /**
     * Get last attempt datetime
     *
     * @param string $email The user's email address
     *
     * @return string MySQL Datetime (Y-m-d H:i:s)
     * @throws PrestaShopException
     */
    public function getLastAttempt($email)
    {
        if (Validate::isEmail($email)) {
            $sql = new DbQuery();
            $sql->select('c.`captcha_failed_attempt');
            $sql->from('customer');
            $sql->where('c.`email` = \''.pSQL($email).'\'');

            $date = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            if (Validate::isDate($date)) {
                return $date;
            }
        }

        return false;
    }

    /**
     * Reset attempts for email address
     *
     * @param string $email  The user's email address
     * @param int    $number Reset the amount of attempts to this number
     *
     * @return bool Whether the reset succeeded
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function resetAttempt($email, $number)
    {
        return RecaptchaVisitor::resetAttemptsByEmail($email, $number);
    }

    /**
     * Check whether a timeout has been configured
     *
     * @return bool
     * @throws PrestaShopException
     */
    public function hasTimeout()
    {
        $mins = (int) Configuration::get(static::ATTEMPTS_MINS);
        $hours = (int) Configuration::get(static::ATTEMPTS_HOURS);
        $days = (int) Configuration::get(static::ATTEMPTS_DAYS);

        return $mins + $hours + $days > 0;
    }

    /**
     * Return whether the email has attempts left
     *
     * @param string $email
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hasAttemptsLeft($email)
    {
        if (Validate::isEmail($email)) {
            // Check if attempts have been expired
            if ($this->attemptsExpired($email)) {
                $this->resetAttempt($email, Configuration::get(static::ATTEMPTS));

                return true;
            }

            // Check if attempts left
            $sql = new DbQuery();
            $sql->select('MAX(rc.`captcha_attempts`) as `captcha_attempts`');
            $sql->from(bqSQL(RecaptchaVisitor::$definition['table']), 'rc');
            $sql->where('rc.`email` = \''.pSQL($email).'\'');
            $val = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
            if (empty($val)) {
                $captchaAttempts = (int) Configuration::get(static::ATTEMPTS);
                Db::getInstance()->insert(
                    bqSQL(RecaptchaVisitor::$definition['table']),
                    [
                        'email'            => pSQL($email),
                        'captcha_disabled' => false,
                        'captcha_attempts' => (int) $captchaAttempts,
                        'failed_attempt'   => date('Y-m-d H:i:s'),
                    ]
                );

                return true;
            } elseif ((int) $val['captcha_attempts'] > 0) {
                return true;
            }

        }

        return false;
    }

    /**
     * Check if attempts need to be reset
     *
     * @param string $email The user's email address
     *
     * @return bool Whether the attempts need to be reset
     * @throws PrestaShopException
     */
    public function attemptsExpired($email)
    {
        // Do not reset attempts if there is no timeout defined
        if (!$this->hasTimeout()) {
            return false;
        }

        $mins = (int) Configuration::get(static::ATTEMPTS_MINS);
        $hours = (int) Configuration::get(static::ATTEMPTS_HOURS);
        $days = (int) Configuration::get(static::ATTEMPTS_DAYS);

        $deadline = new DateTime('NOW');
        $deadline->modify(sprintf('-%d minute%s', $mins, (($mins === 1) ? '' : 's')));
        $deadline->modify(sprintf('-%d hour%s', $hours, (($hours === 1) ? '' : 's')));
        $deadline->modify(sprintf('-%d day%s', $days, (($days === 1) ? '' : 's')));

        $sql = new DbQuery();
        $sql->select('count(rc.`email`)');
        $sql->from(bqSQL(RecaptchaVisitor::$definition['table']), 'rc');
        $sql->where('rc.`email` = \''.pSQL($email).'\'');
        $sql->where('rc.`captcha_failed_attempt` < \''.pSQL($deadline->format('Y-m-d H:i:s')).'\'');

        return (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * Return whether the email has exemption
     *
     * @param string $email The user's email address
     *
     * @return bool True if the user has exemption
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hasExemption($email)
    {
        if (!Validate::isEmail($email)) {
            return false;
        }
        // Check if customer is exempt
        $sql = new DbQuery();
        $sql->select('rc.`captcha_disabled`');
        $sql->from(bqSQL(RecaptchaVisitor::$definition['table']), 'rc');
        $sql->where('rc.`email` = \''.pSQL($email).'\'');
        $val = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (is_array($val) && isset($val['captcha_disabled'])) {
            if ($val['captcha_disabled']) {
                return true;
            }
        } else {
            if ((int) Configuration::get(static::ATTEMPTS) > 0) {
                return true;
            }
        }

        $sql = new DbQuery();
        $sql->select('g.`id_group`, rg.`captcha_disabled`');
        $sql->from('group', 'g');
        $sql->leftJoin('customer_group', 'cg', 'cg.`id_group` = g.`id_group`');
        $sql->innerJoin('customer', 'c', 'c.`id_customer` = cg.`id_customer`');
        $sql->leftJoin(bqSQL(RecaptchaGroup::$definition['table']), 'rg', 'rg.`id_group` = g.`id_group`');
        $sql->where('c.`email` = \''.pSQL($email).'\'');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $exempt = false;
        $outOfSync = false;
        foreach ($results as $result) {
            if (is_null($result['captcha_disabled'])) {
                $outOfSync = true;
            } elseif ($result['captcha_disabled']) {
                $exempt = true;
            }
        }

        if ($outOfSync) {
            $this->syncGroups();
        }

        return $exempt;
    }

    /**
     * Return whether the email needs a recaptcha
     *
     * @param string $type  Captcha type e.g. login, create, password, contact
     * @param string $email The user's email address
     *
     * @return bool Whether the user needs a captcha
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function needsCaptcha($type = 'login', $email = null)
    {
        if ($email && !Validate::isEmail($email)) {
            return false;
        }

        if (Configuration::get(static::PUBLIC_KEY) && Configuration::get(static::PRIVATE_KEY)) {
            switch ($type) {
                case 'login':
                    if (Configuration::get(static::LOGIN)) {
                        if ((int) Configuration::get(static::ATTEMPTS) === 0) {
                            return !$this->hasExemption($email);
                        }
                        if ($this->hasAttemptsLeft($email)) {
                            return false;
                        } else {
                            return !$this->hasExemption($email);
                        }
                    }
                    break;
                case 'register':
                    return (bool) Configuration::get(static::CREATE);
                case 'contact':
                    if (Configuration::get(static::CONTACT)) {
                        if ($this->hasExemption($email)) {
                            return false;
                        }
                        $cookieEmail = Context::getContext()->cookie->email;
                        if ($cookieEmail != '' &&
                            $email == $cookieEmail &&
                            Configuration::get(static::LOGGEDINDISABLE)
                        ) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                    break;
                case 'forgotpassword':
                    return (bool) Configuration::get(static::PASSWORD);
                case 'adminlogin':
                    return (bool) Configuration::get(static::ADMINLOGIN);
            }
        }

        return false;
    }

    /**
     * Detect Back Office settings
     *
     * @return array Error messages strings
     * @throws PrestaShopException
     */
    protected function detectBOSettings()
    {
        $langId = Context::getContext()->language->id;
        $output = [];
        if (Configuration::get('PS_DISABLE_OVERRIDES')) {
            $output[] = $this->l('Overrides are disabled. This module doesn\'t work without overrides. Go to').' "'.
                $this->getTabName('AdminTools', $langId).
                ' > '.
                $this->getTabName('AdminPerformance', $langId).
                '" '.$this->l('and make sure that the option').' "'.
                Translate::getAdminTranslation('Disable all overrides', 'AdminPerformance').
                '" '.$this->l('is set to').' "'.
                Translate::getAdminTranslation('No', 'AdminPerformance').
                '"'.$this->l('.').'<br />';
        }
        if (Configuration::get('PS_DISABLE_NON_NATIVE_MODULE')) {
            $output[] = $this->l('Non native modules such as this one are disabled. Go to').' "'.
                $this->getTabName('AdminTools', $langId).
                ' > '.
                $this->getTabName('AdminPerformance', $langId).
                '" '.$this->l('and make sure that the option').' "'.
                Translate::getAdminTranslation('Disable non PrestaShop modules', 'AdminPerformance').
                '" '.$this->l('is set to').' "'.
                Translate::getAdminTranslation('No', 'AdminPerformance').
                '"'.$this->l('.').'<br />';
        }

        return $output;
    }

    /**
     * Get Tab name from database
     *
     * @param $class string Class name of tab
     * @param $lang  int Language id
     *
     * @return string Returns the localized tab name
     * @throws PrestaShopException
     */
    protected function getTabName($class, $lang)
    {
        if ($class == null || $lang == null) {
            return '';
        }

        $sql = new DbQuery();
        $sql->select('tl.`name`');
        $sql->from('tab', 't');
        $sql->innerJoin('tab_lang', 'tl', 'tl.`id_tab` = t.`id_tab`');
        $sql->where('t.`class_name` = \''.pSQL($class).'\'');
        $sql->where('tl.`id_lang` = '.(int) $lang);


        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * Unescapes the HTML inside JSON
     *
     * Replaces \n and \t with newline and tab resp.
     *
     * @param $htmlString
     *
     * @return string  Unescaped HTML
     */
    protected function unescapeJsonHtml($htmlString)
    {
        return str_replace('\"', '"', str_replace('\n', "\r\n", str_replace('\t', "\t", $htmlString)));
    }

    /**
     * Add all methods in a module override to the override class
     *
     * @param string $classname
     *
     * @return bool
     * @throws Exception
     */
    protected function addOptionalOverride($classname, $source)
    {
        $autoload = Autoload::getInstance();

        $origPath = $path = $autoload->getClassPath($classname.'Core');
        if (!$path) {
            $path = 'modules'.DIRECTORY_SEPARATOR.$classname.DIRECTORY_SEPARATOR.$classname.'.php';
        }
        $pathOverride = $source;
        if (!file_exists($pathOverride)) {
            $this->addError($this->l('Source override file does not exist. Has the module been installed correctly?').$source, true);

            return false;
        } else {
            file_put_contents($pathOverride, preg_replace('#(\r\n|\r)#ism', "\n", file_get_contents($pathOverride)));
        }
        $patternEscapeCom = '#(^\s*?\/\/.*?\n|\/\*(?!\n\s+\* module:.*?\* date:.*?\* version:.*?\*\/).*?\*\/)#ism';
        // Check if there is already an override file, if not, we just need to copy the file
        if ($file = $autoload->getClassPath($classname)) {
            // Check if override file is writable
            $overridePath = _PS_ROOT_DIR_.'/'.$file;
            if ((!file_exists($overridePath) && !is_writable(dirname($overridePath))) || (file_exists($overridePath) && !is_writable($overridePath))) {
                $this->addError(sprintf($this->l('file (%s) not writable'), $overridePath), true);

                return false;
            }
            // Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
            do {
                $uniq = uniqid();
            } while (class_exists($classname.'OverrideOriginal_remove', false));
            // Make a reflection of the override class and the module override class
            $overrideFile = file($overridePath);
            if (empty($overrideFile)) {
                // class_index was out of sync, so we just create a new override on the fly
                $overrideFile = [
                    "<?php\n",
                    "class {$classname} extends {$classname}Core\n",
                    "{\n",
                    "}\n",
                ];
            }
            $overrideFile = array_diff($overrideFile, ["\n"]);
            eval(preg_replace(['#^\s*<\?(?:php)?#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'], [' ', 'class '.$classname.'OverrideOriginal'.$uniq], implode('', $overrideFile)));
            $overrideClass = new ReflectionClass($classname.'OverrideOriginal'.$uniq);
            $moduleFile = file($pathOverride);
            $moduleFile = array_diff($moduleFile, ["\n"]);
            eval(preg_replace(['#^\s*<\?(?:php)?#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'], [' ', 'class '.$classname.'Override'.$uniq], implode('', $moduleFile)));
            $moduleClass = new ReflectionClass($classname.'Override'.$uniq);
            // Check if none of the methods already exists in the override class
            foreach ($moduleClass->getMethods() as $method) {
                if ($overrideClass->hasMethod($method->getName())) {
                    $methodOverride = $overrideClass->getMethod($method->getName());
                    if (preg_match('/module: (.*)/ism', $overrideFile[$methodOverride->getStartLine() - 5], $name) && preg_match('/date: (.*)/ism', $overrideFile[$methodOverride->getStartLine() - 4], $date) && preg_match('/version: ([0-9.]+)/ism', $overrideFile[$methodOverride->getStartLine() - 3], $version)) {
                        if (trim($name[1]) !== $this->name) {
                            $this->addError(
                                sprintf(
                                    $this->l('The method %1$s in the class %2$s is already overridden by the module %3$s version %4$s at %5$s.'),
                                    $method->getName(),
                                    $classname,
                                    $name[1],
                                    $version[1],
                                    $date[1]
                                ),
                                true
                            );
                        }
                    }

                    return true;
                }
                $moduleFile = preg_replace('/((:?public|private|protected)\s+(static\s+)?function\s+(?:\b'.$method->getName().'\b))/ism', "/*\n    * module: ".$this->name."\n    * date: ".date('Y-m-d H:i:s')."\n    * version: ".$this->version."\n    */\n    $1", $moduleFile);
                if ($moduleFile === null) {
                    $this->addError(sprintf($this->l('Failed to override method %1$s in class %2$s.'), $method->getName(), $classname), true);

                    return false;
                }
            }
            // Check if none of the properties already exists in the override class
            foreach ($moduleClass->getProperties() as $property) {
                if ($overrideClass->hasProperty($property->getName())) {
                    $this->addError(sprintf($this->l('The property %1$s in the class %2$s is already defined.'), $property->getName(), $classname), true);

                    return false;
                }
                $moduleFile = preg_replace('/((?:public|private|protected)\s)\s*(static\s)?\s*(\$\b'.$property->getName().'\b)/ism', "/*\n    * module: ".$this->name."\n    * date: ".date('Y-m-d H:i:s')."\n    * version: ".$this->version."\n    */\n    $1$2$3", $moduleFile);
                if ($moduleFile === null) {
                    $this->addError(sprintf($this->l('Failed to override property %1$s in class %2$s.'), $property->getName(), $classname), true);

                    return false;
                }
            }
            foreach ($moduleClass->getConstants() as $constant => $value) {
                if ($overrideClass->hasConstant($constant)) {
                    $this->addError(sprintf($this->l('The constant %1$s in the class %2$s is already defined.'), $constant, $classname), true);

                    return false;
                }
                $moduleFile = preg_replace('/(const\s)\s*(\b'.$constant.'\b)/ism', "/*\n    * module: ".$this->name."\n    * date: ".date('Y-m-d H:i:s')."\n    * version: ".$this->version."\n    */\n    $1$2", $moduleFile);
                if ($moduleFile === null) {
                    $this->addError(sprintf($this->l('Failed to override constant %1$s in class %2$s.'), $constant, $classname), true);

                    return false;
                }
            }
            // Insert the methods from module override in override
            $copyFrom = array_slice($moduleFile, $moduleClass->getStartLine() + 1, $moduleClass->getEndLine() - $moduleClass->getStartLine() - 2);
            array_splice($overrideFile, $overrideClass->getEndLine() - 1, 0, $copyFrom);
            $code = implode('', $overrideFile);
            file_put_contents($overridePath, preg_replace($patternEscapeCom, '', $code));
        } else {
            $overrideSource = $pathOverride;
            $overrideDestination = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.$path;
            $dirName = dirname($overrideDestination);
            if (!$origPath && !is_dir($dirName)) {
                $oldumask = umask(0000);
                @mkdir($dirName, 0777);
                umask($oldumask);
            }
            if (!is_writable($dirName)) {
                $this->addError(sprintf($this->l('directory (%s) not writable'), $dirName), true);

                return false;
            }
            $moduleFile = file($overrideSource);
            $moduleFile = array_diff($moduleFile, ["\n"]);
            if ($origPath) {
                do {
                    $uniq = uniqid();
                } while (class_exists($classname.'OverrideOriginal_remove', false));
                eval(preg_replace(['#^\s*<\?(?:php)?#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'], [' ', 'class '.$classname.'Override'.$uniq], implode('', $moduleFile)));
                $moduleClass = new ReflectionClass($classname.'Override'.$uniq);
                // For each method found in the override, prepend a comment with the module name and version
                foreach ($moduleClass->getMethods() as $method) {
                    $moduleFile = preg_replace('/((:?public|private|protected)\s+(static\s+)?function\s+(?:\b'.$method->getName().'\b))/ism', "/*\n    * module: ".$this->name."\n    * date: ".date('Y-m-d H:i:s')."\n    * version: ".$this->version."\n    */\n    $1", $moduleFile);
                    if ($moduleFile === null) {
                        $this->addError(sprintf($this->l('Failed to override method %1$s in class %2$s.'), $method->getName(), $classname), true);

                        return false;
                    }
                }
                // Same loop for properties
                foreach ($moduleClass->getProperties() as $property) {
                    $moduleFile = preg_replace('/((?:public|private|protected)\s)\s*(static\s)?\s*(\$\b'.$property->getName().'\b)/ism', "/*\n    * module: ".$this->name."\n    * date: ".date('Y-m-d H:i:s')."\n    * version: ".$this->version."\n    */\n    $1$2$3", $moduleFile);
                    if ($moduleFile === null) {
                        $this->addError(sprintf($this->l('Failed to override property %1$s in class %2$s.'), $property->getName(), $classname), true);

                        return false;
                    }
                }
                // Same loop for constants
                foreach ($moduleClass->getConstants() as $constant => $value) {
                    $moduleFile = preg_replace('/(const\s)\s*(\b'.$constant.'\b)/ism', "/*\n    * module: ".$this->name."\n    * date: ".date('Y-m-d H:i:s')."\n    * version: ".$this->version."\n    */\n    $1$2", $moduleFile);
                    if ($moduleFile === null) {
                        $this->addError(sprintf($this->l('Failed to override constant %1$s in class %2$s.'), $constant, $classname), true);

                        return false;
                    }
                }
            }
            if (@!file_put_contents($overrideDestination, preg_replace($patternEscapeCom, '', $moduleFile))) {
                $this->addError($this->l('Couldn\'t install override(s). Make sure that the correct permissions are set on the folder /override.'), true);

                return false;
            }
            // Re-generate the class index
            $autoload->generateIndex();
        }

        return true;
    }

    /**
     * Remove optional override
     *
     * @param string $classname Class name
     * @param string $source    Source location of override
     *
     * @return bool
     */
    public function removeOptionalOverride($classname, $source)
    {
        $autoload = Autoload::getInstance();

        $origPath = $path = $autoload->getClassPath($classname.'Core');
        if (!$path) {
            $path = 'modules'.DIRECTORY_SEPARATOR.$classname.DIRECTORY_SEPARATOR.$classname.'.php';
        }
        $pathOverride = $source;
        $origPath = $path = $autoload->getClassPath($classname.'Core');
        if ($origPath && !$file = $autoload->getClassPath($classname)) {
            return true;
        } elseif (!$origPath && Module::getModuleIdByName($classname)) {
            $path = 'modules'.DIRECTORY_SEPARATOR.$classname.DIRECTORY_SEPARATOR.$classname.'.php';
        }
        // Check if override file is writable
        if ($origPath) {
            $overridePath = _PS_ROOT_DIR_.'/'.$file;
        } else {
            $overridePath = _PS_OVERRIDE_DIR_.$path;
        }
        if (!is_file($overridePath) || !is_writable($overridePath)) {
            $this->addError($this->l('Cannot uninstall override. Make sure that the folder /override has the correct permissions (e.g. 777)'), true);

            return false;
        }
        file_put_contents($overridePath, preg_replace('#(\r\n|\r)#ism', "\n", file_get_contents($overridePath)));
        if ($origPath) {
            // Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
            do {
                $uniq = uniqid();
            } while (class_exists($classname.'OverrideOriginal_remove', false));
            // Make a reflection of the override class and the module override class
            $overrideFile = file($overridePath);
            eval(preg_replace(['#^\s*<\?(?:php)?#', '#class\s+'.$classname.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'], [' ', 'class '.$classname.'OverrideOriginal_remove'.$uniq], implode('', $overrideFile)));
            $overrideClass = new ReflectionClass($classname.'OverrideOriginal_remove'.$uniq);
            $moduleFile = file($source);
            eval(preg_replace(['#^\s*<\?(?:php)?#', '#class\s+'.$classname.'(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'], [' ', 'class '.$classname.'Override_remove'.$uniq], implode('', $moduleFile)));
            $moduleClass = new ReflectionClass($classname.'Override_remove'.$uniq);
            // Remove methods from override file
            foreach ($moduleClass->getMethods() as $method) {
                if (!$overrideClass->hasMethod($method->getName())) {
                    continue;
                }
                $method = $overrideClass->getMethod($method->getName());
                $length = $method->getEndLine() - $method->getStartLine() + 1;
                $moduleMethod = $moduleClass->getMethod($method->getName());
                $moduleLength = $moduleMethod->getEndLine() - $moduleMethod->getStartLine() + 1;
                $overrideFileOrig = $overrideFile;
                $origContent = preg_replace('/\s/', '', implode('', array_splice($overrideFile, $method->getStartLine() - 1, $length, array_pad([], $length, '#--remove--#'))));
                $moduleContent = preg_replace('/\s/', '', implode('', array_splice($moduleFile, $moduleMethod->getStartLine() - 1, $length, array_pad([], $length, '#--remove--#'))));
                $replace = true;
                if (preg_match('/\* module: ('.$this->name.')/ism', $overrideFile[$method->getStartLine() - 5])) {
                    $overrideFile[$method->getStartLine() - 6] = $overrideFile[$method->getStartLine() - 5] = $overrideFile[$method->getStartLine() - 4] = $overrideFile[$method->getStartLine() - 3] = $overrideFile[$method->getStartLine() - 2] = '#--remove--#';
                    $replace = false;
                }
                if (md5($moduleContent) != md5($origContent) && $replace) {
                    $overrideFile = $overrideFileOrig;
                }
            }
            // Remove properties from override file
            foreach ($moduleClass->getProperties() as $property) {
                if (!$overrideClass->hasProperty($property->getName())) {
                    continue;
                }
                // Replace the declaration line by #--remove--#
                foreach ($overrideFile as $lineNumber => &$lineContent) {
                    if (preg_match('/(public|private|protected)\s+(static\s+)?(\$)?'.$property->getName().'/i', $lineContent)) {
                        if (preg_match('/\* module: ('.$this->name.')/ism', $overrideFile[$lineNumber - 4])) {
                            $overrideFile[$lineNumber - 5] = $overrideFile[$lineNumber - 4] = $overrideFile[$lineNumber - 3] = $overrideFile[$lineNumber - 2] = $overrideFile[$lineNumber - 1] = '#--remove--#';
                        }
                        $lineContent = '#--remove--#';
                        break;
                    }
                }
            }
            // Remove properties from override file
            foreach ($moduleClass->getConstants() as $constant => $value) {
                if (!$overrideClass->hasConstant($constant)) {
                    continue;
                }
                // Replace the declaration line by #--remove--#
                foreach ($overrideFile as $lineNumber => &$lineContent) {
                    if (preg_match('/(const)\s+(static\s+)?(\$)?'.$constant.'/i', $lineContent)) {
                        if (preg_match('/\* module: ('.$this->name.')/ism', $overrideFile[$lineNumber - 4])) {
                            $overrideFile[$lineNumber - 5] = $overrideFile[$lineNumber - 4] = $overrideFile[$lineNumber - 3] = $overrideFile[$lineNumber - 2] = $overrideFile[$lineNumber - 1] = '#--remove--#';
                        }
                        $lineContent = '#--remove--#';
                        break;
                    }
                }
            }
            $count = count($overrideFile);
            for ($i = 0; $i < $count; ++$i) {
                if (preg_match('/(^\s*\/\/.*)/i', $overrideFile[$i])) {
                    $overrideFile[$i] = '#--remove--#';
                } elseif (preg_match('/(^\s*\/\*)/i', $overrideFile[$i])) {
                    if (!preg_match('/(^\s*\* module:)/i', $overrideFile[$i + 1])
                        && !preg_match('/(^\s*\* date:)/i', $overrideFile[$i + 2])
                        && !preg_match('/(^\s*\* version:)/i', $overrideFile[$i + 3])
                        && !preg_match('/(^\s*\*\/)/i', $overrideFile[$i + 4])) {
                        for (; $overrideFile[$i] && !preg_match('/(.*?\*\/)/i', $overrideFile[$i]); ++$i) {
                            $overrideFile[$i] = '#--remove--#';
                        }
                        $overrideFile[$i] = '#--remove--#';
                    }
                }
            }
            // Rewrite nice code
            $code = '';
            foreach ($overrideFile as $line) {
                if ($line == '#--remove--#') {
                    continue;
                }
                $code .= $line;
            }
            $toDelete = preg_match('/<\?(?:php)?\s+(?:abstract|interface)?\s*?class\s+'.$classname.'\s+extends\s+'.$classname.'Core\s*?[{]\s*?[}]/ism', $code);
        }
        if (!isset($toDelete) || $toDelete) {
            if (@!unlink($overridePath)) {
                $this->addError($this->l('Couldn\'t remove override(s). Make sure that the correct permissions are set on the folder /override and its contents.'), true);

                return false;
            }
        } else {
            if (@!file_put_contents($overridePath, $code)) {
                $this->addError($this->l('Couldn\'t remove override(s). Make sure that the correct permissions are set on the folder /override and its contents.'), true);

                return false;
            }
        }
        // Re-generate the class index
        $autoload->generateIndex();

        return true;
    }

    /**
     * Update configuration value in ALL contexts
     *
     * @param string $key    Configuration key
     * @param mixed  $values Configuration values, can be string or array with id_lang as key
     *
     * @param bool   $html
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function updateAllValue($key, $values, $html = false)
    {
        foreach (Shop::getShops() as $shop) {
            Configuration::updateValue($key, $values, $html, $shop['id_shop_group'], $shop['id_shop']);
        }
        Configuration::updateGlobalValue($key, $values, $html);
    }

    /**
     * Find the captcha placeholder
     *
     * @param string $html Input HTML
     * @param string $id   The id which the div or span MUST have
     *
     * @return int The amount of placeholders found
     */
    protected function findCaptchaPlaceholder($html, $id)
    {
        $html = str_get_html($html);

        return (int) count($html->find('div#'.$id)) + (int) count($html->find('span#'.$id));
    }

    /**
     * Update the configuration for the current shop context and check the amount of placeholders
     *
     * @param int    $placeholders Amount of placeholders
     * @param string $type         Captcha type
     *
     * @return string Error messages
     */
    protected function checkPlaceHolderAmount($placeholders, $id, $type)
    {
        $output = '';
        if ($placeholders < 1) {
            $this->addError(sprintf($this->l('The attribute "id" with value "%s" is missing from the %s captcha placeholder'), $id, $type), true);
        } elseif ($placeholders > 1) {
            $this->addError(sprintf($this->l('There are too many placeholders for the %s captcha'), $type), true);
        }

        return $output;
    }

    /**
     * Find the captcha placeholder
     *
     * @param string $html Input HTML
     * @param string $type The captcha type: login, create, password, contact
     *
     * @return int The amount of placeholders found
     */
    protected function findHideMeElement($html, $type)
    {
        $html = str_get_html($html);

        return (int) count($html->find('.'.$type.'-captcha-hideme'));
    }

    /**
     * Check if the hideme class can be found
     *
     * @param string $html Input HTML
     * @param string $type The captcha type: login, create, password, contact
     *
     * @return bool Indicates whether there was an error
     */
    protected function checkHideMeElement($elements, $type)
    {
        if ($elements < 1) {
            $this->addError(sprintf($this->l('The attribute "class" with value "%s" is missing from the %s captcha placeholder'), $type.'-captcha-hideme', $type), true);

            return true;
        }

        return false;
    }

    /**
     * Update captcha HTML
     *
     * @param string $id       Mandatory captcha ID
     * @param string $type     Captcha type
     * @param string $typeLang Captcha type (translated)
     * @param string $key      Configuration key
     *
     * @return void
     * @throws PrestaShopException
     */
    protected function updateHTMLValue($id, $type, $typeLang, $key)
    {
        $html = Tools::getValue($key);
        if (empty($html) || empty($key) || empty($id) || empty($typeLang) || empty($id)) {
            $this->addError(sprintf($this->l('There is no HTML for the %s captcha'), $typeLang), true);

            return;
        }
        $placeholders = $this->findCaptchaPlaceholder(Tools::getValue($key), $id);
        $hideme = $this->findHideMeElement(Tools::getValue($key), $type);

        $placeholdersErrors = $this->checkPlaceHolderAmount($placeholders, $id, $this->l($type));
        $hidemeErrors = $this->checkHideMeElement($hideme, $type);

        if (empty($placeholdersErrors) && empty($hidemeErrors)) {
            Configuration::updateValue($key, $html, true);
        }
    }

    /**
     * @param int $listId            List ID
     * @param int $defaultPagination Default pagination value
     *
     * @return mixed
     */
    protected function getSelectedPagination($listId, $defaultPagination = 50)
    {
        $selectedPagination = Tools::getValue(
            $listId.'_pagination',
            isset($this->context->cookie->{$listId.'_pagination'}) ? $this->context->cookie->{$listId.'_pagination'} : $defaultPagination
        );

        return $selectedPagination;
    }

    /**
     * @param int $listId    List ID
     * @param int $listTotal Total on list
     *
     * @return int|mixed
     */
    protected function getSelectedPage($listId, $listTotal)
    {
        /* Determine current page number */
        $page = (int) Tools::getValue('submitFilter'.$listId);

        if (!$page) {
            $page = 1;
        }

        $totalPages = max(1, ceil($listTotal / $this->getSelectedPagination($listId)));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $this->page = (int) $page;

        return $page;
    }

    /**
     * @param $helperList
     * @param $fieldsList
     *
     * @return array|string
     * @throws ReflectionException
     */
    protected function getSQLFilter($helperList, $fieldsList)
    {
        /** @var HelperList $helperList */
        if (!isset($helperList->list_id)) {
            $helperList->list_id = $helperList->table;
        }

        $prefix = '';
        $sqlFilter = '';

        if (isset($helperList->list_id)) {
            foreach ($_POST as $key => $value) {
                if ($value === '') {
                    unset($helperList->context->cookie->{$prefix.$key});
                } elseif (stripos($key, $helperList->list_id.'Filter_') === 0) {
                    $helperList->context->cookie->{$prefix.$key} = !is_array($value) ? $value : serialize($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $helperList->context->cookie->$key = !is_array($value) ? $value : serialize($value);
                }
            }

            foreach ($_GET as $key => $value) {
                if (stripos($key, $helperList->list_id.'Filter_') === 0) {
                    $helperList->context->cookie->{$prefix.$key} = !is_array($value) ? $value : serialize($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $helperList->context->cookie->$key = !is_array($value) ? $value : serialize($value);
                }
                if (stripos($key, $helperList->list_id.'Orderby') === 0 && Validate::isOrderBy($value)) {
                    if ($value === '' || $value == $helperList->_defaultOrderBy) {
                        unset($helperList->context->cookie->{$prefix.$key});
                    } else {
                        $helperList->context->cookie->{$prefix.$key} = $value;
                    }
                } elseif (stripos($key, $helperList->list_id.'Orderway') === 0 && Validate::isOrderWay($value)) {
                    if ($value === '' || (isset($helperList->_defaultOrderWay) && $value == $helperList->_defaultOrderWay)) {
                        unset($helperList->context->cookie->{$prefix.$key});
                    } else {
                        $helperList->context->cookie->{$prefix.$key} = $value;
                    }
                }
            }
        }

        $filters = $helperList->context->cookie->getFamily($prefix.$helperList->list_id.'Filter_');
        $definition = false;
        if (isset($helperList->className) && $helperList->className) {
            $definition = ObjectModel::getDefinition($helperList->className);
        }

        foreach ($filters as $key => $value) {
            /* Extracting filters from $_POST on key filter_ */
            if ($value != null && !strncmp($key, $prefix.$helperList->list_id.'Filter_', 7 + Tools::strlen($prefix.$helperList->list_id))) {
                $key = Tools::substr($key, 7 + Tools::strlen($prefix.$helperList->list_id));
                /* Table alias could be specified using a ! eg. alias!field */
                $tempTab = explode('!', $key);
                $filter = count($tempTab) > 1 ? $tempTab[1] : $tempTab[0];

                if ($field = $this->filterToField($fieldsList, $key, $filter)) {
                    $type = (array_key_exists('filter_type', $field) ? $field['filter_type'] : (array_key_exists('type', $field) ? $field['type'] : false));
                    if (($type == 'date' || $type == 'datetime') && is_string($value)) {
                        $value = Tools::unSerialize($value);
                    }
                    $key = isset($tempTab[1]) ? $tempTab[0].'.`'.$tempTab[1].'`' : '`'.$tempTab[0].'`';

                    /* Only for date filtering (from, to) */
                    if (is_array($value)) {
                        if (isset($value[0]) && !empty($value[0])) {
                            if (!Validate::isDate($value[0])) {
                                return $this->displayError('The \'From\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sqlFilter .= ' AND '.pSQL($key).' >= \''.pSQL(Tools::dateFrom($value[0])).'\'';
                            }
                        }

                        if (isset($value[1]) && !empty($value[1])) {
                            if (!Validate::isDate($value[1])) {
                                return $this->displayError('The \'To\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sqlFilter .= ' AND '.pSQL($key).' <= \''.pSQL(Tools::dateTo($value[1])).'\'';
                            }
                        }
                    } else {
                        $sqlFilter .= ' AND ';
                        $checkKey = ($key == $helperList->identifier || $key == '`'.$helperList->identifier.'`');
                        $alias = ($definition && !empty($definition['fields'][$filter]['shop'])) ? 'sa' : 'a';

                        if ($type == 'int' || $type == 'bool') {
                            $sqlFilter .= (($checkKey || $key == '`active`') ? $alias.'.' : '').pSQL($key).' = '.(int) $value.' ';
                        } elseif ($type == 'decimal') {
                            $sqlFilter .= ($checkKey ? $alias.'.' : '').pSQL($key).' = '.(float) $value.' ';
                        } elseif ($type == 'select') {
                            $sqlFilter .= ($checkKey ? $alias.'.' : '').pSQL($key).' = \''.pSQL($value).'\' ';
                        } elseif ($type == 'price') {
                            $value = (float) str_replace(',', '.', $value);
                            $sqlFilter .= ($checkKey ? $alias.'.' : '').pSQL($key).' = '.pSQL(trim($value)).' ';
                        } else {
                            $sqlFilter .= ($checkKey ? $alias.'.' : '').pSQL($key).' LIKE \'%'.pSQL(trim($value)).'%\' ';
                        }
                    }

                }
            }
        }

        return $sqlFilter;
    }

    /**
     * @param $fieldsList
     * @param $key
     * @param $filter
     *
     * @return bool
     */
    protected function filterToField($fieldsList, $key, $filter)
    {
        foreach ($fieldsList as $field) {
            if (array_key_exists('filter_key', $field) && $field['filter_key'] == $key) {
                return $field;
            }
        }
        if (array_key_exists($filter, $fieldsList)) {
            return $fieldsList[$filter];
        }

        return false;
    }

    /**
     * Add information message
     *
     * @param string $message Message
     * @param bool   $private
     */
    protected function addInformation($message, $private = true)
    {
        if (!Tools::isSubmit('configure')) {
            if (!$private) {
                $this->context->controller->informations[] = '<a href="'.$this->moduleUrl.'">'.$this->displayName.': '.$message.'</a>';
            }
        } else {
            $this->context->controller->informations[] = $message;
        }
    }

    /**
     * Add confirmation message
     *
     * @param string $message Message
     * @param bool   $private
     */
    protected function addConfirmation($message, $private = true)
    {
        if (!Tools::isSubmit('configure')) {
            if (!$private) {
                $this->context->controller->confirmations[] = '<a href="'.$this->moduleUrl.'">'.$this->displayName.': '.$message.'</a>';
            }
        } else {
            $this->context->controller->confirmations[] = $message;
        }
    }

    /**
     * Add warning message
     *
     * @param string $message Message
     * @param bool   $private
     */
    protected function addWarning($message, $private = true)
    {
        if (!Tools::isSubmit('configure')) {
            if (!$private) {
                $this->context->controller->warnings[] = '<a href="'.$this->moduleUrl.'">'.$this->displayName.': '.$message.'</a>';
            }
        } else {
            $this->context->controller->warnings[] = $message;
        }
    }

    /**
     * Add error message
     *
     * @param string $message Message
     */
    protected function addError($message, $private = true)
    {
        if (!Tools::isSubmit('configure')) {
            if (!$private) {
                $this->context->controller->errors[] = '<a href="'.$this->moduleUrl.'">'.$this->displayName.': '.$message.'</a>';
            }
        } else {
            $this->context->controller->errors[] = $message;
        }
    }

    /**
     * Check if the hooks of this module are hooked properly
     *
     * Recommended to run on config page
     *
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function checkHooks()
    {
        // Retrieve all Shop IDs
        $shopIds = array_keys(Shop::getShops(false));
        if (empty($shopIds)) {
            return;
        }
        foreach ($shopIds as &$shopId) {
            $shopId = (int) $shopId;
        }

        // Check after authentication hook
        $afterAuthId = (int) Hook::getIdByName('actionAuthentication');
        if ($afterAuthId) {
            $sql = new DbQuery();
            $sql->select('COUNT(`id_module`)');
            $sql->from('hook_module');
            $sql->where('`id_hook` = '.(int) $afterAuthId);
            $sql->where('`id_module` = '.(int) $this->id);
            $sql->where('`id_shop` IN ('.implode(',', $shopIds).')');

            $count = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            if (count($shopIds) != $count) {
                $this->registerHook('actionAuthentication');
            }
        } else {
            $this->registerHook('actionAuthentication');
            $this->addError(sprintf($this->l('Cannot find hook %s. This module might not work properly.'), 'actionAuthentication'), true);
        }
    }

    /**
     * Get the Shop ID of the current context
     * Retrieves the Shop ID from the cookie
     *
     * @return int Shop ID
     */
    public function getShopId()
    {
        if (isset(Context::getContext()->employee->id) && Context::getContext()->employee->id && Shop::getContext() == Shop::CONTEXT_SHOP) {
            $cookie = Context::getContext()->cookie->getFamily('shopContext');

            return (int) substr($cookie['shopContext'], 2, count($cookie['shopContext']));
        }

        return (int) Context::getContext()->shop->id;
    }
}
