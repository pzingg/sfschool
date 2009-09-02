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
{if $childInfo.extendedCare OR $childInfo.extendedCareEdit}
<fieldset>
<legend>Extended Care Information</legend>
{if $childInfo.extendedCare}
<table class="form-layout-compressed">
  <tr><th>Day</th><th>Time</th><th>Class</th><th>Description</th><th>Instructor</th><th></th></tr>
  {foreach from=$childInfo.extendedCare key=dontCare item=class}
  <tr>
     <td>{$class.day}</td>
     <td>{$class.time}</td>
     <td>{$class.name}</td>
     <td>{$class.desc}</td>
     <td>{$class.instructor}</td>
     <td><a href="{$childInfo.extendedCareEdit}">Edit</a></td>
  </tr>
  {/foreach}
</table>
<br/>
{/if}
{if $childInfo.extendedCareEdit}
<div>
<a href="{$childInfo.extendedCareEdit}">Manage extended care schedule for {$childInfo.name}</a>
</div>
{/if}
<div>
<br/>
<a href="http://www.sfschool.org/programs/extended/extended_day_schedule.pdf">Download the Extended Care Program Schedule and Details</a>
</div>
</fieldset>
{/if}
