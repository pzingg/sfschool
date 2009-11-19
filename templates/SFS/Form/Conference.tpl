<div class="form-item">	
<fieldset>
<legend>Conference Creation Wizard</legend>
<dl>
<dt>{$form.advisor_id.label}</dt><dd>{$form.advisor_id.html}</dd>
<dt>{$form.ptc_date.label}</dt><dd>{$form.ptc_date.html}</dd>
<dt>{$form.ptc_duration.label}</dt><dd>{$form.ptc_duration.html}</dd>
<dt>{$form.ptc_time_1.label}</dt><dd>{$form.ptc_time_1.html}</dd>
<dt>{$form.ptc_time_2.label}</dt><dd>{$form.ptc_time_2.html}</dd>
<dt>{$form.ptc_time_3.label}</dt><dd>{$form.ptc_time_3.html}</dd>
<dt>{$form.ptc_time_4.label}</dt><dd>{$form.ptc_time_4.html}</dd>
<dt>{$form.ptc_time_5.label}</dt><dd>{$form.ptc_time_5.html}</dd>
<dt>{$form.ptc_time_6.label}</dt><dd>{$form.ptc_time_6.html}</dd>
<dt>{$form.ptc_time_7.label}</dt><dd>{$form.ptc_time_7.html}</dd>
<dt>{$form.ptc_time_8.label}</dt><dd>{$form.ptc_time_8.html}</dd>
<dt>{$form.ptc_time_9.label}</dt><dd>{$form.ptc_time_9.html}</dd>
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
       <td>{$detail.signout}{if $detail.pickup} by {$detail.pickup}{/if}</td>
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
