<?php
 
class Brainworx_hearedfrom_Block_Adminhtml_Commissionview_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('commissionview_grid');
    }
 
    /**
     * Prepare data to load in grid
     * (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Grid::_prepareCollection()
     */
    protected function _prepareCollection()
    {
    	try{
			// Get and set our collection for the grid
			$collection = Mage::getModel("hearedfrom/commissionView")->getCollection();
			$collection->setOrder('Zorgpunt', 'DESC');
			$collection->setOrder('jaar', 'DESC');
			$collection->setOrder('maand', 'DESC');
				
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
     * Setup Grid columns
     * (non-PHPdoc)
     * @see Mage_Adminhtml_Block_Widget_Grid::_prepareColumns()
     */
    protected function _prepareColumns()
    {    	
        $this->addColumn('Zorgpunt', array(
        		'header'    => Mage::helper('hearedfrom')->__('Zorgpunt'),
        		'align'     =>'left',
        		//'width'     => '50px',
        		'index'     => 'Zorgpunt',
        		//'filter'	=> true,
        		'type'		=> 'text',
        		'header_css_class'=>'a-left',
        ));
        
        $this->addColumn('jaar', array(
        		'header'    => Mage::helper('hearedfrom')->__('Jaar'),
        		'align'     =>'left',
        		'type'  => 'number',
         		'width' => '25px',
        		'index'     => 'jaar',
        		//'filter'	=> true,
        		'header_css_class'=>'a-left',
        ));
        $this->addColumn('maand', array(
        		'header'    => Mage::helper('hearedfrom')->__('Maand'),
        		'align'     =>'left',
        		'type'  => 'number',
         		'width' => '25px',
        		'index'     => 'maand',
        		//'filter'	=> true,
        		'header_css_class'=>'a-left',
        ));
        $this->addColumn('type', array(
        		'header'    => Mage::helper('hearedfrom')->__('Type'),
        		'align'     =>'center',
        		'width'     => '200px',
        		'index'     => 'type',
        		//'filter'	=> true,
        		'type'		=> 'text',
        		'header_css_class'=>'a-center',
        ));
        $this->addColumn('Ristorno', array(
        		'header'    => Mage::helper('hearedfrom')->__('Ristorno'),
        		'align'     =>'center',
        		'type'  => 'number',
//         		'width' => '25px',
        		'index'     => 'Ristorno',
        		//'filter'	=> false,
        		'header_css_class'=>'a-center',
        ));
        
        //Add exort options on admin panel
        $this->addExportType('*/*/exportCsv', Mage::helper('hearedfrom')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('hearedfrom')->__('Excel XML'));
		
        return parent::_prepareColumns();
    }    
}