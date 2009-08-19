{include file="CRM/Report/Form/Fields.tpl"}
{include file="CRM/Report/Form/Statistics.tpl" top=true}

    {if $rows}
        <div class="report-pager">
            {include file="CRM/common/pager.tpl" noForm=1}
        </div>
        {foreach from=$dayHeaders item=days key=dayId}
            <div>&nbsp;<u><strong> {$days|upper}</strong></u></div>
            <table ><tr><td>      
            {foreach from=$termHeaders.$dayId item=terms key=termId}
               
                <table class="report-layout">
                <tr><td colspan=3><strong> {$termHeaders.$dayId.$termId}</strong></td></tr>
                        <tr>
                        {foreach from=$columnHeaders item=header key=field}             
                             <th>{$header.title}</th>
                        {/foreach}
                        </tr>
                        
                        {foreach from=$rows.$dayId.$termId item=row}
                            <tr>
                            {foreach from=$columnHeaders item=header key=field}
 			        {assign var=fieldLink value=$field|cat:"_link"}
                                {assign var=fieldHover value=$field|cat:"_hover"}
                                <td> 
				{if $row.$fieldLink}<a title="{$row.$fieldHover}" href="{$row.$fieldLink}"> {/if}
                                {$row.$field}
                                {if $row.$fieldLink}</a>{/if}
                                </td>
                            {/foreach}
                            </tr>
                        {/foreach}
                   
                    </table><table></table>
             {/foreach}
            </td></tr> </table>
             {/foreach}      
          {include file="CRM/Report/Form/Statistics.tpl" bottom=true}                
     {/if}

                          
 {include file="CRM/Report/Form/ErrorMessage.tpl"}
