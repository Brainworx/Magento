<?php
/**
override for add comment -- incasso ticket 297
 */

/**
 * Adminhtml sales order edit controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once 'Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php';
class Brainworx_Hearedfrom_Sales_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController
{
    

    public function addCommentAction()
    {
        try {
            $this->getRequest()->setParam('invoice_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])&& empty($data['incasso'])) {
                Mage::throwException($this->__('The Comment Text field cannot be empty.'));
            }
            $invoice = $this->_initInvoice();
            $comment = "";
            if(isset($data['incasso'])&&$data['incasso']==1){
            	$invoice->setIncasso($data['incasso']);
            	if($invoice->getState()==Mage_Sales_Model_Order_Invoice::STATE_OPEN){
            		$invoice->setState(Brainworx_Rental_Model_Order_Invoice::STATE_INCASSO);
            		$comment = $this->__('Incasso actief.');
            	}else{
            		$comment = $this->__('Incasso actief maar status niet wachtende.');
            	}
            }else{
            	$invoice->setIncasso(false);
            	if($invoice->getState()==Brainworx_Rental_Model_Order_Invoice::STATE_INCASSO){
            		$invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_OPEN);
            		$comment = $this->__('Incasso gedeactiveerd.');
            	}
            }
            if (!empty($data['comment'])){
	            $invoice->addComment(
	                $comment." ".$data['comment'],
	                isset($data['is_customer_notified']),
	                isset($data['is_visible_on_front'])
	            );
            }
            
            $invoice->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
            $invoice->save();

            $this->loadLayout();
            $response = $this->getLayout()->getBlock('invoice_comments')->toHtml();
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage()
            );
            $response = Mage::helper('core')->jsonEncode($response);
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot add new comment.')
            );
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }




}
