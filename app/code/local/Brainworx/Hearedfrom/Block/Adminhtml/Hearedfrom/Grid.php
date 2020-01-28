<?php
 
class Brainworx_hearedfrom_Block_Adminhtml_Hearedfrom_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('salescommission_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        
        //$this->setDefaultFilter( Mage::registry('preparedFilter') );
    }
 
    /**
     * Prepare data to load in hearedfrom grid
     * (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Grid::_prepareCollection()
     */
    protected function _prepareCollection()
    {
    	try{
			// Get and set our collection for the grid
			$collection = Mage::getModel('hearedfrom/salesCommission')->getCollection();
			
			// add joined data to the collection
			
			$select = $collection->getSelect();
			$resource = Mage::getSingleton('core/resource');
			$select->join(
					array('force' => $resource->getTableName('hearedfrom/salesForce')),
					'main_table.user_id = force.entity_id',
					array('user_nm')
			);
			$select->join(
					array('order' => $resource->getTableName('sales/order')),
					'main_table.orig_order_id = order.entity_id',
					array('increment_id')
			);
			$select->join(
					array('item' => $resource->getTableName('sales/order_item')),
					'main_table.order_item_id = item.item_id',
					array('product' => 'name', 'sku')
			);
			$collection->addFieldToFilter('main_table.ristorno',array('neq'=>0));
			
			$this->setCollection($collection);
	        
	        return parent::_prepareCollection();
        }catch(Exception $e){
        	Mage::log($e->getMessage());
        	//set error message in session
        	Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het opbouwen van het overzicht.');
        	die;
        }
    }
 
    /**
     * Setup hearedfrom Grid columns
     * (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Grid::_prepareColumns()
     */
    protected function _prepareColumns()
    {
    	
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('hearedfrom')->__('ID'),
        	'header_css_class'=>'a-center',
            'align'     =>'center',
            'width'     => '50px',
            'index'     => 'entity_id',
        	'filter_index' => 'main_table.entity_id'
        ));
        $this->addColumn('user_nm', array(
        		'header'    => Mage::helper('hearedfrom')->__('Seller'),
        		'align'     =>'left',
        		'width'     => '100px',
        		'index'     => 'user_nm',
        		'filter_index' => 'force.user_nm',
        		'type'  => 'options',
        		'options'	=>  Mage::getModel('hearedfrom/salesForce')->getUserNames(),
        ));
        $this->addColumn('sold_by', array(
        		'header'    => Mage::helper('hearedfrom')->__('Seller detail'),
        		'align'     =>'left',
        		'width'     => '100px',
        		'index'     => 'sold_by',
        		'filter_index' => 'sold_by',
        ));
        $this->addColumn('product', array(
        		'header'    => Mage::helper('hearedfrom')->__('Product'),
        		'align'     =>'left',
        		'width'     => '100px',
        		'index'     => 'product',
        		'filter_index' => 'item.name'
        ));
       
        $this->addColumn('increment_id', array(
        		'header'    => Mage::helper('hearedfrom')->__('Bestelling #'),
        		'header_css_class'=>'a-center',
        		'align'     =>'center',
        		'width'     => '50px',
        		'index'     => 'increment_id'
        ));
        $this->addColumn('type', array(
        		'header'    => Mage::helper('hearedfrom')->__('Type'),
        		'header_css_class'=>'a-center',
        		'align'     =>'center',
        		'width'     => '10px',
        		'index'     => 'type',
        		'type'  => 'options',
        		'options'	=>  Mage::getModel('hearedfrom/salesCommission')->getTypes(),
        ));
//         $this->addColumn('net_amount', array(
//         		'header'    => Mage::helper('hearedfrom')->__('Amount ex VAT'),
//         		'header_css_class'=>'a-right',
//         		'width'     => '25px',
//         		'index'     => 'net_amount',
//         		'type'		=> 'number',//price
//         		//'currency_code' => Mage::app()->getStore(0)->getBaseCurrency()->getCode(),
        		
//         ));
        $this->addColumn('ristorno', array(
        		'header'    => Mage::helper('hearedfrom')->__('Ristorno'),
        		'header_css_class'=>'a-right',
        		'width'     => '25px',
        		'index'     => 'ristorno',
        		'type'		=> 'number', //price
        		//'currency_code' => Mage::app()->getStore(0)->getBaseCurrency()->getCode(),
        ));
        $this->addColumn('create_dt', array(
        		'header'    => Mage::helper('hearedfrom')->__('Date'),
        		'header_css_class'=>'a-center',
        		'align'     =>'center',
        		'width'     => '50px',
        		'index'     => 'create_dt',
        		'type'		=> 'datetime',
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        		'filter_index'=>'main_table.create_dt'
        ));    
        
        //Add exort options on admin panel
        $this->addExportType('*/*/exportCsv', Mage::helper('hearedfrom')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('hearedfrom')->__('Excel XML'));
		
        return parent::_prepareColumns();
    }    
    protected function _categoryFilter($collection, $column)
    {
    	if (!$value = $column->getFilter()->getValue()) {
    		return $this;
    	}
    	//create a string with the category ids based on the provided value
    	$cats="(";
    	$names = explode(",",$value);
    	if(!empty($names) && count($names)>0){
	    	foreach($names as $key => $catname){
	    		$cats .= Mage::getResourceModel('catalog/category_collection')
	    		->addFieldToFilter('name', array("like"=>"%".$catname."%"))
	    		->getFirstItem()->getId();
	    		if($key < count($names)-1)
	    			$cats.= ' ,';
	    	}
	    	$cats .= ")";
    	}else{
    		return $this;
    	}
    	if($cats == '()'){
    		return $this;
    	}
    	
    	//add subquery to filter out the product for the category
    	$this->getCollection()->getSelect()->where(
    			"item.sku in (select p.sku from catalog_product_entity p join catalog_category_product cp
    			on p.entity_id =  cp.product_id where cp.category_id in ".$cats.")");    
    
    			return $this;
    }
    
//     public function getRowUrl($row)
//     {
//         return $this->getUrl('*/*/edit', array('id' => $row->getId()));
//     }
}
