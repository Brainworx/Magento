<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_Salesforcestockrequest_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('salesforcestockrequest_grid');
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
			$collection = Mage::getModel('hearedfrom/salesForceStockRequest')->getCollection();
			
			$select = $collection->getSelect();
			$resource = Mage::getSingleton('core/resource');
			$select->join(
					array('force' => $resource->getTableName('hearedfrom/salesForce')),
					'main_table.force_id = force.entity_id',
					array('user_nm')
			);
	        
	        $this->setCollection($collection);
	        
	        return parent::_prepareCollection();
        }catch(Exception $e){
        	Mage::log($e->getMessage());
        	//set error message in session
        	Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het opbouwen van het voorraad aanvraag overzicht.');
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
        
        $this->addColumn('force_id', array(
        		'header'    => Mage::helper('hearedfrom')->__('Zorgpunt ID'),
        		'align'     =>'left',
        		'index'     => 'force_id',
        ));
        
        $this->addColumn('user_nm', array(
        		'header'    => Mage::helper('hearedfrom')->__('Zorgpunt name'),
        		'align'     =>'left',
        		'type'		=>'text',
        		'width'     => '350px',
        		'index'     => 'user_nm',
        ));
        
        $this->addColumn('article_pcd', array(
        		'header'    => Mage::helper('hearedfrom')->__('Product code'),
        		'align'     =>'left',
        		'index'     => 'article_pcd',
        ));
        $this->addColumn('article', array(
        		'header'    => Mage::helper('hearedfrom')->__('Product'),
        		'align'     =>'left',
        		'index'     => 'article',
        ));
        
        $this->addColumn('inrequest_quantity', array(
        		'header'    => Mage::helper('hearedfrom')->__('QTY'),
        		'align'     =>'left',
        		'index'     => 'inrequest_quantity',
        ));
        
        $this->addColumn('create_dt', array(
        		'header'    => Mage::helper('hearedfrom')->__('Created'),
        		'header_css_class'=>'a-center',
	            'align'     =>'center',
	            'width'     => '50px',
	            'index'     => 'create_dt',
	        	'type'		=> 'datetime',
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        ));
				
        $this->addExportType('*/*/exportCsv', Mage::helper('hearedfrom')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('hearedfrom')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
}