{if ! empty( $row )} 
{* wrap in crm-container div so crm styles are used *}
<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
<fieldset>
<table class="form-layout-compressed">
{assign var=fName value="First Name"}
{assign var=lName value="Last Name"}
  <tr id="contact_name"><td class="label">Name</td><td class="view-value">{$row.$fName}&nbsp;{$row.$lName}</td></tr>
  <tr id="contact_email"><td class="label">Email</td><td class="view-value">{$row.Email}</td></tr> 
  <tr id="contact_phone"><td class="label">Phone</td><td class="view-value">{$row.Phone}</td></tr> 
</table>
</fieldset>
{if $parentInfo}
<fieldset>
<legend>Parent Information</legend>
<table class="form-layout-compressed">
  <tr><th>Name</th></tr>
  {foreach from=$parentInfo key=dontCare item=parent}
  <tr>
     <td><a href="{crmURL p='civicrm/profile/view' q="reset=1&gid=3&id=`$parent.id`"}">{$parent.name}</a></td>
  </tr>
  {/foreach}
</table>
</fieldset>
{/if}
</div>
{/if} 
{* fields array is not empty *}