<div class="form-item">
<fieldset><legend>{ts}Student Morning Extended Care Sheet for {$displayDate}{/ts}</legend>
<br/>
<span class="success-status" id="new-status" style="display:none;">{ts}Student have been signed in for morning extended care.{/ts}</span>
<br/>  
<div>
<dl>
  <dt>{$form.pickup_name.label}</dt><dd>{$form.pickup_name.html}</dd>
  <dt>{$form.student_id_1.label}</dt><dd>{$form.student_id_1.html}</dd>
  <dt>{$form.student_id_2.label}</dt><dd>{$form.student_id_2.html}</dd>
  <dt>{$form.student_id_3.label}</dt><dd>{$form.student_id_3.html}</dd>
  <dt>{$form.student_id_4.label}</dt><dd>{$form.student_id_4.html}</dd>
  <dt>{$form.student_id_5.label}</dt><dd>{$form.student_id_5.html}</dd>
  <dt>{$form.student_id_6.label}</dt><dd>{$form.student_id_6.html}</dd>
</dl>
  <dl>
  <dt></dt><dd><input type="submit" name="Add" id="Add" value="Morning Care SignIn"></dd>
  
<div class="spacer"></div>
</fieldset>
</div>

{literal}
<script type="text/javascript">
    cj( function( ) {
        {/literal}
        var sDate      = '{$date}';
        var sTime      = '{$time}';
        {literal}

	var studentID_1  = '';
	var studentID_2  = '';
	var studentID_3  = '';
	var studentID_4  = '';
	var studentID_5  = '';
	var studentID_6  = '';
        cj("#Add").click( function( event ) {
            event.preventDefault( );
            student_id_1  = cj("#student_id_1").val( );
            student_id_2  = cj("#student_id_2").val( );
            student_id_3  = cj("#student_id_3").val( );
            student_id_4  = cj("#student_id_4").val( );
            student_id_5  = cj("#student_id_5").val( );
            student_id_6  = cj("#student_id_6").val( );
            pickupName = cj("#pickup_name").val( );
            if ( ( student_id_1 || student_id_2 || student_id_3 || student_id_4 || student_id_5 || student_id_6 ) && 
	           pickupName ) {
                 var dataUrl = {/literal}"{crmURL p='civicrm/ajax/sfschool/morning' h=0 }"{literal};
                 cj.post( dataUrl, { studentID_1: student_id_1,
                                     studentID_2: student_id_2,
                                     studentID_3: student_id_3,
                                     studentID_4: student_id_4,
                                     studentID_5: student_id_5,
                                     studentID_6: student_id_6,
                                     pickupName : pickupName  ,
				     date       : sDate       ,
      				     time       : sTime       ,
                                   },
                    function(data){
                        // success action
                        var message = 'You have signed in: ' + data;
                        cj("#new-status").html( message );
                    	cj("#new-status").show( );
                    	
                        cj("#pickup_name").val( '' );
                      	cj("#student_id_1").val( '' );
                      	cj("#student_id_2").val( '' );
                      	cj("#student_id_3").val( '' );
                      	cj("#student_id_4").val( '' );
                      	cj("#student_id_5").val( '' );
                      	cj("#student_id_6").val( '' );

                        cj('#pickup_name').focus( );
            	    }
            	);
            }
        });
	
        cj(".success-status").click( function( ) {
	    cj(this).hide( );
	});

       cj('#pickup_name').focus( );
  });

</script>
{/literal}
