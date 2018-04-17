<?php
class Brainworx_Hearedfrom_CreditnotesviewController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('hearedfrom/creditnotesview');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Creditnotes' ) )->_title ( $this->__ ( 'CreditnotesView' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'hearedfrom/creditnotesview' );
		
		$this->renderLayout ();
	}
	public function newAction() {
		// $this->_forward('edit');
		Mage::Log ( "New creditnotesview item button clicked - no action" );
	}
	/**
	 * Edit item after selection in grid.
	 */
	public function editAction() {
		Mage::Log ( "Edit creditnotesview item button clicked - no action" );
		
	}
	public function saveAction() {
		Mage::Log ( "Save creditnotesview item button clicked - no action" );
	}
	public function deleteAction() {
		Mage::Log ( "Delete creditnotesview item button clicked - no action" );
	}
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'invoices_report.csv';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_creditnotesview_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'invoices_excell_report.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_creditnotesview_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	public function exportXmlAction()
	{
		$fileName   = 'invoices_report.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_creditnotesview_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getXmlFile($fileName));
	}
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
	
}