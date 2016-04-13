<?php
class Sandipan_Testimonialmanager_Block_Adminhtml_Testimonialmanager_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('testimonialGrid');
        $this->setDefaultSort('testimonial_position');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $this->setCollection(Mage::getModel('testimonialmanager/testimonialmanager')->getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('testimonial_position', array(
            'header'    => Mage::helper('testimonialmanager')->__('Position'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'testimonial_position',
            'type'      => 'number',
        ));

        $this->addColumn('testimonial_name', array(
            'header'    => Mage::helper('testimonialmanager')->__('Name'),
            'align'     => 'left',
			'width'     => '200px',
            'index'     => 'testimonial_name',
        ));

        $this->addColumn('testimonial_text', array(
            'header'    => Mage::helper('testimonialmanager')->__('Text'),
            'align'     => 'left',
            'index'     => 'testimonial_text',
        ));

      $this->addColumn('rating_summary', array(
          'header'    => Mage::helper('testimonialmanager')->__('Rating'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'rating_summary',
          'type'      => 'options',
          'options'   => array(
              1 => '1 star',
              2 => '2 stars',
              3 => '3 stars',
              4 => '4 stars',
              5 => '5 stars',
          ),
		  'renderer'  => 'testimonialmanager/adminhtml_rating_renderer_rating',
      ));
		
      $this->addColumn('testimonial_sidebar', array(
          'header'    => Mage::helper('testimonialmanager')->__('Display in Sidebar'),
          'align'     => 'left',
          'width'     => '100px',
          'index'     => 'testimonial_sidebar',
          'type'      => 'options',
          'options'   => array(
              1 => 'Yes',
              2 => 'No',
          ),
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('testimonialmanager')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Not Approved',
              2 => 'Approved',
              3 => 'Pending',
          ),
      ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('testimonialmanager')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('testimonialmanager')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('testimonialmanager')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('testimonialmanager')->__('XML'));
	  
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('testimonial_id');
        $this->getMassactionBlock()->setFormFieldName('testimonial');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('testimonialmanager')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('testimonialmanager')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('testimonialmanager/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('testimonialmanager')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('testimonialmanager')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
 
 
         $sideBarstatuses = Mage::getSingleton('testimonialmanager/sidebarstatus')->getOptionArray();

        array_unshift($sideBarstatuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('testimonial_sidebar', array(
             'label'=> Mage::helper('testimonialmanager')->__('Change sidebar status'),
             'url'  => $this->getUrl('*/*/massSidebarstatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'testimonial_sidebar',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('testimonialmanager')->__('Status'),
                         'values' => $sideBarstatuses
                     )
             )
        ));

       return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
