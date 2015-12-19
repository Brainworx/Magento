<?php
class Brainworx_Hearedfrom_FinancialssupplierController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('hearedfrom/supplierSalesOverview');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Overview' ) )->_title ( $this->__ ( 'SupplierSalesOverview' ) );
		$this->loadLayout ();
// 		$block = $this->getLayout()->createBlock('core/text', 'test-block')->setText('<h1>Test</h1>');
// 		$this->_addContent($block);
		$this->_setActiveMenu ( 'hearedfrom/suppliersalesoverview' );
		
		$this->renderLayout ();
	}
	public function newAction() {
		// $this->_forward('edit');
		Mage::Log ( "New financialsupplier item button clicked - no action" );
	}
	/**
	 * Edit rentalitem after selection in grid.
	 */
	public function editAction() {
		Mage::Log ( "Edit financialsupplier item button clicked - no action" );
	}
	public function saveAction() {
		Mage::Log ( "Save financialsupplier item button clicked - no action" );
	}
	public function deleteAction() {
		Mage::Log ( "Delete financialsupplier item button clicked - no action" );
	}
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'financialsupplier.csv';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_financialssupplier_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'financialsupplier.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_financialssupplier_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
}