<?php
class Brainworx_Hearedfrom_RequestformoverviewController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('hearedfrom/requestformoverview');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Overview' ) )->_title ( $this->__ ( 'Requestforms' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'requestforms/requestformoverview' );
		
		$this->renderLayout ();
	}
	
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'zorgpunt_contact_aanvragen.csv';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_requestformoverview_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'zorgpunt_contact_aanvragen.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_requestformoverview_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
	public function newAction() {
		Mage::log('New requestform klicked - no action');
	}
	/**
	 * Edit rentalitem after selection in grid.
	 */
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id', null );
		$model = Mage::getModel ( 'hearedfrom/requestform' );
		if ($id) {
			$model->load ( ( int ) $id );
			if ($model->getEntityId ()) {
				$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
				if ($data) {
					$model->setData ( $data )->setEntityId ( $id );
				}
			} else {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'hearedfrom' )->__ ( 'Requestform does not exist' ) );
				$this->_redirect ( '*/*/' );
			}
		}
		// register the rental_data object to retrieve it and fill the form later
		Mage::register ( 'requestform_data', $model );
	
		$this->_title ( $this->__ ( 'Hearedfrom' ) )->_title ( $this->__ ( 'Edit requestform' ) );
		$this->loadLayout ();
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
		$this->renderLayout ();
	
	}
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'hearedfrom/requestform' );
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
					Mage::throwException ( Mage::helper ( 'hearedfrom' )->__ ( 'Error saving requestform' ) );
				}
	
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'hearedfrom' )->__ ( 'Requestform was successfully saved.' ) );
	
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
		Mage::log('Delete Request form selected -- no action');
	}
}