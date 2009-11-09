<div class="form-item">	
<fieldset>
<legend>Extended Care Report</legend>
<dl>
<dt>{$form.start_date.label}</dt><dd>{$form.start_date.html}</dd>
<dt>{$form.end_date.label}</dt><dd>{$form.end_date.html}</dd>
<dt>{$form.student_id.label}</dt><dd>{$form.student_id.html}</dd>
<dt>&nbsp;</dt><dd>{$form.include_morning.html}&nbsp;{$form.include_morning.label}</dd>
<dt>&nbsp;</dt><dd>{$form.show_details.html}&nbsp;{$form.show_details.label}</dd>
</dl>	 	
<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
 </dl>
</fieldset>
</div>

{if $summary}
<div>
<table class="selector">
  <tr class="columnheader">
     <th>Name</th>
     <th>Total Blocks</th>
{if $showDetails}
     <th>Details</th>
{/if}
  </tr>
{foreach from=$summary item=row}
{if $row.blockCharge > 0 OR $showDetails}
  <tr class="{cycle values="odd-row,even-row"}">
    <td>{$row.name}</td>
    <td>{$row.blockCharge}</td>
{if $showDetails}
    <td>
<table>
{foreach from=$row.details item=detail}
<tr>
       <td>{$detail.charge}</td>
       <td>{$detail.class}</td>
       <td>{$detail.signout}</td>
       <td>{$detail.message}</td>
</tr>
{/foreach}
</table>
    </td>
{/if}
  </tr>
{/if}
{/foreach}
</table>
</div>
{/if}