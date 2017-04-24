var count;
$j(function() {
	count = 0;
	$j('#counter').val(count);
	$j('#addProduct').click(addProduct);
});
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