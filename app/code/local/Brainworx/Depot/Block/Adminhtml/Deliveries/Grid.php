<?php
 
class Brainworx_Depot_Block_Adminhtml_Deliveries_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('deliveries_grid');
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
    		$collection = Mage::getModel('sales/order_shipment_track')->getCollection();
    		$select = $collection->getSelect();
    		$resource = Mage::getSingleton('core/resource');
    		//use joinleft to show all shipments
    		$select->join(
    				array('shipment' => $resource->getTableName('sales/shipment_grid')),
    				'main_table.parent_id = shipment.entity_id',
    				array('created_at','shipment_id' => 'entity_id','shipping_name','order_increment_id','order_created_at','increment_id')
    		);
    		$select->join(array('order' => $resource->getTableName('sales/order')),
    				'shipment.order_id = order.entity_id',
    				array('shipping_address_id','comment_to_zorgpunt'));
    		$select->join(array('address' => $resource->getTableName('sales/order_address')),
    				'order.shipping_address_id = address.entity_id',
    				array('street','postcode','city','telephone'));
    		
    		
    		$this->setCollection($collection);
    		return parent::_prepareCollection();
    		
        }catch(Exception $e){
        	Mage::log("delivery grid:".$e->getMessage());
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
    	$this->addColumn('action',
    			array(
    					'header'    => Mage::helper('sales')->__('Action'),
    					'width'     => '50px',
    					'type'      => 'action',
    					'getter'     => 'getEntityId',
    					'actions'   => array(
    							array(
    									'caption' => Mage::helper('depot')->__('Edit'),
    									'url'     => array('base'=>'*/*/edit'),
    									'field'   => 'id'
    							)
    					),
    					'filter'    => false,
    					'sortable'  => false,
    					'is_system' => true
    			));
    	$this->addColumn('title', array(
    			'header'    => Mage::helper('depot')->__('Carrier'),
    			'index'     => 'title',
    			'filter_index'=>'carrier_code',
    			'type'      => 'options',
    			'options'	=>  Mage::getModel('depot/depot')->getCarriers(),
    			'width' => '35px',
    	));
    	$this->addColumn('entity_id', array(
    			'header'    => Mage::helper('depot')->__('Delivery_id'),
        		'header_css_class'=>'a-center',
    			'align' => 'center',
    			'index' => 'entity_id',
    			'width' => '15px',
    	));
    	$this->addColumn('delivery', array(
    			'header'    => Mage::helper('depot')->__('Delivery Date'),
    			'index'     => 'delivery',
    			'type'      => 'datetime',
    			'format' => 'dd-MM-yyyy HH:mm',
    			'width' => '25px',
    	));
    	$this->addColumn('track_number', array(
    			'header'    => Mage::helper('depot')->__('Tracking Number'),
    			'index' => 'track_number',
    			'width' => '15px',
    	));
    	$this->addColumn('delivered',array(
    			'header'    => Mage::helper('depot')->__('Delivered'),
        		'header_css_class'=>'a-center',
    			'align' => 'center',
    			'index' => 'delivered',
    			'type'	=> 'options',
    			'options' => array(1=>Mage::helper('depot')->__('Y'),0=>Mage::helper('depot')->__('N')),
    			'width' => '15px',
    	
    	));
    	$this->addColumn('shipping_name', array(
    			'header' => Mage::helper('sales')->__('Ship to Name'),
    			'index' => 'shipping_name',
    			'width' => '35px',
    	));
    	$this->addColumn('address', array(
    			'header'    => Mage::helper('depot')->__('Deliveryaddress'),
    			'index'        => array('street', 'postcode','city'),
    			'type'         => 'concat',
    			'separator'    => ' - ',
    			'filter_index' => "CONCAT(street, postcode,city)",
    			'width'        => '200px',
    	));
    	$this->addColumn('telephone', array(
    			'header' => Mage::helper('sales')->__('Telephone'),
    			'index' => 'telephone',
    			'width' => '25px',
    	));
    	
    	$this->addColumn('order_increment_id', array(
    			'header'    => Mage::helper('sales')->__('Order #'),
    			'index'     => 'order_increment_id',
    			'type'      => 'text',
    			'width' => '15px',
    	));
    	$this->addColumn('order_created_at', array(
    			'header'    => Mage::helper('sales')->__('Order Date'),
    			'index'     => 'order_created_at',
    			'type'      => 'datetime',
    	));
    	$this->addColumn('increment_id', array(
    			'header'    => Mage::helper('sales')->__('Shipment #'),
    			'index'     => 'increment_id',
    			'type'      => 'text',
    			'width' => '15px',
    	));
    	$this->addColumn('created_at', array(
    			'header'    => Mage::helper('sales')->__('Date Shipped'),
    			'index'     => 'created_at',
    			'type'      => 'datetime',
    	));
    	$this->addColumn('comment_to_zorgpunt', array(
    			'header' => Mage::helper('depot')->__('Comment Customer'),
    			'index' => 'comment_to_zorgpunt',
    			'width' => '350px',
    	));
    	

    	$this->addExportType('*/*/exportPdf', Mage::helper('sales')->__('PDF'));
//     	$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
//     	$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));
		
        return parent::_prepareColumns();
    }   
    public function getPdfFile(){
    	$this->_isExport = true;
    	$this->_prepareGrid();
    	
    	$this->getCollection()->getSelect()->limit();
    	$this->getCollection()->setPageSize(0);
    	$this->getCollection()->load();
    	$this->_afterLoadCollection();
    
    	$pdf = new Zend_Pdf();
    	$page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
    	$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
    	$fontbold = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    	$page->setFont($font, 12);
    	$width = $page->getWidth();
    	$columns = array('Levering','Leverdatum','Address'); //,'Track nr.','Comment'   
    	$colwidth = array('Levering'=>50,'Leverdatum'=>100,'Address'=>100); 	
    	$tcolumns = array('entity_id','delivery','address'); //,'track_number','comment'
    	$collection = $this->getCollection();
    	$j = 60;
    	$o = 0;
    	$i = 40;
    	$itemheigth = 0;
    	$page->drawText(Mage::helper('depot')->__('Zorgpunt delivery note page ').(count($pdf->pages)+1), 200, $page->getHeight()-$j);
    	$j+=5;
    	$page->drawLine(0, $page->getHeight()-$j, $page->getWidth(), $page->getHeight()-$j);
    	$j+=2;
    	$page->drawLine(0, $page->getHeight()-$j, $page->getWidth(), $page->getHeight()-$j);
    	$j+=20;
    	foreach ($collection as $item) {
    		// 2 header + 4 standard lines per delivery
    		$itemheigth += 30 + 6 * 20 + 10 + 30;
    		//prepare wirte items : 40 lines per parcel
    		$shipments = Mage::getModel('sales/order_shipment')->load($item->getParentId())->getAllItems();
    		$itemheigth += count($shipments)*40;
    		if($itemheigth > $page->getHeight()-60){
    			$pdf->pages[] = $page; //push element on array
    			//new page
    			$itemheigth = 30 + 6 * 20 + 10 + 30 + (count($shipments)*40);
    			$j=60;
    			$page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
    			$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
    			$page->setFont($font, 12);
    			$page->drawText(Mage::helper('depot')->__('Zorgpunt delivery note page ').(count($pdf->pages)+1), 200, $page->getHeight()-$j);
    			$j+=5;
    			$page->drawLine(0, $page->getHeight()-$j, $page->getWidth(), $page->getHeight()-$j);
    			$j+=2;
    			$page->drawLine(0, $page->getHeight()-$j, $page->getWidth(), $page->getHeight()-$j);
    			$j+=20;
    			
    		}
    		$page->setFont($fontbold, 12);
    		
    		foreach ($columns as $header) {
    			$i+=10;
    			$page->drawText(Mage::helper('depot')->__($header), $i, $page->getHeight()-$j);
    			//$width = $font->widthForGlyph($font->glyphNumberForCharacter($header))*1.5;
    			$width = $colwidth[$header];
    			//$i+=($width/$font->getUnitsPerEm()*12)*strlen($header)+10;
    			$i+=$width;
    			//}
    		}
    		$page->setFont($font, 12);
    		$i = 40;  
    		$j += 20;  		
    		$y = $page->getHeight()-$j;    		
	    	foreach ($tcolumns as $cell) {
	    		//if (!$column->getIsSystem()) {
	    		$i+=10;
	    		//	$header = $column->getExportHeader();
	    		if($cell == 'address'){
	    			$order = Mage::getModel('sales/order')->load($item['order_id']);
	    			$text =  
	    				$order->getBillingAddress()->getFirstname().' '.
	 	    			$order->getBillingAddress()->getLastname().' | '. 
	 	    			$order->getBillingAddress()->getStreet()[0].', '.
	    			    $order->getBillingAddress()->getPostcode().' '. 
	    				$order->getBillingAddress()->getCity().' | '. 
	    				$order->getBillingAddress()->getTelephone();
	    		}else{
	    			$text = $item[$cell];
	    		}
	    		$header = $columns[$o++];
	    		$page->drawText($text, $i, $y);
	    		$width = $colwidth[$header];
	    		$i+=$width;
	    		//}
	    	}

	    	//reset counters to start at front and 1 line below
	    	$j+=30;
	    	$i = 60;
	    	$o = 0;
	    	//write carrier
	    	$page->drawText(Mage::helper('depot')->__('Carrier').' : '.$item['title'], $i, $page->getHeight()-$j);
	    	$j+=20;
	    	//write tracknumber
	    	$page->drawText(Mage::helper('depot')->__('Tracking Number').' : '.$item['track_number'], $i, $page->getHeight()-$j);
	    	$j+=30;
	    	
	    	$page->drawText(Mage::helper('depot')->__('Items').' : ', $i, $page->getHeight()-$j);
	    	$j+=20;
	    	//write delivery content
	    	foreach($shipments as $parcel){
	    		$page->drawText(Mage::helper('depot')->__('Item').': '.$parcel->getName(), $i+15, $page->getHeight()-$j);
	    		$j+=20;
	    		$page->drawText(Mage::helper('depot')->__('Qty').': '.$parcel->getQty(), $i+15, $page->getHeight()-$j);
	    		$j+=20;
	    	}
	    	$j+=10;
	    	//write comment
	    	$page->drawText(Mage::helper('depot')->__('Comment Customer').' : ', $i, $page->getHeight()-$j);
	    	
	    	$i+=100;
	    	if(!empty($item['comment_to_zorgpunt'])){
	    		$cmt = $item['comment_to_zorgpunt'];
	    		$pos = 0;
	    		while(strlen($cmt)>50){
	    			$scmt = substr($cmt,$pos,50);
	    			$pos=50;
	    			$cmt = substr($cmt,50);
	    			if(strlen($scmt)>=50){
	    			$page->drawText($scmt, $i, $page->getHeight()-$j);
	    			$j+=20;	
	    			}    			
	    		}
	    		$page->drawText($cmt, $i, $page->getHeight()-$j);
	    	}
	    	$i=60;
	    	
	    	$j+=20;
	    	//write comment
	    	$page->drawText(Mage::helper('depot')->__('Additional Comment').' : '.$item['comment'], $i, $page->getHeight()-$j);
	    	
	    	$j+= 10;
	    	$page->drawLine(0, $page->getHeight()-$j, $page->getWidth(), $page->getHeight()-$j);
	    	
	    	$j+=20;
	    	
	    	$i = 40;
	    	
    	}
    	$pdf->pages[] = $page;
    	return $pdf->render();
    }
    public function getRowUrl($row)
    {
         return $this->getUrl('*/*/edit', array('id' => $row->getEntityId()));
    }
}