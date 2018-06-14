<?php
 
class Brainworx_Hearedfrom_Block_Adminhtml_Requesttype_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('requesttype_grid');
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
			$collection = Mage::getModel('hearedfrom/requesttype')->getCollection();
			
			// add joined data to the collection
			
			$select = $collection->getSelect();
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
    	
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('hearedfrom')->__('ID'),
        	'header_css_class'=>'a-center',
            'align'     =>'center',
            'width'     => '50px',
            'index'     => 'entity_id',
        	'filter_index' => 'main_table.entity_id'
        ));
        $this->addColumn('type', array(
        		'header'    => Mage::helper('hearedfrom')->__('Type'),
        		'align'     =>'left',
        		'width'     => '100px',
        		'index'     => 'type',
        ));
        $this->addColumn('description', array(
        		'header'    => Mage::helper('hearedfrom')->__('Description'),
        		'align'     =>'left',
        		'width'     => '100px',
        		'index'     => 'description',
        ));
        $this->addColumn('partner_name',array(
        		'header'=> Mage::helper('hearedfrom')->__('Partner name'),
        		'type'  => 'text',
        		'width'     => '100px',
        		'index' => 'partner_name',
        ));
        $this->addColumn('partner_email', array(
        		'header'    => Mage::helper('hearedfrom')->__('Partner email'),
        		'align'     =>'left',
        		'width'     => '100px',
        		'index'     => 'partner_email',
        ));
       
        $this->addColumn('created_at', array(
        		'header'    => Mage::helper('hearedfrom')->__('Create Date'),
        		'header_css_class'=>'a-center',
        		'align'     =>'center',
        		'width'     => '50px',
        		'index'     => 'created_at',
        		'type'		=> 'datetime',
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        ));   
        $this->addColumn('updated_at', array(
        		'header'    => Mage::helper('hearedfrom')->__('Update Date'),
        		'header_css_class'=>'a-center',
        		'align'     =>'center',
        		'width'     => '50px',
        		'index'     => 'updated_at',
        		'type'		=> 'datetime',
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        ));
        $this->addColumn('end_dt', array(
        		'header'    => Mage::helper('hearedfrom')->__('End Date'),
        		'header_css_class'=>'a-center',
        		'align'     =>'center',
        		'width'     => '50px',
        		'index'     => 'end_dt',
        		'type'		=> 'datetime',
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        ));
        
        //Add exort options on admin panel
        $this->addExportType('*/*/exportCsv', Mage::helper('hearedfrom')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('hearedfrom')->__('Excel XML'));
		
        return parent::_prepareColumns();
    } 
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}