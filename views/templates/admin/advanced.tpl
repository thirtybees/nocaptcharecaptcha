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
{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
	<br/>
	<br/>
	<form id="module_form_advanced" class="defaultForm form-horizontal"
		  action="{$advancedAction|escape:'htmlall':'UTF-8'}" method="post"
		  enctype="multipart/form-data" novalidate="">
		<fieldset id="advanced-panel">
			<legend>{l s='Advanced settings' mod='nocaptcharecaptcha'}</legend>
			<div class="btn-group" role="group">
				<button type="button" class="button" id="restoreLogin16">
					{l s='Restore defaults for thirtybees' mod='nocaptcharecaptcha'}
				</button>
			</div>
			<br/>
			<br/>
			<label class="control-label col-lg-2 required">
				{l s='jQuery selector' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<input type="text" name="NCRC_LOGINSELECT" id="NCRC_LOGINSELECT"
					   value="{$NCRC_LOGINSELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
					   required="required">
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2 required">
				{l s='Position' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				{html_options name="NCRC_LOGINPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_LOGINPOS class=" fixed-width-xl" id="NCRC_LOGINPOS"}
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2">
				{l s='Login captcha HTML' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<div class="ace-container">
					<div class="ace-editor ace-html-editor" data-name="loginCaptchaHTML"
						 id="loginCaptchaHTMLACE">{$NCRC_LOGINHTML|escape:'htmlall':'UTF-8'}</div>
					<input type="hidden" id="loginCaptchaHTML" name="NCRC_LOGINHTML">
				</div>
			</div>
			<hr style="border: solid 1px lightgrey;">
			<div class="btn-group" role="group">
				<button type="button" class="button" id="restoreCreate16">
					{l s='Restore defaults for thirty bees' mod='nocaptcharecaptcha'}
				</button>
			</div>
			<br/>
			<br/>
			<label class="control-label col-lg-2 required">
				{l s='jQuery selector' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<input type="text" name="NCRC_CREATESELECT" id="NCRC_CREATESELECT"
					   value="{$NCRC_CREATESELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
					   required="required">
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2 required">
				{l s='Position' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				{html_options name="NCRC_CREATEPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_CREATEPOS class=" fixed-width-xl" id="NCRC_CREATEPOS"}
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2">
				{l s='Register captcha HTML' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<div class="col-lg-10 ace-container">
					<div class="ace-editor ace-html-editor" data-name="registerCaptchaHTML"
						 id="registerCaptchaHTMLACE">{$NCRC_CREATEHTML|escape:'htmlall':'UTF-8'}</div>
					<input type="hidden" id="registerCaptchaHTML" name="NCRC_CREATEHTML">
				</div>
			</div>
			<div class="clear"></div>
			<hr style="border: solid 1px lightgrey;">
			<div class="btn-group" role="group">
				<button type="button" class="button" id="restorePassword16">
					{l s='Restore defaults for thirty bees' mod='nocaptcharecaptcha'}
				</button>
			</div>
			<br/>
			<br/>
			<label class="control-label col-lg-2 required">
				{l s='jQuery selector' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<input type="text" name="NCRC_PASSWORDSELECT" id="NCRC_PASSWORDSELECT"
					   value="{$NCRC_PASSWORDSELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
					   required="required">
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2 required">
				{l s='Position' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<div class="col-lg-10">
					{html_options name="NCRC_PASSWORDPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_PASSWORDPOS class=" fixed-width-xl" id="NCRC_PASSWORDPOS"}
				</div>
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2">
				{l s='Password captcha HTML' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<div class="col-lg-10 ace-container">
					<div class="ace-editor ace-html-editor" data-name="passwordCaptchaHTML"
						 id="passwordCaptchaHTMLACE">{$NCRC_PASSWORDHTML|escape:'htmlall':'UTF-8'}</div>
					<input type="hidden" id="passwordCaptchaHTML" name="NCRC_PASSWORDHTML">
				</div>
			</div>
			<div class="clear"></div>
			<hr style="border: solid 1px lightgrey;">
			<div class="btn-group" role="group">
				<button type="button" class="button" id="restoreContact16">
					{l s='Restore defaults for thirty bees' mod='nocaptcharecaptcha'}
				</button>
			</div>
			<br/>
			<br/>
			<label class="control-label col-lg-2 required">
				{l s='jQuery selector' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<input type="text" name="NCRC_CONTACTSELECT" id="NCRC_CONTACTSELECT"
					   value="{$NCRC_CONTACTSELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
					   required="required">
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2 required">
				{l s='Position' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				{html_options name="NCRC_CONTACTPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_CONTACTPOS class=" fixed-width-xl" id="NCRC_CONTACTPOS"}
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2">
				{l s='Contact captcha HTML' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<div class="col-lg-10 ace-container">
					<div class="ace-editor ace-html-editor" data-name="contactCaptchaHTML"
						 id="contactCaptchaHTMLACE">{$NCRC_CONTACTHTML|escape:'htmlall':'UTF-8'}</div>
					<input type="hidden" id="contactCaptchaHTML" name="NCRC_CONTACTHTML">
				</div>
			</div>
			<hr style="border: solid 1px lightgrey;">
			<div class="btn-group" role="group">
				<button type="button" class="button" id="restoreOPCLogin16">
					{l s='Restore defaults for thirty bees' mod='nocaptcharecaptcha'}
				</button>
			</div>
			<br/>
			<br/>
			<label class="control-label col-lg-2 required">
				{l s='jQuery selector' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<input type="text" name="NCRC_OPCLOGINSELECT" id="NCRC_OPCLOGINSELECT"
					   value="{$NCRC_OPCLOGINSELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
					   required="required">
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2 required">
				{l s='Position' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				{html_options name="NCRC_OPCLOGINPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_OPCLOGINPOS class=" fixed-width-xl" id="NCRC_OPCLOGINPOS"}
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2">
				{l s='OPC Login captcha HTML' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<div class="ace-container">
					<div class="ace-editor ace-html-editor" data-name="opcloginCaptchaHTML"
						 id="opcloginCaptchaHTMLACE">{$NCRC_OPCLOGINHTML|escape:'htmlall':'UTF-8'}</div>
					<input type="hidden" id="opcloginCaptchaHTML" name="NCRC_OPCLOGINHTML">
				</div>
			</div>

			<label class="control-label col-lg-2 required">
				{l s='jQuery selector' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<input type="text" name="NCRC_OPCCREATESELECT" id="NCRC_OPCCREATESELECT"
					   value="{$NCRC_OPCCREATESELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
					   required="required">
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2 required">
				{l s='Position' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				{html_options name="NCRC_OPCCREATEPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_OPCCREATEPOS class=" fixed-width-xl" id="NCRC_OPCCREATEPOS"}
			</div>
			<div class="clear"></div>
			<label class="control-label col-lg-2">
				{l s='OPC Create captcha HTML' mod='nocaptcharecaptcha'}
			</label>
			<div class="margin-form">
				<div class="ace-container">
					<div class="ace-editor ace-html-editor" data-name="opccreateCaptchaHTML"
						 id="opccreateCaptchaHTMLACE">{$NCRC_OPCCREATEHTML|escape:'htmlall':'UTF-8'}</div>
					<input type="hidden" id="opccreateCaptchaHTML" name="NCRC_OPCCREATEHTML">
				</div>
			</div>

			<div class="clear"></div>

			<hr style="border: solid 1px lightgrey;">
			<div class="form-wrapper">
				<div class="btn-group" role="group">
					<button type="button" class="button" id="restoreCSS16">
						{l s='Restore default CSS for thirty bees' mod='nocaptcharecaptcha'}
					</button>
				</div>
				<br/>
				<br/>

				<label class="control-label col-lg-2 required">
					{l s='Custom CSS' mod='nocaptcharecaptcha'}
				</label>
				<div class="margin-form">
					<div class="col-lg-10 ace-container">
						<div class="ace-editor ace-css-editor" data-name="captchaCSS"
							 id="captchaCSSACE">{$NCRC_EXTRACSS|escape:'htmlall':'UTF-8'}</div>
						<input type="hidden" id="captchaCSS" name="NCRC_EXTRACSS">
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<div class="margin-form">
				<input type="submit" id="module_form_submit_btn" class="button" value="{l s='Save' mod='nocaptcharecaptcha'}"
					   name="submitRecaptchaAdvanced"></div>
		</fieldset>
	</form>
{else}
	<form id="module_form_advanced" class="defaultForm form-horizontal"
		  action="{$advancedAction|escape:'htmlall':'UTF-8'}" method="post"
		  enctype="multipart/form-data" novalidate="">
		<div class="panel" id="advanced-panel">
			<h3><i class="icon icon-gear"></i> {l s='Advanced settings' mod='nocaptcharecaptcha'}</h3>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#tab-login" data-toggle="tab">Login</a></li>
				<li><a href="#tab-register" data-toggle="tab">Register</a></li>
				<li><a href="#tab-password" data-toggle="tab">Password forgotten</a></li>
				<li><a href="#tab-contact" data-toggle="tab">Contact</a></li>
				<li><a href="#tab-opc" data-toggle="tab">OPC</a></li>
			</ul>
			<div class="advanced-tab">
				<div class="tab-content">
					<div class="tab-pane form-wrapper active" id="tab-login">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-default" id="restoreLogin16"><i
										class="icon icon-refresh"></i> {l s='Restore defaults for thirty bees' mod='nocaptcharecaptcha'}
							</button>
						</div>
						<br/>
						<br/>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='jQuery selector' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								<input type="text" name="NCRC_LOGINSELECT" id="NCRC_LOGINSELECT"
									   value="{$NCRC_LOGINSELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
									   required="required">
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='Position' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								{html_options name="NCRC_LOGINPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_LOGINPOS class=" fixed-width-xl" id="NCRC_LOGINPOS"}
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2">
								{l s='Login captcha HTML' mod='nocaptcharecaptcha'}
							</label>

							<div class="col-lg-10 ace-container">
								<div class="ace-editor ace-html-editor" data-name="loginCaptchaHTML"
									 id="loginCaptchaHTMLACE">{$NCRC_LOGINHTML|escape:'htmlall':'UTF-8'}</div>
								<input type="hidden" id="loginCaptchaHTML" name="NCRC_LOGINHTML">
							</div>
						</div>
					</div>
					<div class="tab-pane form-wrapper" id="tab-register">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-default" id="restoreCreate16"><i
										class="icon icon-refresh"></i> {l s='Restore defaults for thirty bees' mod='nocaptcharecaptcha'}
							</button>
						</div>
						<br/>
						<br/>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='jQuery selector' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								<input type="text" name="NCRC_CREATESELECT" id="NCRC_CREATESELECT"
									   value="{$NCRC_CREATESELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
									   required="required">
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='Position' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								{html_options name="NCRC_CREATEPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_CREATEPOS class=" fixed-width-xl" id="NCRC_CREATEPOS"}
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2">
								{l s='Register captcha HTML' mod='nocaptcharecaptcha'}
							</label>

							<div class="col-lg-10 ace-container">
								<div class="ace-editor ace-html-editor" data-name="registerCaptchaHTML"
									 id="registerCaptchaHTMLACE">{$NCRC_CREATEHTML|escape:'htmlall':'UTF-8'}</div>
								<input type="hidden" id="registerCaptchaHTML" name="NCRC_CREATEHTML">
							</div>
						</div>
					</div>
					<div class="tab-pane form-wrapper" id="tab-password">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-default" id="restorePassword16"><i
										class="icon icon-refresh"></i> {l s='Restore defaults for thirty bees' mod='nocaptcharecaptcha'}
							</button>
						</div>
						<br/>
						<br/>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='jQuery selector' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								<input type="text" name="NCRC_PASSWORDSELECT" id="NCRC_PASSWORDSELECT"
									   value="{$NCRC_PASSWORDSELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
									   required="required">
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='Position' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								{html_options name="NCRC_PASSWORDPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_PASSWORDPOS class=" fixed-width-xl" id="NCRC_PASSWORDPOS"}
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2">
								{l s='Password captcha HTML' mod='nocaptcharecaptcha'}
							</label>

							<div class="col-lg-10 ace-container">
								<div class="ace-editor ace-html-editor" data-name="passwordCaptchaHTML"
									 id="passwordCaptchaHTMLACE">{$NCRC_PASSWORDHTML|escape:'htmlall':'UTF-8'}</div>
								<input type="hidden" id="passwordCaptchaHTML" name="NCRC_PASSWORDHTML">
							</div>
						</div>
					</div>
					<div class="tab-pane form-wrapper" id="tab-contact">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-default" id="restoreContact16"><i
										class="icon icon-refresh"></i> {l s='Restore defaults for thirty bees' mod='nocaptcharecaptcha'}
							</button>
						</div>
						<br/>
						<br/>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='jQuery selector' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								<input type="text" name="NCRC_CONTACTSELECT" id="NCRC_CONTACTSELECT"
									   value="{$NCRC_CONTACTSELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
									   required="required">
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='Position' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								{html_options name="NCRC_CONTACTPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_CONTACTPOS class=" fixed-width-xl" id="NCRC_CONTACTPOS"}
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2">
								{l s='Contact captcha HTML' mod='nocaptcharecaptcha'}
							</label>

							<div class="col-lg-10 ace-container">
								<div class="ace-editor ace-html-editor" data-name="contactCaptchaHTML"
									 id="contactCaptchaHTMLACE">{$NCRC_CONTACTHTML|escape:'htmlall':'UTF-8'}</div>
								<input type="hidden" id="contactCaptchaHTML" name="NCRC_CONTACTHTML">
							</div>
						</div>
					</div>
					<div class="tab-pane form-wrapper" id="tab-opc">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-default" id="restoreOPCLogin16"><i
										class="icon icon-refresh"></i> {l s='Restore defaults for thirty bees' mod='nocaptcharecaptcha'}
							</button>
						</div>
						<br/>
						<br/>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='jQuery selector' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								<input type="text" name="NCRC_OPCLOGINSELECT" id="NCRC_OPCLOGINSELECT"
									   value="{$NCRC_OPCLOGINSELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
									   required="required">
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='Position' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								{html_options name="NCRC_OPCLOGINPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_OPCLOGINPOS class=" fixed-width-xl" id="NCRC_OPCLOGINPOS"}
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2">
								{l s='OPC Login captcha HTML' mod='nocaptcharecaptcha'}
							</label>

							<div class="col-lg-10 ace-container">
								<div class="ace-editor ace-html-editor" data-name="opcloginCaptchaHTML"
									 id="opcloginCaptchaHTMLACE">{$NCRC_OPCLOGINHTML|escape:'htmlall':'UTF-8'}</div>
								<input type="hidden" id="opcloginCaptchaHTML" name="NCRC_OPCLOGINHTML">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='jQuery selector' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								<input type="text" name="NCRC_OPCCREATESELECT" id="NCRC_OPCCREATESELECT"
									   value="{$NCRC_OPCCREATESELECT|escape:'htmlall':'UTF-8'}" class="" size="64"
									   required="required">
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2 required">
								{l s='Position' mod='nocaptcharecaptcha'}
							</label>
							<div class="col-lg-10">
								{html_options name="NCRC_OPCCREATEPOS" options=$NCRC_JQUERYOPTS selected=$NCRC_OPCCREATEPOS class=" fixed-width-xl" id="NCRC_OPCCREATEPOS"}
							</div>
						</div>

						<div class="form-group">
							<label class="control-label col-lg-2">
								{l s='OPC Create captcha HTML' mod='nocaptcharecaptcha'}
							</label>

							<div class="col-lg-10 ace-container">
								<div class="ace-editor ace-html-editor" data-name="opccreateCaptchaHTML"
									 id="opccreateCaptchaHTMLACE">{$NCRC_OPCCREATEHTML|escape:'htmlall':'UTF-8'}</div>
								<input type="hidden" id="opccreateCaptchaHTML" name="NCRC_OPCCREATEHTML">
							</div>
						</div>
					</div>
				</div>
			</div>
			<hr>
			<div class="form-wrapper">
				<div class="btn-group" role="group">
					<button type="button" class="btn btn-default" id="restoreCSS16"><i
								class="icon icon-refresh"></i> {l s='Restore default CSS for thirty bees' mod='nocaptcharecaptcha'}
					</button>
				</div>
				<br/>
				<br/>

				<div class="form-group">
					<label class="control-label col-lg-2 required">
						{l s='Custom CSS' mod='nocaptcharecaptcha'}
					</label>

					<div class="col-lg-10 ace-container">
						<div class="ace-editor ace-css-editor" data-name="captchaCSS"
							 id="captchaCSSACE">{$NCRC_EXTRACSS|escape:'htmlall':'UTF-8'}</div>
						<input type="hidden" id="captchaCSS" name="NCRC_EXTRACSS">
					</div>
				</div>
			</div>
			<div class="panel-footer">
				<button type="submit" value="1" name="submitRecaptchaAdvanced" class="btn btn-default pull-right">
					<i class="process-icon-save"></i> {l s='Save' mod='nocaptcharecaptcha'}
				</button>
			</div>
		</div>
	</form>
{/if}
