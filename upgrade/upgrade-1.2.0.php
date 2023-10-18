<?php

/**
 * @param NoCaptchaRecaptcha $module
 *
 * @return bool
 * @throws PrestaShopException
 */
function upgrade_module_1_2_0($module)
{
    $module->registerHook('actionRegisterCaptcha');
    return true;
}