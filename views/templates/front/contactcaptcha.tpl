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
<script type="text/javascript">
	var nocaptcharecaptcha_contact = {if $NCRC_CONTACT}true{else}false{/if};
	var nocaptcharecaptcha_contact_theme = '{$NCRC_CONTACT_THEME|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_public_key = '{$NCRC_PUBLIC_KEY|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_module_link = '{$link->getModuleLink('nocaptcharecaptcha', 'captchaenabled', array(), true)|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_lang_iso = '{$nocaptcharecaptcha_lang_iso|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_show_at_start = {if $nocaptcharecaptcha_show_at_start}true{else}false{/if};

	var nocaptcharecaptcha_contact_captcha_html = '{$NCRC_CONTACTHTML|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_contact_select = '{$NCRC_CONTACTSELECT|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_contact_pos = {$NCRC_CONTACTPOS|intval};

	{literal}
	function checkCaptcha(type, email, captchaid) {
		$.ajax({
			'type': 'post',
			'url': nocaptcharecaptcha_module_link,
			'data': 'method=getCaptchaEnabled&type='+type+'&email='+email,
			'dataType': 'json',
			'success': function (data) {
				if (data.email == email) {
					if (data.captchaEnabled) {
						$(captchaid).show();
					} else {
						$(captchaid).hide();
					}
				}
			}
		})
	}

	function onloadCallback() {
		if (nocaptcharecaptcha_contact && nocaptcharecaptcha_public_key) {
			$('#contactCaptcha').empty();
			contactCaptchaId = grecaptcha.render('contactCaptcha', {
				'sitekey': nocaptcharecaptcha_public_key,
				'theme': (nocaptcharecaptcha_contact_theme === '2') ? 'dark' : 'light',
			});
		}
	}

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

	$(document).ready(function () {
		if (nocaptcharecaptcha_contact && nocaptcharecaptcha_public_key) {
			placeCaptcha(nocaptcharecaptcha_contact_pos, $(nocaptcharecaptcha_contact_select), nocaptcharecaptcha_contact_captcha_html);
			$.getScript('https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' +nocaptcharecaptcha_lang_iso);
		}
		if (nocaptcharecaptcha_show_at_start) {
			var emailInput = $('#email');
			checkCaptcha('contact', emailInput.val(), '.contact-captcha-hideme');
			emailInput.bind('input', function () {
				checkCaptcha('contact', emailInput.val(), '.contact-captcha-hideme');
			});
		}
	});
	{/literal}
</script>
