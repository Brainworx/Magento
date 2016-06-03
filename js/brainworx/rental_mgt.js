 $j(function() {
    $j( "#btn_term_i" ).click(terminateitem);
 }); 
 function enableSelectors(){
	 $j( "termselect" ).addClass( "visiblecheck" );
 }
 function disableSelectors(){
	 $j( "termselect" ).removeClass( "visiblecheck" );
 }
 function terminateitem(){
	 var datastring;
	 $j(".termselect").each(function(index,element){
		 if($j(element).is( ":checked" )){
			 if(datastring){
				 datastring += '-'+ $j(element).attr('value');
			 }else{
				 datastring = $j(element).attr('value');
			 }
		 }
	 })
	 if(datastring){
		 //TODO add request for date input
		 if (confirm("Bent u zeker dat u deze verhuur vandaag wenst te beeindigen?")) {
			 $j.ajax({ // ajax call starts
			      url: BASE_URL+'rental/apo/terminate', // JQuery loads serverside.php
			      type: 'POST',
			      data:  {'items':datastring,'realorderid':$j('#realorderid').val()}, // Send value of the clicked button
			      success: function(response){
			    	  var result = JSON.parse(response);
			    	  alert(result.message);// ==> geeft undefined
			    	  location.reload()},
			      error: function(){alert("Fout bij verwerking")}
			    })
	     }
	 }else{
		 alert("Er werden geen verhuurde items geselecteerd.");
	 }
 }
