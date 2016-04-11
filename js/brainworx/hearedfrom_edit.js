function updateHearedfrom(url)
{
    console.log(document.getElementById("ooid").value);
    new Ajax.Request(url, {
        method:'post',
        parameters: { sellerusernm: document.getElementById("sellerusernm").value  ,
        	ooid: document.getElementById("ooid").value}
        , requestHeaders: {Accept: 'application/json'},
        onSuccess: function() {
        	location.reload();
        }
    }); 
}
function loadHearedfromEdit(){
	document.getElementById("sellerblock").className += " hidden";
	document.getElementById("sellerblockedit").className = " ";
	
}
function loadHearedfromDefault(){
	document.getElementById("sellerblockedit").className += " hidden";
	document.getElementById("sellerblock").className = " ";
	
}