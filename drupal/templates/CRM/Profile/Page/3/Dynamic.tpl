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
<fieldset>
<legend>Child Information</legend>
<table class="form-layout-compressed">
 <tr><th>Child Name</th><th>Grade</th><th>Parent Teacher Meetings</tr>
  {foreach from=$childrenInfo key=dontCare item=child}
  <tr>
     <td><a href="{crmURL p='civicrm/profile/view' q="reset=1&gid=4&id=`$child.id`"}">{$child.name}</a></td>
     <td>{$child.grade}</td>
     <td>{$child.meeting}</td>
  </tr>
  {/foreach}
</table>
</fieldset>
{/if}
</div>
{/if} 
{* fields array is not empty *}