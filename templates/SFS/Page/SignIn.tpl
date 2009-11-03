<div style="float: right;"><a href="#addNew">Enroll new student for the course</a></div>
<div>
Attendance Sheet for {$dayOfWeek}, {$date} {$time}
</div>
<span class="success-status" id="existing-status" style="display:none;">{ts}Attendance is saved.{/ts}</span>
<table id="records" class="display">
    <thead>
        <tr>
            <th>{ts}Student Name{/ts}</th>
            <th>{ts}Grade{/ts}</th>
            <th>{ts}Class Name{/ts}</th>
            <th>{ts}Attended{/ts}</th>
	    {if $signOut}
            <th>{ts}Sign Out Time{/ts}</th>
            {/if}
        </tr>
    </thead>
    
    <tbody>
        {foreach from=$studentDetails item=row}
        <tr>
            <td>{$row.display_name}</td>	
            <td>{$row.grade}</td>	
            <td>{$row.course_name}</td>	
            <td><input type="checkbox" class="status" name="check_{$row.contact_id}" value="{$row.contact_id}:::{$row.course_name}" {if $row.is_marked}checked="1"{/if}"></td>
	    {if $signOut}
            <td>
            <select name="signout_{$row.contact_id}" id="signout_{$row.contact_id}" class="form-select">
   	      <option value="">- select -</option>
	      <option value="1" {if $row.signout_block eq 1}selected="selected"{/if}>Before 3:30 pm</option>
	      <option value="2" {if $row.signout_block eq 2}selected="selected"{/if}>3:30 - 4:30 pm</option>
	      <option value="3" {if $row.signout_block eq 3}selected="selected"{/if}>4:30 - 5:15 pm</option>
	      <option value="4" {if $row.signout_block eq 4}selected="selected"{/if}>5:15 - 6:00 pm</option>
	      <option value="5" {if $row.signout_block eq 5}selected="selected"{/if}>After 6:00 pm</option>
            </select> 
            </td>
           {/if}
        </tr>
        {/foreach}
    </tbody>
</table>

<br/>
<span class="success-status" id="new-status" style="display:none;">{ts}Student has been enrolled for the course.{/ts}</span>
<br/>
<div class="form-layout">
    <table class="form-layout">
        <tr id="addNew">
            <td>
                {ts}Student Name{/ts}&nbsp;<input type="text" name="contact" id="contact">
                <input type="hidden" name="contact_id">
                &nbsp;&nbsp;{ts}Class Name{/ts}&nbsp;<input type="text" name="course" id="course">
                <input type="hidden" name="course_name">
                &nbsp;&nbsp;<input type="submit" name="Add" id="Add" value="Add">
            </td>
        </tr>
    </table>
</div>

{literal}
<script type="text/javascript">
    cj( function( ) {
        {/literal}
        var sDayOfWeek = '{$dayOfWeek}';
        var sDate      = '{$date}';
        var sTime      = '{$time}';
        {literal}

        cj('#records').dataTable( {
            "bPaginate": false,
            "bInfo": false,
            "aoColumns": [
                          null,
                          null,
                          null,
                          { "bSortable": false }
                         ],
            "aaSorting": [[2,'asc'], [0,'asc']]
        } );        
    
        cj(".status").click( function( ) {
            {/literal}
            var dataUrl = "{crmURL p='civicrm/ajax/sfschool/signin' h=0 }"
            {literal}
            cj.post( dataUrl, { contactID: cj(this).val(), dayOfWeek: sDayOfWeek, date: sDate, time: sTime, checked: cj(this).attr('checked') },
               function(data){
                  cj("#existing-status").show( );
            });
        });
    
        {/literal}    
        var contactUrl = "{crmURL p='civicrm/ajax/sfschool/contactlist' q="dayOfWeek=`$dayOfWeek`" h=0 }"
        var classUrl  = "{crmURL p='civicrm/ajax/sfschool/classlist' q="dayOfWeek=`$dayOfWeek`" h=0 }"

        {literal};
        cj("#contact").autocomplete( contactUrl, {
            selectFirst: false, 
            matchContains: true 
          }).result(function(event, data, formatted) {
          	 cj("input[name=contact_id]").val(data[1]);
        });

        cj("#course").autocomplete( classUrl, {
            selectFirst: false, 
            matchContains: true 
          }).result(function(event, data, formatted) {
          	 cj("input[name=course_name]").val(data[1]);
        });
        
          cj("#Add").click( function( ) {
              var contactID = cj("input[name=contact_id]").val( );
	      var course    = cj("#course").val( );
	      if ( contactID && course ) {
	      	     var dataUrl = {/literal}"{crmURL p='civicrm/ajax/sfschool/addnew' h=0 }"{literal};
              	     cj.post( dataUrl, { contactID: cj("input[name=contact_id]").val( ), course: cj("#course").val( ), dayOfWeek: sDayOfWeek, date: sDate, time: sTime },
                       function(data){
                         // success action
                         cj("#contact").val( '' )
                    	 cj("input[name=contact_id]").val( '' )
                     	 cj("#course").val( '' )
                     	 cj("#new-status").show( );
              	     });
	      }
          });
      	
      	  cj(".success-status").click( function( ) {
      	      cj(this).hide( );
      	  });    
    });
    
</script>
{/literal}