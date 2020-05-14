<?php
class Brainworx_Rental_RentalController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('rental/renteditem');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Rental' ) )->_title ( $this->__ ( 'RentedItems' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'rental/renteditem' );
		$this->renderLayout ();
	}
	public function newAction() {
		// $this->_forward('edit');
		Mage::Log ( "New rental item button clicked - no action" );
	}
	/**
	 * Edit rentalitem after selection in grid.
	 */
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id', null );
		$model = Mage::getModel ( 'rental/rentedItem' );
		if ($id) {
			$model->load ( ( int ) $id );
			if ($model->getEntityId ()) {
				$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
				if ($data) {
					$model->setData ( $data )->setEntityId ( $id );
				}
			} else {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'rental' )->__ ( 'Rented item does not exist' ) );
				$this->_redirect ( '*/*/' );
			}
		}
		// register the rental_data object to retrieve it and fill the form later
		Mage::register ( 'rental_data', $model );
		
		$this->_title ( $this->__ ( 'Rental' ) )->_title ( $this->__ ( 'Edit RentedItem' ) );
		$this->loadLayout ();
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
		$this->renderLayout ();
		
	}
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'rental/rentedItem' );
			
			$history = "[backend]";
			
			$id = $this->getRequest ()->getParam ( 'id' );
			
			foreach ( $data as $key => $value ) {
				if (is_array ( $value )) {
					$data [$key] = implode ( ',', $this->getRequest ()->getParam ( $key ) );
				}
			}
			
			$pickupsuccess=false;
			
			if ($id) {
				$history = $history . " bewerk verhuur id ".$id;
				$model->load ( $id );
				
				//check for ended rental item - not for edit
				if(empty($model->getEndDt()) && !empty($data["end_dt"])){
					$rentalstoend = array();
					$rentalstoend[]=$id;
					$basedt = date("Y-m-d");
					$enddt = date("Y-m-d",strtotime($data["end_dt"]));
					if($basedt < $enddt){
						$basedt = $data["end_dt"];
					}
					
					$preferredDT =date('d-m-Y', strtotime('+3 Weekdays',strtotime($basedt)));
				
					$pickupsuccess = Mage::helper('rental/terminator')->TerminateRentals($preferredDT,$rentalstoend,null,$data["end_dt"]);
						
					if (!$pickupsuccess) {
						Mage::log("ending rentals but some error occurred - rental ".$id);
						try{
							Mage::helper('rental/error')->sendErrorMail('Probleem end rental - edit renteditem '.$id);
						}catch (Exception $e){Mage::log("Error sending error mail - mass end rental");}
						Mage::throwException ( Mage::helper('rental')->__('Er liep iets fout bij het be�indigen van de huur of maken van de excel.') );
					}
					Mage::log("ending rentals from edit rental form finished - rental".$id);
					$history = $history . " - einde huur ".$enddt." ophaling ".$preferredDT;
				}elseif (empty($model->getEndDt())&& empty($data["end_dt"])){
					$history = $history . " - verhuur blijft lopen";
				}elseif(!empty($model->getEndDt()) && empty($data["end_dt"])){
					$history = $history . " - eerder beeindigde huur heropend";
					if(!empty($model->getClosedForInvoice())){
						$data['closed_for_invoice']=0;
					}
				}elseif(!empty($model->getEndDt()) && !empty($data["end_dt"])&&$model->getEndDt()!=$data["end_dt"]){
					$history = $history . " - verplaatsing einddatum huur";
					if(!empty($model->getClosedForInvoice())){
						$data['closed_for_invoice']=0;
					}
				}else{
					$history = $history . " - verhuur is reeds beeindigd, andere bewerking";
				}
			}else{
				$history = $history . " nieuwe verhuur";
			}
						
			$model->setData ( $data );			

			$order = Mage::getModel('sales/order')->load($model->getOrigOrderId());
			$item = Mage::getModel('sales/order_item')->load($model->getOrderItemId());
			

			$history = $history . " - item ".$item->getSku()." ".$item->getName();
						
			Mage::getSingleton ( 'adminhtml/session' )->setFormData ( $data );
			try {
				if ($id) {
					$model->setEntityId ( $id );
				}else{
					//unset id as otherwise null will be applied an no insert will be done
					unset($data['entity_id']);
				}
				$model->save ();
				
				if (! $model->getEntityId ()) {
					Mage::throwException ( Mage::helper ( 'rental' )->__( 'Error saving rented item' ) );
				}
				$text = Mage::helper ( 'rental' )->__( 'Rented item was successfully saved.' );
				
				if($pickupsuccess){
					$text = $text.' '.Mage::helper ( 'rental' )->__( 'Pickup excel mail was sent successfully.');
				}
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( $text);
				
				Mage::getSingleton ( 'adminhtml/session' )->setFormData ( false );				

				$order->addStatusHistoryComment($history,false)->save();

				// The following line decides if it is a "save" or "save and continue"
				if ($this->getRequest ()->getParam ( 'back' )) {
					$this->_redirect ( '*/*/edit', array (
							'id' => $model->getEntityId () 
					) );
				} else {
					$this->_redirect ( '*/*/' );
				}
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				if ($model && $model->getId ()) {
					$this->_redirect ( '*/*/edit', array (
							'id' => $model->getEntityId () 
					) );
				} else {
					$this->_redirect ( '*/*/' );
				}
			}				
			
			return;
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'rental' )->__ ( 'No data found to save' ) );
		$this->_redirect ( '*/*/' );
	}
	public function deleteAction() {
		if ($id = $this->getRequest ()->getParam ( 'id' )) {
			try {
				$model = Mage::getModel ( 'rental/rentedItem' );
				$model->setEntityId ( $id );
				$model->delete ();
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rental' )->__ ( 'The rented item has been deleted.' ) );
				$this->_redirect ( '*/*/' );
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				$this->_redirect ( '*/*/edit', array (
						'id' => $this->getRequest ()->getParam ( 'id' ) 
				) );
				return;
			}
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'adminhtml' )->__ ( 'Unable to find the rented item to delete.' ) );
		$this->_redirect ( '*/*/' );
	}
	
	/**
	 * Controller functie die aangeroepen wordt vanop de rental admin grid -> mass end rental
	 */
	public function massEndRentalAction() {
		$rentalIds = $this->getRequest ()->getParam ( 'rentalitem_id' ); // Form field name zoals gebruikt in Brainworx_Rental_Block_Adminhtml_Rental_Grid prepareMassAction
		if (! is_array ( $rentalIds )) {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'rental' )->__ ( 'Selecteer 1 of meerdere verhuuritem(s).' ) );
		} else {
			try {
				
				$success = Mage::helper('rental/terminator')->TerminateRentals(date('d-m-Y', strtotime('+3 Weekdays')),$rentalIds);
				if($success){
					Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'rental' )->__ ( '%d verhuuritem(s) werden vandaag beeindigd.', count ( $rentalIds ) ) );
					Mage::log(count ( $rentalIds ).' verhuuritem(s) werden vandaag beeindigd vanuit backend: '.implode(",", $rentalIds ));
				}else{
					Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'rental' )->__ ( 'Er liep iets fout, gelieve de resultaten te controleren.'));
				}
				} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				try{
					Mage::helper('rental/error')->sendErrorMail('Probleem mass end rental');
				}catch (Exception $e){Mage::log("Error sending error mail - mass end rental");}
			}
		}
		
		$this->_redirect ( '*/*/' );
	}
	/**
	 * Action method when clicked on createInvoices button on rental admin page
	 */
	public function createInvoicesAction() {
		try{
			//set the time limit for this operation
			//set_time_limit(300);
			
			//start timecount
			$startInvoicing = microtime(TRUE);
			$maxinvoicetime = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('INVOICE_RUNTIME_SECONDS')->getValue('text');	;
			$timeout = false;
			$rentalcounter = 0;
			$invoicecounter = 0;
			
			$invoiceDt  = date('Y-m-d', strtotime('last day of -1 month'));
			$count = 0;
			Mage::Log ( "creating invoices - " . $invoiceDt );
			// select orders to invoice sorted by order id - exclude already invoiced 
			$rentalsToInvoice = Mage::getModel ( 'rental/rentedItem' )->getCollection ()
			->addFieldToFilter( //last inv dt null or -2 month or sooner 
					array('last_inv_dt','last_inv_dt'),
					array(
							array('to'=>date('Y-m-d', strtotime('last day of -2 month'))),
							array('null' => true))
					)
			->addFieldToFilter( //end_dt null or after last invoice date which is -2 month
					array('end_dt','end_dt','last_inv_dt'),
					array(
							array('gt'=>date('Y-m-d', strtotime('last day of -6 month'))),
							array('null' => true),
							array('null' => true))
					)
			->addFieldToFilter( 
					array('closed_for_invoice','closed_for_invoice'),
					array(
							array('eq'	=>	0),
							array('null' => true))
					)
			->addFieldToFilter(
					array('start_dt'),
					array(
							array('to'=>$invoiceDt))
					)
			->setOrder('orig_order_id','asc');
			/* SELECT * FROM `rental_renteditem` AS `main_table` WHERE ((last_inv_dt <= '2015-09-30') OR (last_inv_dt IS NULL)) AND ((end_dt > '2015-05-31') OR (end_dt IS NULL) OR (last_inv_dt IS NULL)) AND ((start_dt <= '2015-10-31')) group by orig_order_id */
				
			if(count($rentalsToInvoice) > 0){
			
				$qtys = array (); // this will be used for processing the invoice
				// run invoices creation
				$rentalToInvoice = null;
				$comment = "Verhuurperiode details:";
				$grandTotal = 0;
				$grandTotalInclTax = 0;
				$tax = 0; 
				$custTaxClassID = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('CUST_TAX_ID')->getValue('text');	
				$sendInvoiceEmailAuto = Mage::getModel('core/variable')->setStoreId(Mage::app()->getStore()->getId())->loadByCode('ASEND_INV_ML')->getValue('text');
				foreach ( $rentalsToInvoice as $rental ) {
                    $product = null;
                    
					if(Mage::getModel ( 'sales/order' )->load ( $rental->getOrigOrderId() )->getStatus() == 'canceled'){
						//Mage::log('Set closed for invoice - skipping invoice for '.$rental->getEntityId().' as order '.$rental->getOrigOrderId ().' is cancelled');
						$rental->setClosedForInvoice(true)->save();
						continue;
					}
					if($rental->getLastInvDt()!=null && $rental->getEndDt() != null && $rental->getLastInvDt() >= $rental->getEndDt()){
						//Mage::log('Set closed for invoice - skipping invoice for '.$rental->getEntityId().' as lastinvdt('.$rental->getLastInvDt().') >= enddt ('.$rental->getEndDt().')');
						$rental->setClosedForInvoice(true)->save();
						continue;
					}
					if($rental->getEndDt() != null && $rental->getEndDt() <= $rental->getStartDt()){
						//Mage::log('Set closed for invoice - skipping invoice for '.$rental->getEntityId().' as enddt('.$rental->getEndDt().') <= startdt('.$rental->getStartDt().')');
						$rental->setClosedForInvoice(true)->save();
						continue;
					}
					if($rentalToInvoice == null){
						$rentalToInvoice = $rental;
					}
					
					if ($rentalToInvoice->getOrigOrderId() != $rental->getOrigOrderId()) {
						
						$this->createInvoice($rentalToInvoice,$comment,$grandTotal,$grandTotalInclTax,$tax,$qtys,$invoiceDt,$sendInvoiceEmailAuto);
						$invoicecounter++;
						//reset data for next rental
						$qtys = array (); // this will be used for processing the invoice
						$comment = "Verhuurperiode details:";
						$grandTotal = 0;
						$grandTotalInclTax = 0;
						$tax = 0;
						$transactionSave = Mage::getModel('core/resource_transaction');
						//check timing, continue to next or return to user
						$now = microtime(TRUE);
						if(($now-$startInvoicing)>$maxinvoicetime){
							$timeout = true;
							break;
						}
					}
					$rentalToInvoice = $rental;

					$rentalcounter++;
		
					$item = Mage::getModel ( 'sales/order_item' )->load($rentalToInvoice->getOrderItemId());
					$qty_to_invoice = 0;
					//calculate days since last invoice or start rental
					if($rentalToInvoice->getLastInvDt() == null){
						$startrental = new Datetime($rentalToInvoice->getStartDt());
						//initial time set the q to 0 as this are the nr of items, not days
						if($item->getQtyOrdered() == $rentalToInvoice->getQuantity()){
							$item->setQtyOrdered(0);
							$item->setQtyInvoiced(0);
						}
					}else{
						$startrental = new Datetime($rentalToInvoice->getLastInvDt());
						$startrental->add(new DateInterval('P1D'));
					}
					if($rentalToInvoice->getEndDt() != null && $rentalToInvoice->getEndDt() < $invoiceDt){
						$endrental = new DateTime($rentalToInvoice->getEndDt());
						Mage::log('Process last invoice for rental '.$rentalToInvoice->getEntityId());
						$rentalToInvoice->setClosedForInvoice(true);
					}else{
						$endrental = new DateTime($invoiceDt);
					}			
					//count quatity for rental
					$interval = $startrental->diff($endrental);
                    
                    $product = Mage::getModel ( 'catalog/product' )->load ($item->getProductId());
                    
                    $rentalinterval = $rentalToInvoice->getRentalinterval();
                    $intervalcmt = "dagen";
                    if(!empty($rentalinterval) && $rentalinterval == "m"){
                        $qty_to_invoice = 1 + $interval->m + (12*$interval->y);
                        if($qty_to_invoice > 1){
                            $intervalcmt = "maanden";
                        }else{
                            $intervalcmt = "maand";
                        }
                        //Mage::log('Rental invoice per month '.$rentalToInvoice->getQuantity()." x " . $product->getSku(). " - ".$qty_to_invoice." ".$intervalcmt." - van " . $startrental->format("Y-m-d") . " tot " . $endrental->format("Y-m-d"));
                    }else{
                        $qty_to_invoice = 1 + $interval->days;
                    }
					$comment = $comment . "<br>*".$rentalToInvoice->getQuantity()." x " . $product->getSku() 
					. " - ".$qty_to_invoice." ".$intervalcmt." - van " . $startrental->format("Y-m-d") . " tot " . $endrental->format("Y-m-d") ;
                    
					$qty_to_invoice = $qty_to_invoice * $rentalToInvoice->getQuantity();
					
					//set q to invoice for this item - set to 0 if you dont want to invoice this item
					$qtys [$item->getId ()] = ($qty_to_invoice );
					$item->setQtyToInvoice($qty_to_invoice );
							
					//add new q to the q ordered		
					$item->setQtyOrdered($item->getQtyOrdered()+$qty_to_invoice);
					//reset custom price back to the original price without tax
					$item->setPrice($item->getOriginalPrice());
					$item->setBasePrice($item->getPrice());
					//TODO check discounts
					
					//TODO check inclTAx -- default in calcTaxAmount is false
					$rowTotal = $item->getPrice() * $qty_to_invoice;
					$rowTaxamt = Mage::getSingleton('tax/calculation')->calcTaxAmount($rowTotal,$item->getTaxPercent());
					$taxamnt = $rowTaxamt / $qty_to_invoice;
					$rowTotalIncl = $rowTotal + $rowTaxamt;
					//only add the new invoiced amounts - others have been added earlier
					$grandTotal += $rowTotal;
					$grandTotalInclTax += $rowTotalIncl;
					$tax += $rowTaxamt;
					
					$item->setPriceInclTax($item->getPrice()+$taxamnt); //TODO check is this needed
					$item->setBasePriceInclTax($item->getPriceInclTax());
					$item->setTaxAmount($item->getTaxAmount()+$rowTaxamt); 
					$item->setBaseTaxAmount($item->getTaxAmount());
					$item->setRowTotal($item->getRowTotal()+$rowTotal);
					$item->setBaseRowTotal($item->getRowTotal());
					$item->setRowTotalInclTax($item->getRowTotalInclTax()+$rowTotalIncl);
					$item->setBaseRowTotalInclTax($item->getRowTotalInclTax());
					
					//make sure the custom price from checkout (0) will not be used to invoice
					$item->getProduct()->setIsSuperMode(false);
					
					//get tax code for product and save tax amount for sale in tax summary table
					$taxCollection = Mage::getModel('tax/calculation')->getCollection();
					$taxCollection->getSelect()->join(
							array('rate' => 'tax_calculation_rate'),
							'main_table.tax_calculation_rate_id = rate.tax_calculation_rate_id',
							array('code')
					);
						
					$taxCollection->addFieldToFilter(
							array('customer_tax_class_id'),
							array(
									array('eq'=>$custTaxClassID)) //TODO update customer tax class from real customer
					)->addFieldToFilter(
							array('product_tax_class_id'),
							array(
									array('eq'=>$item->getProduct()->getData('tax_class_id')))
					);
					$saleTax =  Mage::getModel('tax/sales_order_tax')->getCollection()
										->addFieldToFilter(
											array('order_id'),
											array(array('eq'=> $rental->getOrigOrderId())))
										->addFieldToFilter(
											array('code'),
											array(array('eq'=> $taxCollection->getFirstItem()->getCode())))->getFirstItem();
					$saleTax->setAmount($saleTax->getAmount()+$rowTaxamt);
					$saleTax->setBaseAmount($saleTax->getBaseAmount()+$rowTaxamt);
					$saleTax->setBaseRealAmount($saleTax->getBaseRealAmount()+$rowTaxamt);
					
					//TODO save invoiceid als last order id (of rename naar last invoice id) to the rental record
					$rentalToInvoice->setLastInvDt($invoiceDt);
					//$rentalToInvoice->setLastOrderId($invoice->getEntityId());
					
					$transactionSave = Mage::getModel('core/resource_transaction')
					->addObject($item)
					->addObject($rentalToInvoice)
					->addObject($saleTax);
					
					$transactionSave->save();
					
				}
				//last rental invoice - only invoice when there is an amount
				if($grandTotal > 0){
					$this->createInvoice($rentalToInvoice,$comment,$grandTotal,$grandTotalInclTax,$tax,$qtys,$invoiceDt,$sendInvoiceEmailAuto);	
					$invoicecounter++;
				}	
				
				Mage::Log("Invoices processed : " .  $rentalcounter . " rentalitems in ".$invoicecounter." invoices. (timeout:".$timeout.')');
				
				if($timeout){
					Mage::getSingleton('core/session')->addWarning('We verwerkten reeds '.$rentalcounter.' verhuuritems in '.$invoicecounter.' facturen. Maar er zijn nog meer facturen te maken, klik nog een keer op "Maak maandfacturen"');
				}else{
					Mage::getSingleton('core/session')->addSuccess('Alle '.$rentalcounter.' verhuuritems zijn verwerkt in '.$invoicecounter.' facturen (indien meerdere runs: op te tellen bij voorgaande aantallen).');
				}
			}else{
				Mage::Log("No rentals to be invoiced.");
				Mage::getSingleton('core/session')->addWarning('Sorry, er zijn geen facturen te maken voor '.$invoiceDt.'.');
			}
		
		}catch (Exception $e){
			Mage::Log("Error while processing invoices: " .$e->getMessage());
			Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het opbouwen van de facturen.');
			die;
		}
		
		$this->_redirect ( '*/*/' );
		
	}
	private function createInvoice($rentalToInvoice,$comment,$grandTotal,$grandTotalInclTax,$tax,$qtys,$invoiceDt,$sendInvoiceEmailAuto='F'){
		try{
			// all items processed - create and send invoice
			$order = Mage::getModel ( 'sales/order' )->load ( $rentalToInvoice->getOrigOrderId () );
			//Set qty 0 for items we do not want ot invoice
			$items = $order->getItemsCollection ();
			$non_rental_subtotal = 0;
			$non_rental_total = 0;
			foreach ( $items as $oitem ) {
				//Add 0 or uninvoiced quantity for other items
				if(!isset($qtys[$oitem->getId()])) {
                    // <!-- please note that if you don't want to invoice this product, set this value to 0 -->
					$qtys [$oitem->getId ()] = $oitem->getQtyOrdered()-$oitem->getQtyInvoiced()-$oitem->getQtyRefunded();
					if($qtys [$oitem->getId ()]<0){
						$qtys [$oitem->getId ()]=0;
					}else{
						//amount must be taken into account for check
						$non_rental_subtotal+=$oitem->getPrice()*$qtys [$oitem->getId ()];
						$non_rental_total+=$oitem->getPriceInclTax()*$qtys [$oitem->getId ()];
					}
                }
			}		
			//set totals on the order - add them to previous set totals
			$order->setSubtotal($order->getSubtotal() + $grandTotal);
			$order->setSubtotalInclTax($order->getSubtotalInclTax() + $grandTotalInclTax);
			//TODO check tax issue when multiple % - update // create sales_order_tax + sales_order_tax_item
			$order->setTaxAmount($order->getTaxAmount() + $tax);
			$order->setGrandTotal($order->getGrandTotal() + $grandTotalInclTax);
			
			$order->setBaseSubtotal($order->getSubtotal());
			$order->setBaseSubtotalInclTax($order->getSubtotalInclTax());
			$order->setBaseTaxAmount($order->getTaxAmount());
			$order->setBaseGrandTotal($order->getGrandTotal());
			//TODO set payment transation pending (ispaymenttransactionpending)
			//prepare invoice for the quantities we want to invoice
			$invoice = Mage::getModel ( 'sales/service_order', $order )->prepareInvoice ( $qtys );
			//TODO add Mage::getModel('sales/order_payment_transaction')
			//$invoice->getOrder()->getPayment()->setCanCapture(true);
			
			$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
			
			//register invoice to update order totals and invoiced quantities
			$invoice->register();
			// remove invoice grand total from order paid amount as they where added during register
			$order->setTotalPaid($order->getTotalPaid()-$invoice->getGrandTotal());
			$order->setBaseTotalPaid($order->getBaseTotalPaid()-$invoice->getBaseGrandTotal());
			
			//FIX ticket 159 tickets wrong invoice: Check subtotal generated invoice to prevent issue with amount CN
			if($invoice->getSubtotal()<>$grandTotal+$non_rental_subtotal 
			   && ($invoice->getSubtotal()-($grandTotal+$non_rental_subtotal))>0.01){
				$grandTotal+=$non_rental_subtotal;
				$grandTotalInclTax+=$non_rental_total;
				$correction = $invoice->getSubtotal()-$grandTotal;
				//Mage::log('Invoice subtotal wrong '.$order->getIncrementId().' amount '.$invoice->getSubtotal().' should be '.$grandTotal.' correction '.$correction);
				$invoice->setSubtotal($grandTotal);
				$invoice->setBaseSubtotal($grandTotal);
				$invoice->setSubtotalInclTax($grandTotalInclTax);
				$invoice->setBaseSubtotalInclTax($grandTotalInclTax);
				$invoice->setGrandTotal($grandTotalInclTax);
				$invoice->setBaseGrandTotal($grandTotalInclTax);
					
				// Correct order invoiced amounts - total due is auto generated correctly
				$order->setBaseTotalInvoiced($order->getGrandTotal());
				$order->setTotalInvoiced($order->getGrandTotal());
				$order->setSubtotalInvoiced($order->getSubTotal());
				$order->setBaseSubtotalInvoiced($order->getSubTotal());
				
				//fix //sales_flat_order_payment - base amount paid, amount_paid contain difference
				$payment = $order->getPayment();
				$payment->setAmountPaid($order->getGrandTotal());
				$payment->setBaseAmountPaid($order->getGrandTotal());
				$payment->save();
			}		
			//add note to invoice about invoiced period
			$invoice->addComment($comment);
			//$invoice->register ()->pay ();
			$invoice->getOrder ()->setIsInProcess ( true );
			// Options: STATE_OPEN / STATE_PAID / STATE_CANCELED					
			$invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_OPEN);
				
			//add comment about money transfer
			$history = $invoice->getOrder ()->addStatusHistoryComment ( 'Verhuur factuur '. $invoiceDt
					.' bedrag ' . $invoice->getGrandTotal()
					. ' automatisch verwerkt.', false );
				
			$history->setIsCustomerNotified ( true );
			if($sendInvoiceEmailAuto == 'Y'){
				// set this to false to not send the invoice via email
				// remark: invoice cc email addresses in config will receive email
				$invoice->sendEmail ( true, '' ); 
			}
				
			$invoice->setCreatedAt($invoiceDt);
			$invoice->setUpdatedAt(new DateTime('NOW'));
				
			$transactionSave = Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder());
				
			$transactionSave->save();	
		
		}catch (Exception $e){
			Mage::Log("Error while processing invoice for order ".$rentalToInvoice->getOrigOrderId()
			.": " .$e->getMessage());
			Mage::getSingleton('core/session')
			->addError('Sorry, er gebeurde een fout tijdens het opbouwen van de factuur voor order '
					. Mage::getModel ( 'sales/order' )->load ( $rentalToInvoice->getOrigOrderId () )->getIncrementId());
		}
	}
	/**
	 * Action called from the invoice admin view -> button markpaid
	 * The action is only available for open invoices and will update invoice status, order paid amount
	 * Total due for the order will be updated automatically
	 * Button markpaid is added by rental/observer
	 */ 
	public function markpaidAction() {
		try{
			$invoiceId = $this->getRequest()->getParam('iid');
			
			$invoice = Mage::getModel ( 'sales/order_invoice')->load($invoiceId); 
			
			if($invoice->getState()==Brainworx_Rental_Model_Order_Invoice::STATE_INCASSO){
				$invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_OPEN);
			}
			
			if($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_OPEN){
				$grandTotal = $invoice->getGrandTotal();
				$order = $invoice->getOrder();
				
				$order->setTotalPaid($order->getTotalPaid()+$invoice->getGrandTotal());
				$order->setBaseTotalPaid($order->getBaseTotalPaid()+$invoice->getBaseGrandTotal());
				
				$comment = 'Betaling ontvangen. '
						.' bedrag ' . round($invoice->getGrandTotal(),2) . ' euro.';

				$invoice->addComment($comment);
				
				//add comment about money transfer
				$history = $invoice->getOrder ()->addStatusHistoryComment ('Factuur ' . $invoiceId
						. ': '. $comment, false );
				
				$history->setIsCustomerNotified ( true );
				
				// set this to false to not send the invoice via email (remark: if copy to is activated the email to this address will be send)
				//$invoice->sendEmail ( false, '' );
				
				$invoice->setUpdatedAt(new DateTime('NOW'));
		
				$invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID);
				
				$transactionSave = Mage::getModel('core/resource_transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder());
				
				$transactionSave->save();
			}
			else{
				Mage::Log("Trying to mark an invoice as paid but invoice is not open! (id= " .$invoiceId." - state: " . $invoice->getState()." )");
			}
		}catch (Exception $e){
			Mage::Log("Error while processing payment: " .$e->getMessage());
			Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens invoeren van de betaling.');
			die;
		}
		
		//$this->_redirect ( '*/*/' );
		$this->_redirect('*/sales_order_invoice/view', array('invoice_id' => $invoiceId, 'order_id' => $invoice->getOrder()->getEntityId()));
	}
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'rentals.csv';
		$grid       = $this->getLayout()->createBlock('rental/adminhtml_rental_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'rentals.xml';
		$grid       = $this->getLayout()->createBlock('rental/adminhtml_rental_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
}
