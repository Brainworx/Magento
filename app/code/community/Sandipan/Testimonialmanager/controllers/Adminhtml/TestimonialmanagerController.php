<?php
class Sandipan_Testimonialmanager_Adminhtml_TestimonialmanagerController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout();
		$this->_setActiveMenu('testimonialmanager/testimonialmanagerbackend');
		$this->_addBreadcrumb(Mage::helper('testimonialmanager')->__('Testimonials'), Mage::helper('testimonialmanager')->__('Testimonials'));
	}

    /**
     * View grid action
     */
	public function indexAction()
	{
		$this->_initAction();
		$this->renderLayout();
	}

    /**
     * View edit form action
     */
	public function editAction()
	{
		$this->_initAction();
		$this->_addContent($this->getLayout()->createBlock('testimonialmanager/adminhtml_testimonialmanager_edit'));
		$this->renderLayout();
	}

    /**
     * View new form action
     */
	public function newAction()
	{
		$this->editAction();
	}

    /**
     * Save form action
     */
	public function saveAction()
	{
		if ($this->getRequest()->getPost()) {
			try {
				$data = $this->getRequest()->getPost();
				if (isset($_FILES['testimonial_img']['name']) and (file_exists($_FILES['testimonial_img']['tmp_name']))) {
					$uploader = new Varien_File_Uploader('testimonial_img');
					$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					$uploader->setFilesDispersion(false);
					$path = Mage::getBaseDir('media') . DS . 'testimonial_user' . DS ;
					$uploader->save($path, $_FILES['testimonial_img']['name']);
					$data['testimonial_img'] = 'testimonial_user/' . $_FILES['testimonial_img']['name'];
				} else {
					if(isset($data['testimonial_img']['delete']) && $data['testimonial_img']['delete'] == 1) {
						$data['testimonial_img'] = '';
					} else {
						unset($data['testimonial_img']);
					}
				}

				$model = Mage::getModel('testimonialmanager/testimonialmanager');
				$model->setData($data)->setTestimonialId($this->getRequest()->getParam('id'))->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('testimonialmanager')->__('Testimonial was successfully saved'));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}

		$this->_redirect('*/*/');
	}

    /**
     * Delete action
     */
	public function deleteAction()
	{
		if ($this->getRequest()->getParam('id') > 0) {
			try {
				$model = Mage::getModel('testimonialmanager/testimonialmanager');
				$model->setTestimonialId($this->getRequest()->getParam('id'))
				      ->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('testimonialmanager')->__('Testimonial was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}

		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $testimonialIds = $this->getRequest()->getParam('testimonial');
        if(!is_array($testimonialIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select testimonial(s)'));
        } else {
            try {
                foreach ($testimonialIds as $testimonialId) {
                    $testimonial = Mage::getModel('testimonialmanager/testimonialmanager')->load($testimonialId);
                    $testimonial->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($testimonialIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $testimonialIds = $this->getRequest()->getParam('testimonial');
        if(!is_array($testimonialIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select testimonial(s)'));
        } else {
            try {
                foreach ($testimonialIds as $testimonialId) {
                    $testimonial = Mage::getSingleton('testimonialmanager/testimonialmanager')
                        ->load($testimonialId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($testimonialIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massSidebarstatusAction()
    {
        $testimonialIds = $this->getRequest()->getParam('testimonial');
        if(!is_array($testimonialIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select testimonial(s)'));
        } else {
            try {
                foreach ($testimonialIds as $testimonialId) {
                    $testimonial = Mage::getSingleton('testimonialmanager/testimonialmanager')
                        ->load($testimonialId)
                        ->setTestimonialSidebar($this->getRequest()->getParam('testimonial_sidebar'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($testimonialIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'testimonial.csv';
        $content    = $this->getLayout()->createBlock('testimonialmanager/adminhtml_testimonialmanager_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'testimonial.xml';
        $content    = $this->getLayout()->createBlock('testimonialmanager/adminhtml_testimonialmanager_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('testimonialmanager/testimonialmanagerbackend');
	}

}