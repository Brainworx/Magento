var count;
$j(function() {
	count = 0;
	$j("#loaderDiv").hide();
	$j('#counter').val(count);
	$j('#addProduct').click(addProduct);
	$j('#btn-stock-submit' ).click(submitform);
});

function submitform(event){
	if(validateForm()){
		$j('#btn-stock-submit').prop('disabled', true);
		$j.ajax({
		   url: BASE_URL + 'customer/stockrequestpage/requestPost',
	       type: 'POST',
	       data: $j("#form-validate").serialize(), // serializes the form's elements.
	       beforeSend: function(){
               $j("#loaderDiv").show();
           },
	       success: function(response){
	    	   $j("#loaderDiv").hide();
		    	  var result = JSON.parse(response);
		    	  alert(result.message);// ==> geeft undefined
		    	  $j('#btn-stock-submit').prop('disabled', false);
		    	  location.replace(BASE_URL+'customer/stockpage/');
		    	  },       //location.reload()
		   error: function(){
			   $j("#loaderDiv").hide();
			   $j('#btn-stock-submit').prop('disabled', false);
			   alert("Fout bij verwerking");
		       location.reload();}
	     });
	}
}
function addProduct(){
	count++;
	var text = '<li id="prodli'+count+'" class="fields">'
			+'<div class="field">'
			+'<div class="input-box">'
				+'<label for="product_code'+count+'" class="required">'+$j('#lb_product_code0').html()+'</label>'
					+'&nbsp;<select id="product_code'+count+'" name="product_code'+count+'" title="Product" class="validate-select" required>';
	var counter=0;
	$j('select#product_code0').each(function(){
		text+='<option value="'+$j(this).val()+'">'+$j(this).html()+'</option>';
	});
	text += '</select>';
	
	text += '<label id="lb_quantity'+count+'" for="quantity'+count+'" class="required">&nbsp;'+$j('#lb_quantity0').html()+' </label>'; 				
	text += '&nbsp;<input id="quantity'+count+'" type="number" name="quantity'+count+'" value="0" title="aantal" class="input-number-small" min=1 max=5/>';
	
	text += '&nbsp;<input class="button" id="removeProduct'+count+'" type="button" title="verwijder" value=" - " data-counter="'+count+'"></input>';
	text += '</div></div></li>';
	$j('#stockinputfields').append(text);
	
	$j('select#product_code'+count+' option').first().remove();
	
	$j('#removeProduct'+count).click(removeProduct);
	$j('#counter').val(count);

}
function removeProduct(){
	console.log($j(this).data('counter'));
	$j('#prodli'+$j(this).data('counter')+'').remove();
}

function validateForm() {
//	var validator = new Validation('form-validate', {immediate : true});
//	return validator.validate();
	
	$j('input').each(function(){
		$j(this).removeClass('validation-failed');
		$j(this).removeClass('error');
	});
	$j('select').each(function(){
		$j(this).removeClass('validation-failed');
		$j(this).removeClass('error');
	});
	var valid = true;
	$j('select.validate-select').each(function(){		
		if($j(this).val() == ""){
			valid = false;
			$j(this).addClass('validation-failed');
			$j(this).addClass('error');
			alert("Selecteer een product.");
		}
	});
	if(valid){
		$j('input.input-number-small').each(function(){
			if($j(this).val() < parseInt($j(this).attr('min')) || $j(this).val() > parseInt($j(this).attr('max'))){
				valid = false;
				$j(this).addClass('validation-failed');
				$j(this).addClass('error');
				alert("Selecteer een aantal van "+ parseInt($j(this).attr('min'))+" tot "+ parseInt($j(this).attr('max')));
			}
		});
	}
	return valid;
}