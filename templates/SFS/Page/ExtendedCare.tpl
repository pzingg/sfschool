{if $signoutDetail}
<div>
<h2>Total Extended Care Activity Blocks for {$signoutDetail.name}: {if $signoutDetail.doNotCharge}0 ({$signoutDetail.doNotCharge}, {$signoutDetail.blockCharge}){else}{$signoutDetail.blockCharge}{/if}</h2>
<br/>
<table class="selector">
  <tr class="columnheader">
     <th>Number of Blocks</th>
     <th>Class</th>
     <th>Time</th>
     <th>Message</th>
     {if $enableActions}
     <th>&nbsp;</th>
     {/if}
  </tr>
{foreach from=$signoutDetail.details item=detail}
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

{if $enableActions}
    <div class="action-link">
        <a href="{$addActivityBlock}" class="button"><span>&raquo; {ts}Add Activity Block{/ts}</span></a>
    </div>
    <div class="spacer"></div>
{/if}

{/if}

{if $feeDetail}
<br/>
<br/>
<div>
<h2>Total Extended Care Fee Details for {$feeDetail.name}</h2>
<br/>
<table class="selector">
  <tr class="columnheader">
     <th>Category</th>
     <th>Description</th>
     <th>Date</th>
     <th>Total Blocks</th>
     {if $enableActions}
     <th>&nbsp;</th>
     {/if}
  </tr>
{foreach from=$feeDetail.details item=detail}
<tr>
       <td>{$detail.category}</td>
       <td>{$detail.description}</td>
       <td>{$detail.fee_date}</td>
       <td>{$detail.total_blocks}</td>
       {if $enableActions}
       <td>{$detail.action}</td>
       {/if}
</tr>
{/foreach}
</table>
</div>
{/if}

{if $enableActions}
    <div class="action-link">
        <a href="{$addFeeEntity}" class="button"><span>&raquo; {ts}Add Fee Entry{/ts}</span></a>
    </div>
    <div class="spacer"></div>
{/if}

{if $signoutDetail OR $feeDetail}
<div class="footer" id="civicrm-footer">
If the above information is incorrect, please send a detailed email to <a href="mailto:rbrown@sfschool.org">Rahna Hassett</a>
</div>
{else}
<div>
No Extended Care Activity recorded for {$displayName}
</div>
{/if}
