{foreach from=$schedule key=day item=dayValues}
<div>
<fieldset><legend>Activities for {$day}</legend>
<table class="report-layout">
  <tr>
     <th>Class Name</th>
     <th>Time</th>
     <th>Instructor</th>
     <th>Fees</th>
     <th>Grade(s)</th>
     <th>Location</th>
     <th>&nbsp;</th>
  </tr>
  {foreach from=$dayValues item=class}
  <tr class="{cycle values="odd-row,even-row"}">
    <td>{$class.name}</td>
    <td>{$class.session}</td>
    <td>{$class.instructor}</td>
{if $class.fee_block > 0}
    <td>{$class.fee_block}</td>
{else}
    <td>&nbsp;</td>
{/if}
{if $class.min_grade == 1 && $class.max_grade == 8}
    <td>All Grades</td>
{else}
    <td>Grades {$class.min_grade} - {$class.max_grade}</td>
{/if}
    <td>{$class.location}</td>
{if $class.url}
    <td><a href="javascript:popUp('{$class.url}')">More Info</a></td>
{else}
    <td>&nbsp;</td>
{/if}
  </tr>
  {/foreach}
</table>
</fieldset>
</div>
{/foreach}
