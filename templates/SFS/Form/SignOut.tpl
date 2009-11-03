<div class="form-item">
<fieldset><legend>{ts}Student Sign Out Sheet{/ts}</legend>
  <dl>
     <dt>{$form.pickup_name.label}</dt><dd>{$form.pickup_name.html}</dd>
     {section start=1 name=rows loop=$maxNumber}
       {assign var=rowName value='grade_student_id_'|cat:$smarty.section.rows.index}
       <dt>{$form.$rowName.label}</dt><dd>{$form.$rowName.html}</dd>
     {/section}
  </dl>
        <dt></dt><dd>{$form.buttons.html}</dd>
       </dl>
<div class="spacer"></div>
</fieldset>
</div>

