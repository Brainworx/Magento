<?php
class Brainworx_Hearedfrom_SalesforceController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('hearedfrom/salesforceoverview');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Overview' ) )->_title ( $this->__ ( 'SalesForceOverview' ) );
		$this->loadLayout ();
// 		$block = $this->getLayout()->createBlock('core/text', 'test-block')->setText('<h1>Test</h1>');
// 		$this->_addContent($block);
		$this->_setActiveMenu ( 'hearedfrom/salesforceoverview' );
		
		$this->renderLayout ();
	}
	public function newAction() {
		$this->_forward('edit');
		Mage::Log ( "New salesforce item button clicked - no action" );
	}
	/**
	 * Edit salesforce after selection in grid.
	 */
	public function editAction() {
		Mage::Log ( "Edit salesforce item button clicked" );
		$id = $this->getRequest ()->getParam ( 'id', null );
		$model = Mage::getModel ( 'hearedfrom/salesForce' );
		if ($id) {
			$model->load ( ( int ) $id );
			if ($model->getEntityId ()) {
				$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
				if ($data) {
					$model->setData ( $data )->setEntityId ( $id );
				}
			} else {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'hearedfrom' )->__ ( 'SalesForce does not exist' ) );
				$this->_redirect ( '*/*/' );
			}
		}
		// register the rental_data object to retrieve it and fill the form later
		Mage::register ( 'salesforce_data', $model );
		
		$this->_title ( $this->__ ( 'Hearedfrom' ) )->_title ( $this->__ ( 'Edit Salesforce' ) );
		$this->loadLayout ();
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
		$this->renderLayout ();
	}
	public function saveAction() {
		Mage::Log ( "Save salesforce item button clicked" );
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'hearedfrom/salesForce' );
			$id = $this->getRequest ()->getParam ( 'id' );
				
			foreach ( $data as $key => $value ) {
				if (is_array ( $value )) {
					$data [$key] = implode ( ',', $this->getRequest ()->getParam ( $key ) );
				}
			}
								
			if ($id) {
				$model->load ( $id );
			}else{
				//unset id as otherwise null will be applied an no insert will be done
				unset($data['entity_id']);
			}
			$model->setData ( $data );
				
			Mage::getSingleton ( 'adminhtml/session' )->setFormData ( $data );
			try {
				if ($id) {
					$model->setEntityId ( $id );
				}
				$model->save();
		
				if (! $model->getEntityId ()) {
					Mage::throwException ( Mage::helper ( 'hearedfrom' )->__( 'Error saving salesforce item' ) );
				}
				$text = Mage::helper ( 'hearedfrom' )->__( 'Salesforce was successfully saved.' );
				
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( $text);
		
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
				Mage::Log ( "Save salesforce item error: ".$e->getMessage () );
				
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
		Mage::Log ( "Delete salesforce item button clicked - no action" );
	}
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'salesforce.csv';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_salesforce_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'salesforce.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_salesforce_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
}