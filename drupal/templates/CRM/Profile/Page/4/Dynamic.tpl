{if ! empty( $row )} 
{* wrap in crm-container div so crm styles are used *}
<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
<fieldset>
<table class="form-layout-compressed">
{assign var=fName value="First Name"}
{assign var=lName value="Last Name"}
  <tr id="contact_name"><td class="label">Student Name</td><td class="view-value">{$row.$fName}&nbsp;{$row.$lName}</td></tr>
</table>
</fieldset>
{if $childInfo.parents}
<fieldset>
<legend>Parent Information</legend>
<table class="form-layout-compressed">
  <tr><th>Parent Name</th><th>Email</th><th>Phone</th></tr>
  {foreach from=$childInfo.parents key=dontCare item=parent}
  <tr>
     <td><a href="{crmURL p='civicrm/profile/view' q="reset=1&gid=3&id=`$parent.id`"}">{$parent.name}</a></td>
     <td>{$parent.email}</td>
     <td>{$parent.phone}</td>
  </tr>
  {/foreach}
</table>
</fieldset>
{/if}
{if $childInfo.extendedCare}
<fieldset>
<legend>Extended Care Information</legend>
<table class="form-layout-compressed">
  <tr><th>Day</th><th>Class</th><th>Description</th><th>Instructor</th></tr>
  {foreach from=$childInfo.extendedCare key=dontCare item=class}
  <tr>
     <td>{$class.day}</td>
     <td>{$class.name}</td>
     <td>{$class.desc}</td>
     <td>{$class.instructor}</td>
  </tr>
  {/foreach}
</table>
</fieldset>
{/if}

</div>
{/if} 
{* fields array is not empty *}