<label>{l s='Captcha' mod='nocaptcharecaptcha'} <sup>*</sup></label>
<div id="{$captchaHtmlId|escape:'html'}" class="g-recaptcha"></div>

<script type="text/javascript">
	function onloadCallback{$captchaHtmlId}() {
		grecaptcha.render('{$captchaHtmlId}', {
			'sitekey': '{$captchaPublicKey|escape:'javascript':'UTF-8'}',
			'theme': '{$captchaTheme|escape:'javascript':'UTF-8'}',
		});
	}

	$(document).ready(function() {
		$.getScript('https://www.google.com/recaptcha/api.js?onload=onloadCallback{$captchaHtmlId}&render=explicit&hl={$languageIso|escape:'javascript':'UTF-8'}');
	});
</script>
