<div class="form-item">
<fieldset><legend>{ts}Student Morning Extended Care Sheet{/ts}</legend>
<br/>
<span class="success-status" id="new-status" style="display:none;">{ts}Student have been signed in for morning extended care.{/ts}</span>
<br/>  
<div>
<dl>
  <dt>{$form.student_1.label}</dt><dd>{$form.student_1.html}</dd>
  <dt>{$form.student_2.label}</dt><dd>{$form.student_2.html}</dd>
  <dt>{$form.student_3.label}</dt><dd>{$form.student_3.html}</dd>
  <dt>{$form.student_4.label}</dt><dd>{$form.student_4.html}</dd>
  <dt>{$form.student_5.label}</dt><dd>{$form.student_5.html}</dd>
  <dt>{$form.student_6.label}</dt><dd>{$form.student_6.html}</dd>
</dl>
  <dl>
  <dt></dt><dd><input type="submit" name="Add" id="Add" value="Morning Care SignIn"></dd>
  
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
        cj("#Add").click( function( event ) {
            event.preventDefault( );
            student_id_1  = cj("input[name=student_id_1]").val( );
            student_id_2  = cj("input[name=student_id_2]").val( );
            student_id_3  = cj("input[name=student_id_3]").val( );
            student_id_4  = cj("input[name=student_id_4]").val( );
            student_id_5  = cj("input[name=student_id_5]").val( );
            student_id_6  = cj("input[name=student_id_6]").val( );
            if ( student_id_1 || student_id_2 || student_id_3 || student_id_4 || student_id_5 || student_id_6 ) {
                 var dataUrl = {/literal}"{crmURL p='civicrm/ajax/sfschool/morning' h=0 }"{literal};
                 cj.post( dataUrl, { studentID_1: student_id_1,
                                     studentID_2: student_id_2,
                                     studentID_3: student_id_3,
                                     studentID_4: student_id_4,
                                     studentID_5: student_id_5,
                                     studentID_6: student_id_6 },
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
                        
                        var message = students + ' have been signed in for morning care.';
                        cj("#new-status").html( message );
                    	cj("#new-status").show( );
                    	
                        cj("#student_1").val( '' )
                      	cj("input[name=student_id_1]").val( '' );
                        cj("#student_2").val( '' )
                      	cj("input[name=student_id_2]").val( '' );
                        cj("#student_3").val( '' )
                      	cj("input[name=student_id_3]").val( '' );
                        cj("#student_4").val( '' )
                      	cj("input[name=student_id_4]").val( '' );
                        cj("#student_5").val( '' )
                      	cj("input[name=student_id_5]").val( '' );
                        cj("#student_6").val( '' )
                      	cj("input[name=student_id_6]").val( '' );
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
