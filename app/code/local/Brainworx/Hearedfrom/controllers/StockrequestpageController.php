<?php
class Brainworx_Hearedfrom_StockrequestpageController extends Mage_Core_Controller_Front_Action  {

	protected function _getSession() {         
		return Mage::getSingleton('customer/session');     
	}     
		
	public function preDispatch() {         
		parent::preDispatch();             
		if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
        }
    }
 
    public function indexAction() { // landing page
        $this->loadLayout();
        $this->renderLayout(); 
    }
    public function requestAction() { // landing page
    	$this->loadLayout();
    	$this->renderLayout();
    }
    public function formPostAction()
    {
    	// Save data
    	if ($this->getRequest()->isPost()) {
    		try {
    			if(is_null($this->getRequest()->getParam('counter'))){
    				return $this->_redirect('*/stockrequestpage/request');
    			}
    			if(empty($this->getRequest()->getParam('product_code0'))){
    				return $this->_redirect('*/stockrequestpage/request');
    			}
    			if($this->getRequest()->getParam('quantity0')<=0){
    				return $this->_redirect('*/stockrequestpage/request');
    			}
	    		$customer = $this->_getSession()->getCustomer();
	    		$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
	    		if(empty($salesforce)){
	    			$this->_getSession()->addError($this->__('You are not allowed to perform this request, please contact Zorgpunt.'));
	    			return $this->_redirect('*/*/');
	    		}
	    		$counter = $this->getRequest()->getParam('counter');
	    		//prepare delivery
	    		$deliveryhelper = Mage::helper("hearedfrom/delivery");
	    		$shippinglist=array();
	    		$stockrequestids="";
	    		$street = $salesforce['street_nr'];
	    		if(empty($street)){
	    			$street=$customer->getPrimaryShippingAddress()->getStreetFull();
	    		}
	    		$zipcode = $salesforce['zip_cd'];
	    		if(empty($zipcode)){
	    			$zipcode=$customer->getPrimaryShippingAddress()->getPostcode();
	    		}
	    		$city=$salesforce['city'];
	    		if(empty($city)){
	    			$city=$customer->getPrimaryShippingAddress()->getCity();
	    		}
	    		$country=$salesforce['country'];
	    		if(empty($country)){
	    			$country=$customer->getPrimaryShippingAddress()->getCountry();
	    		}
	    		$phone=$salesforce['phone'];
	    		if(empty($phone)){
	    			$phone=$customer->getPrimaryShippingAddress()->getTelephone();
	    		}
	    		
	    		for($i = 0; $i <= $counter ; $i++){
	    			if(!empty($model)){
	    				unset($model);
	    			}
	    			if(empty($this->getRequest()->getParam('product_code'.$i))){
	    				//removed row
	    				continue;
	    			}
	    			if($this->getRequest()->getParam('quantity'.$i)<1){
	    				//error in quantity
	    				continue;
	    			}
	    			$quantity = $this->getRequest()->getParam('quantity'.$i);
	    			$model = Mage::getModel ( 'hearedfrom/salesForceStockRequest' );
	    			$model->setData('force_id',$salesforce['entity_id']);
	    			$model->setData('article_pcd',$this->getRequest()->getParam('product_code'.$i));
	    			$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$this->getRequest()->getParam('product_code'.$i));
	    			$model->setData('article',$_product->getName());
	    			$model->setData('inrequest_quantity',$quantity);
	    			 
	    			$model->save();
	    			//create and send excel
	    			$shippingitem = array();
	    			//items not supplied by supplier
	    			$shippingitem['Stockrequest #']=$model->getEntityId();
	    			$shippingitem['Leverdatum']=date('d-m-Y', strtotime('+ 3 weekday'));
	    			$shippingitem['Naam']=$salesforce['user_nm'];
	    			$shippingitem['Adres (straat + nr)']=$street;
	    			$shippingitem['Gemeente']=$city;
	    			$shippingitem['Postcode']=$zipcode;
	    			$shippingitem['Land']=$country;
	    			$shippingitem['Telefoon']=$phone;
	    			 
	    			$shippingitem['Artikel']=$_product->getName();
	    			$shippingitem['Aantal']=$quantity;
	    			$shippingitem['Artikelnr.']=$this->getRequest()->getParam('product_code'.$i);
	    			$shippingitem['Gewicht']=$_product->getWeight();
	    			$shippinglist[]=$shippingitem;
	    			unset($shippingitem);
	    			
	    			//update stock record
	    			$stock = Mage::getModel ( 'hearedfrom/salesForceStock');
	    			$stockrow = Mage::getModel ( 'hearedfrom/salesForceStock' )->loadByProdCodeAndSalesForce($model->getArticlePcd(),$model->getForceId());
	    			if(!empty($stockrow) && !empty($stockrow['entity_id'])){
	    				$stock->load($stockrow['entity_id']);
	    				$quantity = $quantity + $stock->getStockQuantity();
	    			}else{
	    				$stock->setData('force_id',$salesforce['entity_id']);
	    				$stock->setData('article_pcd',$this->getRequest()->getParam('product_code'.$i));
	    				$stock->setData('article',$_product->getName());
	    				$stock->setData('inrent_quantity',0);
	    				$stock->setData('enabled',1);
	    			}
	    			$stock->setData('stock_quantity',$quantity);
	    			$stock->setData('update_dt',date('d-m-Y H:i:s', strtotime('now')));
	    			$stock->save();
	    			
	    			unset($stock);
	    			unset($stockrow);
	    			if(empty($stockrequestids)){
	    				$stockrequestids = $model->getEntityId();
	    			}else{
	    				$stockrequestids =  $stockrequestids.'.'.$model->getEntityId();
	    			}
	    		}
	    		
	    		$deliveryhelper->createStockShipmentsReport($shippinglist,$stockrequestids);
	    		
	    		//$this->_getSession()->addSuccess($this->__('The request has been saved.'));
	    		$this->_redirectSuccess(Mage::getUrl('*/stockrequestpage/', array('_secure'=>true)));
	    		return;
    			
    		} catch (Mage_Core_Exception $e) {
    			$this->_getSession()->addException($e, $this->__('Cannot save stock.'));
    			Mage::log("Error saving stock: ". $e->getMessage());
    			Mage::helper("hearedfrom/error")->sendErrorMail("Error saving stock: ". $e->getMessage());
    		} catch (Exception $e) {
    			$this->_getSession()->addException($e, $this->__('Cannot save stock.'));
    			Mage::log("Error saving stock: ". $e->getMessage());
    			Mage::helper("hearedfrom/error")->sendErrorMail("Error saving stock: ". $e->getMessage());
    		}
    	}
    
    	return $this->_redirectError(Mage::getUrl('*/account', array('_secure'=>true)));
    }
    public function requestPostAction()
    {
    	$success = true;
    	// Save data
    	if ($this->getRequest()->isPost()) {
    		try {
    			if(is_null($this->getRequest()->getParam('counter'))){
    				return $this->_redirect('*/stockrequestpage/request');
    			}
    			if(empty($this->getRequest()->getParam('product_code0'))){
    				return $this->_redirect('*/stockrequestpage/request');
    			}
    			if($this->getRequest()->getParam('quantity0')<=0){
    				return $this->_redirect('*/stockrequestpage/request');
    			}
    			$customer = $this->_getSession()->getCustomer();
    			$salesforce = Mage::getModel('hearedfrom/salesForce')->loadByCustid($customer->getEntityId());
    			if(empty($salesforce)){
    				$this->_getSession()->addError($this->__('You are not allowed to perform this request, please contact Zorgpunt.'));
    				return $this->_redirect('*/*/');
    			}
    			$counter = $this->getRequest()->getParam('counter');
    			//prepare delivery
    			$deliveryhelper = Mage::helper("hearedfrom/delivery");
    			$shippinglist=array();
    			$stockrequestids="";
    			$street = $salesforce['street_nr'];
    			if(empty($street)){
    				$street=$customer->getPrimaryShippingAddress()->getStreetFull();
    			}
    			$zipcode = $salesforce['zip_cd'];
    			if(empty($zipcode)){
    				$zipcode=$customer->getPrimaryShippingAddress()->getPostcode();
    			}
    			$city=$salesforce['city'];
    			if(empty($city)){
    				$city=$customer->getPrimaryShippingAddress()->getCity();
    			}
    			$country=$salesforce['country'];
    			if(empty($country)){
    				$country=$customer->getPrimaryShippingAddress()->getCountry();
    			}
    			$phone=$salesforce['phone'];
    			if(empty($phone)){
    				$phone=$customer->getPrimaryShippingAddress()->getTelephone();
    			}
    	   
    			for($i = 0; $i <= $counter ; $i++){
    				if(!empty($model)){
    					unset($model);
    				}
    				if(empty($this->getRequest()->getParam('product_code'.$i))){
    					//removed row
    					continue;
    				}
    				if($this->getRequest()->getParam('quantity'.$i)<1){
    					//error in quantity
    					continue;
    				}
    				$quantity = $this->getRequest()->getParam('quantity'.$i);
    				$model = Mage::getModel ( 'hearedfrom/salesForceStockRequest' );
    				$model->setData('force_id',$salesforce['entity_id']);
    				$model->setData('article_pcd',$this->getRequest()->getParam('product_code'.$i));
    				$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$this->getRequest()->getParam('product_code'.$i));
    				$model->setData('article',$_product->getName());
    				$model->setData('inrequest_quantity',$quantity);
    	    
    				$model->save();
    				//create and send excel
    				$shippingitem = array();
    				//items not supplied by supplier
    				$shippingitem['Stockrequest #']=$model->getEntityId();
    				$shippingitem['Leverdatum']=date('d-m-Y', strtotime('+ 3 weekday'));
    				$shippingitem['Naam']=$salesforce['user_nm'];
    				$shippingitem['Adres (straat + nr)']=$street;
    				$shippingitem['Gemeente']=$city;
    				$shippingitem['Postcode']=$zipcode;
    				$shippingitem['Land']=$country;
    				$shippingitem['Telefoon']=$phone;
    	    
    				$shippingitem['Artikel']=$_product->getName();
    				$shippingitem['Aantal']=$quantity;
    				$shippingitem['Artikelnr.']=$this->getRequest()->getParam('product_code'.$i);
    				$shippingitem['Gewicht']=$_product->getWeight();
    				$shippinglist[]=$shippingitem;
    				unset($shippingitem);
    
    				//update stock record
    				$stock = Mage::getModel ( 'hearedfrom/salesForceStock');
    				$stockrow = Mage::getModel ( 'hearedfrom/salesForceStock' )->loadByProdCodeAndSalesForce($model->getArticlePcd(),$model->getForceId());
    				if(!empty($stockrow) && !empty($stockrow['entity_id'])){
    					$stock->load($stockrow['entity_id']);
    					$quantity = $quantity + $stock->getStockQuantity();
    				}else{
    					$stock->setData('force_id',$salesforce['entity_id']);
    					$stock->setData('article_pcd',$this->getRequest()->getParam('product_code'.$i));
    					$stock->setData('article',$_product->getName());
    					$stock->setData('inrent_quantity',0);
    					$stock->setData('enabled',1);
    				}
    				$stock->setData('stock_quantity',$quantity);
    				$stock->setData('update_dt',date('d-m-Y H:i:s', strtotime('now')));
    				$stock->save();
    
    				unset($stock);
    				unset($stockrow);
    				if(empty($stockrequestids)){
    					$stockrequestids = $model->getEntityId();
    				}else{
    					$stockrequestids =  $stockrequestids.'.'.$model->getEntityId();
    				}
    			}
    	   
    			$deliveryhelper->createStockShipmentsReport($shippinglist,$stockrequestids);
    			 
    		} catch (Mage_Core_Exception $e) {
    			$this->_getSession()->addException($e, $this->__('Cannot save stock.'));
    			Mage::log("Error saving stock: ". $e->getMessage());
    			Mage::helper("hearedfrom/error")->sendErrorMail("Error saving stock: ". $e->getMessage());
    			$success = false;
    		} catch (Exception $e) {
    			$this->_getSession()->addException($e, $this->__('Cannot save stock.'));
    			Mage::log("Error saving stock: ". $e->getMessage());
    			Mage::helper("hearedfrom/error")->sendErrorMail("Error saving stock: ". $e->getMessage());
    			$success = false;
    		}
    	}
    
    	//return $this->_redirectError(Mage::getUrl('*/account', array('_secure'=>true)));
    	$response = array();
    	$response['success'] = $success;
    	
    	$response['message'] = Mage::helper('hearedfrom')->__('Je aanvraag werd geregistreerd. De voorraad zal spoedig geleverd worden.');
    	if (!$success) {
    		$response['message'] = Mage::helper('hearedfrom')->__('Er liep iets fout, gelieve het resultaat te controleren of contact op te nemen met Zorgpunt.');
    	}
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    	 
    	//$this->_getSession()->addSuccess($this->__('The request has been saved.'));
    	//$this->_redirectSuccess(Mage::getUrl('*/stockpage/index', array('_secure'=>true)));
    	return;
    }
    
    
}
