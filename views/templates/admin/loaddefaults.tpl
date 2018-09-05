{**
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
 *}

<script type="text/javascript">
    {literal}
    $(document).ready(function () {
      function processHtmlJson(string) {
        return string.replace(/\\t/i, '\t').replace(/\\n/i, '\n');
      }

      $.getJSON('{/literal}{$module_dir|escape:'javascript':'UTF-8'}{literal}views/json/admin/defaults.json', function (data) {
        $('#restoreLogin16').click(function () {
          console.log('swag');
          ace.edit('loginCaptchaHTMLACE')
            .setValue(processHtmlJson(data['1.6'].login.content), -1);
          $('#NCRC_LOGINSELECT').val(data['1.6'].login.select);
          $('#NCRC_LOGINPOS').val(data['1.6'].login.position);
        });
        $('#restoreCreate16').click(function () {
          ace.edit('registerCaptchaHTMLACE')
            .setValue(processHtmlJson(data['1.6'].create.content), -1);
          $('#NCRC_CREATESELECT').val(data['1.6'].create.select);
          $('#NCRC_CREATEPOS').val(data['1.6'].create.position);
        });
        $('#restorePassword16').click(function () {
          ace.edit('passwordCaptchaHTMLACE')
            .setValue(processHtmlJson(data['1.6'].password.content), -1);
          $('#NCRC_PASSWORDSELECT').val(data['1.6'].password.select);
          $('#NCRC_PASSWORDPOS').val(data['1.6'].password.position);
        });
        $('#restoreContact16').click(function () {
          ace.edit('contactCaptchaHTMLACE')
            .setValue(processHtmlJson(data['1.6'].contact.content), -1);
          $('#NCRC_CONTACTSELECT').val(data['1.6'].contact.select);
          $('#NCRC_CONTACTPOS').val(data['1.6'].contact.position);
        });
        $('#restoreOPCLogin16').click(function () {
          ace.edit('opcloginCaptchaHTMLACE')
            .setValue(processHtmlJson(data['1.6'].opclogin.content), -1);
          $('#NCRC_OPCLOGINSELECT').val(data['1.6'].opclogin.select);
          $('#NCRC_OPCLOGINPOS').val(data['1.6'].opclogin.position);
          ace.edit('opccreateCaptchaHTMLACE')
            .setValue(processHtmlJson(data['1.6'].opccreate.content), -1);
          $('#NCRC_OPCCREATESELECT').val(data['1.6'].opccreate.select);
          $('#NCRC_OPCCREATEPOS').val(data['1.6'].opccreate.position);
        });
        $('#restoreCSS16').click(function () {
          ace.edit('captchaCSSACE')
            .setValue(processHtmlJson(data['1.6'].css.extra), -1);
        });
      });
    });
    {/literal}
</script>
