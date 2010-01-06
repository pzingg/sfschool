<div class="form-item">	
<fieldset>

{if $action eq 8}
<legend>Delete Acivity Block</legend>
<div class="messages status"> 
        <dl> 
            <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt> 
            <dd>{ts}Do you want to Delete Activity block of student  '{$displayName}' for class  '{$class}' ?{/ts}</dd>
        </dl> 
</div> 
{/if}

{if $action eq 2}
<legend> Edit Activity Block </legend>

<dl>
<dt>{$form.entity_id.label}</dt>
<dd>{$form.entity_id.html}</dd>
</dl>
<dl>
<dt>{$form.pickup_person_name.label}</dt>
<dd>{$form.pickup_person_name.html}</dd>
</dl>
<dl>
<dt>{$form.signin_time.label}</dt>
<dd>{include file="CRM/common/jcalendar.tpl" elementName=signin_time}</dd>
</dl>
<dl>
<dt>{$form.signout_time.label}</dt>
<dd>{include file="CRM/common/jcalendar.tpl" elementName=signout_time}</dd>
</dl>
<dl>
<dt>{$form.class.label}</dt>
<dd>{$form.class.html}</dd>
</dl>	 	
<dl>
<dt>{$form.is_morning.label}</dt>
<dd>{$form.is_morning.html}</dd>
</dl>
<dl>
<dt>{$form.at_school_meeting.label}</dt>
<dd>{$form.at_school_meeting.html}</dd>
</dl>
{/if}
<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
 </dl>
</fieldset>
</div>
