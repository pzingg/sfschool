<div id="customData">
<div id="class_edit" class="section-shown form-item">
<fieldset>
<legend>
Edit Class Information
</legend>
<dl>
{foreach from=$elements item=field}
<dt>{$form.$field.label}</dt><dd>{$form.$field.html}</dd>
{/foreach}
</dl>	 	
</fieldset>
</div>
<div class="html-adjust">
{$form.buttons.html}
</div>
</div>
