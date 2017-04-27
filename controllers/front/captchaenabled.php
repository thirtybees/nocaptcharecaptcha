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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class NocaptcharecaptchaCaptchaenabledModuleFrontController
 */
class NocaptcharecaptchaCaptchaenabledModuleFrontController extends ModuleFrontController
{
    /**
     * NocaptcharecaptchaCaptchaenabledModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->ssl = Tools::usingSecureMode();
    }

    /**
     * Initialize content
     */
    public function initContent()
    {
        parent::initContent();

        $this->ajax = true;
    }

    /**
     * Display Ajax response
     */
    public function displayAjax()
    {
        $recaptcha = new NoCaptchaRecaptcha();
        if ($this->errors) {
            die(json_encode(['hasError' => true, 'errors' => $this->errors]));
        } else {
            switch (Tools::getValue('method')) {
                case 'getCaptchaEnabled':
                    switch (Tools::getValue('type')) {
                        case 'custlogin':
                            $email = trim(Tools::getValue('email'));
                            header('Content-Type: application/json');
                            die(json_encode(
                                [
                                'email' => $email,
                                'captchaEnabled' => $recaptcha->needsCaptcha('login', $email),
                                ]
                            ));
                        case 'contact':
                            $email = trim(Tools::getValue('email'));
                            header('Content-Type: application/json');
                            die(json_encode(
                                [
                                'email' => $email,
                                'captchaEnabled' => $recaptcha->needsCaptcha('contact', $email),
                                ]
                            ));
                        default:
                            exit;
                    }
                    break;
                default:
                    exit;
            }
        }
    }
}
