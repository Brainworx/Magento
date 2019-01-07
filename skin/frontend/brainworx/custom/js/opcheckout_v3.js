/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
var Checkout = Class.create();
Checkout.prototype = {
    initialize: function(accordion, urls){
        this.accordion = accordion;
        this.progressUrl = urls.progress;
        this.reviewUrl = urls.review;
        this.saveMethodUrl = urls.saveMethod;
        this.failureUrl = urls.failure;
        this.billingForm = false;
        this.patientForm = false;
        this.shippingForm= false;
        this.syncBillingShipping = false;
        this.syncPatientBilling = false;
        this.syncPatientShipping = false;
        this.method = '';
        this.payment = '';
        this.loadWaiting = false;
        this.steps = ['login','patient', 'billing', 'shipping_method','shipping','review'];
        //We use patient as beginning step since progress bar tracks from billing
        this.currentStep = 'patient';

        this.accordion.sections.each(function(section) {
            Event.observe($(section).down('.step-title'), 'click', this._onSectionClick.bindAsEventListener(this));
        }.bind(this));
        
        $('opc-billing').removeClassName('allow');

        this.accordion.disallowAccessToNextSections = true;
    },

    /**
     * Section header click handler
     *
     * @param event
     */
    _onSectionClick: function(event) {
        var section = $(Event.element(event).up().up());
        if (section.hasClassName('allow')) {
            Event.stop(event);
            this.gotoSection(section.readAttribute('id').replace('opc-', ''), false);
            return false;
        }
    },

    ajaxFailure: function(){
        location.href = this.failureUrl;
    },

    reloadProgressBlock: function(toStep) {
        this.reloadStep(toStep);
//        if (this.syncBillingShipping) {
//            this.syncBillingShipping = false;
//            this.reloadStep('shipping');
//        }
        if (this.syncPatientBilling) {
            this.syncPatientBilling = false;
            this.reloadStep('billing');
        }
        if (this.syncPatientShipping) {
            this.syncPatientShipping = false;
            this.reloadStep('shipping');
        }
    },

    reloadStep: function(prevStep) {
        var updater = new Ajax.Updater(prevStep + '-progress-opcheckout', this.progressUrl, {
            method:'get',
            onFailure:this.ajaxFailure.bind(this),
            onComplete: function(){
                this.checkout.resetPreviousSteps();
            },
            parameters:prevStep ? { prevStep:prevStep } : null
        });
    },

    reloadReviewBlock: function(){
        var updater = new Ajax.Updater('checkout-review-load', this.reviewUrl, {method: 'get', onFailure: this.ajaxFailure.bind(this)});
    },

    _disableEnableAll: function(element, isDisabled) {
        var descendants = element.descendants();
        for (var k in descendants) {
            descendants[k].disabled = isDisabled;
        }
        element.disabled = isDisabled;
    },

    setLoadWaiting: function(step, keepDisabled) {
        if (step) {
            if (this.loadWaiting) {
                this.setLoadWaiting(false);
            }
            var container = $(step+'-buttons-container');
            container.addClassName('disabled');
            container.setStyle({opacity:.5});
            this._disableEnableAll(container, true);
            Element.show(step+'-please-wait');
        } else {
            if (this.loadWaiting) {
                var container = $(this.loadWaiting+'-buttons-container');
                var isDisabled = (keepDisabled ? true : false);
                if (!isDisabled) {
                    container.removeClassName('disabled');
                    container.setStyle({opacity:1});
                }
                this._disableEnableAll(container, isDisabled);
                Element.hide(this.loadWaiting+'-please-wait');
            }
        }
        this.loadWaiting = step;
    },

    gotoSection: function (section, reloadProgressBlock) {

        if (reloadProgressBlock) {
            this.reloadProgressBlock(this.currentStep);
        }
        this.currentStep = section;
        var sectionElement = $('opc-' + section);
        sectionElement.addClassName('allow');
        this.accordion.openSection('opc-' + section);
        if(!reloadProgressBlock) {
            this.resetPreviousSteps();
        }
    },

    resetPreviousSteps: function () {
        var stepIndex = this.steps.indexOf(this.currentStep);

        //Clear other steps if already populated through javascript
        for (var i = stepIndex; i < this.steps.length; i++) {
            var nextStep = this.steps[i];
            var progressDiv = nextStep + '-progress-opcheckout';
            if ($(progressDiv)) {
                //Remove the link
                $(progressDiv).select('.changelink').each(function (item) {
                    item.remove();
                });
                $(progressDiv).select('dt').each(function (item) {
                    item.removeClassName('complete');
                });
                //Remove the content
                $(progressDiv).select('dd.complete').each(function (item) {
                    item.remove();
                });
            }
        }
    },

    changeSection: function (section) {
        var changeStep = section.replace('opc-', '');
        this.gotoSection(changeStep, false);
    },

    setMethod: function(){
        if ($('login:guest') && $('login:guest').checked) {
            this.method = 'guest';
            var request = new Ajax.Request(
                this.saveMethodUrl,
                {method: 'post', onFailure: this.ajaxFailure.bind(this), parameters: {method:'guest'}}
            );
            Element.hide('register-customer-password');
            this.gotoSection('patient', true);
        }
        else if($('login:register') && ($('login:register').checked || $('login:register').type == 'hidden')) {
            this.method = 'register';
            var request = new Ajax.Request(
                this.saveMethodUrl,
                {method: 'post', onFailure: this.ajaxFailure.bind(this), parameters: {method:'register'}}
            );
            Element.show('register-customer-password');
            this.gotoSection('patient', true);
        }
        else{
            alert(Translator.translate('Please choose to register or to checkout as a guest').stripTags());
            return false;
        }
        document.body.fire('login:setMethod', {method : this.method});
    },
    
    setPatient: function(){
    	if (($('patient:use_for_billing')) && ($('patient:use_for_billing').checked)) {
            billing.syncWithPatient();
            $('opc-billing').addClassName('allow');
            this.gotoSection('shipping_method', true);
        } else {
        	$('patient:same_as_billing').checked = false;
            this.gotoSection('billing', true);
    	} 
    },

    setBilling: function() {
    	//this.nextStep();
        this.gotoSection('shipping_method', true);
        //this.accordion.openNextSection(true);

        // this refreshes the checkout progress column

//        if ($('patient:use_for_shipping') && $('patient:use_for_shipping').checked){
//            shipping.syncWithBilling();
//            //this.setShipping();
//            //shipping.save();
//            $('opc-shipping').addClassName('allow');
//            this.gotoSection('shipping_method');
//        } else {
//            $('shipping:same_as_billing').checked = false;
//            this.gotoSection('shipping');
//        }
//        this.reloadProgressBlock();
//        //this.accordion.openNextSection(true);
    },

    setShipping: function() {
        //this.nextStep();
//        this.gotoSection('payment', true);
    	this.gotoSection('review', true);
        //this.accordion.openNextSection(true);
    },

    setShippingMethod: function() {
        
        if (($('patient:use_for_shipping')) && ($('patient:use_for_shipping').checked)) {
            shipping.syncWithBilling();
            $('opc-shipping').addClassName('allow');
//            this.gotoSection('payment', true);
            this.gotoSection('review', true);
        } else {
            $('shipping:same_as_billing').checked = false;
            this.gotoSection('shipping', true);
        }
    },
    
//    setHearedfrom: function() {
//        //this.nextStep();
//        this.gotoSection('payment', true);
//        //this.accordion.openNextSection(true);
//    },
//
    setPayment: function() {
        //this.nextStep();
        this.gotoSection('review', true);
        //this.accordion.openNextSection(true);
    },

    setReview: function() {
        this.reloadProgressBlock();
        //this.nextStep();
        //this.accordion.openNextSection(true);
    },

    back: function(){
        if (this.loadWaiting) return;
        //Navigate back to the previous available step
        var stepIndex = this.steps.indexOf(this.currentStep);
        var section = this.steps[--stepIndex];
        var sectionElement = $('opc-' + section);

        //Traverse back to find the available section. Ex Virtual product does not have shipping section
        while (sectionElement === null && stepIndex > 0) {
            --stepIndex;
            section = this.steps[stepIndex];
            sectionElement = $('opc-' + section);
        }
        this.changeSection('opc-' + section);
    },

    setStepResponse: function(response){
        if (response.update_section) {
            $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
        }
        if (response.allow_sections) {
            response.allow_sections.each(function(e){
                $('opc-'+e).addClassName('allow');
            });
        }
        if(response.duplicatePatientInfo)
        {
            this.syncPatientBilling = true;
            billing.setSameAsPatient(true);
        }
        if(response.shippingDuplicatePatientInfo)
        {
            this.syncPatientShipping = true;
            shipping.setSameAsPatient(true);
        }

//        if(response.duplicateBillingInfo)
//        {
//            this.syncBillingShipping = true;
//            shipping.setSameAsBilling(true);
//        }

        if (response.goto_section) {
            this.gotoSection(response.goto_section, true);
            return true;
        }
        if (response.redirect) {
            location.href = response.redirect;
            return true;
        }
        return false;
    }
}
// patient
var Patient = Class.create();
Patient.prototype = {
    initialize: function(form, saveUrl){
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        this.saveUrl = saveUrl;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },
    setAddress: function(addressId){
        if (addressId) {
            request = new Ajax.Request(
                this.addressUrl+addressId,
                {method:'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
            );
        }
        else {
            this.fillForm(false);
        }
    },
    fillForm: function(transport){
        var elementValues = {};
        if (transport && transport.responseText){
            try{
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else{
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^patient:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && billingForm){
                    billingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },
    newAddress: function(isNew){
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('patient-new-address-form');
        } else {
            Element.hide('patient-new-address-form');
        }
        billing.setSameAsPatient(false);
    },

    resetSelectedAddress: function(){
        var selectElement = $('patient-address-select')
        if (selectElement) {
            selectElement.value='';
            //SHE added clear billing address fields when reset is called
            Field.clear('patient:firstname');
            Field.clear('patient:lastname');
            Field.clear('patient:address_id');  
            Field.clear('patient:street1');
            Field.clear('patient:city');
            Field.clear('patient:postcode');
            Field.clear('patient:telephone');
            if($('patient:email'))
                Field.clear('patient:email');
            if($('patient:email2'))
                Field.clear('patient:email2');
            if($('patient:day'))
                Field.clear('patient:day');
            if($('patient:month'))
                Field.clear('patient:month');
            if($('patient:year'))
                Field.clear('patient:year');
        }
    },
    save: function(){
        if (checkout.loadWaiting!=false) return;

        var validator = new Validation(this.form);
        if (validator.validate()) {

            checkout.setLoadWaiting('patient');


            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error){
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.patientRegionUpdater) {
                    patientRegionUpdater.update();
                }

                alert(response.message.join("\n"));
            }

            return false;
        }

        checkout.setStepResponse(response);
    }    
}
// billing
var Billing = Class.create();
Billing.prototype = {
    initialize: function(form, addressUrl, saveUrl){
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        this.addressUrl = addressUrl;
        this.saveUrl = saveUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
        
    },

    setAddress: function(addressId){
        if (addressId) {
            request = new Ajax.Request(
                this.addressUrl+addressId,
                {method:'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
            );
        }
        else {
            this.fillForm(false);
        }
    },

    newAddress: function(isNew){
    	var element = $('shipping:same_as_billing');
    	if(element){
    		$('shipping:same_as_billing').checked = false;
    	}
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('billing-new-address-form');
        } else {
            Element.hide('billing-new-address-form');
        }
    },

    resetSelectedAddress: function(){
        var selectElement = $('billing-address-select')
        if (selectElement) {
            selectElement.value='';
            //SHE added clear billing address fields when reset is called
            Field.clear('billing:firstname');
            Field.clear('billing:lastname');
            Field.clear('billing:address_id');  
            Field.clear('billing:company');
            Field.clear('billing:street1');
            Field.clear('billing:vat_id');
            Field.clear('billing:city');
            Field.clear('billing:postcode');
            Field.clear('billing:telephone');
            Field.clear('billing:email2');
        }
    },

    fillForm: function(transport){
        var elementValues = {};
        if (transport && transport.responseText){
            try{
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else{
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && billingForm){
                    billingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },

//    setUseForShipping: function(flag) {
//        $('shipping:same_as_billing').checked = flag;
//    },
    setSameAsPatient: function(flag) {
//        $('patient:use_for_billing').checked = flag;
//    	$('shipping:same_as_billing').checked = flag;
        if (flag) {
            this.syncWithPatient();
        }
    },

    syncWithPatient: function () {
//    	$('shipping:same_as_billing').checked = true;
        $('patient-address-select') && this.newAddress(!$('patient-address-select').value);
//        $('patient:use_for_billing').checked = true;
        if (!$('patient-address-select') || !$('patient-address-select').value) {
            arrElements = Form.getElements(this.form);
            for (var elemIndex in arrElements) {
                if (arrElements[elemIndex].id) {
                    var sourceField = $(arrElements[elemIndex].id.replace(/^billing:/, 'patient:'));
                    if (sourceField){
                        arrElements[elemIndex].value = sourceField.value;
                    }
                }
            }
            //$('shipping:country_id').value = $('billing:country_id').value;
//            shippingRegionUpdater.update();
//            $('shipping:region_id').value = $('billing:region_id').value;
//            $('shipping:region').value = $('billing:region').value;
            //shippingForm.elementChildLoad($('shipping:country_id'), this.setRegionValue.bind(this));
        } else {
            $('billing-address-select').value = $('patient-address-select').value;
        }
    },


    save: function(){
        if (checkout.loadWaiting!=false) return;

        var validator = new Validation(this.form);
        if (validator.validate()) {
            checkout.setLoadWaiting('billing');

            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
        document.body.fire('billing-request:completed', {transport: transport});
    },

    /**
     This method recieves the AJAX response on success.
     There are 3 options: error, redirect or html with shipping options.
     */
    nextStep: function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error){
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.billingRegionUpdater) {
                    billingRegionUpdater.update();
                }

                alert(response.message.join("\n"));
            }

            return false;
        }

        checkout.setStepResponse(response);
//        payment.initWhatIsCvvListeners();
        // DELETE
        //alert('error: ' + response.error + ' / redirect: ' + response.redirect + ' / shipping_methods_html: ' + response.shipping_methods_html);
        // This moves the accordion panels of one page checkout and updates the checkout progress
        //checkout.setBilling();
    }
}

// shipping
var Shipping = Class.create();
Shipping.prototype = {
    initialize: function(form, addressUrl, saveUrl, methodsUrl){
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        this.addressUrl = addressUrl;
        this.saveUrl = saveUrl;
        this.methodsUrl = methodsUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    setAddress: function(addressId){
        if (addressId) {
            request = new Ajax.Request(
                this.addressUrl+addressId,
                {method:'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
            );
        }
        else {
            this.fillForm(false);
        }
    },

    newAddress: function(isNew){
    	if($('shipping:same_as_billing')){
    		$('shipping:same_as_billing').checked = false;
    	}
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('shipping-new-address-form');
        } else {
            Element.hide('shipping-new-address-form');
        }
        //shipping.setSameAsBilling(false);
    },

    resetSelectedAddress: function(){
        var selectElement = $('shipping-address-select')
        if (selectElement) {
            selectElement.value='';
            Field.clear('shipping:firstname');
            Field.clear('shipping:lastname');
            Field.clear('shipping:address_id');  
            Field.clear('shipping:company');
            Field.clear('shipping:street1');
            Field.clear('shipping:city');
            Field.clear('shipping:postcode');
            Field.clear('shipping:telephone');
        }
    },

    fillForm: function(transport){
        var elementValues = {};
        if (transport && transport.responseText){
            try{
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else{
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^shipping:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && shippingForm){
                    shippingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },

//    setSameAsBilling: function(flag) {
//        $('shipping:same_as_billing').checked = flag;
//        if (flag) {
//            this.syncWithBilling();
//        }
//    },
    setSameAsPatient: function(flag) {    	
        if (flag) {
            this.syncWithPatient();
        }else{
        	//shipping addres not same as patient -> shipping will be different from billing
        	$('shipping:same_as_billing').checked = false;
        }
    },
    syncWithPatient: function () {
        $('patient-address-select') && this.newAddress(!$('patient-address-select').value);
        $('patient:use_for_shipping').checked = true;
        if (!$('patient-address-select') || !$('patient-address-select').value) {
            arrElements = Form.getElements(this.form);
            for (var elemIndex in arrElements) {
                if (arrElements[elemIndex].id) {
                    var sourceField = $(arrElements[elemIndex].id.replace(/^shipping:/, 'patient:'));
                    if (sourceField){
                        arrElements[elemIndex].value = sourceField.value;
                    }
                }
            }
            //$('shipping:country_id').value = $('billing:country_id').value;
//            shippingRegionUpdater.update();
//            $('shipping:region_id').value = $('billing:region_id').value;
//            $('shipping:region').value = $('billing:region').value;
            //shippingForm.elementChildLoad($('shipping:country_id'), this.setRegionValue.bind(this));
        } else {
            $('shipping-address-select').value = $('patient-address-select').value;
        }
    },


//    syncWithBilling: function () {
//        $('billing-address-select') && this.newAddress(!$('billing-address-select').value);
//        $('shipping:same_as_billing').checked = true;
//        if (!$('billing-address-select') || !$('billing-address-select').value) {
//            arrElements = Form.getElements(this.form);
//            for (var elemIndex in arrElements) {
//                if (arrElements[elemIndex].id) {
//                    var sourceField = $(arrElements[elemIndex].id.replace(/^shipping:/, 'billing:'));
//                    if (sourceField){
//                        arrElements[elemIndex].value = sourceField.value;
//                    }
//                }
//            }
//            //$('shipping:country_id').value = $('billing:country_id').value;
//            shippingRegionUpdater.update();
//            $('shipping:region_id').value = $('billing:region_id').value;
//            $('shipping:region').value = $('billing:region').value;
//            //shippingForm.elementChildLoad($('shipping:country_id'), this.setRegionValue.bind(this));
//        } else {
//            $('shipping-address-select').value = $('billing-address-select').value;
//        }
//    },

    setRegionValue: function(){
        $('shipping:region').value = $('billing:region').value;
    },

    save: function(){
        if (checkout.loadWaiting!=false) return;
        var validator = new Validation(this.form);
        if (validator.validate()) {
            checkout.setLoadWaiting('shipping');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error){
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.shippingRegionUpdater) {
                    shippingRegionUpdater.update();
                }
                alert(response.message.join("\n"));
            }

            return false;
        }

        checkout.setStepResponse(response);

        /*
         var updater = new Ajax.Updater(
         'checkout-shipping-method-load',
         this.methodsUrl,
         {method:'get', onSuccess: checkout.setShipping.bind(checkout)}
         );
         */
        //checkout.setShipping();
    }
}

// shipping method
var ShippingMethod = Class.create();
ShippingMethod.prototype = {
    initialize: function(form, saveUrl){
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        this.saveUrl = saveUrl;
        this.validator = new Validation(this.form);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    validate: function() {
        var methods = document.getElementsByName('shipping_method');
        if (methods.length==0) {
            alert(Translator.translate('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.').stripTags());
            return false;
        }        

        if(!this.validator.validate()) {
            return false;
        }
        try{
            
	        var dateinputs = document.getElementsByName('shipping_method');
	        var name;
	        for (var i = 0; i < dateinputs.length; i++) {
	            if (dateinputs[i].checked) {
	              name = dateinputs[i].value;
	              break;
	            }
		    }
		    name = name+'_delrange';
		    if(document.getElementsByName(name)[0]){
			    if(document.getElementsByName(name)[0].value==""){
			    	return false;
			    }
        	}
        }catch(e){}

        for (var i=0; i<methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
        alert(Translator.translate('Please specify shipping method.').stripTags());
        return false;
    },

    save: function(){

        if (checkout.loadWaiting!=false) return;
        if (this.validate()) {
            checkout.setLoadWaiting('shipping-method');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        if (response.error) {
            alert(response.message);
            return false;
        }

        if (response.update_section) {
            $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
        }

//        payment.initWhatIsCvvListeners();
        
        checkout.setStepResponse(response);

//        if (response.goto_section) {
//            checkout.gotoSection(response.goto_section, true);
//            checkout.reloadProgressBlock();
//            return;
//        }


//        checkout.setShippingMethod();
    }
}
//hearedfrom
//var Hearedfrom = Class.create();
//Hearedfrom.prototype = {
//    initialize: function(form, saveUrl){
//        this.form = form;
//        if ($(this.form)) {
//            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
//        }
//        this.saveUrl = saveUrl;
//        this.onSave = this.nextStep.bindAsEventListener(this);
//        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
//    },
//
//    save: function(){
//        if (checkout.loadWaiting!=false) return;
//
//        var validator = new Validation(this.form);
//        if (validator.validate()) {
//
//            checkout.setLoadWaiting('hearedfrom');
//
//
//            var request = new Ajax.Request(
//                this.saveUrl,
//                {
//                    method: 'post',
//                    onComplete: this.onComplete,
//                    onSuccess: this.onSave,
//                    onFailure: checkout.ajaxFailure.bind(checkout),
//                    parameters: Form.serialize(this.form)
//                }
//            );
//        }
//    },
//
//    resetLoadWaiting: function(transport){
//        checkout.setLoadWaiting(false);
//    },
//
//    nextStep: function(transport){
//        if (transport && transport.responseText){
//            try{
//                response = eval('(' + transport.responseText + ')');
//            }
//            catch (e) {
//                response = {};
//            }
//        }
//
//        if (response.error){
//            if ((typeof response.message) == 'string') {
//                alert(response.message);
//            } else {
//                if (window.hearedfromRegionUpdater) {
//                    hearedfromRegionUpdater.update();
//                }
//
//                alert(response.message.join("\n"));
//            }
//
//            return false;
//        }
//
//        checkout.setStepResponse(response);
//    }    
//}
//
// payment
//var Payment = Class.create();
//Payment.prototype = {
//    beforeInitFunc:$H({}),
//    afterInitFunc:$H({}),
//    beforeValidateFunc:$H({}),
//    afterValidateFunc:$H({}),
//    initialize: function(form, saveUrl){
//        this.form = form;
//        this.saveUrl = saveUrl;
//        this.onSave = this.nextStep.bindAsEventListener(this);
//        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
//    },
//
//    addBeforeInitFunction : function(code, func) {
//        this.beforeInitFunc.set(code, func);
//    },
//
//    beforeInit : function() {
//        (this.beforeInitFunc).each(function(init){
//            (init.value)();;
//        });
//    },
//
//    init : function () {
//        this.beforeInit();
//        var elements = Form.getElements(this.form);
//        if ($(this.form)) {
//            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
//        }
//        var method = null;
//        for (var i=0; i<elements.length; i++) {
//            if (elements[i].name=='payment[method]') {
//                if (elements[i].checked) {
//                    method = elements[i].value;
//                }
//            } else {
//                elements[i].disabled = true;
//            }
//            elements[i].setAttribute('autocomplete','off');
//        }
//        if (method) this.switchMethod(method);
//        this.afterInit();
//    },
//
//    addAfterInitFunction : function(code, func) {
//        this.afterInitFunc.set(code, func);
//    },
//
//    afterInit : function() {
//        (this.afterInitFunc).each(function(init){
//            (init.value)();
//        });
//    },
//
//    switchMethod: function(method){
//        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
//            this.changeVisible(this.currentMethod, true);
//            $('payment_form_'+this.currentMethod).fire('payment-method:switched-off', {method_code : this.currentMethod});
//        }
//        if ($('payment_form_'+method)){
//            this.changeVisible(method, false);
//            $('payment_form_'+method).fire('payment-method:switched', {method_code : method});
//        } else {
//            //Event fix for payment methods without form like "Check / Money order"
//            document.body.fire('payment-method:switched', {method_code : method});
//        }
//        if (method) {
//            this.lastUsedMethod = method;
//        }
//        this.currentMethod = method;
//    },
//
//    changeVisible: function(method, mode) {
//        var block = 'payment_form_' + method;
//        [block + '_before', block, block + '_after'].each(function(el) {
//            element = $(el);
//            if (element) {
//                element.style.display = (mode) ? 'none' : '';
//                element.select('input', 'select', 'textarea', 'button').each(function(field) {
//                    field.disabled = mode;
//                });
//            }
//        });
//    },
//
//    addBeforeValidateFunction : function(code, func) {
//        this.beforeValidateFunc.set(code, func);
//    },
//
//    beforeValidate : function() {
//        var validateResult = true;
//        var hasValidation = false;
//        (this.beforeValidateFunc).each(function(validate){
//            hasValidation = true;
//            if ((validate.value)() == false) {
//                validateResult = false;
//            }
//        }.bind(this));
//        if (!hasValidation) {
//            validateResult = false;
//        }
//        return validateResult;
//    },
//
//    validate: function() {
//        var result = this.beforeValidate();
//        if (result) {
//            return true;
//        }
//        var methods = document.getElementsByName('payment[method]');
//        if (methods.length==0) {
//            alert(Translator.translate('Your order cannot be completed at this time as there is no payment methods available for it.').stripTags());
//            return false;
//        }
//        for (var i=0; i<methods.length; i++) {
//            if (methods[i].checked) {
//                return true;
//            }
//        }
//        result = this.afterValidate();
//        if (result) {
//            return true;
//        }
//        alert(Translator.translate('Please specify payment method.').stripTags());
//        return false;
//    },
//
//    addAfterValidateFunction : function(code, func) {
//        this.afterValidateFunc.set(code, func);
//    },
//
//    afterValidate : function() {
//        var validateResult = true;
//        var hasValidation = false;
//        (this.afterValidateFunc).each(function(validate){
//            hasValidation = true;
//            if ((validate.value)() == false) {
//                validateResult = false;
//            }
//        }.bind(this));
//        if (!hasValidation) {
//            validateResult = false;
//        }
//        return validateResult;
//    },
//
//    save: function(){
//        if (checkout.loadWaiting!=false) return;
//        var validator = new Validation(this.form);
//        if (this.validate() && validator.validate()) {
//            checkout.setLoadWaiting('payment');
//            var request = new Ajax.Request(
//                this.saveUrl,
//                {
//                    method:'post',
//                    onComplete: this.onComplete,
//                    onSuccess: this.onSave,
//                    onFailure: checkout.ajaxFailure.bind(checkout),
//                    parameters: Form.serialize(this.form)
//                }
//            );
//        }
//    },
//
//    resetLoadWaiting: function(){
//        checkout.setLoadWaiting(false);
//    },
//
//    nextStep: function(transport){
//        if (transport && transport.responseText){
//            try{
//                response = eval('(' + transport.responseText + ')');
//            }
//            catch (e) {
//                response = {};
//            }
//        }
//        /*
//         * if there is an error in payment, need to show error message
//         */
//        if (response.error) {
//            if (response.fields) {
//                var fields = response.fields.split(',');
//                for (var i=0;i<fields.length;i++) {
//                    var field = null;
//                    if (field = $(fields[i])) {
//                        Validation.ajaxError(field, response.error);
//                    }
//                }
//                return;
//            }
//            alert(response.error);
//            return;
//        }
//
//        checkout.setStepResponse(response);
//
//        //checkout.setPayment();
//    },
//
//    initWhatIsCvvListeners: function(){
//        $$('.cvv-what-is-this').each(function(element){
//            Event.observe(element, 'click', toggleToolTip);
//        });
//    }
//}

var Review = Class.create();
Review.prototype = {
    initialize: function(saveUrl, successUrl, agreementsForm){
        this.saveUrl = saveUrl;
        this.successUrl = successUrl;
        this.agreementsForm = agreementsForm;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    save: function(){
        if (checkout.loadWaiting!=false) return;
        checkout.setLoadWaiting('review');
        var params;
        if (this.agreementsForm) {
            params += Form.serialize(this.agreementsForm);
        }
        if(params)
        	params.save = true;
        var request = new Ajax.Request(
            this.saveUrl,
            {
                method:'post',
                parameters:params,
                onComplete: this.onComplete,
                onSuccess: this.onSave,
                onFailure: checkout.ajaxFailure.bind(checkout)
            }
        );
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false, this.isSuccess);
    },

    nextStep: function(transport){
        if (transport && transport.responseText) {
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
            if (response.redirect) {
                this.isSuccess = true;
                location.href = response.redirect;
                return;
            }
            if (response.success) {
                this.isSuccess = true;
                window.location=this.successUrl;
            }
            else{
                var msg = response.error_messages;
                if (typeof(msg)=='object') {
                    msg = msg.join("\n");
                }
                if (msg) {
                    alert(msg);
                }
            }

            if (response.update_section) {
                $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
            }

            if (response.goto_section) {
                checkout.gotoSection(response.goto_section, true);
            }
        }
    },

    isSuccess: false
}
