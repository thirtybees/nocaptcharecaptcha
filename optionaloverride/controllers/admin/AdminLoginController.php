<?php

class AdminLoginController extends AdminLoginControllerCore
{
    public function processLogin()
    {
        if (!Module::isEnabled('NoCaptchaRecaptcha')
            || !@filemtime(_PS_MODULE_DIR_.'nocaptcharecaptcha/nocaptcharecaptcha.php')
        ) {
            return parent::processLogin();
        }

        require_once _PS_MODULE_DIR_.'nocaptcharecaptcha/nocaptcharecaptcha.php';
        $recaptcha = new NoCaptchaRecaptcha();
        if ($recaptcha->needsCaptcha('adminlogin', trim(Tools::getValue('from')))) {
            $recaptcha = new NoCaptchaRecaptchaModule\RecaptchaLib(Configuration::get('NCRC_PRIVATE_KEY'));
            $resp = $recaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], Tools::getValue('g-recaptcha-response'));
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
            }
        }

        return parent::processLogin();
    }

    public function processForgot()
    {
        if (!Module::isEnabled('NoCaptchaRecaptcha')) {
            return parent::processForgot();
        }

        require_once _PS_MODULE_DIR_.'nocaptcharecaptcha/nocaptcharecaptcha.php';
        $recaptcha = new NoCaptchaRecaptcha();
        if ($recaptcha->needsCaptcha('adminlogin', trim(Tools::getValue('from')))) {
            $recaptcha = new NoCaptchaRecaptchaModule\RecaptchaLib(Configuration::get('NCRC_PRIVATE_KEY'));
            $resp = $recaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], Tools::getValue('g-recaptcha-response'));
            if ($resp == null || !($resp->success)) {
                if ($resp->error_codes[0] === 'invalid-input-secret') {
                    $this->errors[] = Tools::displayError(
                        Translate::getModuleTranslation(
                            'NoCaptchaRecaptcha',
                            'The reCAPTCHA secret key is invalid. Please contact the site administrator.',
                            'configure'
                        )
                    );
                } else {
                    $this->errors[] = Tools::displayError(
                        Translate::getModuleTranslation(
                            'NoCaptchaRecaptcha',
                            'Your captcha was wrong. Please try again.',
                            'configure'
                        )
                    );
                }
            }
        }

        return parent::processForgot();
    }
}
