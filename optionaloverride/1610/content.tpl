{**
 * Copyright (C) 2017-2019 thirty bees
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
 * @copyright 2017-2019 thirty bees
 * @license   Academic Free License (AFL 3.0)
 *}

<script src="https://www.google.com/recaptcha/api.js?onload=loginCallback&render=explicit&hl={$lang_iso|escape:'url':'UTF-8'}" async defer></script>
<script type="text/javascript">
	{literal}
	function loginCallback() {
		passwordCallback();
			$('#adminloginCaptcha').empty();
			window.adminloginCaptchaId = grecaptcha.render('adminloginCaptcha', {
				'sitekey': '{/literal}{Configuration::get('NCRC_PUBLIC_KEY')|escape:'javascript':'UTF-8'}{literal}',
				'theme': ({/literal}{Configuration::get('NCRC_ADMINLOGIN_THEME')|escape:'javascript':'UTF-8'}{literal} === '2') ? 'dark' : 'light',
				'callback': verifyLoginCallback
			});
	}

	function verifyLoginCallback (response) {
		if (typeof response !== 'undefined') {
			$('#adminlogin-recaptcha-token').val(response);
		}
	};

	function passwordCallback() {
		console.log('password');
		$('#adminpasswordCaptcha').empty();
		window.adminpasswordCaptchaId = grecaptcha.render('adminpasswordCaptcha', {
			'sitekey': '{/literal}{Configuration::get('NCRC_PUBLIC_KEY')|escape:'javascript':'UTF-8'}{literal}',
			'theme': ({/literal}{Configuration::get('NCRC_ADMINLOGIN_THEME')|escape:'javascript':'UTF-8'}{literal} === '2') ? 'dark' : 'light',
			'callback': verifyPasswordCallback
	});
	}

	function verifyPasswordCallback (response) {
		console.log('verifypassword');
		if (typeof response !== 'undefined') {
			$('#adminpassword-recaptcha-token').val(response);
		}
	};

	function doAjaxLogin(redirect) {
		$('#error').hide();
		$('#login_form').fadeIn('slow', function() {
			$.ajax({
				type: "POST",
				headers: { "cache-control": "no-cache" },
				url: "ajax-tab.php" + '?rand=' + new Date().getTime(),
				async: true,
				dataType: "json",
				data: {
					'ajax': "1",
					'token': "",
					'controller': "AdminLogin",
					'submitLogin': "1",
					'g-recaptcha-response': $('#adminlogin-recaptcha-token').val(),
					'passwd': $('#passwd').val(),
					'email': $('#email').val(),
					'redirect': redirect,
					'stay_logged_in': $('#stay_logged_in:checked').val()
				},
				beforeSend: function() {
					feedbackSubmit();
					l.start();
				},
				success: function(jsonData) {
					if (jsonData.hasErrors) {
						grecaptcha.reset(window.adminloginCaptchaId);
						displayErrors(jsonData.errors);
						l.stop();
					} else {
						window.location.assign(jsonData.redirect);
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					grecaptcha.reset(window.adminloginCaptchaId);
					l.stop();
					$('#error').html('<h3>TECHNICAL ERROR:</h3><p>Details: Error thrown: ' + XMLHttpRequest + '</p><p>Text status: ' + textStatus + '</p>').removeClass('hide');
					$('#login_form').fadeOut('slow');
				}
			});
		});
	}

	function doAjaxForgot() {
		$('#error').hide();
		$('#forgot_password_form').fadeIn('slow', function() {
			$.ajax({
				type: 'POST',
				headers: {'cache-control': 'no-cache'},
				url: 'ajax-tab.php' + '?rand=' + new Date().getTime(),
				async: true,
				dataType: 'json',
				data: {
					'ajax': 1,
					'controller': 'AdminLogin',
					'submitForgot': 1,
					'g-recaptcha-response': $('#adminpassword-recaptcha-token').val(),
					'email_forgot': $('#email_forgot').val()
				},
				success: function(jsonData) {
					if (jsonData.hasErrors) {
						grecaptcha.reset(window.adminpasswordCaptchaId);
						displayErrors(jsonData.errors);
					} else {
						alert(jsonData.confirm);
						$('#forgot_password_form').hide();
						$('.show-forgot-password').hide();
						displayLogin();
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					grecaptcha.reset(window.adminpasswordCaptchaId);
					$('#error').html(XMLHttpRequest.responseText).removeClass('hide').fadeIn('slow');
				}
			});
		});
	}

	{/literal}
</script>
<div id="login-panel">
	<div id="login-header">
		<h1 class="text-center">
			<img id="logo" src="{$img_dir}thirty-bees@2x.png" width="155px" height="39px" alt="thirty bees" />
		</h1>
		<div class="text-center">{$ps_version}</div>
		<div id="error" class="hide alert alert-danger">
			{if isset($errors)}
				<h4>
					{if isset($nbErrors) && $nbErrors > 1}
						{l s='There are %d errors.' sprintf=$nbErrors}
					{else}
						{l s='There is %d error.' sprintf=$nbErrors}
					{/if}
				</h4>
				<ol>
					{foreach from=$errors item="error"}
						<li>{$error|escape:'htmlall':'UTF-8'}</li>
					{/foreach}
				</ol>
			{/if}
		</div>

		{if isset($warningSslMessage)}
			<div class="alert alert-warning">{$warningSslMessage|escape:'htmlall':'UTF-8'}</div>
		{/if}
	</div>
	<div class="flip-container">
		<div class="flipper">
			<div class="front panel">
				<h4 id="shop_name">{$shop_name|escape:'htmlall':'UTF-8'}</h4>
				{if !isset($wrong_folder_name) && !isset($wrong_install_name)}
				<form action="#" id="login_form" method="post">
					<input type="hidden" name="redirect" id="redirect" value="{$redirect|escape:'htmlall':'UTF-8'}"/>
					<div class="form-group">
						<label class="control-label" for="email">{l s='Email address'}</label>
						<input name="email" type="email" id="email" class="form-control" value="{if isset($email)}{$email|escape:'html':'UTF-8'}{/if}" autofocus="autofocus" tabindex="1" placeholder="&#xf0e0 test@example.com" />
					</div>
					<div class="form-group">
						<label class="control-label" for="passwd">
							{l s='Password'}
						</label>
						<input name="passwd" type="password" id="passwd" class="form-control" value="{if isset($password)}{$password|escape:'html':'UTF-8'}{/if}" tabindex="2" placeholder="&#xf084 {l s='Password'}" />
					</div>
					<div class="form-group">
						<label class="control-label adminlogin-captcha-hideme">Captcha</label>
						<div id="adminloginCaptcha" class="g-recaptcha adminlogin-captcha-hideme"></div>
						<input id="adminlogin-recaptcha-token" type="hidden" name="g-adminloginrecaptcha-response"/>
					</div>
					<div class="form-group row-padding-top">
						<button name="submitLogin" type="submit" tabindex="4" class="btn btn-primary btn-lg btn-block ladda-button" data-style="slide-up" data-spinner-color="white" >
							<span class="ladda-label">
								{l s='Log in'}
							</span>
						</button>
					</div>
					<div class="form-group">
						<div id="remind-me" class="checkbox pull-left">
							<label for="stay_logged_in">
								<input name="stay_logged_in" type="checkbox" id="stay_logged_in" value="1"	tabindex="3"/>
								{l s='Stay logged in'}
							</label>
						</div>
						<a href="#" class="show-forgot-password pull-right" >
							{l s='I forgot my password'}
						</a>
					</div>
				</form>
			</div>

			<div class="back panel">
				<form action="#" id="forgot_password_form" method="post">
					<div class="alert alert-info">
						<h4>{l s='Forgot your password?'}</h4>
						<p>{l s='In order to receive your access code by email, please enter the address you provided during the registration process.'}</p>
					</div>
					<div class="form-group">
						<label class="control-label" for="email_forgot">
							{l s='Email'}
						</label>
						<input type="text" name="email_forgot" id="email_forgot" class="form-control" autofocus="autofocus" tabindex="5" placeholder="&#xf0e0 test@example.com" />
					</div>
					<div class="form-group">
						<label class="control-label adminpassword-captcha-hideme">Captcha</label>
						<div id="adminpasswordCaptcha" class="g-recaptcha adminpassword-captcha-hideme"></div>
						<input id="adminpassword-recaptcha-token" type="hidden" name="g-adminpasswordrecaptcha-response"/>
					</div>
					<div class="panel-footer">
						<button type="button" href="#" class="btn btn-default show-login-form" tabindex="7">
							<i class="icon-caret-left"></i>
							{l s='Back to login'}
						</button>
						<button class="btn btn-default pull-right" name="submitLogin" type="submit" tabindex="6">
							<i class="icon-ok text-success"></i>
							{l s='Send'}
						</button>
					</div>
				</form>
			</div>
		</div>
		{else}
		<div class="alert alert-danger">
			<p>{l s='For security reasons, you cannot connect to the back office until you have:'}</p>
			<ul>
				{if isset($wrong_install_name) && $wrong_install_name == true}
					<li>{l s='deleted the /install folder'}</li>
				{/if}
				{if isset($wrong_folder_name) && $wrong_folder_name == true}
					<li>{l s='renamed the /admin folder (e.g. %s)' sprintf=$randomNb}</li>
				{/if}
			</ul>
			<p>
				<a href="{$adminUrl|escape:'html':'UTF-8'}">
					{l s='Please then access this page by the new URL (e.g. %s)' sprintf=$adminUrl}
				</a>
			</p>
		</div>
		{/if}
	</div>
	{hook h="displayAdminLogin"}
	<div id="login-footer">
		<p class="text-center text-muted">
			<a href="http://www.thirtybees.com/" onclick="return !window.open(this.href);">
				&copy; thirty bees&#8482; 2007-{$smarty.now|date_format:"%Y"} - All rights reserved
			</a>
		</p>
		<p class="text-center">
			<a class="link-social link-twitter _blank" href="https://twitter.com/thethirtybees" title="Twitter">
				<i class="icon-twitter"></i>
			</a>
			<a class="link-social link-facebook _blank" href="https://www.facebook.com/thirtybees" title="Facebook">
				<i class="icon-facebook"></i>
			</a>
			<a class="link-social link-github _blank" href="https://github.com/thirtybees" title="Github">
				<i class="icon-github"></i>
			</a>
			<a class="link-social link-reddit _blank" href="https://www.reddit.com/r/thirtybees/" title="Reddit">
				<i class="icon-reddit"></i>
			</a>
		</p>
	</div>
</div>
