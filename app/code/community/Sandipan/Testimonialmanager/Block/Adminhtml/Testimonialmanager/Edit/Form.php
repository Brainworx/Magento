<?php
class Sandipan_Testimonialmanager_Block_Adminhtml_Testimonialmanager_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('testimonial_form', array(
            'legend'	  => Mage::helper('testimonialmanager')->__('Testimonial'),
            )
        );

        $fieldset->addField('testimonial_name', 'text', array(
            'name'      => 'testimonial_name',
            'label'     => Mage::helper('testimonialmanager')->__('Contact Name'),
            'required'  => true,
        ));

        $fieldset->addField('testimonial_company', 'text', array(
            'name'      => 'testimonial_company',
            'label'     => Mage::helper('testimonialmanager')->__('Company'),
        ));

        $fieldset->addField('testimonial_email', 'text', array(
            'name'      => 'testimonial_email',
            'label'     => Mage::helper('testimonialmanager')->__('Email'),
            'required'  => true,
			'class'		=> ' validate-email',
        ));
		
        $fieldset->addField('testimonial_website', 'text', array(
            'name'      => 'testimonial_website',
            'label'     => Mage::helper('testimonialmanager')->__('Website URL'),
			'class'		=> 'validate-clean-url',
        ));

        $fieldset->addField('testimonial_img', 'image', array(
            'name'      => 'testimonial_img',
            'label'     => Mage::helper('testimonialmanager')->__('Image'),
        ));

        $fieldset->addField('summary_rating', 'note', array(
            'label'     => Mage::helper('review')->__('Summary Rating'),
            'text'      => $this->getLayout()->createBlock('testimonialmanager/adminhtml_rating_summary')->toHtml(),
        ));

        $fieldset->addField('detailed_rating', 'note', array(
            'label'     => Mage::helper('review')->__('Detailed Rating'),
            'text'      => '<div id="rating_detail">'
                           . $this->getLayout()->createBlock('testimonialmanager/adminhtml_rating_detailed')->toHtml()
                           . '</div>',
        ));

        $fieldset->addField('testimonial_text', 'editor', array(
            'name'      => 'testimonial_text',
            'label'     => Mage::helper('testimonialmanager')->__('Text'),
            'title'     => Mage::helper('testimonialmanager')->__('Text'),
            'style'     => 'width:100%;height:200px;',
            'required'  => true,
        ));

        $fieldset->addField('testimonial_position', 'text', array(
            'name'      => 'testimonial_position',
            'label'     => Mage::helper('testimonialmanager')->__('Sort Order / Position'),
        ));

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('testimonialmanager')->__('Status'),
            'name'      => 'status',
            'values'    => array(
                array(
                    'value'     => 3,
                    'label'     => Mage::helper('core')->__('Pending'),
                ),
                array(
                    'value'     => 2,
                    'label'     => Mage::helper('core')->__('Approved'),
                ),
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('core')->__('Not Approved'),
                ),
            ),
        ));

        $fieldset->addField('testimonial_sidebar', 'select', array(
            'label'     => Mage::helper('testimonialmanager')->__('Display in sidebox'),
            'name'      => 'testimonial_sidebar',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('core')->__('Yes'),
                ),
                array(
                    'value'     => 2,
                    'label'     => Mage::helper('core')->__('No'),
                ),
            ),
        ));

        if (Mage::registry('testimonialmanager')) {
            $form->setValues(Mage::registry('testimonialmanager')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}
