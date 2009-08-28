{foreach from=$schedule key=day item=dayValues}
<div>
<fieldset><legend>Activities for {$day}</legend>
<table class="report-layout">
  <tr>
     <th style="width: 20%">Class Name</th>
     <th style="width: 15%">Time</th>
     <th style="width: 15%">Instructor</th>
     <th style="width: 5%">Fees</th>
     <th style="width: 15%">Grade(s)</th>
     <th style="width: 20%">Location</th>
     <th style="width: 10%">&nbsp;</th>
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
