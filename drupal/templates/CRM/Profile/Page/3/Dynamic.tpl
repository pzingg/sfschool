{if ! empty( $row )} 
{* wrap in crm-container div so crm styles are used *}
<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
<fieldset>
<table class="form-layout-compressed">
{assign var=fName value="First Name"}
{assign var=lName value="Last Name"}
  <tr id="contact_name"><td class="label">Parent Name</td><td class="view-value">{$row.$fName}&nbsp;{$row.$lName}</td></tr>
  <tr id="contact_email"><td class="label">Email</td><td class="view-value">{$row.Email}</td></tr> 
  <tr id="contact_phone"><td class="label">Phone</td><td class="view-value">{$row.Phone}</td></tr> 
</table>
</fieldset>
{if $childrenInfo}
{foreach from=$childrenInfo key=dontCare item=childInfo}
<fieldset>
<legend>{$childInfo.name} Information</legend>
{include file="sfschool/common/child.tpl"}
</fieldset>
{/foreach}
{/if}
</div>
{/if} 
{* fields array is not empty *}