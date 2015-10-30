<?php
 
class Brainworx_hearedfrom_Block_Adminhtml_Financialssupplier_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('financialssupplier_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
       // $this->setSaveParametersInSession(true);
        
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
			$collection = Mage::getModel("sales/order_item")->getCollection();
			// add joined data to the collection
			
			$select = $collection->getSelect();
			$select->columns("DATE_FORMAT(main_table.created_at,'%Y-%m') as date")->columns('COUNT(*) AS qty')
			->columns('SUM(original_price * qty_ordered) AS total')
			->group(array("supplierneworderemail","DATE_FORMAT(main_table.created_at,'%Y-%m')"));
			
			$select->join(array('order' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
					'main_table.order_id = order.entity_id',
					array('status'));
			
			$collection->addFieldToFilter('supplierinvoice',1);
			$collection->addFieldToFilter('order.status',array('neq'=>'canceled'));
			 
			
			$this->setCollection($collection);
			
			Mage::log((string)$collection->getSelect());
	        
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
        $this->addColumn('date', array(
        		'header'    => Mage::helper('hearedfrom')->__('Invoice Period'),
        		'align'     =>'center',
        		'width'     => '50px',
        		'index'     => 'date',
        		'filter'	=> false,
        		'type'		=> 'text',
        		'header_css_class'=>'a-center',
        ));
        $this->addColumn('supplierneworderemail', array(
        		'header'    => Mage::helper('hearedfrom')->__('Supplier'),
        		'align'     =>'center',
        		'width'     => '50px',
        		'index'     => 'supplierneworderemail',
        		'type'		=> 'text',
        		'header_css_class'=>'a-center',
        ));
        $this->addColumn('qty',array(
        		'header'=> Mage::helper('hearedfrom')->__('Quantity'),
        		'header_css_class'=>'a-right',
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'qty',
        		'filter'	=> false,
        ));
        $this->addColumn('total',array(
        		'header'=> Mage::helper('hearedfrom')->__('Total Amount'),
        		'header_css_class'=>'a-right',
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'total',
        		'filter'	=> false,
        ));
       
        
        //Add exort options on admin panel
        $this->addExportType('*/*/exportCsv', Mage::helper('hearedfrom')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('hearedfrom')->__('Excel XML'));
		
        return parent::_prepareColumns();
    }    
}