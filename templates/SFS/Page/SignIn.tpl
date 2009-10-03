<div style="float: right;"><a href="#addNew">Enroll new student for the course</a></div>
<span class="success-status" id="existing-status" style="display:none;">{ts}Attendance is saved.{/ts}</span>
<table id="records" class="display">
    <thead>
        <tr>
            <th>{ts}Student Name{/ts}</th>
            <th>{ts}Course Name{/ts}</th>
            <th>{ts}Attended{/ts}</th>
        </tr>
    </thead>
    
    <tbody>
        {foreach from=$studentDetails item=row}
        <tr>
            <td>{$row.display_name}</td>	
            <td>{$row.course_name}</td>	
            <td><input type="checkbox" class="status" name="check{$row.contact_id}" value="{$row.contact_id}"></td>
        </tr>
        {/foreach}
    </tbody>
</table>

<br/>
<span class="success-status" id="new-status" style="display:none;">{ts}Student as been enrolled for the course.{/ts}</span>
<br/>
<div class="form-layout">
    <table class="form-layout">
        <tr id="addNew">
            <td>
                {ts}Student Name{/ts}&nbsp;<input type="text" name="contact" id="contact">
                <input type="hidden" name="contact_id">
                &nbsp;&nbsp;{ts}Course Name{/ts}&nbsp;<input type="text" name="course" id="course">
                &nbsp;&nbsp;<input type="submit" name="Add" id="Add" value="Add">
            </td>
        </tr>
    </table>
</div>

{literal}
<script type="text/javascript">
    cj( function( ) {
        cj('#records').dataTable( {
            "bPaginate": false,
            "bInfo": false,
    		"aoColumns": [
    		            null,
            			null,
            			{ "bSortable": false }
            		]
        } );        
    
        cj(".status").click( function( ) {
            var dataUrl = {/literal}"{crmURL p='civicrm/ajax/sfschool/signin' h=0 }"{literal};
            cj.post( dataUrl, { contactID: cj(this).val(), day: "Monday" },
               function(data){
                  cj("#existing-status").show( );
            });
        });
        
        var contactUrl = {/literal}"{crmURL p='civicrm/ajax/sfschool/contactlist' q='context=newcontact' h=0 }"{literal};

        cj("#contact").autocomplete( contactUrl, {
          	selectFirst: false, 
    	    matchContains: true 
          }).result(function(event, data, formatted) {
          	 cj("input[name=contact_id]").val(data[1]);
          });
        
          cj("#Add").click( function( ) {
              var dataUrl = {/literal}"{crmURL p='civicrm/ajax/sfschool/addnew' h=0 }"{literal};
              cj.post( dataUrl, { contactID: cj("input[name=contact_id]").val( ), course: cj("#course").val( ), day: "Monday" },
                 function(data){
                     // success action
                     cj("#contact").val( '' )
                     cj("input[name=contact_id]").val( '' )
                     cj("#course").val( '' )
                     cj("#new-status").show( );
              });
          });
      	
      	  cj(".success-status").click( function( ) {
      	      cj(this).hide( );
      	  });    
    });
    
</script>
{/literal}