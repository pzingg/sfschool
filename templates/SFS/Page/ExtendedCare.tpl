{if $action neq 4}
    {if $monthlySignout OR $feeDetail}
    <div>
    <h2>Total Extended Care Fee Details for {$feeDetail.name}</h2>
    <br/>
{if $balanceDetails}
    <div class="messages status">
Extended Day Block charges for {$childName}:  {$balanceDetails.blockCharges}
<br/>
Extended Day Extra Fee class block charges (Sept - Dec 2009) for {$childName}:  {$balanceDetails.classCharges} **
<br/>
<br/>
Extended Day Total charges for {$childName}:  {$balanceDetails.totalCharges}
<br/>
Extended Day Block payment for {$childName}:  {$balanceDetails.totalPayments}
{if $balanceDetails.balanceDue GT 0}
<br/>
<br/>
Extended Day Balance Due: {$balanceDetails.balanceDue}
{else}
<br/>
<br/>
Extended Day Balance Credit: {$balanceDetails.balanceCredit}
{/if}
<br/>
<br/>
Extended Day Rates:
<br/>
Less than 100 blocks pay $10.35 per block
<br/>
If total use is over 100 blocks or if on Indexed Tuition pay $8.80 per block
<br/>
<br/>
** These rates have NOT been discounted for Indexed tuition. Please contact the Business office for your pro-rated block charges.
<br/>
Please make cheques payable to The San Francisco School. If you have any questions, please contact
Rahna Brown at rbrown@sfschool.org
    </div>
{/if}

    <table class="selector">
        <tr class="columnheader">
     	    <th>Category</th>
     	    <th>Description</th>
     	    <th>Date</th>
     	    <th>Total Blocks</th>
            <th>&nbsp;</th>
  	</tr>
        {foreach from=$monthlySignout key=month item=detail}
          <tr>
       	    <td>{ts}Standard Fee{/ts}</td>
	    <td>{$detail.description}</td>
       	    <td>{$month}</td>
      	    <td>{$detail.blockCharge}</td>
            <td>{$detail.action}</td>
          </tr>
        {/foreach}
	{foreach from=$feeDetail.details item=detail}
	    {if $detail.fee_type eq 'Payment' OR $detail.fee_type eq 'Credit'}
                <tr class="row-selected">
	    {else}
	        <tr>
	    {/if}
       	    <td>{$detail.category}</td>
	    <td>{$detail.description}</td>
       	    <td>{$detail.fee_date}</td>
       	    <td>{$detail.total_blocks}</td>
            <td>
       	    {if $enableActions}{$detail.action}{else}&nbsp;{/if}
            </td>
	    </tr>
	{/foreach}
    </table>
    </div>
  {/if}
{/if}

{if $enableActions}
     <div class="action-link">
         <a href="{$addFeeEntity}" class="button"><span>&raquo; {ts}Add Fee Entry{/ts}</span></a>
         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         <a href="{$addActivityBlock}" class="button"><span>&raquo; {ts}Add Activity Block{/ts}</span></a>
     </div>
     <div class="spacer"></div>
{/if}

{if $signoutDetail}
    {if $action neq 4}
         <br/><br/>
    {/if}	 
    <div>
    {if $action eq 4}
        <h2>Total Extended Care Activity Blocks for {$signoutDetail.name}: {if $signoutDetail.doNotCharge}0 ({$signoutDetail.doNotCharge}, {$signoutDetail.blockCharge}){else}{$signoutDetail.blockCharge}{/if}</h2>
    {else} 
	<h2>Recent Extended Care Activity for {$signoutDetail.name} </h2>
    {/if}
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
{else}
    {if $action eq 4}
        <div>
        No Extended Care Activity recorded for {$displayName}
        </div>
    {/if}
{/if}

{if $action eq 4}
    <div class="action-link">
        <a href="{$backButtonUrl}" class="button"><span>&raquo; {ts}Done{/ts}</span></a>
    </div>
    <div class="spacer"></div>
{/if}

{if ($action neq 4) AND ($monthlySignout OR $feeDetail) }
    <div class="footer" id="civicrm-footer">
        If the above information is incorrect, please send a detailed email to <a href="mailto:rbrown@sfschool.org">Rahna Hassett</a>
    </div>
{/if}