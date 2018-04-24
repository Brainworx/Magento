// a minimal jQuery library for reacting to innerHTML changes
(function($) {
  $.fn.change = function(cb, e) {
    e = e || { subtree:true, childList:true, characterData:true };
    $(this).each(function() {
      function callback(changes) { cb.call(node, changes, this); }
      var node = this;
      (new MutationObserver(callback)).observe(node, e);
    });
  };
})(jQuery); 
$j(function() {
	setuphearedfrom(); 
	$j('#shipping-method-buttons-container button').click(validateshippingandresethearedfrom);
	$j('#hearedfrom-buttons-container button').click(validate);
	
	$j('#checkout-shipping-method-load').change(function(changes, observer) {
		
		/*express*/
		$j("#tablerate_express_deldate").val(dateToText(determineExpressDeliveryDay()));
		$j("#tablerate_express_deldate").prop('readonly', true);
		//$j("#tablerate_express_deldate").prop('disabled', true);	
		
		//set default
		$j("#s_method_tablerate_bestway").prop('checked', true);
		$j("#s_method_salesrate_flatrate").prop('checked', true);

		/*standard= +2werkdagen + voor 15u + niet op zat of zon*/
		$j("#tablerate_bestway_deldate" ).datepicker({ 
	     	minDate: determineMinDaysNormal(),
	     	dateFormat: 'dd-mm-yy', selectOtherMonths: true,
	      	beforeShowDay: function(date) {
	      		var day = date.getDay();
	       		return [day != 0 && day !=6,''];
	       		},
	       	onSelect: function(dateText, inst) {
	       		$j("#s_method_tablerate_bestway").prop('checked', true);
	    }});
		$j("#tablerate_bestway_deldate").prop('readonly', true);
		/*weekend= zaterdag indien vrijdag voor 15u*/
		$j("#tablerate_weekend_deldate" ).datepicker({ 
	     	minDate: (new Date().getHours()<15?1:2), dateFormat: 'dd-mm-yy', selectOtherMonths: true,
	      	beforeShowDay: function(date) {
	      		var day = date.getDay();
	       		return [day == 6,''];
	       		},
	       	onSelect: function(dateText, inst) {
	       		$j("#s_method_tablerate_weekend").prop('checked', true);
	    }});
		$j("#tablerate_weekend_deldate").prop('readonly', true);
		/*pickup = next day, not sunday*/
		$j( "#freeshipping_freeshipping_deldate" ).datepicker({ 
	     	minDate: 0, dateFormat: 'dd-mm-yy', selectOtherMonths: true,
	      	beforeShowDay: function(date) {
	      		var day = date.getDay();
	       		return [day != 0,''];
	       		},
	       	onSelect: function(dateText, inst) {
	       		$j("#s_method_freeshipping_freeshipping").prop('checked', true);
	    }});
		$j("#freeshipping_freeshipping_deldate").prop('readonly', true);
		/*sales delivery any day*/
		$j("#salesrate_flatrate_deldate" ).datepicker({ 
			minDate:determineMinDaysNormal(),
	     	dateFormat: 'dd-mm-yy', selectOtherMonths: true,
	      	beforeShowDay: function(date) {
	      		var day = date.getDay();
	       		return [day != 0,''];
	       		},
	       	onSelect: function(dateText, inst) {
	       		$j("#s_method_salesrate_flatrate").prop('checked', true);
	    }});
		$j("#salesrate_flatrate_deldate").prop('readonly', true);
		$j("#specialrate_urgent_deldate" ).datepicker({ 
			minDate:0,	dateFormat: 'dd-mm-yy', selectOtherMonths: true,
	      	beforeShowDay: function(date) {
	      		var day = date.getDay();
	       		return [day != 0,''];
	       		},
	       	onSelect: function(dateText, inst) {
	       		$j("#s_method_specialrate_urgent").prop('checked', true);
	    }});
		$j("#specialrate_urgent_deldate").prop('readonly', true);
	});
  });
 function setuphearedfrom(){
	 /*patient birth date*/
	 $j("#patientbdt").prop('readonly', true);
	 $j("#patientbdt").datepicker({
    	 changeMonth: true,
    	 changeYear:true,
    	 dateFormat: "dd-mm-yy",
    	 yearRange: (new Date().getFullYear()-115)+':'+new Date().getFullYear()
     });
 }
 /**
  * returns a date, x working days after the current date
  * @param nrdays
  * @returns {Date}
  */
 function determineExpressDeliveryDay(){
	 var dt = new Date();
	 var day = dt.getDay();
	 var hour = dt.getHours();
	 var nrdays = day==0?2: /*zo -> +2d = di*/
                    day==6?3: /*za -> +3d = di*/
                        day==5?(hour<15?1:4): /*vr voor 15u --> +1d = za ANDERS +4d = di*/	
                            hour<15?1:2; /*alle andere dagen voor 15u --> +1d = volgende dag ANDERS +2d (met levering op zaterdag*/
	 dt.setDate(dt.getDate()+nrdays);
	 return dt;
 }
 function determineMinDaysNormal(){
	 var dt = new Date();
	 var day = dt.getDay();
	 var hour = dt.getHours();
	 var min = day==0?3: /*zo -> +3d = woe*/
  		        day==6?4: /*za --> +4d = woe */
                    day==5?(hour<15?3:5):	/*vr -> voor 15u ma, na 15u wo	*/	
  			           day==4?4:	/*do -> voor 15u en na 15u ma	*/	
  				          day==3?(hour<15?2:5): /*woe -> voor 15u vr, na 15u ma */
  					         hour<15?2:3; /*alle andere dagen voor 15u +2d, na 15u +3d -- geen levering op zaterdag*/
  	return min;  						
 }
 function determineMinDaysNormalNextDay(){
	 var dt = new Date();
	 var day = dt.getDay();
	 var hour = dt.getHours();
	 var min = day==0?2:day==6?3:day==5?(hour<15?3:4):			
					day==4?(hour<15?1:4):hour<15?1:2;
	 return min;
 }

 function validateshippingandresethearedfrom(){	 
	 $j('#pddatelbl').removeClass("error");
	 $j('#bdoptiontext').removeClass("error");
	 $j('#vlbl').removeClass("error");
	 $j('#pddate').val('');
	 $j("#patientbdt").val('');
	 setuphearedfrom();
	 validateshipping();
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
 function validateshipping(){
	 $j('input:radio[name=shipping_method]').each(function(){
		 $j('#'+$j(this).val()+'_deldate').removeClass("validation-failed");
	 });
	 $j('input:radio[name=shipping_method]').each(function(){
		 $j('#lb_'+$j(this).val()).removeClass("validation-failed");
	 });
	 if($j('#vaph_order_id').val()==1 || 
	    typeof $j('#'+$j('input:radio[name=shipping_method]:checked').val()+'_deldate').val() == 'undefined' || $j('#'+$j('input:radio[name=shipping_method]:checked').val()+'_deldate').val().length>0){
		 shippingMethod.save();
	 }else{
		 $j('#'+$j('input:radio[name=shipping_method]:checked').val()+'_deldate').addClass("validation-failed");
		 $j('#lb_'+$j('input:radio[name=shipping_method]:checked').val()).addClass("validation-failed");
	 }
 }
 /**
  * Verifies the date selection
  */
 function validate(){
	 $j('#vlbl').removeClass("error");
	 $j('#bdoptiontext').removeClass("error");
	 if($j("#patientbdt").val()==''){
		 $j('#bdoptiontext').addClass("error");
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
