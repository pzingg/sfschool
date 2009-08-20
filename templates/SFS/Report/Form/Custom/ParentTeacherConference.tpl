{include file="CRM/Report/Form/Fields.tpl"}
{include file="CRM/Report/Form/Statistics.tpl" top=true}

    {if $contactSelected}
        <div class="report-pager">
            {include file="CRM/common/pager.tpl" noForm=1}
        </div>
        {foreach from=$contactSelected item=contact key=contactId}
            <table><tr><td> 
	    <table class="report-layout">
                <tr><th>Student</th><th>Grade</th></tr>
		<tr><td>{$contact.display_name}</td><td>{$contact.grade}</td></tr>
	    </table>
	    {if $paraentInfo.$contactId}
	    <table class="report-layout">
	        <tr>
                    {foreach from=$headersParentInfo item=header key=field}             
                        <th>{$header.title}</th>
                    {/foreach}
                </tr>
		{foreach from=$paraentInfo.$contactId item=row}
		    <tr>
		    {foreach from=$headersParentInfo item=header key=field}
		        <td> {$row.$field}</td>
		    {/foreach}
		    </tr>
		{/foreach}
	    </table>
	    {/if}

	    {if $activityInfo.$contactId}
	    <table class="report-layout">
                <tr>
                    {foreach from=$headersActivityInfo item=headerAct key=fieldAct}             
                        <th>{$headerAct.title}</th>
                    {/foreach}
                </tr>
		{foreach from=$activityInfo.$contactId item=rowAct}
	            <tr>
		    {foreach from=$headersActivityInfo item=headerAct key=fieldAct}
		        <td>{$rowAct.$fieldAct}</td>
		    {/foreach}
		    </tr>
		{/foreach}
	    </table>
	    {/if}
            </td></tr></table>
	{/foreach}
	{include file="CRM/Report/Form/Statistics.tpl" bottom=true}                
          
     {/if}

                          
 {include file="CRM/Report/Form/ErrorMessage.tpl"}
