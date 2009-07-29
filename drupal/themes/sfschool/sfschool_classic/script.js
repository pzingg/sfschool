function loadPage(){

	var container = document.getElementById('container');

    	var squeeze = document.getElementById('squeeze').offsetHeight; 
    	var sidebar_left = document.getElementById('sidebar-left').offsetHeight;

    	var bodyHeight = document.body.clientHeight;
    	var footer = document.getElementById('footer');

    	if( sidebar_left > squeeze ) {
        	var height=sidebar_left;
    	} else {
        	var height=squeeze;
    	} 
        
    	container.style.height = height + 'px';    
       	
}
