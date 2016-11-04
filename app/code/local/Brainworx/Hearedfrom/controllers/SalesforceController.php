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
		Mage::Log ( "Save salesforce item button clicked - no action" );
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