function updateHearedfrom(url)
{
    console.log(document.getElementById("ooid").value);
    new Ajax.Request(url, {
        method:'post',
        parameters: { sellerusernm: $j('#sellerusernm').val()  ,
        	ooid: $j("#ooid").val()}
        , requestHeaders: {Accept: 'application/json'},
        onSuccess: function() {
        	location.reload();
        }
    }); 
}
function updateBirthdate(url)
{
	if($j('#patientBirthDate').val() != ''){
	    new Ajax.Request(url, {
	        method:'post',
	        parameters: { patientBirthDate: $j('#patientBirthDate').val()  ,
	        	ooid: $j("#ooid").val()}
	        , requestHeaders: {Accept: 'application/json'},
	        onSuccess: function() {
	        	location.reload();
	        }
	    }); 
	}else{
		alert("Gelieve een datum te selecteren.");
	}
}
function loadHearedfromEdit(){
	$j("#patientBirthDate").prop('readonly', true);
	$j('#patientBirthDate').datepicker({
	 changeMonth: true,
	 changeYear:true,
	 dateFormat: "dd-mm-yy",
	 yearRange: (new Date().getFullYear()-115)+':'+new Date().getFullYear()
	});
	
	document.getElementById("sellerblock").className += " hidden";
	document.getElementById("sellerblockedit").className = " ";
	
	document.getElementById("birthdateblock").className += " hidden";
	document.getElementById("birthdateblockedit").className = " ";
	
}
function loadHearedfromDefault(){
	document.getElementById("sellerblockedit").className += " hidden";
	document.getElementById("sellerblock").className = " ";

	document.getElementById("birthdateblock").className = " ";
	document.getElementById("birthdateblockedit").className += " hidden";
	
}