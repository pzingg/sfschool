<div class="form-item">
<fieldset><legend>{ts}Student Sign Out Sheet{/ts}</legend>
<br/>
<span class="success-status" id="new-status" style="display:none;">{ts}Student has been signed out.{/ts}</span>
<div>
Please enter your name in the "Pickup Person Name" box. Type the first few charaters of the student name in each "Student" box and choose the right child. If this is a school meeting please indicate so in the adjacent checkbox. Click "Sign Out" after you have entered the names of all the children you are picking up.
</div>
<br/>
<div>
<dl>
  <dt>{$form.pickup_name.label}</dt><dd>{$form.pickup_name.html}</dd>
  <dt>{$form.student_1.label}</dt><dd>{$form.student_1.html}&nbsp;{$form.at_school_meeting_1.html}</dd>
  <dt>{$form.student_2.label}</dt><dd>{$form.student_2.html}&nbsp;{$form.at_school_meeting_2.html}</dd>
  <dt>{$form.student_3.label}</dt><dd>{$form.student_3.html}&nbsp;{$form.at_school_meeting_3.html}</dd>
  <dt>{$form.student_4.label}</dt><dd>{$form.student_4.html}&nbsp;{$form.at_school_meeting_4.html}</dd>
  <dt>{$form.student_5.label}</dt><dd>{$form.student_5.html}&nbsp;{$form.at_school_meeting_5.html}</dd>
  <dt>{$form.student_6.label}</dt><dd>{$form.student_6.html}&nbsp;{$form.at_school_meeting_6.html}</dd>
</dl>
  <dl>
  <dt></dt><dd><input type="submit" name="Add" id="Add" value="Sign Out"></dd>
  </dl>
</div>
  
<div class="spacer"></div>
</fieldset>
</div>

{literal}
<script type="text/javascript">
    cj( function( ) {
        var contactUrl = {/literal}"{crmURL p='civicrm/ajax/sfschool/contactlist' h=0 q='nograde=1'}"{literal};
        
        cj("#student_1").autocomplete( contactUrl, {
            selectFirst: false, 
            matchContains: true 
          }).result(function(event, data, formatted) {
          	 cj("input[name=student_id_1]").val(data[1]);
        });

        cj("#student_2").autocomplete( contactUrl, {
            selectFirst: false, 
            matchContains: true 
          }).result(function(event, data, formatted) {
          	 cj("input[name=student_id_2]").val(data[1]);
        });

        cj("#student_3").autocomplete( contactUrl, {
            selectFirst: false, 
            matchContains: true 
          }).result(function(event, data, formatted) {
          	 cj("input[name=student_id_3]").val(data[1]);
        });

        cj("#student_4").autocomplete( contactUrl, {
            selectFirst: false, 
            matchContains: true 
          }).result(function(event, data, formatted) {
          	 cj("input[name=student_id_4]").val(data[1]);
        });

        cj("#student_5").autocomplete( contactUrl, {
            selectFirst: false, 
            matchContains: true 
          }).result(function(event, data, formatted) {
          	 cj("input[name=student_id_5]").val(data[1]);
        });

        cj("#student_6").autocomplete( contactUrl, {
            selectFirst: false, 
            matchContains: true 
          }).result(function(event, data, formatted) {
          	 cj("input[name=student_id_6]").val(data[1]);
        });
    

	var studentID_1  = '';
	var studentID_2  = '';
	var studentID_3  = '';
	var studentID_4  = '';
	var studentID_5  = '';
	var studentID_6  = '';
        var pickupName   = '';
        cj("#Add").click( function( event ) {
            event.preventDefault( );
            student_id_1  = cj("input[name=student_id_1]").val( );
            at_school_meeting_1  = cj("#at_school_meeting_1").attr('checked');
            student_id_2  = cj("input[name=student_id_2]").val( );
            at_school_meeting_2  = cj("#at_school_meeting_2").attr('checked');
            student_id_3  = cj("input[name=student_id_3]").val( );
            at_school_meeting_3  = cj("#at_school_meeting_3").attr('checked');
            student_id_4  = cj("input[name=student_id_4]").val( );
            at_school_meeting_4  = cj("#at_school_meeting_4").attr('checked');
            student_id_5  = cj("input[name=student_id_5]").val( );
            at_school_meeting_5  = cj("#at_school_meeting_5").attr('checked');
            student_id_6  = cj("input[name=student_id_6]").val( );
            at_school_meeting_6  = cj("#at_school_meeting_6").attr('checked');
            pickupName = cj("#pickup_name").val( );
            if ( ( student_id_1 || student_id_2 || student_id_3 || student_id_4 || student_id_5 || student_id_6 ) && 
	           pickupName ) {
                 var dataUrl = {/literal}"{crmURL p='civicrm/ajax/sfschool/signout' h=0 }"{literal};
                 cj.post( dataUrl, { studentID_1: student_id_1,
		                     atSchoolMeeting_1: at_school_meeting_1,
                                     studentID_2: student_id_2,
		                     atSchoolMeeting_2: at_school_meeting_2,
                                     studentID_3: student_id_3,
		                     atSchoolMeeting_3: at_school_meeting_3,
                                     studentID_4: student_id_4,
		                     atSchoolMeeting_4: at_school_meeting_4,
                                     studentID_5: student_id_5,
		                     atSchoolMeeting_5: at_school_meeting_5,
                                     studentID_6: student_id_6,
		                     atSchoolMeeting_6: at_school_meeting_6,
                                     pickupName: pickupName },
                    function(data){
                        // success action
                        var students = '';
                        if ( cj("#student_1").val( ) ) {
                            students = students + cj("#student_1").val( )
                        }
                        
                        if ( cj("#student_2").val( ) ) {
                            students = students + ', ' +cj("#student_2").val( )
                        }

                        if ( cj("#student_3").val( ) ) {
                            students = students + ', ' +cj("#student_3").val( )
                        }

                        if ( cj("#student_4").val( ) ) {
                            students = students + ', ' +cj("#student_4").val( )
                        }

                        if ( cj("#student_5").val( ) ) {
                            students = students + ', ' +cj("#student_5").val( )
                        }

                        if ( cj("#student_6").val( ) ) {
                            students = students + ', ' +cj("#student_6").val( )
                        }
                        
                        var message = students + ' has been signed out.';
                        cj("#new-status").html( message );
                    	cj("#new-status").show( );
                    	
                        cj("#pickup_name").val( '' );
                        cj("#student_1").val( '' )
                      	cj("input[name=student_id_1]").val( '' );
                        cj("#at_school_meeting_1").removeAttr('checked');
                        cj("#student_2").val( '' )
                      	cj("input[name=student_id_2]").val( '' );
                        cj("#at_school_meeting_2").removeAttr('checked');
                        cj("#student_3").val( '' )
                      	cj("input[name=student_id_3]").val( '' );
                        cj("#at_school_meeting_3").removeAttr('checked');
                        cj("#student_4").val( '' )
                      	cj("input[name=student_id_4]").val( '' );
                        cj("#at_school_meeting_4").removeAttr('checked');
                        cj("#student_5").val( '' )
                      	cj("input[name=student_id_5]").val( '' );
                        cj("#at_school_meeting_5").removeAttr('checked');
                        cj("#student_6").val( '' )
                      	cj("input[name=student_id_6]").val( '' );
                        cj("#at_school_meeting_6").removeAttr('checked');
            	    }
            	);
            }
        });
	
	    cj(".success-status").click( function( ) {
	        cj(this).hide( );
	    });
  });

</script>
{/literal}
