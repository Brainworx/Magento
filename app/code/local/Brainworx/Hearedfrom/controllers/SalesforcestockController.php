<?php
class Brainworx_Hearedfrom_SalesforcestockController extends Mage_Adminhtml_Controller_Action {
	/*
	 * Update required for security for non-admin users after patch 6285
	 */
	protected function _isAllowed(){
		return Mage::getSingleton('admin/session')->isAllowed('hearedfrom/salesforcestockoverview');
	}
	public function indexAction() {
		$this->_title ( $this->__ ( 'Overview' ) )->_title ( $this->__ ( 'SalesForceStockOverview' ) );
		$this->loadLayout ();
		$this->_setActiveMenu ( 'hearedfrom/salesforceostockverview' );
		
		$this->renderLayout ();
	}
	public function newAction() {
		$this->_forward('edit');
	}
	/**
	 * Edit salesforce after selection in grid.
	 */
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id', null );
		$model = Mage::getModel ( 'hearedfrom/salesForceStock' );
		if ($id) {
			$model->load ( ( int ) $id );
			if ($model->getEntityId ()) {
				$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
				if ($data) {
					$model->setData ( $data )->setEntityId ( $id );
				}
			} else {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'hearedfrom' )->__ ( 'SalesForceStock does not exist' ) );
				$this->_redirect ( '*/*/' );
			}
		}
		// register the rental_data object to retrieve it and fill the form later
		Mage::register ( 'salesforcestock_data', $model );
		
		$this->_title ( $this->__ ( 'Hearedfrom' ) )->_title ( $this->__ ( 'Edit Salesforcestock' ) );
		$this->loadLayout ();
		$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
		$this->renderLayout ();
	}
	public function saveAction() {
		Mage::Log ( "Save salesforcestock item button clicked" );
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'hearedfrom/salesForceStock' );
			$id = $this->getRequest ()->getParam ( 'id' );
				
			foreach ( $data as $key => $value ) {
				if (is_array ( $value )) {
					$data [$key] = implode ( ',', $this->getRequest ()->getParam ( $key ) );
				}
			}
								
			if ($id) {
				$model->load ( $id );
			}else{
				//new record
				$data['enabled']=1;
				//unset id as otherwise null will be applied an no insert will be done
				unset($data['entity_id']);
			}
			$model->setData ( $data );
				
			Mage::getSingleton ( 'adminhtml/session' )->setFormData ( $data );
			try {
				/*20170410 - add the name of the article*/
				$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$data['article_pcd']);
				
				//only merge stockrecords if enabled
				if(!empty($data['enabled']) && $data['enabled']==1){
					//check for existing record of this product
					$stockrow = Mage::getModel ( 'hearedfrom/salesForceStock' )->loadByProdCodeAndSalesForce($data['article_pcd'],$data['force_id']);
					if(!empty($stockrow) && !empty($stockrow['entity_id'])){
						if($id && $stockrow['entity_id']!=$id){
							Mage::Log("[should never occur]Old stockrecords replaced by existing ".$id." - ".$stockrow['entity_id']." disabled");
							$stock = Mage::getModel ( 'hearedfrom/salesForceStock');
							$stock->load($stockrow['entity_id']);
							$stock->setData('enabled',0);
							$stock->setData('update_dt',date('d-m-Y H:i:s', strtotime('now')));
							$stock->save();
						}elseif($id){
							//nothing, the stockrecord we found is being editted
						}else{
							//stockrecord found for this new record, merging
							$quantity = $stockrow['stock_quantity'];
							$quantity_inrent=$stockrow['inrent_quantity'];
							$model->load($stockrow['entity_id']);
							$model->setData('stock_quantity',$data['stock_quantity'] + $quantity);
							$model->setData('inrent_quantity',$data['inrent_quantity'] + $quantity_inrent);
						}						
					}
				}
				
				$model->setData('article',$_product->getName());
				if(!empty($data['enabled'])){
					$model->setData('enabled',$data['enabled']);
				}else{
					$model->setData('enabled',0);
				}
				$model->setData('update_dt',date('d-m-Y H:i:s', strtotime('now')));
				
				if ($id) {
					$model->setEntityId ( $id );
				}
				$model->save();
		
				if (! $model->getEntityId ()) {
					Mage::throwException ( Mage::helper ( 'hearedfrom' )->__( 'Error saving salesforcestock item' ) );
				}
				$text = Mage::helper ( 'hearedfrom' )->__( 'Salesforcestock was successfully saved.' );
				
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
				Mage::Log ( "Save salesforcestock item error: ".$e->getMessage () );
				
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
		Mage::Log ( "Delete salesforcestock item button clicked - no action" );
	}
	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction()
	{
		$fileName   = 'zorgpunt_voorraad.csv';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_salesforcestock_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}
	
	/**
	 *  Export order grid to Excel XML format
	 */
	public function exportExcelAction()
	{
		$fileName   = 'zorgpunt_voorraad.xml';
		$grid       = $this->getLayout()->createBlock('hearedfrom/adminhtml_salesforcestock_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$this->_prepareDownloadResponse($fileName, $content, $contentType);
	}
}