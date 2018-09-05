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
	var nocaptcharecaptcha_login = {if $NCRC_LOGIN}true{else}false{/if};
	var nocaptcharecaptcha_login_theme = '{$NCRC_LOGIN_THEME|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_create = {if $NCRC_CREATE}true{else}false{/if};
	var nocaptcharecaptcha_public_key = '{$NCRC_PUBLIC_KEY|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_guest_checkout_enabled = {if $nocaptcharecaptcha_guest_checkout_enabled}true{else}false{/if};
	var nocaptcharecaptcha_module_link = '{$nocaptcharecaptcha_module_link|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_lang_iso = '{$nocaptcharecaptcha_lang_iso|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_login_captcha_html = '{$NCRC_LOGINHTML|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_create_captcha_html = '{$NCRC_CREATEHTML|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_login_select = '{$NCRC_LOGINSELECT|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_create_select = '{$NCRC_CREATESELECT|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_login_pos = {$NCRC_LOGINPOS|intval};
	var nocaptcharecaptcha_create_pos = {$NCRC_CREATEPOS|intval};
	var nocaptcharecaptcha_controller = '{$smarty.get.controller|escape:'javascript':'UTF-8'}';

	{literal}
	function checkCaptcha(type, captchaid, email) {
		$.ajax({
			type: 'POST',
			url: nocaptcharecaptcha_module_link,
			data: 'method=getCaptchaEnabled&type=' + type + '&email=' + email,
			dataType: 'json',
			success: function (data) {
				if (data.email == email) {
					if (data.captchaEnabled) {
						$(captchaid).show();
					} else {
						$(captchaid).hide();
					}
				}
			}
		});
	}

	function verifyCallback(response) {
		if (typeof response !== 'undefined') {
			$('#recaptcha-token').val(response);
			$('#guestToken').val(response);
		}
	}

	window.onloadCallback = function() {
		if (nocaptcharecaptcha_login && nocaptcharecaptcha_public_key &&
				$('#loginCaptcha').is(':empty') && $('#loginCaptcha').is(':visible')) {
			loginCaptchaId = grecaptcha.render('loginCaptcha', {
				'sitekey': nocaptcharecaptcha_public_key,
				'theme': (nocaptcharecaptcha_login_theme === '2') ? 'dark' : 'light',
				'callback': verifyCallback
			});
		}

		if (nocaptcharecaptcha_create && nocaptcharecaptcha_public_key &&
				$('#createCaptcha').is(':empty') && $('#createCaptcha').is(':visible')) {
			createCaptchaId = grecaptcha.render('createCaptcha', {
				'sitekey': nocaptcharecaptcha_public_key,
				'theme': (nocaptcharecaptcha_login_theme === '2') ? 'dark' : 'light',
				'callback': verifyCallback
			});
		}

		if (nocaptcharecaptcha_create && nocaptcharecaptcha_public_key &&
				nocaptcharecaptcha_guest_checkout_enabled &&
				$('#guestCaptcha').is(':empty')) {
			guestCaptchaId = grecaptcha.render('guestCaptcha', {
				'sitekey': nocaptcharecaptcha_public_key,
				'theme': (nocaptcharecaptcha_login_theme === '2') ? 'dark' : 'light',
				'callback': verifyCallback
			})
		}
	};

	function placeCaptcha(position, $target, html) {
		switch (position) {
			case 1:
				$target.before(html);
				break;
			case 2:
				$target.after(html);
				break;
			case 3:
				$target.prepend(html);
				break;
			case 4:
				$target.append(html);
				break;
			case 5:
				$target.parent().before(html);
				break;
			case 6:
				$target.parent().after(html);
				break;
			case 7:
				$target.parent().prepend(html);
				break;
			case 8:
				$target.parent().append(html);
				break;
			case 9:
				$target.parent().parent().before(html);
				break;
			case 10:
				$target.parent().parent().after(html);
				break;
			case 11:
				$target.parent().parent().prepend(html);
				break;
			case 12:
				$target.parent().parent().append(html);
				break;
		}
	}

	$(document).ready(function() {
		if (nocaptcharecaptcha_create && nocaptcharecaptcha_public_key) {
			function placeCreateCaptcha() {
				if (!$(nocaptcharecaptcha_create_select).is(':visible')) {
					setTimeout(placeCreateCaptcha, 1000);
					return;
				}

				placeCaptcha(nocaptcharecaptcha_create_pos, $(nocaptcharecaptcha_create_select), nocaptcharecaptcha_create_captcha_html);
				$.getScript('https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' + nocaptcharecaptcha_lang_iso);
			}

			placeCreateCaptcha();
		}

		if (nocaptcharecaptcha_create && nocaptcharecaptcha_guest_checkout_enabled && nocaptcharecaptcha_public_key) {
			function placeGuestCaptcha() {
				if (!$('#opc_account_form').is(':visible')) {
					setTimeout(placeGuestCaptcha);
					return;
				}

				$('#opc_account_form').append(
						'<input type="hidden" name="g-recaptcha-guestworkaround" id="guestToken"/>' +
						'<div id="guestCaptcha" class="g-recaptcha" name="ignore"></div><br/>');
				$.getScript('https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' + nocaptcharecaptcha_lang_iso);
			}

			placeGuestCaptcha();
		}

		if (nocaptcharecaptcha_login && nocaptcharecaptcha_public_key) {
			function placeLoginCaptcha() {
				if (!$(nocaptcharecaptcha_login_select).is(':visible')) {

					setTimeout(placeLoginCaptcha, 1000);
					return;
				}

				placeCaptcha(nocaptcharecaptcha_login_pos, $(nocaptcharecaptcha_login_select), nocaptcharecaptcha_login_captcha_html);

				$.getScript('https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' + nocaptcharecaptcha_lang_iso);
				$(document).ready(function () {
					var emailInput = $('#login_form').find('#email');
					checkCaptcha('custlogin', '.login-captcha-hideme', emailInput.val());
					emailInput.bind('input', function () {
						checkCaptcha('custlogin', '.login-captcha-hideme', emailInput.val());
					});
				});
			}

			placeLoginCaptcha();
		}
	});
	{/literal}
</script>
