<?php
 
class Brainworx_hearedfrom_Block_Adminhtml_Invoicesview_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('invoicesview_grid');
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
			$collection = Mage::getModel("hearedfrom/invoicesView")->getCollection();
			//$collection->setOrder('Zorgpunt', 'DESC');
				
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
        $this->addColumn('Id', array(
        		'header'    => Mage::helper('hearedfrom')->__('Id'),
        		'align'     =>'left',
        		'width'     => '10px',
        		'index'     => 'Id',
        		//'filter'	=> true,
        		'type'		=> 'number',
        		'header_css_class'=>'a-left',
        ));
        
        $this->addColumn('Factuurnr', array(
        		'header'    => Mage::helper('hearedfrom')->__('InvoiceId'),
        		'align'     =>'left',
        		'type'  => 'text',
         		'width' => '100px',
        		'index'     => 'Factuurnr',
        		'header_css_class'=>'a-left',
        ));
        $this->addColumn('Datum', array(
        		'header'    => Mage::helper('hearedfrom')->__('Datum'),
        		'align'     =>'left',
        		'type'  => 'datetime',
        		'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
         		'width' => '100px',
        		'index'     => 'Datum',
        		'header_css_class'=>'a-center',
        ));
        $this->addColumn('Klantnr', array(
        		'header'    => Mage::helper('hearedfrom')->__('Customernr'),
        		'align'     =>'center',
        		'width'     => '25px',
        		'index'     => 'Klantnr',
        		//'filter'	=> true,
        		'type'		=> 'number',
        		'header_css_class'=>'a-left',
        ));
        $this->addColumn('Voornaam', array(
        		'header'    => Mage::helper('hearedfrom')->__('First name'),
        		'align'     =>'center',
        		'type'  => 'text',
         		'width' => '150px',
        		'index'     => 'Voornaam',
        		'header_css_class'=>'a-left',
        ));
        $this->addColumn('Naam', array(
        		'header'    => Mage::helper('hearedfrom')->__('Naam'),
        		'align'     =>'center',
        		'type'  => 'text',
        		'width' => '150px',
        		'index'     => 'Naam',
        		'header_css_class'=>'a-left',
        ));
        $this->addColumn('Postcode', array(
        		'header'    => Mage::helper('hearedfrom')->__('Zipcode'),
        		'align'     =>'center',
        		'type'  => 'text',
        		'width' => '25px',
        		'index'     => 'Postcode',
        		'header_css_class'=>'a-left',
        ));
        $this->addColumn('Stad', array(
        		'header'    => Mage::helper('hearedfrom')->__('Stad'),
        		'align'     =>'center',
        		'type'  => 'text',
        		'width' => '150px',
        		'index'     => 'Stad',
        		'header_css_class'=>'a-left',
        ));
        $this->addColumn('Straat', array(
        		'header'    => Mage::helper('hearedfrom')->__('Street'),
        		'align'     =>'center',
        		'type'  => 'text',
        		'width' => '250px',
        		'index'     => 'Straat',
        		'header_css_class'=>'a-left',
        ));
        $this->addColumn('Landcode', array(
        		'header'    => Mage::helper('hearedfrom')->__('Country code'),
        		'align'     =>'center',
        		'type'  => 'text',
        		'width' => '25px',
        		'index'     => 'Landcode',
        		'header_css_class'=>'a-left',
        ));
        $this->addColumn('Btwnr', array(
        		'header'    => Mage::helper('hearedfrom')->__('Vatid'),
        		'align'     =>'center',
        		'type'  => 'text',
        		'width' => '25px',
        		'index'     => 'Btwnr',
        		'header_css_class'=>'a-left',
        ));        
        $this->addColumn('Subtotaal',array(
        		'header'=> Mage::helper('hearedfrom')->__('Subtotal'),
        		'header_css_class'=>'a-right',
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'Subtotaal',
        		'filter'	=> false,
        ));
        $this->addColumn('Subtotaal_Verhuur',array(
        		'header'=> Mage::helper('hearedfrom')->__('Subtotal_rental'),
        		'header_css_class'=>'a-right',
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'Subtotaal_Verhuur',
        		'filter'	=> false,
        ));
        $this->addColumn('Subtotaal_Verkoop',array(
        		'header'=> Mage::helper('hearedfrom')->__('Subtotal_sale'),
        		'header_css_class'=>'a-right',
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'Subtotaal_Verkoop',
        		'filter'	=> false,
        ));
        $this->addColumn('Totaal_Btw',array(
        		'header'=> Mage::helper('hearedfrom')->__('Total_vat'),
        		'header_css_class'=>'a-right',
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'Totaal_Btw',
        		'filter'	=> false,
        ));
        $this->addColumn('Btw6',array(
        		'header'=> Mage::helper('hearedfrom')->__('Vat_6%'),
        		'header_css_class'=>'a-right',
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'Btw6',
        		'filter'	=> false,
        ));
        $this->addColumn('Btw21',array(
        		'header'=> Mage::helper('hearedfrom')->__('Vat_21%'),
        		'header_css_class'=>'a-right',
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'Btw21',
        		'filter'	=> false,
        ));
        $this->addColumn('Verzendkosten',array(
        		'header'=> Mage::helper('hearedfrom')->__('Verzendkosten'),
        		'header_css_class'=>'a-right',
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'Verzendkosten',
        		'filter'	=> false,
        ));
        $this->addColumn('Totaal_incl_Btw',array(
        		'header'=> Mage::helper('hearedfrom')->__('Total_vat_included'),
        		'header_css_class'=>'a-right',
        		'type'  => 'number',
        		'width' => '25px',
        		'index' => 'Totaal_incl_Btw',
        		'filter'	=> false,
        ));
        $this->addColumn('ogm',array(
        		'header'=> Mage::helper('hearedfrom')->__('Ogm'),
        		'header_css_class'=>'a-left',
        		'type'  => 'text',
        		'width' => '75px',
        		'index' => 'ogm',
        		'filter'	=> false,
        ));
        
        //Add exort options on admin panel

        $this->addExportType('*/*/exportXml', Mage::helper('hearedfrom')->__('XML'));
        $this->addExportType('*/*/exportCsv', Mage::helper('hearedfrom')->__('CSV'));
		
        return parent::_prepareColumns();
    }    
    /**
     * Retrieve a file container array by grid data as MS Excel 2003 XML Document
     *
     * Return array with keys type and value
     *
     * @return string
     */
    public function getXmlFile($sheetName = '')
    {
    	$this->_isExport = true;
    	$this->_prepareGrid();
    
    	$io     = new Varien_Io_File();
    
    	$path = Mage::getBaseDir('var') . DS . 'export' . DS;
    	$name = md5(microtime());
    	$file = $path . DS . $name . '.xml';
    
    	$io->setAllowCreateFolders(true);
    	$io->open(array('path' => $path));
    	$io->streamOpen($file, 'w+');
    	$io->streamLock(true);
    	$io->streamWrite($this->getXml(Mage::helper('hearedfrom')->__('invoices')));
    	$io->streamUnlock();
    	$io->streamClose();
    
    	return array(
    			'type'  => 'filename',
    			'value' => $file,
    			'rm'    => true // can delete file after use
    	);
    }
    public function getXml($items='items')
    {
    	$this->_isExport = true;
    	$this->_prepareGrid();
    	$this->getCollection()->getSelect()->limit();
    	$this->getCollection()->setPageSize(0);
    	$this->getCollection()->load();
    	$this->_afterLoadCollection();
    	$indexes = array();
    	foreach ($this->_columns as $column) {
    		if (!$column->getIsSystem()) {
    			$indexes[] = $column->getIndex();
    		}
    	}
    	$xml = '<?xml version="1.0" encoding="UTF-8"?>';
    	$xml.= '<'.$items.'>';
    	foreach ($this->getCollection() as $item) {
    		$xml.= $item->toXml($indexes,Mage::helper('hearedfrom')->__('invoice'),false,false);
    	}
    	if ($this->getCountTotals())
    	{
    		$xml.= $this->getTotals()->toXml($indexes);
    	}
    	$xml.= '</'.$items.'>';
    	return $xml;
    }
}