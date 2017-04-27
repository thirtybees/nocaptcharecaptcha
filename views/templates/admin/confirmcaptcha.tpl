{*
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
 *
*}
{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
	<br />
	<fieldset id="confirm-panel">
		<legend>{l s='Example' mod='nocaptcharecaptcha'}</legend>
		{if isset($differentDomain) && $differentDomain}
			<div class="warning">{l s='Your current domain (%s) differs from the currently selected store\'s domain (%s). If the stores do not share the key, then this example might not work.' mod='nocaptcharecaptcha' sprintf=[$domain1, $domain2]}</div>
		{/if}
		<strong>{l s='This is an example of what the captcha will look like with the current settings' mod='nocaptcharecaptcha'}</strong>
		<p><em>{l s='Site key' mod='nocaptcharecaptcha'}:</em> <pre id="sitekeyPlaceholder">{$site_key|escape:'html':'UTF-8'}</pre></p>
		<p><em>{l s='Secret key' mod='nocaptcharecaptcha'}:</em> <pre id="secretkeyPlaceholder">{$secret_key|escape:'html':'UTF-8'}</pre></p>
		<strong>{l s='Captcha' mod='nocaptcharecaptcha'}:</strong>
		<div id="confirmCaptcha"></div>
		<br />
		<strong>{l s='Confirmation' mod='nocaptcharecaptcha'}:</strong>
		<div id="confirmationRow" class="row">
			<div class="info">{l s='Unknown' mod='nocaptcharecaptcha'}</div>
		</div>
	</fieldset>
{else}
	<div class="panel" id="confirm-panel">
		<h3><i class="icon icon-lock"></i> {l s='Example' mod='nocaptcharecaptcha'}</h3>
		{if isset($differentDomain) && $differentDomain}
			<div class="alert alert-warning">{l s='Your current domain (%s) differs from the currently selected store\'s domain (%s). If the stores do not share the key, then this example might not work.' mod='nocaptcharecaptcha' sprintf=[$domain1, $domain2]}</div>
		{/if}
		<strong>{l s='This is an example of what the captcha will look like with the current settings' mod='nocaptcharecaptcha'}</strong>
		<p><em>{l s='Site key' mod='nocaptcharecaptcha'}:</em> <pre id="sitekeyPlaceholder">{$site_key|escape:'html':'UTF-8'}</pre></p>
		<p><em>{l s='Secret key' mod='nocaptcharecaptcha'}:</em> <pre id="secretkeyPlaceholder">{$secret_key|escape:'html':'UTF-8'}</pre></p>
		<strong>{l s='Captcha' mod='nocaptcharecaptcha'}:</strong>
		<div id="confirmCaptcha"></div>
		<br />
		<strong>{l s='Confirmation' mod='nocaptcharecaptcha'}:</strong>
		<div id="confirmationRow" class="row">
			<div class="alert alert-info">{l s='Unknown' mod='nocaptcharecaptcha'}</div>
		</div>
	</div>
{/if}

<script type="text/javascript">
	var nocaptcharecaptcha_lang_iso = '{$language_iso|escape:'javascript':'UTF-8'}';
	window.captchaConfirmed = false;

	function onloadCallback() {
		window.confirmCaptchaId = grecaptcha.render('confirmCaptcha', {
			'sitekey': $('#NCRC_PUBLIC_KEY').val(),
			'theme': 'light',
			'callback': verifyCallback,
		});
	}

	function verifyCallback(response) {
		$.ajax({
			url: '{$nocaptcharecaptcha_confirm_link|escape:'javascript':'UTF-8'}',
			method: 'POST',
			dataType: 'JSON',
			data: {
				recaptchaToken: response,
				secretKey: $('#NCRC_PRIVATE_KEY').val(),
				method: 'confirm',
				ajax: true,
			},
			success: function (result) {
				if (!!result && !!result.data && result.data.confirmed) {
					$('#confirmationRow').html('<div class="{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}conf{else}alert alert-success{/if}">{l s='Captcha accepted!' mod='nocaptcharecaptcha' js=1}</div>');
					window.captchaConfirmed = true;
				} else {
					$('#confirmationRow').html('<div class="{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}error{else}alert alert-danger{/if}">{l s='Captcha rejected. The secret key could be wrong.' mod='nocaptcharecaptcha' js=1}</div>');
					window.captchaConfirmed = false;
				}
			}
		});
		$('#recaptcha-token').val(response);
	}

	$(document).ready(function() {
		$('#module_form').submit(function(e) {
			{if !(isset($differentDomain) && $differentDomain)}
			if (!window.captchaConfirmed) {
				e.preventDefault();
				swal({
					text: '{l s='Please confirm that the captcha is working properly with the "Example" panel underneath, before applying the settings.' mod='nocaptcharecaptcha' js=1}',
					type: 'error',
				});
			}
			{/if}
		});
		$('#NCRC_PUBLIC_KEY, #NCRC_PRIVATE_KEY').on('change', function () {
			window.grecaptcha = null;
			window.captchaConfirmed = false;
			$('#confirmCaptcha').empty();
			$('#sitekeyPlaceholder').text($('#NCRC_PUBLIC_KEY').val());
			$('#secretkeyPlaceholder').text($('#NCRC_PRIVATE_KEY').val());
			$('#confirmationRow').html('<div class="{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}info{else}alert alert-info{/if}">{l s='Unknown' mod='nocaptcharecaptcha' js=1}</div>');
			$.getScript('https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' + nocaptcharecaptcha_lang_iso);
		});

		$.getScript('https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' + nocaptcharecaptcha_lang_iso);
	});
</script>
