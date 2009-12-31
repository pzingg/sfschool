<div class="form-item">	
<fieldset>
<legend>Conference Creation Wizard</legend>
<dl>
<dt>{$form.advisor_id.label}</dt><dd>{$form.advisor_id.html}</dd>
<dt>{$form.ptc_date.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date}</dd>
<dt>{$form.ptc_duration.label}</dt><dd>{$form.ptc_duration.html}</dd>
<dt>{$form.ptc_date_1.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date_1}</dd>
<dt>{$form.ptc_date_2.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date_2}</dd>
<dt>{$form.ptc_date_3.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date_3}</dd>
<dt>{$form.ptc_date_4.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date_4}</dd>
<dt>{$form.ptc_date_5.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date_5}</dd>
<dt>{$form.ptc_date_6.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date_6}</dd>
<dt>{$form.ptc_date_7.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date_7}</dd>
<dt>{$form.ptc_date_8.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date_8}</dd>
<dt>{$form.ptc_date_9.label}</dt><dd>{include file="CRM/common/jcalendar.tpl" elementName=ptc_date_9}</dd>
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

{literal}
<script type="text/javascript">
    for (var i=1; i<=9; i++) {
        cj('#ptc_date_' + i).hide( );
        cj('label[for="ptc_date_' + i + '_time"]').hide( );
    }
</script>
{/literal}