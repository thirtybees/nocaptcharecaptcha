{*
 * Copyright (C) 2017-2018 thirty bees
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
 *  @copyright 2017-2018 thirty bees
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
*}
<script type="text/javascript">
	var nocaptcharecaptcha_login = {if $NCRC_LOGIN}true{else}false{/if};
	var nocaptcharecaptcha_login_theme = '{$NCRC_LOGIN_THEME|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_create = {if $NCRC_CREATE}true{else}false{/if};
	var nocaptcharecaptcha_create_theme = '{$NCRC_CREATE_THEME|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_public_key = '{$NCRC_PUBLIC_KEY|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_guest_checkout_enabled = {if $nocaptcharecaptcha_guest_checkout_enabled}true{else}false{/if};
	var nocaptcharecaptcha_module_link = '{$nocaptcharecaptcha_module_link|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_lang_iso = '{$nocaptcharecaptcha_lang_iso|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_login_captcha_html = '{$NCRC_OPCLOGINHTML|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_login_pos = {$NCRC_OPCLOGINPOS|intval};
	var nocaptcharecaptcha_login_select = '{$NCRC_OPCLOGINSELECT|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_create_captcha_html = '{$NCRC_OPCCREATEHTML|escape:'javascript':'UTF-8'}';
	var nocaptcharecaptcha_create_pos = {$NCRC_OPCCREATEPOS|intval};
	var nocaptcharecaptcha_create_select = '{$NCRC_OPCCREATESELECT|escape:'javascript':'UTF-8'}';
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

	function initSubmitLogin() {
		var initLogin = function (e) {
			e.preventDefault();
			$.ajax({
				type: 'POST',
				headers: {"cache-control": "no-cache"},
				url: authenticationUrl + '?rand=' + new Date().getTime(),
				async: false,
				cache: false,
				dataType: "json",
				data: 'SubmitLogin=true&ajax=true&g-recaptcha-response=' + $('#recaptcha-token').val() + '&email=' +
				encodeURIComponent($('#login_email').val())
				+ '&passwd=' + encodeURIComponent($('#login_passwd').val()) + '&token=' + static_token,
				success: function (jsonData) {
					if (jsonData.hasError) {
						var errors = '<b>' + txtThereis + ' ' + jsonData.errors.length + ' ' + txtErrors + ':</b><ol>';
						for (var error in jsonData.errors)
							//IE6 bug fix
							if (error !== 'indexOf')
								errors += '<li>' + jsonData.errors[error] + '</li>';
						errors += '</ol>';
						$('#opc_login_errors').html(errors).slideDown('slow');
						grecaptcha.reset();
					} else {
						// update token
						static_token = jsonData.token;
						updateNewAccountToAddressBlock();
					}
					checkCaptcha('custlogin', '.login-captcha-hideme', $('#login_email').val());
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					if (textStatus !== 'abort') {
						error = "TECHNICAL ERROR: unable to send login informations \n\nDetails:\nError thrown: " +
								XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
						if (!!$.prototype.fancybox)
							$.fancybox.open([
										{
											type: 'inline',
											autoScale: true,
											minHeight: 30,
											content: '<p class="fancybox-error">' + error + '</p>'
										}
									],
									{
										padding: 0
									}
							);
						else {
							alert(error);
						}
					}
				}
			});
		};

		{/literal}
		{if $NCRC_PS15COMPAT}
		$('#SubmitLogin').off('click');
		$('#SubmitLogin').on('click', initLogin);
		{else}
		$(document).off('click', '#SubmitLogin');
		$(document).on('click', '#SubmitLogin', initLogin);
		{/if}
		{literal}
	}

	function initSubmitAccount() {
		if ($('#is_new_customer').val() == '1') {
			var initAccount = function (e) {
				e.preventDefault();
				$('#opc_new_account-overlay, #opc_delivery_methods-overlay, #opc_payment_methods-overlay').fadeIn('slow')

				var callingFile = '';
				var params = '';

				if (parseInt($('#opc_id_customer').val()) == 0) {
					callingFile = authenticationUrl;
					params = 'submitAccount=true&';
				}
				else {
					callingFile = orderOpcUrl;
					params = 'method=editCustomer&';
				}

				$('#opc_account_form input:visible, #opc_account_form input[type=hidden]').each(function () {
					if ($(this).is('input[type=checkbox]')) {
						if ($(this).is(':checked'))
							params += encodeURIComponent($(this).attr('name')) + '=1&';
					}
					else if ($(this).is('input[type=radio]')) {
						if ($(this).is(':checked'))
							params += encodeURIComponent($(this).attr('name')) + '=' + encodeURIComponent($(this).val()) + '&';
					}
					else
						params += encodeURIComponent($(this).attr('name')) + '=' + encodeURIComponent($(this).val()) + '&';
				});
				$('#opc_account_form select:visible').each(function () {
					params += encodeURIComponent($(this).attr('name')) + '=' + encodeURIComponent($(this).val()) + '&';
				});
				params += 'customer_lastname=' + encodeURIComponent($('#customer_lastname').val()) + '&';
				params += 'customer_firstname=' + encodeURIComponent($('#customer_firstname').val()) + '&';
				params += 'alias=' + encodeURIComponent($('#alias').val()) + '&';
				params += 'other=' + encodeURIComponent($('#other').val()) + '&';
				params += 'is_new_customer=' + encodeURIComponent($('#is_new_customer').val()) + '&';
				// Clean the last &
				params = params.substr(0, params.length - 1);

				$.ajax({
					type: 'POST',
					headers: {"cache-control": "no-cache"},
					url: callingFile + '?rand=' + new Date().getTime(),
					async: false,
					cache: false,
					dataType: "json",
					data: 'ajax=true&' + params + '&token=' + static_token,
					success: function (jsonData) {
						if (jsonData.hasError) {
							var tmp = '';
							var i = 0;
							for (var error in jsonData.errors)
								//IE6 bug fix
								if (error !== 'indexOf') {
									i = i + 1;
									tmp += '<li>' + jsonData.errors[error] + '</li>';
								}
							tmp += '</ol>';
							var errors = '<b>' + txtThereis + ' ' + i + ' ' + txtErrors + ':</b><ol>' + tmp;
							$('#opc_account_errors').slideUp('fast', function () {
								$(this).html(errors).slideDown('slow', function () {
									$.scrollTo('#opc_account_errors', 800);
								});
							});
							grecaptcha.reset();
						}
						else {
							$('#opc_account_errors').slideUp('slow', function () {
								$(this).html('');
							});
						}

						isGuest = parseInt($('#is_new_customer').val()) == 1 ? 0 : 1;
						// update addresses id
						if (jsonData.id_address_delivery !== undefined && jsonData.id_address_delivery > 0)
							$('#opc_id_address_delivery').val(jsonData.id_address_delivery);
						if (jsonData.id_address_invoice !== undefined && jsonData.id_address_invoice > 0)
							$('#opc_id_address_invoice').val(jsonData.id_address_invoice);

						if (jsonData.id_customer !== undefined && jsonData.id_customer !== 0 && jsonData.isSaved) {
							// update token
							static_token = jsonData.token;

							// It's not a new customer
							if ($('#opc_id_customer').val() !== '0')
								if (!saveAddress('delivery'))
									return false;

							// update id_customer
							$('#opc_id_customer').val(jsonData.id_customer);

							if ($('#invoice_address:checked').length !== 0) {
								if (!saveAddress('invoice'))
									return false;
							}

							// update id_customer
							$('#opc_id_customer').val(jsonData.id_customer);

							// force to refresh carrier list
							if (isGuest) {
								isLogged = 1;
								$('#opc_account_saved').fadeIn('slow');
								$('#submitAccount').hide();
								updateAddressSelection();
							}
							else
								updateNewAccountToAddressBlock();
						}
						$('#opc_new_account-overlay, #opc_delivery_methods-overlay, #opc_payment_methods-overlay')
								.fadeIn('slow');
					},
					error: function (XMLHttpRequest, textStatus, errorThrown) {
						if (textStatus !== 'abort')
							alert("TECHNICAL ERROR: unable to save account \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n"
									+ 'Text status: ' + textStatus);
						$('#opc_new_account-overlay, #opc_delivery_methods-overlay, #opc_payment_methods-overlay')
								.fadeIn('slow')
					}
				});
				return false;
			};

			{/literal}
			{if $NCRC_PS15COMPAT}
				$('#submitAccount').off('click');
				$('#submitAccount').on('click', initAccount);

				$('#submitGuestAccount').off('click');
				$('#submitGuestAccount').on('click', initAccount);
			{else}
			$(document).off('click', '#submitAccount');
			$(document).off('click', '#submitGuestAccount');
			$(document).off('click', '#submitAccount, #submitGuestAccount');
			$(document).on('click', '#submitAccount, #submitGuestAccount', initAccount);
			{/if}
			{literal}

		} else {
			// VALIDATION / CREATION AJAX
			var creationAjax = function (e) {
				e.preventDefault();
				$('#opc_new_account-overlay, #opc_delivery_methods-overlay, #opc_payment_methods-overlay').fadeIn('slow')

				var callingFile = '';
				var advApiParam = '';
				var params = '';

				if ($(this).attr('data-adv-api')) {
					advApiParam = '&isAdvApi=1';
				}

				if (parseInt($('#opc_id_customer').val()) == 0) {
					callingFile = authenticationUrl;
					params = 'submitAccount=true&';
				}
				else {
					callingFile = orderOpcUrl;
					params = 'method=editCustomer&';
				}

				$('#opc_account_form input:visible, #opc_account_form input[type=hidden]').each(function () {
					if ($(this).is('input[type=checkbox]')) {
						if ($(this).is(':checked'))
							params += encodeURIComponent($(this).attr('name')) + '=1&';
					}
					else if ($(this).is('input[type=radio]')) {
						if ($(this).is(':checked'))
							params += encodeURIComponent($(this).attr('name')) + '=' + encodeURIComponent($(this).val()) + '&';
					}
					else
						params += encodeURIComponent($(this).attr('name')) + '=' + encodeURIComponent($(this).val()) + '&';
				});

				$('#opc_account_form select:visible').each(function () {
					params += encodeURIComponent($(this).attr('name')) + '=' + encodeURIComponent($(this).val()) + '&';
				});
				params += 'customer_lastname=' + encodeURIComponent($('#customer_lastname').val()) + '&';
				params += 'customer_firstname=' + encodeURIComponent($('#customer_firstname').val()) + '&';
				params += 'alias=' + encodeURIComponent($('#alias').val()) + '&';
				params += 'other=' + encodeURIComponent($('#other').val()) + '&';
				params += 'is_new_customer=' + encodeURIComponent($('#is_new_customer').val()) + '&';
				// Clean the last &
				params = params.substr(0, params.length - 1);

				$.ajax({
					type: 'POST',
					headers: {"cache-control": "no-cache"},
					url: callingFile + '?rand=' + new Date().getTime() + advApiParam,
					async: false,
					cache: false,
					dataType: "json",
					data: 'ajax=true&' + params + '&token=' + static_token,
					success: function (jsonData) {
						if (jsonData.hasError) {
							grecaptcha.reset();
							var tmp = '';
							var i = 0;
							for (var error in jsonData.errors)
								//IE6 bug fix
								if (error !== 'indexOf') {
									i = i + 1;
									tmp += '<li>' + jsonData.errors[error] + '</li>';
								}
							tmp += '</ol>';
							var errors = '<b>' + txtThereis + ' ' + i + ' ' + txtErrors + ':</b><ol>' + tmp;
							$('#opc_account_errors').slideUp('fast', function () {
								$(this).html(errors).slideDown('slow', function () {
									$.scrollTo('#opc_account_errors', 800);
								});
							});
						}
						else {
							$('#opc_account_errors').slideUp('slow', function () {
								$(this).html('');
							});
						}

						isGuest = parseInt($('#is_new_customer').val()) == 1 ? 0 : 1;
						// update addresses id
						if (jsonData.id_address_delivery !== undefined && jsonData.id_address_delivery > 0)
							$('#opc_id_address_delivery').val(jsonData.id_address_delivery);
						if (jsonData.id_address_invoice !== undefined && jsonData.id_address_invoice > 0)
							$('#opc_id_address_invoice').val(jsonData.id_address_invoice);

						if (jsonData.id_customer !== undefined && jsonData.id_customer !== 0 && jsonData.isSaved) {
							// update token
							static_token = jsonData.token;

							// It's not a new customer
							if ($('#opc_id_customer').val() !== '0')
								if (!saveAddress('delivery'))
									return false;

							// update id_customer
							$('#opc_id_customer').val(jsonData.id_customer);

							if ($('#invoice_address:checked').length !== 0) {
								if (!saveAddress('invoice'))
									return false;
							}

							// update id_customer
							$('#opc_id_customer').val(jsonData.id_customer);

							// force to refresh carrier list
							if (isGuest) {
								isLogged = 1;
								$('#opc_account_saved').fadeIn('slow');
								$('#submitAccount').hide();
								updateAddressSelection(advApiParam);
							}
							else
								updateNewAccountToAddressBlock(advApiParam);
						}
						$('#opc_new_account-overlay, #opc_delivery_methods-overlay, #opc_payment_methods-overlay').fadeIn('slow');
					},
					error: function (XMLHttpRequest, textStatus, errorThrown) {
						if (textStatus !== 'abort') {
							error = "TECHNICAL ERROR: unable to save account \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
							if (!!$.prototype.fancybox)
								$.fancybox.open([
									{
										type: 'inline',
										autoScale: true,
										minHeight: 30,
										content: '<p class="fancybox-error">' + error + '</p>'
									}
								], {
									padding: 0
								});
							else
								alert(error);
						}
						$('#opc_new_account-overlay, #opc_delivery_methods-overlay, #opc_payment_methods-overlay').fadeIn('slow')
					}
				});
			};
			{/literal}
			{if $NCRC_PS15COMPAT}
			$('#submitAccount').off('click');
			$('#submitGuestAccount').off('click');
			$('#submitAccount').on('click', creationAjax);
			$('#submitGuestAccount').on('click', creationAjax);
			{else}
			$(document).off('click', '#submitAccount, #submitGuestAccount');
			$(document).on('click', '#submitAccount, #submitGuestAccount', creationAjax);
			{/if}
			{literal}

			bindInputs();

			$('#opc_account_form input,select,textarea').change(function() {
				if ($(this).is(':visible')) {
					$('#opc_account_saved').fadeOut('slow');
					$('#submitAccount').show();
				}
			});
		}
	}

	function verifyCallback(response) {
		if (typeof response !== 'undefined') {
			$('#recaptcha-token').val(response);
			$('#g-recaptcha-guestworkaround').val(response);
		}
	};

	function verifyCreateCallback(response) {
		if (typeof response !== 'undefined') {
			$('#recaptcha-token').val(response);
			$('#g-recaptcha-guestworkaround').val(response);
		}
	};

	function onloadCallback() {
		if (typeof grecaptcha !== 'undefined' && (nocaptcharecaptcha_login && nocaptcharecaptcha_public_key)) {
			$('#loginCaptcha').empty();
			if ($('#loginCaptcha').is(':visible')) {
				opcLoginCaptchaId = grecaptcha.render('loginCaptcha', {
					'sitekey': nocaptcharecaptcha_public_key,
					'theme': (nocaptcharecaptcha_login_theme === '2') ? 'dark' : 'light',
					'callback': verifyCallback
				})
			}
		}
	};

	function onloadCreateCallback() {
		if (typeof grecaptcha !== 'undefined' && (nocaptcharecaptcha_create && nocaptcharecaptcha_public_key)) {
			$('#createCaptcha').empty();
			if ($('#createCaptcha').is(':visible')) {
				opcCreateCaptchaId = grecaptcha.render('createCaptcha', {
					'sitekey': nocaptcharecaptcha_public_key,
					'theme': (nocaptcharecaptcha_create_theme === '2') ? 'dark' : 'light',
					'callback': verifyCreateCallback
				});
			}
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

	$(document).ready(function () {
		if (nocaptcharecaptcha_login && nocaptcharecaptcha_create && nocaptcharecaptcha_public_key) {
			$('#opc_account_form').find('submit')
					.before('<input id="recaptcha-token" type="hidden" name="g-recaptcha-response"/>');
		}

		if ((nocaptcharecaptcha_login || nocaptcharecaptcha_create) && nocaptcharecaptcha_public_key) {
			$('#opc_account_form').find('.submit')
					.before('<input id="recaptcha-token" type="hidden" name="g-recaptcha-response"/>');
		}

		if (nocaptcharecaptcha_login && nocaptcharecaptcha_public_key) {
			var initLoginForm = function(e){
				e.preventDefault();
				$('#openNewAccountBlock').show();
				$(this).hide();
				$('#login_form_content').slideDown('slow', function() {
					if ($('#login_form_content').is(':visible')) {
						grecaptcha = null;
						$('#createCaptcha').remove();
						$('#loginCaptcha').remove();
						placeCaptcha(nocaptcharecaptcha_login_pos, $(nocaptcharecaptcha_login_select), nocaptcharecaptcha_login_captcha_html);

						$.ajax({
							async:false,
							type:'GET',
							url:'https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' + nocaptcharecaptcha_lang_iso,
							data:null,
							dataType:'script',
							success: function() {
								// Fallback mechanism
								setTimeout(function() {
									if (typeof opcLoginCaptchaId === 'undefined') {
										onloadCallback();
									}
								}, 1000);
							}
						});
					}

					initSubmitLogin();

					var emailInput = $('#login_email');
					checkCaptcha('custlogin', '.login-captcha-hideme', emailInput.val());
					emailInput.bind('input', function () {
						checkCaptcha('custlogin', '.login-captcha-hideme', emailInput.val());
					});
				});
				$('#new_account_form').slideUp('slow');

			};

			{/literal}
			{if $NCRC_PS15COMPAT}
			$('#openLoginFormBlock').off('click');
			$('#openLoginFormBlock').on('click', initLoginForm);
			{else}
			$(document).off('click', '#openLoginFormBlock');
			$(document).on('click', '#openLoginFormBlock', initLoginForm);
			{/if}
			{literal}
		}

		if (nocaptcharecaptcha_create && nocaptcharecaptcha_public_key) {
			if ($('#opc_account_form').is(':visible')) {
				placeCaptcha(nocaptcharecaptcha_create_pos, $(nocaptcharecaptcha_create_select), nocaptcharecaptcha_create_captcha_html);

				$.ajax({
					async: false,
					type: 'GET',
					url: 'https://www.google.com/recaptcha/api.js?onload=onloadCreateCallback&render=explicit&hl=' + nocaptcharecaptcha_lang_iso,
					data: null,
					dataType: 'script',
					success: function () {
						// Fallback mechanism
						setTimeout(function () {
							if (typeof opcCreateCaptchaId === 'undefined') {
								onloadCreateCallback();
							}
						}, 1000);
					}
				});
				initSubmitAccount();
			}

			$(document).off('click', '#opc_createAccount');
			$(document).on('click', '#opc_createAccount', function(e){
				e.preventDefault();
				$('.is_customer_param').show();
				$('#opc_account_form').slideDown('slow', function() {
					if ($('#opc_account_form').is(':visible')) {
						placeCaptcha(nocaptcharecaptcha_create_pos, $(nocaptcharecaptcha_create_select), nocaptcharecaptcha_create_captcha_html);

						$.ajax({
							async: false,
							type: 'GET',
							url: 'https://www.google.com/recaptcha/api.js?onload=onloadCreateCallback&render=explicit&hl=' + nocaptcharecaptcha_lang_iso,
							data: null,
							dataType: 'script',
							success: function() {
								// Fallback mechanism
								setTimeout(function() {
									if (typeof opcCreateCaptchaId === 'undefined') {
										onloadCreateCallback();
									}
								}, 1000);
							}
						});
					}
					initSubmitAccount();
				});
				$('#is_new_customer').val('1');
				$('#opc_account_choice, #opc_invoice_address').hide();
				if (typeof bindUniform !=='undefined') {
					bindUniform();
				}
			});

			if (nocaptcharecaptcha_guest_checkout_enabled) {
				$(document).off('click', '#opc_guestCheckout');
				$(document).on('click', '#opc_guestCheckout',function(e){
					console.log('opc guest checkout');
					e.preventDefault();
					$('.is_customer_param').hide();
					$('#opc_account_form').slideDown('slow', function() {
						if ($('#opc_account_form').is(':visible')) {
							placeCaptcha(nocaptcharecaptcha_create_pos, $(nocaptcharecaptcha_create_select), nocaptcharecaptcha_create_captcha_html);

							$.ajax({
								async: false,
								type: 'GET',
								url: 'https://www.google.com/recaptcha/api.js?onload=onloadCreateCallback&render=explicit&hl=' + nocaptcharecaptcha_lang_iso,
								data: null,
								dataType: 'script',
								success: function() {
									// Fallback mechanism
									setTimeout(function() {
										if (typeof opcCreateCaptchaId === 'undefined') {
											onloadCreateCallback();
										}
									}, 1000);
								}
							});
						}
						initSubmitAccount();
					});
					$('#is_new_customer').val('0');
					$('#opc_account_choice, #opc_invoice_address').hide();
					$('#new_account_title').html(txtInstantCheckout);
					$('#submitAccount').attr({id : 'submitGuestAccount', name : 'submitGuestAccount'});
					if (typeof bindUniform !=='undefined') {
						bindUniform();
					}
				});
			}
		}
	});
	{/literal}
</script>
