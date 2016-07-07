 $j(function() {
	$j('#shipping-method-buttons-container button').click(resetView);
	$j('#hearedfrom-buttons-container button').click(validate);
    $j( "#pddate" ).datepicker({ 
    	minDate: 1, dateFormat: 'dd-mm-yy', selectOtherMonths: true,
    	onSelect: function(dateText, inst) {
    		var enddate3 = parseDate(dateText);
            enddate3.setDate(enddate3.getDate() + 14);	
    		removeLI();            
    		if($j('#limit').val()!="1"){
	    		var date = parseDate(dateText);  
	    		var enddate2 = parseDate(dateText); 
	            enddate2.setDate(enddate2.getDate() + 3);	                       
	            $j("#dateoptions").append('<li id="delrange1"> <input required type="radio" class="radio" name="delrange" value="'+dateToText(date)+'"></input><label class="elong"> Binnen 24u (op '+dateToText(date)+')</label></li>');
	            $j("#dateoptions").append('<li id="delrange2"> <input required type="radio" class="radio" name="delrange" value="'+dateToText(enddate2)+'" checked></input><label class="elong"> Binnen 3 dagen (ten laatste op '+dateToText(enddate2)+')</label></li>');
	            $j("#dateoptions").append('<li id="delrange3"> <input required type="radio" class="radio" name="delrange" value="'+dateToText(enddate3)+'" ></input><label class="elong"> Binnen 14 dagen (ten laatste op '+dateToText(enddate3)+')</label></li>');
    		}else{
    			$j("#dateoptions").append('<li id="delrange3"> <input required type="radio" class="radio" name="delrange" value="'+dateToText(enddate3)+'" checked></input><label class="elong"> Binnen 14 dagen (ten laatste op '+dateToText(enddate3)+')</label></li>');
    		}
        }});
  });
 function removeLI(){
	 $j("#delrange1").remove();
     $j("#delrange2").remove();
     $j("#delrange3").remove();
     $j(".delrange").remove();
 }
 function resetView(){
	 $j('#pddatelbl').removeClass("error");
	 $j('#deloptiontext').removeClass("error");
	 $j('#vlbl').removeClass("error");
	 $j('#pddate').val('');
	 if($j('#co-shipping-method-form input[name=shipping_method]:radio:checked').val()=="freeshipping_freeshipping")
	 {
		 $j('#limit').val("1");
	 }else{
		 $j('#limit').val("0");
	 }
	 removeLI();
     if($j('#limit').val()!="1"){
    	 $j("#dateoptions").append('<li class="delrange"> <input required type="radio" class="radio" name="delrange" value="" disabled></input><label class="elong"> Binnen 24u</label></li>');
         $j("#dateoptions").append('<li class="delrange"> <input required type="radio" class="radio" name="delrange" value="" disabled></input><label class="elong"> Binnen 3 dagen</label></li>');
     }
     $j("#dateoptions").append('<li class="delrange"> <input required type="radio" class="radio" name="delrange" value="" disabled></input><label class="elong"> Binnen 14 dagen</label></li>');    
 }
//parse a date from dd-mm-yyyy format
 function parseDate(input) {
   var parts = input.split('-');
   // new Date(year, month [, day [, hours[, minutes[, seconds[, ms]]]]])
   return new Date(Date.UTC(parts[2], parts[1], parts[0])); 
 }
 function dateToText(input) {
	 var d = input.getDate();
	 var text = d;
	 if(d<10){
		 text = "0"+d;
	 }
	 var m = input.getMonth();
	 if(m<10){
		 text += "-0"+m;
	 }else{
		 text += "-"+m;
	 }
	 text += "-"+input.getFullYear();
	 return text;
}
 function validate(){
	 $j('#pddatelbl').removeClass("error");
	 $j('#deloptiontext').removeClass("error");
	 $j('#vlbl').removeClass("error");
	 if(!($j( "#pddate" ).val())){
		 $j('#pddatelbl').addClass("error");
	 }
	 else if(getDeliveryTermValue ==0){
		 $j('#deloptiontext').addClass("error");
	 }
	 else if($j("#getvoice option:selected").val() == "Selecteer"){
		 $j('#vlbl').addClass("error");
	 }
	 else{
		 hearedfrom.save();
	 }
	 
 }
 function getDeliveryTermValue () {
	    if( $j('#co-hearedfrom-form input[name=delrange]:radio:checked').length > 0 ) {
	        return $j('#co-hearedfrom-form input[name=delrange]:radio:checked').val();
	    }
	    else {
	        return 0;
	    } 
	}
 