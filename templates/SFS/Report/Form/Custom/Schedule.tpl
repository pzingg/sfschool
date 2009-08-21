
{include file="CRM/Report/Form/Fields.tpl"}
{include file="CRM/Report/Form/Statistics.tpl" top=true}

    {if $contactDetails}
        <div class="report-pager">
            {include file="CRM/common/pager.tpl" noForm=1}
        </div>

        {foreach from=$contactDetails item=contacts key=contactId}
            <table ><tr><td>
	        <table class="report-layout"><tr>
	        {foreach from=$contactHeaders item=header}
		    <th>{$header.title}</th>
		{/foreach} 
                </tr>
		<tr>
		{foreach from=$contactHeaders item=header key=field}
		    <td>{$contacts.$field}</td>	 
		{/foreach}
		</tr></table>
	        
		{if $activityDetails.$contactId}
		<table class="report-layout">
                    <tr>
	            {foreach from=$activityHeaders item=header}
		        <th>{$header.title}</th>
		    {/foreach}
		    </tr>
		    {foreach from=$activityDetails.$contactId item=contactActivity}
		        <tr>	
		        {foreach from=$activityHeaders item=header key=field}
                            <td>{$contactActivity.$field}</td>
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
