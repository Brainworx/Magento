<?php
 
class Brainworx_hearedfrom_Block_Adminhtml_Hearedfrom_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('salescommission_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
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
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'entity_id',
        ));
        $this->addColumn('user_nm', array(
        		'header'    => Mage::helper('hearedfrom')->__('Seller'),
        		'align'     =>'left',
        		'index'     => 'user_nm',
        		'filter_index' => 'force.user_nm'
        ));
//         $this->addColumn('increment_id', array(
//         		'header'    => Mage::helper('hearedfrom')->__('Bestelling #'),
//         		'align'     =>'left',
//         		'width'     => '50px',
//         		'index'     => 'increment_id'
//         ));
        $this->addColumn('orig_order_id', array(
        		'header'    => Mage::helper('hearedfrom')->__('Bestelling #'),
        		'align'     =>'left',
        		'width'     => '50px',
        		'index'     => 'orig_order_id'
        ));
        $this->addColumn('type', array(
        		'header'    => Mage::helper('hearedfrom')->__('Type'),
        		'align'     =>'left',
        		'width'     => '10px',
        		'index'     => 'type'
        ));
        $this->addColumn('net_amount', array(
        		'header'    => Mage::helper('hearedfrom')->__('Amount ex VAT'),
        		'align'     =>'left',
        		'width'     => '25px',
        		'index'     => 'net_amount'
        ));
        $this->addColumn('brut_amount', array(
        		'header'    => Mage::helper('hearedfrom')->__('Amount invl VAT'),
        		'align'     =>'left',
        		'width'     => '25px',
        		'index'     => 'brut_amount'
        ));
        $this->addColumn('create_dt', array(
        		'header'    => Mage::helper('hearedfrom')->__('Registered'),
        		'align'     =>'right',
        		'width'     => '50px',
        		'index'     => 'create_dt',
        		'type'		=> 'date',
        ));    
		
        return parent::_prepareColumns();
    }    
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}