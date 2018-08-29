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

class AuthController extends AuthControllerCore
{
    public function processSubmitLogin()
    {
        if (!Module::isEnabled('NoCaptchaRecaptcha')
            || !@filemtime(_PS_MODULE_DIR_.'nocaptcharecaptcha/nocaptcharecaptcha.php')
        ) {
            return parent::processSubmitLogin();
        }

        require_once _PS_MODULE_DIR_.'nocaptcharecaptcha/nocaptcharecaptcha.php';
        $recaptcha = new NoCaptchaRecaptcha();
        $email = trim(Tools::getValue('email'));
        if ($recaptcha->needsCaptcha('login', $email)) {
            $recaptchalib = new NoCaptchaRecaptchaModule\RecaptchaLib(Configuration::get('NCRC_PRIVATE_KEY'));
            $resp = $recaptchalib->verifyResponse(Tools::getRemoteAddr(), Tools::getValue('g-recaptcha-response'));

            if ($resp == null || !($resp->success)) {
                if ($resp->error_codes[0] === 'invalid-input-secret') {
                    $this->errors[] = Tools::displayError(
                        Translate::getModuleTranslation(
                            'NoCaptchaRecaptcha',
                            'The reCAPTCHA secret key is invalid. Please contact the site administrator.',
                            'configure'
                        )
                    );
                } elseif ($resp->error_codes[0] === 'google-no-contact') {
                    if (!Configuration::get('NCRC_GOOGLEIGNORE')) {
                        $this->errors[] = Tools::displayError(
                            Translate::getModuleTranslation(
                                'NoCaptchaRecaptcha',
                                'Unable to connect to Google in order to verify the captcha. Please check your server settings or contact your hosting provider.',
                                'configure'
                            )
                        );
                    }
                } else {
                    $this->errors[] = Tools::displayError(
                        Translate::getModuleTranslation(
                            'NoCaptchaRecaptcha',
                            'Your captcha was wrong. Please try again.',
                            'configure'
                        )
                    );
                }
                if ($this->ajax && !empty($this->errors)) {
                    $return = [
                        'hasError' => !empty($this->errors),
                        'errors' => $this->errors,
                        'token' => Tools::getToken(false),
                    ];
                    die(json_encode($return));
                }
                $this->context->smarty->assign('authentification_error', $this->errors);

                return;
            }
        }

        parent::processSubmitLogin();

        if (empty($this->context->cookie->id_customer)) {
            $recaptcha->failedAttempt($email);
        } else {
            $recaptcha->resetAttempt($email, Configuration::get('NCRC_ATTEMPTS'));
        }

        return;
    }

    public function processSubmitAccount()
    {
        if (!Module::isEnabled('NoCaptchaRecaptcha')) {
            return parent::processSubmitAccount();
        }

        require_once _PS_MODULE_DIR_.'nocaptcharecaptcha/nocaptcharecaptcha.php';
        $recaptcha = new NoCaptchaRecaptcha();
        if ($recaptcha->needsCaptcha('register')) {
            $recaptchalib = new NoCaptchaRecaptchaModule\RecaptchaLib(Configuration::get('NCRC_PRIVATE_KEY'));
            $resp = $recaptchalib->verifyResponse(Tools::getRemoteAddr(), Tools::getValue('g-recaptcha-response'));

            if ($resp == null || !($resp->success)) {
                $resp = $recaptchalib->verifyResponse(
                    $_SERVER['REMOTE_ADDR'],
                    Tools::getValue('g-recaptcha-guestworkaround')
                );
                if ($resp == null || !($resp->success)) {
                    if ($resp->error_codes[0] === 'invalid-input-secret') {
                        $this->errors[] = Tools::displayError(
                            Translate::getModuleTranslation(
                                'NoCaptchaRecaptcha',
                                'The reCAPTCHA secret key is invalid. Please contact the site administrator.',
                                'configure'
                            )
                        );
                    } elseif ($resp->error_codes[0] === 'google-no-contact') {
                        if (!Configuration::get('NCRC_GOOGLEIGNORE')) {
                            $this->errors[] = Tools::displayError(
                                Translate::getModuleTranslation(
                                    'NoCaptchaRecaptcha',
                                    'Unable to connect to Google in order to verify the captcha. Please check your server settings or contact your hosting provider.',
                                    'configure'
                                )
                            );
                        }
                    } else {
                        $this->errors[] = Tools::displayError(
                            Translate::getModuleTranslation(
                                'NoCaptchaRecaptcha',
                                'Your captcha was wrong. Please try again.',
                                'configure'
                            )
                        );
                    }
                    if ($this->ajax && !empty($this->errors)) {
                        $return = [
                            'hasError' => !empty($this->errors),
                            'errors' => $this->errors,
                            'token' => Tools::getToken(false),
                        ];
                        die(json_encode($return));
                    }
                }
            }
        }

        return parent::processSubmitAccount();
    }
}
