 $j(function() {
	setup();
	$j('#shipping-method-buttons-container button').click(resetView);
	$j('#hearedfrom-buttons-container button').click(validate);
  });
 function setup(){
	 /*flatrate for consignation stock supply*/
	 if($j('#s_method_flatrate_flatrate').is(':checked')) {
		$j("#dateoptions").append('<li id="delrange3"> <input required type="radio" class="radio" name="delrange" value="'+dateToText(determineDeliveryDay(3))+'" checked></input><label class="elong"> Binnen 3 dagen (ten laatste op '+dateToText(determineDeliveryDay(3))+')</label></li>');
	 }else if($j('#s_method_freeshipping_freeshipping').is(':checked')){ 
		 /*pickup*/
		$j("#dateoptions").append('<li id="delrange2"> <input id="rsel2" required type="radio" class="radio" name="delrange" value="'+dateToText(determineDeliveryDay(0))+'"></input><label class="elong"> Onmiddellijk meegenomen <input class="tsmall" type="text" name="" id="dummy" value="'+dateToText(determineDeliveryDay(0))+'" disabled/></label></li>');
		$j("#dateoptions").append('<li id="delrange1"> <input id="rsel" required type="radio" class="radio" name="delrange" value="0"></input><label class="elong"> Selecteer een datum: <input class="tsmall" type="text" name="pddate" id="pddate" value=""/> </label></li>');
	    $j( "#pddate" ).datepicker({ 
	     	minDate: 1, dateFormat: 'dd-mm-yy', selectOtherMonths: true,
	      	beforeShowDay: function(date) {
	      		var day = date.getDay();
	       		return [day != 0,''];
	       		},
	       	onSelect: function(dateText, inst) {
	       		$j("#rsel").prop('checked', true);
	       		$j("#rsel").val(dateText);
	    }});
	    $j("#rsel2").prop('checked', true);
	 }else {
		 /*delivered*/
		$j("#dateoptions").append('<li id="delrange1"> <input id="rsel" required type="radio" class="radio" name="delrange" value="0"></input><label class="elong"> Selecteer een datum: <input class="tsmall" type="text" name="pddate" id="pddate" value=""/> </label></li>');
        $j("#dateoptions").append('<li id="delrange2"> <input required type="radio" class="radio" name="delrange" value="'+dateToText(determineDeliveryDay(1))+'"></input><label class="elong"> DRINGEND - 24u - <input class="tsmall" type="text" name="" id="dummy" value="'+dateToText(determineDeliveryDay(1))+'" disabled/></label></li>');
        $j( "#pddate" ).datepicker({ 
        	minDate: 2, dateFormat: 'dd-mm-yy', selectOtherMonths: true,
        	beforeShowDay: function(date) {
        		var day = date.getDay();
        		return [day != 0,''];
        		},
        	onSelect: function(dateText, inst) {
        		$j("#rsel").prop('checked', true);
        		$j("#rsel").val(dateText);
            }});
        $j("#rsel").prop('checked', true);
	 }
 }
 /**
  * returns a date, x working days after the current date
  * @param nrdays
  * @returns {Date}
  */
 function determineDeliveryDay(nrdays){
	 var dt = new Date();
	 var day = dt.getDay();
	 if(nrdays < 6 && dt.getDay()>(6-nrdays)){
		 nrdays++;
	 }
	 dt.setDate(dt.getDate()+nrdays);
	 return dt;
 }
// function parseInputDt(dateText){
//	 var dt = parseDate(dateText);
//	 if(dt.getMonth()==0){
//		 dt.setMonth(11);
//		 dt.setFullYear(dt.getFullYear()-1);
//	 }else{
//		 dt.setMonth(dt.getMonth()-1);
//	 }
//	 return dt;
// }
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
	 $j('#rsel').val(0);
	 removeLI();
	 setup();
//	 if(!$j('#s_method_flatrate_flatrate').is(':checked')) {
//    	 $j("#dateoptions").append('<li class="delrange"> <input required type="radio" class="radio" name="delrange" value="" disabled></input><label class="elong"> Binnen 24u</label></li>');
//         $j("#dateoptions").append('<li class="delrange"> <input required type="radio" class="radio" name="delrange" value="" disabled></input><label class="elong"> Binnen 3 dagen</label></li>');
//     }
//     $j("#dateoptions").append('<li class="delrange"> <input required type="radio" class="radio" name="delrange" value="" disabled></input><label class="elong"> Binnen 14 dagen</label></li>');    
 }
//parse a date from dd-mm-yyyy format
 function parseDate(input) {
   var parts = input.split('-');
   // new Date(year, month [, day [, hours[, minutes[, seconds[, ms]]]]])
   return new Date(Date.UTC(parts[2], parts[1], parts[0])); 
 }
 /**
  * Transforms a date object to a displayable string dd-mm-yyyy
  * @param input
  * @returns {String}
  */
 function dateToText(input) {
	 var d = input.getDate();
	 var text = d;
	 if(d<10){
		 text = "0"+d;
	 }
	 var m = input.getMonth()+1;
	 if(m<10){
		 text += "-0"+m;
	 }else{
		 text += "-"+m;
	 }
	 text += "-"+input.getFullYear();
	 return text;
}
 /**
  * Verifies the date selection
  */
 function validate(){
	 $j('#deloptiontext').removeClass("error");
	 $j('#vlbl').removeClass("error");
	 if(getDeliveryTermValue() ==0){
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
 