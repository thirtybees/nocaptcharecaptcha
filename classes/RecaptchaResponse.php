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

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
* A RecaptchaResponse is returned from checkAnswer().
*/
class RecaptchaResponse
{
    /**
     * @var
     */
    public $success;

    /**
     * @var
     */
    public $error_codes;
}
