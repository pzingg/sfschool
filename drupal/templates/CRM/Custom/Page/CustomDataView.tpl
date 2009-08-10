{if $groupId != 2}
  {include file="CRM/Custom/Page/StandardCustomDataView.tpl"}
{else}
<table>
<tr>
  <th>Term</th>
  <th>Day</th>
  <th>Session</th>
  <th>Name</th>
  <th>Description</th>
  <th>Instructor</th>
  <th>Fee Block</th>
  <th>Start Date</th>
  <th>End Date</th>
  <th></th>
</tr>
{assign var="showEdit" value=1}
{foreach from=$viewCustomData item=customValues key=customGroupId}
{foreach from=$customValues item=cd_edit key=cvID}
    {assign var='index' value=$groupId|cat:"_$cvID"}
    {if $showEdit and $editCustomData and $groupId}	
      <div class="action-link">
        <a href="{crmURL p="civicrm/contact/view/cd/edit" q="tableId=`$contactId`&cid=`$contactId`&groupId=`$groupId`&action=update&reset=1"}" class="button" style="margin-left: 6px;"><span>&raquo; {ts 1=$cd_edit.title}Edit %1 Records{/ts}</span></a><br/><br/>
      </div>      
    {/if}
    {assign var="showEdit" value=0}
<tr>
  {foreach from=$cd_edit.fields item=element key=field_id}
  <td>{$element.field_value}</td>
  {/foreach}
  <td>&nbsp;&nbsp;&nbsp;<a href="javascript:showDelete( {$cvID}, '{$cd_edit.name}_{$index}', {$customGroupId} );"><img title="delete this record" src="{$config->resourceBase}i/delete.png" class="action-icon" alt="{ts}delete this record{/ts}" /></a></td>
</tr>
{/foreach}
{/foreach}
</table>
{/if}