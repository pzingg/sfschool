<div class="form-item">
<fieldset><legend>{ts}Student Sign Out Sheet{/ts}</legend>
<br/>
<span class="success-status" id="new-status" style="display:none;">{ts}Student has been Sign Out.{/ts}</span>
<br/>  
  {$form.pickup_name.label}&nbsp;{$form.pickup_name.html}&nbsp;{$form.student.label}&nbsp;{$form.student.html}&nbsp;&nbsp;
  <input type="submit" name="Add" id="Add" value="Sign Out">
  
  {*
  <dl>
     <dt>{$form.pickup_name.label}</dt><dd>{$form.pickup_name.html}</dd>
     {section start=1 name=rows loop=$maxNumber}
       {assign var=rowName value='grade_student_id_'|cat:$smarty.section.rows.index}
       <dt>{$form.$rowName.label}</dt><dd>{$form.$rowName.html}</dd>
     {/section}
  </dl>
        <dt></dt><dd>{$form.buttons.html}</dd>
       </dl>
       
*}       
<div class="spacer"></div>
</fieldset>
</div>

{literal}
<script type="text/javascript">
    cj( function( ) {
        var contactUrl = {/literal}"{crmURL p='civicrm/ajax/sfschool/contactlist' h=0 q='nograde=1'}"{literal};
        
        cj("#student").autocomplete( contactUrl, {
            selectFirst: false, 
            matchContains: true 
          }).result(function(event, data, formatted) {
          	 cj("input[name=student_id]").val(data[1]);
        });
    
        var contactID  = '';
        var pickupName = '';
        cj("#Add").click( function( event ) {
            event.preventDefault( );
            contactID  = cj("input[name=student_id]").val( );
            pickupName = cj("#pickup_name").val( );
            if ( contactID && pickupName ) {
        	     var dataUrl = {/literal}"{crmURL p='civicrm/ajax/sfschool/signout' h=0 }"{literal};
                 cj.post( dataUrl, { contactID: contactID, pickupName: pickupName },
                    function(data){
                        // success action
                        cj("#pickup_name").val( '' );
                        cj("#student").val( '' )
                  	    cj("input[name=student_id]").val( '' );
                   	    cj("#new-status").show( );
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
