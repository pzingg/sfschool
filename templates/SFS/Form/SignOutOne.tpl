<div class="form-item">
<fieldset><legend>{ts}Student Sign Out Sheet for {/ts} {$date}</legend>
<br/>
<span class="success-status" id="new-status" style="display:none;">{ts}Student has been signed out.{/ts}</span>
<div>
Please enter your name in the "Pickup Person Name" box. Type the first few charaters of the student name in each "Student" box and choose the right child. If this is a school meeting please indicate so in the adjacent checkbox. Click "Sign Out" after you have entered the names of all the children you are picking up.
</div>
<br/>
<div>
<dl>
  <dt>{$form.pickup_name.label}</dt><dd>{$form.pickup_name.html}</dd>
  <dt>{$form.student_id_1.label}</dt><dd>{$form.student_id_1.html}&nbsp;{$form.at_school_meeting_1.html}</dd>
  <dt>{$form.student_id_2.label}</dt><dd>{$form.student_id_2.html}&nbsp;{$form.at_school_meeting_2.html}</dd>
  <dt>{$form.student_id_3.label}</dt><dd>{$form.student_id_3.html}&nbsp;{$form.at_school_meeting_3.html}</dd>
  <dt>{$form.student_id_4.label}</dt><dd>{$form.student_id_4.html}&nbsp;{$form.at_school_meeting_4.html}</dd>
  <dt>{$form.student_id_5.label}</dt><dd>{$form.student_id_5.html}&nbsp;{$form.at_school_meeting_5.html}</dd>
  <dt>{$form.student_id_6.label}</dt><dd>{$form.student_id_6.html}&nbsp;{$form.at_school_meeting_6.html}</dd>
</dl>
  <dl>
  <dt></dt><dd>{$form.buttons.html}</dd>
  </dl>
</div>
  
<div class="spacer"></div>
</fieldset>
</div>

