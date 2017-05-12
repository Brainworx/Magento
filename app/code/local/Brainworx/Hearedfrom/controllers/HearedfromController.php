<?php
class Brainworx_Hearedfrom_HearedfromController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('hearedfrom/salescommission');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Commission' ) )->_title ( $this->__ ( 'SalesCommission' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'hearedfrom/salescommission' );
		
		$this->renderLayout ();
	}
	public function newAction() {
		// $this->_forward('edit');
		Mage::Log ( "New hearedfrom item button clicked - no action" );
	}
	/**
	 * Edit rentalitem after selection in grid.
	 */
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id', null );
		$model = Mage::getModel ( 'hearedfrom/salesCommission' );
		if ($id) {
			$model->load ( ( int ) $id );
			if ($model->getEntityId ()) {
				$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
				if ($data) {
					$model->setData ( $data )->setEntityId ( $id );
				}
			} else {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'hearedfrom' )->__ ( 'Commission does not exist' ) );
				$this->_redirect ( '*/*/' );
			}
		}
		// register the rental_data object to retrieve it and fill the form later
		Mage::register ( 'hearedfrom_data', $model );
		
		$this->_title ( $this->__ ( 'Hearedfrom' ) )->_title ( $this->__ ( 'Edit Salescommission' ) );
		$this->loadLayout ();
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
		$this->renderLayout ();
		
	}
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'hearedfrom/salesCommission' );
			$id = $this->getRequest ()->getParam ( 'id' );
			
			foreach ( $data as $key => $value ) {
				if (is_array ( $value )) {
					$data [$key] = implode ( ',', $this->getRequest ()->getParam ( $key ) );
				}
			}
			
			if ($id) {
				$model->load ( $id );
			}
			$model->setData ( $data );
			
			Mage::getSingleton ( 'adminhtml/session' )->setFormData ( $data );
			try {
				if ($id) {
					$model->setEntityId ( $id );
				}
				$model->save ();
				
				if (! $model->getEntityId ()) {
					Mage::throwException ( Mage::helper ( 'hearedfrom' )->__ ( 'Error saving salescommission' ) );
				}
				
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'hearedfrom' )->__ ( 'Salescommision was successfully saved.' ) );
				
				Mage::getSingleton ( 'adminhtml/session' )->setFormData ( false );
				
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
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'hearedfrom' )->__ ( 'No data found to save' ) );
		$this->_redirect ( '*/*/' );
	}
	public function deleteAction() {
		if ($id = $this->getRequest ()->getParam ( 'id' )) {
			try {
				$model = Mage::getModel ( 'hearedfrom/salesCommission' );
				$model->setEntityId ( $id );
				$model->delete ();
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'hearedfrom' )->__ ( 'The salescommission has been deleted.' ) );
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
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'adminhtml' )->__ ( 'Unable to find the salescommission to delete.' ) );
		$this->_redirect ( '*/*/' );
	}
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'commission.csv';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_hearedfrom_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'commission.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_hearedfrom_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
	/**
	 * Action to update the sales commission for an order
	 */
	public function updateAction(){
		$sellernm = $this->getRequest()->getParam('sellerusernm');
		$orderid = $this->getRequest()->getParam('ooid');
		$salesseller = Mage::getModel("hearedfrom/salesSeller")->load(Mage::getModel("hearedfrom/salesSeller")->loadByOrderId($orderid)['entity_id']);
		$_hearedfrom_salesforce =  Mage::getModel("hearedfrom/salesForce")->loadByUsername($sellernm);
		$salesseller->setData("user_id",$_hearedfrom_salesforce["entity_id"]);
		$salesseller->save();
		Mage::log('update order '.$orderid.' to seller '.$sellernm);
		
	}
	/**
	 * Action to update the patients birth date for an order
	 */
	public function updateBirthDateAction(){
		$birthdate = $this->getRequest()->getParam('patientBirthDate');
		$orderIncrementId = $this->getRequest()->getParam('ooid');
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		$order->setPatientBirthDate($birthdate);
		$order->save();
		Mage::log('update order '.$orderid.' birthdate patient to '.$birthdate);	
	}
}