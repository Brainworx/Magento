<?php
class Brainworx_Hearedfrom_RequesttypeController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('requests/requesttype');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Overview' ) )->_title ( $this->__ ( 'Requesttype' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'requests/requesttype' );
		
		$this->renderLayout ();
	}
	public function newAction() {
		$this->_forward('edit');
	}
	/**
	 * Edit rentalitem after selection in grid.
	 */
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id', null );
		$model = Mage::getModel ( 'hearedfrom/requesttype' );
		if ($id) {
			$model->load ( ( int ) $id );
			if ($model->getEntityId ()) {
				$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
				if ($data) {
					$model->setData ( $data )->setEntityId ( $id );
				}
			} else {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'hearedfrom' )->__ ( 'Requesttype does not exist' ) );
				$this->_redirect ( '*/*/' );
			}
		}
		// register the rental_data object to retrieve it and fill the form later
		Mage::register ( 'requesttype_data', $model );
		
		$this->_title ( $this->__ ( 'Hearedfrom' ) )->_title ( $this->__ ( 'Edit requesttype' ) );
		$this->loadLayout ();
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
		$this->renderLayout ();
		
	}
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'hearedfrom/requesttype' );
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
					Mage::throwException ( Mage::helper ( 'hearedfrom' )->__ ( 'Error saving requesttype' ) );
				}
				
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'hearedfrom' )->__ ( 'Requesttype was successfully saved.' ) );
				
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
				$model = Mage::getModel ( 'hearedfrom/requesttype')->load($id);
				$model->setEndDt( date("Y-m-d") );
				$model->save();
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'hearedfrom' )->__ ( 'The requesttype has been terminated.' ) );
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
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'adminhtml' )->__ ( 'Unable to find the requesttype to delete.' ) );
		$this->_redirect ( '*/*/' );
	}
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'requesttype.csv';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_requesttype_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'requesttype.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_requesttype_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	
}