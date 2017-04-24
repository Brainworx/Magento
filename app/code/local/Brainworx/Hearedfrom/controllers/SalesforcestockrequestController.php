<?php
class Brainworx_Hearedfrom_SalesforcestockrequestController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('hearedfrom/salesforcestockrequestoverview');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Overview' ) )->_title ( $this->__ ( 'SalesForceStockRequestOverview' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'hearedfrom/salesforceostockrequestverview' );
		
		$this->renderLayout ();
	}
	
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'zorgpunt_voorraad_aanvragen.csv';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_salesforcestockrequest_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'zorgpunt_voorraad_aanvragen.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_salesforcestockrequest_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
}