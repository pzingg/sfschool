<div class="form-item">	
<fieldset>
{if $action eq 64}
<legend> Disable Class </legend>
<div class="messages status"> 
        <dl> 
            <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt> 
            <dd>{ts}Do you want to Disable selected Class?{/ts}
        </dl> 
    </div> 
{/if}
{if $action eq 32}
<legend> Enable Class </legend>
<div class="messages status"> 
        <dl> 
            <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt> 
            <dd>{ts}Do you want to Enable selected Class?{/ts}
        </dl> 
    </div> 
{/if}
{if $action eq 2}
<legend> Edit Class Information </legend>
<dl>
{foreach from=$elements item=field}
<dt>{$form.$field.label}</dt><dd>{$form.$field.html}</dd>
{/foreach}
</dl>	 	
{/if}
<dl>
    <dt></dt>
    <dd>{$form.buttons.html}<dd/>
 </dl>
</fieldset>
</div>
