<?php
 
class Brainworx_Rental_Block_Adminhtml_Rental_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('renteditem_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        
        //$this->setDefaultFilter( Mage::registry('preparedFilter') );
    }
 
    /**
     * Prepare data to load in rental grid
     * (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Grid::_prepareCollection()
     */
    protected function _prepareCollection()
    {
    	try{
			// Get and set our collection for the grid
			$collection = Mage::getModel('rental/rentedItem')->getCollection();
			
			// add joined data to the collection
			
			$select = $collection->getSelect();
			$resource = Mage::getSingleton('core/resource');
			$select->join(
					array('order' => $resource->getTableName('sales/order')),
					'main_table.orig_order_id = order.entity_id',
					array('customer_id','customer_lastname','customer_firstname','increment_id')
			);
	        $select->join(
	        		array('item' => $resource->getTableName('sales/order_item')),
	        		'main_table.order_item_id = item.item_id',
	        		array('product' => 'name', 'sku')
	        );
	        
	        $this->setCollection($collection);
	        
	        return parent::_prepareCollection();
        }catch(Exception $e){
        	Mage::log($e->getMessage());
        	//set error message in session
        	Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het opbouwen van het verhuuroverzicht.');
        	die;
        }
    }
 
    /**
     * Setup Rental Grid columns
     * (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Grid::_prepareColumns()
     */
    protected function _prepareColumns()
    {
    	
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('rental')->__('ID'),
        	'header_css_class'=>'a-center',
            'align'     =>'center',
            'width'     => '50px',
            'index'     => 'entity_id',
        ));
        
//         $this->addColumn('orig_order_id', array(
//         		'header'    => Mage::helper('rental')->__('Original Order Id'),
//         		'align'     =>'left',
//         	    'width'     => '50px',
//         		'index'     => 'orig_order_id',
//         		'type'  => 'options',
//         		'options'	=>  Mage::getModel('rental/rentedItem')->getOrderIds(),
//         ));
        $this->addColumn('increment_id', array(
        		'header'    => Mage::helper('rental')->__('Bestelling #'),
        		'align'     =>'left',
        		'width'     => '50px',
        		'index'     => 'increment_id',
        		'type'  => 'options',
        		'options'	=>  Mage::getModel('rental/rentedItem')->getIncrementIds(),
        ));
 
        $this->addColumn('customer_id', array(
            'header'    => Mage::helper('rental')->__('Customer Id'),
            'align'     =>'left',
        	'type'		=>'text',
        	'width'     => '50px',
            'index'     => 'customer_id',
        	'filter_index' => 'order.customer_id',
        ));
        
        $this->addColumn('customer_lastname', array(
        		'header'    => Mage::helper('rental')->__('Customer Lastname'),
        		'align'     =>'left',
        		'index'     => 'customer_lastname',
        		'filter_index' => 'order.customer_lastname'
        ));
//         $this->addColumn('customer_firstname', array(
//         		'header'    => Mage::helper('rental')->__('Customer Firstname'),
//         		'align'     =>'left',
//         		'index'     => 'customer_firstname',
//         		'filter_index' => 'order.customer_firstname'
//         ));
 
//         $this->addColumn('sku', array(
//             'header'    => Mage::helper('rental')->__('SKU'),
//             'align'     =>'right',
//             'width'     => '50px',
//             'index'     => 'sku',
//         	'filter_index' => 'item.sku'
//         ));
        $this->addColumn('product', array(
        		'header'    => Mage::helper('rental')->__('Produkt'),
        		'align'     =>'left',
        		'index'     => 'product',
        		'filter_index' => 'item.name'
        ));
        
        $this->addColumn('start_dt', array(
            'header'    => Mage::helper('rental')->__('Start Rental'),
        	'header_css_class'=>'a-center',
            'align'     =>'center',
            'width'     => '50px',
            'index'     => 'start_dt',
        	'type'		=> 'date',
        ));
		
        $this->addColumn('last_inv_dt', array(
            'header'    => Mage::helper('rental')->__('Last Invoiced'),
        	'header_css_class'=>'a-center',
            'align'     =>'center',
            'index'     => 'last_inv_dt',
        	'type'		=> 'date',
        ));
		
        $this->addColumn('end_dt', array(
            'header'    => Mage::helper('rental')->__('End Rental'),
        	'header_css_class'=>'a-center',
            'align'     =>'center',
            'index'     => 'end_dt',
        	'type'		=> 'date',
        ));
				
        $this->addColumn('create_dt', array(
            'header'    => Mage::helper('rental')->__('Created'),
            'align'     =>'left',
            'index'     => 'create_dt',
        	'type'		=> 'datetime',
        ));
		
        return parent::_prepareColumns();
    }
    
//     protected function _applyMyFilter(Varien_Data_Collection_Db $collection, Mage_Adminhtml_Block_Widget_Grid_Column $column)
//     {
//     	$select = $collection->getSelect();
//     	$field = $column->getIndex();
//     	$value = $column->getFilter()->getValue();
//     	$select->having("$field=?", $value);
//     }
    			
    /**
     * Prepare grid for mass action -> add endrental and line select checkbox
     */ 
    protected function _prepareMassaction()
    {
    	//DB id for unique identification of the row to update
    	$this->setMassactionIdField('entity_id');
    	//Add field with name to be used for later retrieval in the rentalcontroller
    	$this->getMassactionBlock()->setFormFieldName('rentalitem_id');
    
    	//TODO add pop question 
    	//Add action item to dropdown and link it to the massEndRental controller
    	$this->getMassactionBlock()->addItem('endrental', array(
    			'label'=> Mage::helper('rental')->__('End Rentals today'),
    			'url'  => $this->getUrl('*/*/massEndRental', array('' => '')), // public function massDeleteAction() in Mage_Adminhtml_Tax_RateController
    			//'confirm' => Mage::helper('rental')->__('De geselecteerde verhuuritems beëindigen?')
    	));
    
    	return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}