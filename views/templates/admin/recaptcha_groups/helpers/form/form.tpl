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

{extends file="helpers/form/form.tpl"}

{block name="input"}
	{if $input.type == 'desc'}
		{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
			<p class="description">{$input.text|escape:'htmlall':'UTF-8'}</p>
		{else}
			<div class="alert alert-info">{$input.text|escape:'htmlall':'UTF-8'}</div>
		{/if}
	{elseif $input.type == 'hr'}
		{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
			<hr style="display: block;-webkit-margin-before: 0.5em;-webkit-margin-after: 0.5em;
			-webkit-margin-start: auto;-webkit-margin-end: auto;border-style: inset;border-width: 1px;">
		{else}
			<hr>
		{/if}
	{elseif $input.type == 'br'}
		<br />
	{elseif $input.type == 'switch' && $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
		{foreach $input.values as $value}
			<input type="radio" name="{$input.name|escape:'htmlall':'UTF-8'}"
				   id="{$input.name|escape:'htmlall':'UTF-8'}_{$value.id|escape:'htmlall':'UTF-8'}"
				   value="{$value.value|escape:'htmlall':'UTF-8'}"
				   {if $fields_value[$input.name] == $value.value}checked="checked"{/if}
					{if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
			<label class="t" for="{$input.name|escape:'htmlall':'UTF-8'}_{$value.id|escape:'htmlall':'UTF-8'}">
				{if isset($input.is_bool) && $input.is_bool == true}
					{if $value.value == 1}
						<img src="../img/admin/enabled.gif" alt="{$value.label|escape:'htmlall':'UTF-8'}"
							 title="{$value.label|escape:'htmlall':'UTF-8'}" />
					{else}
						<img src="../img/admin/disabled.gif" alt="{$value.label|escape:'htmlall':'UTF-8'}"
							 title="{$value.label|escape:'htmlall':'UTF-8'}" />
					{/if}
				{else}
					{$value.label|escape:'htmlall':'UTF-8'}
				{/if}
			</label>
			{if isset($input.br) && $input.br}<br />{/if}
			{if isset($value.p) && $value.p}<p>{$value.p|escape:'htmlall':'UTF-8'}</p>{/if}
		{/foreach}
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
