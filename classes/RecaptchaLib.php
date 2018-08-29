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

/**
 * This is a PHP library that handles calling reCAPTCHA.
 *
 * @link      http://www.google.com/recaptcha
 * @author    Google, Inc.
 * @copyright Copyright (c) 2014, Google Inc.
 * @license   public domain
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Documentation and latest version
 * https://developers.google.com/recaptcha/docs/php
 * Get a reCAPTCHA API Key
 * https://www.google.com/recaptcha/admin/create
 * Discussion group
 * http://groups.google.com/group/recaptcha
 */

namespace NoCaptchaRecaptchaModule;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

if (!defined('_TB_VERSION_')) {
    exit;
}

class RecaptchaLib
{
    private static $signup_url = 'https://www.google.com/recaptcha/admin';
    private static $siteVerifyUrl =
        'https://www.google.com/recaptcha/api/siteverify?';
    private static $version = 'php_1.0';
    private $secret;

    /**
     * Constructor.
     *
     * @param string $secret shared secret between site and ReCAPTCHA server.
     */
    public function __construct($secret)
    {
        if ($secret == null || $secret == '') {
            die(
                "To use reCAPTCHA you must get an API key from <a href='"
                .self::$signup_url."'>".self::$signup_url.'</a>'
            );
        }
        $this->secret = $secret;
    }

    /**
     * Calls the reCAPTCHA siteverify API to verify whether the user passes
     * CAPTCHA test.
     *
     * @param string $remoteIp IP address of end user.
     * @param string $response response string from recaptcha verification.
     *
     * @return RecaptchaResponse
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function verifyResponse($remoteIp, $response)
    {
        // Discard empty solution submissions
        if ($response == null || \Tools::strlen($response) == 0) {
            $recaptchaResponse = new RecaptchaResponse();
            $recaptchaResponse->success = false;
            $recaptchaResponse->error_codes[] = 'missing-input';

            return $recaptchaResponse;
        }

        $getResponse = $this->submitHttpGet(
            static::$siteVerifyUrl,
            [
                'secret'   => $this->secret,
                'remoteip' => $remoteIp,
                'v'        => static::$version,
                'response' => $response,
            ]
        );
        if (empty($getResponse)) {
            $error = error_get_last();
            \Logger::addLog(sprintf('reCAPTCHA: Could not contact Google. Check your server settings. Error: %s', $error['message']), 3);

            if (!\Configuration::get('NCRC_GOOGLEIGNORE')) {
                $recaptchaResponse = new RecaptchaResponse();
                $recaptchaResponse->success = false;
                $recaptchaResponse->error_codes[] = 'google-no-contact';

                return $recaptchaResponse;
            } else {
                $recaptchaResponse = new RecaptchaResponse();
                $recaptchaResponse->success = true;

                return $recaptchaResponse;
            }
        }
        $answers = json_decode($getResponse, true);
        $recaptchaResponse = new RecaptchaResponse();

        if (trim($answers['success']) == true) {
            $recaptchaResponse->success = true;
        } else {
            $recaptchaResponse->success = false;
            @$recaptchaResponse->error_codes = $answers['error-codes'];
        }

        return $recaptchaResponse;
    }

    /**
     * Submits an HTTP GET to a reCAPTCHA server.
     *
     * @param string $path url path to recaptcha server.
     * @param array  $data array of parameters to be sent.
     *
     * @return array response
     */
    private function submitHttpGet($path, $data)
    {
        $req = $this->encodeQs($data);
        try {
            $response = (string) (new Client(['verify' => _PS_TOOL_DIR_.'cacert.pem']))->get($path.$req)->getBody();
        } catch (ClientException $e) {
            $response = '';
        }

        return $response;
    }

    /**
     * Encodes the given data into a query string format.
     *
     * @param array $data array of string elements to be encoded.
     *
     * @return string - encoded request.
     */
    private function encodeQs($data)
    {
        $req = '';
        foreach ($data as $key => $value) {
            $req .= $key.'='.urlencode(stripslashes($value)).'&';
        }

        // Cut the last '&'
        $req = substr($req, 0, strlen($req) - 1);

        return $req;
    }
}
