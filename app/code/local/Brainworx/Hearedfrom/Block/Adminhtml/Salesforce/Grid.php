<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_Salesforce_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('salesforce_grid');
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
			$collection = Mage::getModel('hearedfrom/salesForce')->getCollection();
			
			// add joined data to the collection
// 			FROM customer_entity_varchar AS val
// 			INNER JOIN eav_attribute AS attr ON attr.attribute_id  = val.attribute_id
// 			WHERE attr.attribute_code IN ( 'firstname',  'lastname' )
// 			GROUP BY entity_id
			$select = $collection->getSelect();
// 			$resource = Mage::getSingleton('core/resource');
// 			$select->joinLeft(
// 					array('customer' => 'customer_entity_varchar'),
// 					'main_table.cust_id = customer.entity_id',
// 					array('value','attribute_id')
// 			);
// 			$select->join(
// 					array('attribute' => 'eav_attribute'),
// 					'customer.attribute_id = attribute.attribute_id',
// 					array('attribute_id','attribute_code')
// 			);
// 			$collection->addFieldToFilter('attribute_code','lastname');
	        
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
            'header'    => Mage::helper('hearedfrom')->__('ID'),
        	'header_css_class'=>'a-center',
            'align'     =>'center',
            'width'     => '50px',
            'index'     => 'entity_id',
        	'filter_index' => 'main_table.entity_id'
        ));
        
        $this->addColumn('create_dt', array(
        		'header'    => Mage::helper('hearedfrom')->__('Created'),
        		'header_css_class'=>'a-center',
	            'align'     =>'center',
	            'width'     => '50px',
	            'index'     => 'create_dt',
	        	'type'		=> 'date',
        ));
 
        $this->addColumn('user_nm', array(
            'header'    => Mage::helper('hearedfrom')->__('User name'),
            'align'     =>'left',
        	'type'		=>'text',
        	'width'     => '350px',
            'index'     => 'user_nm',
        ));
        
//         $this->addColumn('value', array(
//         		'header'    => Mage::helper('hearedfrom')->__('Customer Lastname'),
//         		'align'     =>'left',
//         		'index'     => 'value',
//         		'filter_index' => 'customer.value'
//         ));
        $this->addColumn('cust_id', array(
        		'header'    => Mage::helper('hearedfrom')->__('Customer #'),
        		'align'     =>'left',
        		'index'     => 'cust_id',
        ));
        $this->addColumn('linked_to', array(
        		'header'    => Mage::helper('hearedfrom')->__('Linked to Zorgpunt'),
        		'align'     =>'left',
        		'index'     => 'linked_to',
        ));
       
        $this->addColumn('end_dt', array(
            'header'    => Mage::helper('hearedfrom')->__('End'),
        	'header_css_class'=>'a-center',
            'align'     =>'center',
            'index'     => 'end_dt',
        	'type'		=> 'date',
        ));
				
        $this->addExportType('*/*/exportCsv', Mage::helper('rental')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('rental')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}