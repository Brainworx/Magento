<?php
class Brainworx_Hearedfrom_CommissionviewController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('hearedfrom/commissionview');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Commission' ) )->_title ( $this->__ ( 'CommissionView' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'hearedfrom/commissionview' );
		
		$this->renderLayout ();
	}
	public function newAction() {
		// $this->_forward('edit');
		Mage::Log ( "New commissionview item button clicked - no action" );
	}
	/**
	 * Edit item after selection in grid.
	 */
	public function editAction() {
		Mage::Log ( "Edit commissionview item button clicked - no action" );
		
	}
	public function saveAction() {
		Mage::Log ( "Save commissionview item button clicked - no action" );
	}
	public function deleteAction() {
		Mage::Log ( "Delete commissionview item button clicked - no action" );
	}
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'commission_report.csv';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_commissionview_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'commission_report.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_commissionview_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
	
}