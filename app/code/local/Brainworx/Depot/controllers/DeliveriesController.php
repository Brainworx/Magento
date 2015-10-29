<?php
class Brainworx_Depot_DeliveriesController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('depot/deliveries');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Overview' ) )->_title ( $this->__ ( 'Deliveries' ) );
		$this->loadLayout ();
// 		$block = $this->getLayout()->createBlock('core/text', 'test-block')->setText('<h1>Test</h1>');
// 		$this->_addContent($block);
		$this->_setActiveMenu ( 'depot/deliveries' );
		
		$this->renderLayout ();
	}
	public function newAction() {
		// $this->_forward('edit');
		Mage::Log ( "New deliveries item button clicked - no action" );
	}
	/**
	 * Edit rentalitem after selection in grid.
	 */
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id', null );
		$model = Mage::getModel ( 'sales/order_shipment_track' );
		if ($id) {
			$model->load ( ( int ) $id );
			if ($model->getEntityId ()) {
				$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
				if ($data) {
					$model->setData ( $data )->setEntityId ( $id );
				}
			} else {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'depot' )->__ ( 'Shipment item does not exist' ) );
				$this->_redirect ( '*/*/' );
			}
		}
		// register the rental_data object to retrieve it and fill the form later
		Mage::register ( 'shipment_data', $model );
		
		$this->_title ( $this->__ ( 'depot' ) )->_title ( $this->__ ( 'Edit Delivery Item' ) );
		$this->loadLayout ();
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
		$this->renderLayout ();
		
	}
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'sales/order_shipment_track' );
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
					Mage::throwException ( Mage::helper ( 'depot' )->__ ( 'Error saving delivery item' ) );
				}
				
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'depot' )->__ ( 'Delivery item was successfully saved.' ) );
				
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
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'depot' )->__ ( 'No data found to save' ) );
		$this->_redirect ( '*/*/' );
	}
	public function deleteAction() {
		Mage::Log ( "Delete deliveries item button clicked - no action" );
	}
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'deliveries.csv';
		$grid       = $this->getLayout()->createBlock('depot/adminhtml_deliveries_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'deliveries.xml';
		$grid       = $this->getLayout()->createBlock('depot/adminhtml_deliveries_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	public function exportPdfAction(){
		$fileName   = 'deliveries.pdf';
		$grid       = $this->getLayout()->createBlock('depot/adminhtml_deliveries_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getPdfFile($fileName));
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
	
}