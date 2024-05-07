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
	{literal}
	$(document).ready(function() {
		// Define the tour!
		var tour = {
			id: "hello-hopscotch",
			steps: [
				{
					title: "{/literal}{l s='Welcome to No CAPTCHA reCAPTCHA module page' mod='nocaptcharecaptcha'}{literal}",
					content: "{/literal}{l s='Thank you for your purchase! Here\'s a short tour to get you started with the module.' mod='nocaptcharecaptcha'}{literal}",
					target: "main-panel",
					placement: "bottom"
				},
				{
					title: "{/literal}{l s='Welcome to No CAPTCHA reCAPTCHA module page' mod='nocaptcharecaptcha'}{literal}",
						content: "{/literal}{l s='The main panel shows you some quick steps to configure the module.' mod='nocaptcharecaptcha'}{literal}",
						target: "main-panel",
						placement: "bottom"
				},
				{
					title: "{/literal}{l s='Site and secret keys' mod='nocaptcharecaptcha'}{literal}",
					content: "{/literal}{l s='reCAPTCHA requires your site and secret keys. These have been locked in the demo.' mod='nocaptcharecaptcha'}{literal}",
					target: "NCRC_PRIVATE_KEY",
					placement: "bottom"
				},
				{
					title: "{/literal}{l s='Choose your forms' mod='nocaptcharecaptcha'}{literal}",
					content: "{/literal}{l s='You can choose the forms on which you want to show the captcha. This toggle enables or disables the captcha on the customer login form on both the authentication page and on the standard one page checkout if available.' mod='nocaptcharecaptcha'}{literal}",
					target: "NCRC_LOGIN_on",
					placement: "bottom"
				},
				{
					title: "{/literal}{l s='Login attempts' mod='nocaptcharecaptcha'}{literal}",
					content: "{/literal}{l s='You can set the amount of login attempts per customer. You can also set a time limit before the attempts get reset. There\'s a dedicated page for customers and customer groups available where you can modify the settings per customer.' mod='nocaptcharecaptcha'}{literal}",
					target: "NCRC_ATTEMPTS",
					placement: "top"
				},
				{
					title: "{/literal}{l s='reCAPTCHA theme' mod='nocaptcharecaptcha'}{literal}",
					content: "{/literal}{l s='No CAPTCHA reCAPTCHA comes with two themes: dark and light. You configure these per form type.' mod='nocaptcharecaptcha'}{literal}",
					target: "NCRC_LOGIN_THEME",
					placement: "top"
				},
				{
					title: "{/literal}{l s='Disable captcha if logged in' mod='nocaptcharecaptcha'}{literal}",
					content: "{/literal}{l s='The captcha can be disabled for the user when he or she is logged in.' mod='nocaptcharecaptcha'}{literal}",
					target: "NCRC_LOGGEDINDISABLE_off",
					placement: "top"
				},
				{
					title: "{/literal}{l s='Advanced settings' mod='nocaptcharecaptcha'}{literal}",
					content: "{/literal}{l s='Modules are designed for the standard PrestaShop themes. If your theme has been customized, then there is a slight chance that the module does not show or look very weird.' mod='nocaptcharecaptcha'}{l s='With these settings you can adjust that.' mod='nocaptcharecaptcha'}{literal}",
					target: "advanced-panel",
					placement: "top"
				},
			],
			showPrevButton: true,
			showCloseButton: true,
			onEnd: function() {
				Cookies.set('recaptchaTourFinished', '1', {expires: 2147483647})
			},
			onClose: function() {
				Cookies.set('recaptchaTourFinished', '1', {expires: 2147483647})
			}
		};

		if (!Cookies.get('recaptchaTourFinished')) {
			hopscotch.startTour(tour);
		}
	});
	{/literal}
</script>
