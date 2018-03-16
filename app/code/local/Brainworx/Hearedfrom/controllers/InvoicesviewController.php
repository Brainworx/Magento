<?php
class Brainworx_Hearedfrom_InvoicesviewController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('hearedfrom/invoicesview');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Invoices' ) )->_title ( $this->__ ( 'InvoicesView' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'hearedfrom/invoicesview' );
		
		$this->renderLayout ();
	}
	public function newAction() {
		// $this->_forward('edit');
		Mage::Log ( "New invoicesview item button clicked - no action" );
	}
	/**
	 * Edit item after selection in grid.
	 */
	public function editAction() {
		Mage::Log ( "Edit invoicesview item button clicked - no action" );
		
	}
	public function saveAction() {
		Mage::Log ( "Save invoicesview item button clicked - no action" );
	}
	public function deleteAction() {
		Mage::Log ( "Delete invoicesview item button clicked - no action" );
	}
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'invoices_report.csv';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_invoicesview_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'invoices_excell_report.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_invoicesview_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	public function exportXmlAction()
	{
		$fileName   = 'invoices_report.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_invoicesview_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getXmlFile($fileName));
	}
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
	
}