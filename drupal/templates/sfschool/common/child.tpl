{if $childInfo.meeting}
<fieldset>
<legend>Parent Teacher Conference Information</legend>
<div>
{$childInfo.meeting.title}
</div>
<br/>
<div>
{$childInfo.meeting.edit}
</div>
</fieldset>
{/if}
{if $childInfo.extendedCare}
<fieldset>
<legend>Extended Care Information</legend>
<table class="form-layout-compressed">
  <tr><th>Day</th><th>Time</th><th>Class</th><th>Description</th><th>Instructor</th></tr>
  {foreach from=$childInfo.extendedCare key=dontCare item=class}
  <tr>
     <td>{$class.day}</td>
     <td>{$class.time}</td>
     <td>{$class.name}</td>
     <td>{$class.desc}</td>
     <td>{$class.instructor}</td>
  </tr>
  {/foreach}
</table>
{if $childInfo.extendedCareEdit}
<br/>
<div>
<a href="{$childInfo.extendedCareEdit}">Manage extended care schedule for {$childInfo.name}</a>
</div>
{/if}
</fieldset>
{/if}
