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
function updatePatient(url)
{
	if($j('#patientBirthDate').val() != ''||$j('#patientName').val() != ''||$j('#patientFirstname').val() != ''){
	    new Ajax.Request(url, {
	        method:'post',
	        parameters: { 
	        	patientBirthDate: $j('#patientBirthDate').val()  ,
	        	patientName: $j('#patientName').val(),
	        	patientFirstname: $j('#patientFirstname').val(),
	        	patientStreet: $j('#patientStreet').val(),
	        	patientZip: $j('#patientZip').val(),
	        	patientCity: $j('#patientCity').val(),
	        	ooid: $j("#ooid").val()}
	        , requestHeaders: {Accept: 'application/json'},
	        onSuccess: function() {
	        	location.reload();
	        }
	    }); 
	}else{
		alert("Gelieve patientgegevens in te voeren.");
	}
}
function updateVaphDocnr(url)
{
	if($j('#vaphdocnr').val() != ''){
	    new Ajax.Request(url, {
	        method:'post',
	        parameters: { vaphdocnr: $j('#vaphdocnr').val()  ,
	        	ooid: $j("#ooid").val()}
	        , requestHeaders: {Accept: 'application/json'},
	        onSuccess: function() {
	        	location.reload();
	        }
	    }); 
	}else{
		alert("Gelieve een documentnr in te voeren.");
	}
}
function loadHearedfromEdit(){
	
	document.getElementById("sellerblock").className += " hidden";
	document.getElementById("sellerblockedit").className = " ";

	document.getElementById("vaphblock").className += " hidden";
	document.getElementById("vaphblockedit").className = " ";
	
}
function loadPatientEdit(){
	$j("#patientBirthDate").prop('readonly', true);
	$j('#patientBirthDate').datepicker({
	 changeMonth: true,
	 changeYear:true,
	 dateFormat: "dd-mm-yy",
	 yearRange: (new Date().getFullYear()-115)+':'+new Date().getFullYear()
	});
	
	document.getElementById("patientblock").className += " hidden";
	document.getElementById("patientblockedit").className = " ";
	
}
function loadHearedfromDefault(){
	document.getElementById("sellerblockedit").className += " hidden";
	document.getElementById("sellerblock").className = " ";

	document.getElementById("vaphblock").className = " ";
	document.getElementById("vaphblockedit").className += " hidden";
}
function loadPatientDefault(){
	document.getElementById("patientblock").className = " ";
	document.getElementById("patientblockedit").className += " hidden";
}
function upperCaseF(word){
       word.value = word.value.charAt(0).toUpperCase() + word.value.slice(1);
}
