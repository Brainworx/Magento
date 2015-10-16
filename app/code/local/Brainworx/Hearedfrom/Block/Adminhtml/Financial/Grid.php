<?php
 
class Brainworx_hearedfrom_Block_Adminhtml_Financial_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('financial_grid');
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
			$collection = Mage::getModel("sales/order_invoice")->getCollection();
			// add joined data to the collection
			
			$select = $collection->getSelect();
			$select->columns("DATE_FORMAT(created_at,'%Y-%m') as date")->columns('COUNT(*) AS qty')
			->columns('SUM(grand_total) AS total')
			->columns('SUM(subtotal) AS sub_total')
			->columns('SUM(tax_amount) AS tax')
			->columns('SUM(shipping_amount) AS shipping')
			->columns('sum(if (state = 1, grand_total,0)) as open')
			->columns('sum(if (state = 2, grand_total,0)) as paid')
			->columns('sum(if (state = 3, grand_total,0)) as cancelled')
			->columns('sum(if (state > 3 or state < 1, grand_total,0)) as other')
			->group("DATE_FORMAT(created_at,'%Y-%m')");
			$resource = Mage::getSingleton('core/resource');
			
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
        $this->addColumn('date', array(
        		'header'    => Mage::helper('hearedfrom')->__('Invoice Period'),
        		'align'     =>'left',
        		'width'     => '50px',
        		'index'     => 'date',
        		'filter'	=> false,
        		'type'		=> 'text',
        ));
        $this->addColumn('qty',array(
        		'header'=> Mage::helper('hearedfrom')->__('Quantity'),
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'qty',
        		'filter'	=> false,
        ));
        $this->addColumn('total',array(
        		'header'=> Mage::helper('hearedfrom')->__('Total Invoiced Amount'),
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'total',
        		'filter'	=> false,
        ));
        $this->addColumn('sub_total',array(
        		'header'=> Mage::helper('hearedfrom')->__('Total (excl.vat)'),
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'sub_total',
        		'filter'	=> false,
        ));
        $this->addColumn('tax',array(
                'header'=> Mage::helper('hearedfrom')->__('Total Tax Amount'),
             	'type'  => 'number',
             	'width' => '25px',
             	'index' => 'tax',
        		'filter'	=> false,
        ));
        $this->addColumn('shipping_amount',array(
           		'header'=> Mage::helper('hearedfrom')->__('Shipping'),
           		'type'  => 'number',
           		'width' => '25px',
           		'index' => 'shipping',
        		'filter'	=> false,
        ));
        $this->addColumn('open',array(
        		'header'=> Mage::helper('hearedfrom')->__('Open'),
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'open',
        		'filter'	=> false,
        ));
        $this->addColumn('paid',array(
        		'header'=> Mage::helper('hearedfrom')->__('Paid'),
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'paid',
        		'filter'	=> false,
        ));
        $this->addColumn('cancelled',array(
        		'header'=> Mage::helper('hearedfrom')->__('Cancelled'),
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'cancelled',
        		'filter'	=> false,
        ));
        
        //Add exort options on admin panel
        $this->addExportType('*/*/exportCsv', Mage::helper('hearedfrom')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('hearedfrom')->__('Excel XML'));
		
        return parent::_prepareColumns();
    }    
}