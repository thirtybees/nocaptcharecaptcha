<?php

class ContactController extends ContactControllerCore
{
    public function postProcess()
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return;
        }

        if (!Module::isEnabled('NoCaptchaRecaptcha')
            || !@filemtime(_PS_MODULE_DIR_.'nocaptcharecaptcha/nocaptcharecaptcha.php')
        ) {
            return parent::postProcess();
        }

        require_once _PS_MODULE_DIR_.'nocaptcharecaptcha/nocaptcharecaptcha.php';
        $recaptcha = new NoCaptchaRecaptcha();
        if (Tools::isSubmit('submitMessage') && $recaptcha->needsCaptcha('contact', trim(Tools::getValue('from')))) {
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
                $this->context->smarty->assign('authentification_error', $this->errors);

                return;
            }
        }

        return parent::postProcess();
    }
}
