{**
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
 *}

<script type="text/javascript">
	var nocaptcharecaptcha_cust_password = {if $NCRC_PASSWORD}true{else}false{/if};
	var nocaptcharecaptcha_cust_password_theme = '{$NCRC_PASSWORD_THEME|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_public_key = '{$NCRC_PUBLIC_KEY|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_module_link = '{$nocaptcharecaptcha_module_link|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_lang_iso = '{$nocaptcharecaptcha_lang_iso|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_show_at_start = {if $nocaptcharecaptcha_show_at_start}true{else}false{/if};

	var nocaptcharecaptcha_password_captcha_html = '{$NCRC_PASSWORDHTML|escape:'javascript':'UTF-8'}';

	var nocaptcharecaptcha_password_select = '{$NCRC_PASSWORDSELECT|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_password_pos = {$NCRC_PASSWORDPOS|intval};
	{literal}
	function onloadCallback() {
		if (nocaptcharecaptcha_cust_password && nocaptcharecaptcha_public_key) {
			$('#passwordCaptcha').empty();
			passwordCaptchaId = grecaptcha.render('passwordCaptcha', {
				'sitekey': nocaptcharecaptcha_public_key,
				'theme': (nocaptcharecaptcha_cust_password_theme == '2') ? 'dark' : 'light'
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
		if (nocaptcharecaptcha_cust_password && nocaptcharecaptcha_public_key) {
			placeCaptcha(nocaptcharecaptcha_password_pos, $(nocaptcharecaptcha_password_select), nocaptcharecaptcha_password_captcha_html);
			$.getScript('https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' + nocaptcharecaptcha_lang_iso);
		}
	});
	{/literal}
</script>
