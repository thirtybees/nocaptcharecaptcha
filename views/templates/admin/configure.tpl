{*
 * Copyright (C) Mijn Presta - All Rights Reserved
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 *
 * @author    Michael Dekker <prestashopaddons@mijnpresta.nl>
 * @copyright 2015-2016 Mijn Presta
 * @license   proprietary
 * Intellectual Property of Mijn Presta
*}
<div class="panel" id="main-panel">
	<h3><i class="icon icon-rocket"></i> {l s='No CAPTCHA reCAPTCHA' mod='nocaptcharecaptcha'}</h3>
	<p>
		{l s='This modules enables the new reCAPTCHA by Google on several forms and protects the store from spambots and brute force attacks.' mod='nocaptcharecaptcha'}<br />
	</p>
    <strong>{l s='Quick start' mod='nocaptcharecaptcha'}</strong>
    <ol>
        <li><a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">{l s='Register your domain with reCAPTCHA and find your secret and site keys.' mod='nocaptcharecaptcha'}</a></li>
        <li>{l s='Enter your keys in the fields below.' mod='nocaptcharecaptcha'}</li>
        <li>{l s='Select the forms on which you would like to enable the captchas and optionally pick a theme.' mod='nocaptcharecaptcha'}</li>
        <li>{l s='Go to Advanced Parameters > Performance and clear the cache. Make sure that both "Disable non PrestaShop modules" and "Disable all overrides" are set to NO.' mod='nocaptcharecaptcha'}</li>
        <li>{l s='You are good to go!' mod='nocaptcharecaptcha'}</li>
    </ol>
</div>

<div class="panel" id="doc-panel">
    <div class="panel-heading"><i class="icon icon-book"></i> Tour</div>
    <a href="#" class="btn btn-default" id="restarttour">{l s='Restart tour' mod='nocaptcharecaptcha'}</a>
</div>

<div style="display: none;">{l s='Your captcha was wrong. Please try again.' mod='nocaptcharecaptcha'}</div>
