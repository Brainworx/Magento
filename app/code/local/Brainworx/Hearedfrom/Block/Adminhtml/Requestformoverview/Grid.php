<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_Requestformoverview_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('requestformoverview_grid');
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
			$collection = Mage::getModel('hearedfrom/requestform')->getCollection();
			
			$select = $collection->getSelect();
			$resource = Mage::getSingleton('core/resource');
			$select->join(
					array('type' => $resource->getTableName('hearedfrom/requesttype')),
					'main_table.type_id = type.entity_id',
					array('type','description','partner_name','partner_email')
			);
			$select->join(
					array('force' => $resource->getTableName('hearedfrom/salesForce')),
					'main_table.salesforce_id = force.entity_id',
					array('user_nm')
			);
	        
	        $this->setCollection($collection);
	        
	        return parent::_prepareCollection();
        }catch(Exception $e){
        	Mage::log($e->getMessage());
        	//set error message in session
        	Mage::getSingleton('core/session')->addError('Sorry, er gebeurde een fout tijdens het opbouwen van het aanvraag overzicht.');
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
        
        $this->addColumn('created_at', array(
        		'header'    => Mage::helper('hearedfrom')->__('Created'),
        		'header_css_class'=>'a-center',
        		'align'     =>'center',
        		'width'     => '50px',
        		'index'     => 'created_at',
        		'type'		=> 'datetime',
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        ));
        
        $this->addColumn('type', array(
        		'header'    => Mage::helper('hearedfrom')->__('Requesttype'),
        		'align'     =>'left',
        		'index'     => 'type',
        		'filter_index' => 'type.type',
        ));
        
        $this->addColumn('request', array(
        		'header'    => Mage::helper('hearedfrom')->__('Request'),
        		'align'     =>'left',
        		'type'		=>'text',
        		'width'     => '150px',
        		'index'     => 'request',
        ));
        
        $this->addColumn('cust_id', array(
        		'header'    => Mage::helper('hearedfrom')->__('Customer Id'),
        		'align'     =>'left',
        		'index'     => 'cust_id',
        ));
        $this->addColumn('name', array(
        		'header'    => Mage::helper('hearedfrom')->__('Name'),
        		'align'     =>'left',
        		'type'		=>'text',
        		'width'     => '150px',
        		'index'     => 'name',
        ));
        
        $this->addColumn('address', array(
        		'header'    => Mage::helper('hearedfrom')->__('Address'),
        		'align'     =>'left',
        		'index'     => 'address',
        ));
        
        $this->addColumn('phone', array(
        		'header'    => Mage::helper('hearedfrom')->__('Phone'),
        		'align'     =>'left',
        		'index'     => 'phone',
        ));
        $this->addColumn('email', array(
        		'header'    => Mage::helper('hearedfrom')->__('Email'),
        		'align'     =>'left',
        		'index'     => 'email',
        ));
        $this->addColumn('comment', array(
        		'header'    => Mage::helper('hearedfrom')->__('Comment'),
        		'align'     =>'left',
        		'index'     => 'comment',
        ));
        $this->addColumn('comment', array(
        		'header'    => Mage::helper('hearedfrom')->__('Comment'),
        		'align'     =>'left',
        		'index'     => 'comment',
        ));
        $this->addColumn('user_nm', array(
        		'header'    => Mage::helper('hearedfrom')->__('Zorgpunt name'),
        		'align'     =>'left',
        		'index'     => 'user_nm',
        		'filter_index' => 'force.user_nm',
        		'type'  => 'options',
        		'options'	=>  Mage::getModel('hearedfrom/salesForce')->getUserNames(),
        ));
        $this->addColumn('partner_name', array(
        		'header'    => Mage::helper('hearedfrom')->__('Partner name'),
        		'align'     =>'left',
        		'index'     => 'partner_name',
        ));
        $this->addColumn('partner_email', array(
        		'header'    => Mage::helper('hearedfrom')->__('Partner email'),
        		'align'     =>'left',
        		'index'     => 'partner_email',
        ));
        
        $this->addColumn('completed_at', array(
        		'header'    => Mage::helper('hearedfrom')->__('Completed'),
        		'header_css_class'=>'a-center',
        		'align'     =>'center',
        		'width'     => '50px',
        		'index'     => 'completed_at',
        		'type'		=> 'datetime',
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        ));
				
        $this->addExportType('*/*/exportCsv', Mage::helper('hearedfrom')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('hearedfrom')->__('Excel XML'));
        
        return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
    	return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}