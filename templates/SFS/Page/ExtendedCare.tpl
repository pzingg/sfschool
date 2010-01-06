{if $detail}
<div>
<h2>Total Extended Care Activity Blocks for {$detail.name}: {if $detail.doNotCharge}0 ({$detail.doNotCharge}, {$detail.blockCharge}){else}{$detail.blockCharge}{/if}</h2>
<br/>
<table class="selector">
  <tr class="columnheader">
     <th>Number of Blocks</th>
     <th>Class</th>
     <th>Time</th>
     <th>Message</th>
     {if $enableActions}
     <th>&nbsp;#</th>
     {/if}
  </tr>
{foreach from=$detail.details item=detail}
<tr>
       <td>{$detail.charge}</td>
       <td>{$detail.class}</td>
       <td>{$detail.signout}{if $detail.pickup} by {$detail.pickup}{/if}</td>
       <td>{$detail.message}</td>
       {if $enableActions}
       <td>{$detail.action}</td>
       {/if}
</tr>
{/foreach}
</table>
</div>
<div class="footer" id="civicrm-footer">
If the above information is incorrect, please send a detailed email to <a href="mailto:rbrown@sfschool.org">Rahna Hassett</a>
</div>
{else}
<div>
No Extended Care Activity recorded for {$displayName}
{/if}
