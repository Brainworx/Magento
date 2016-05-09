<?php
/**
 * MageWorx
 * Admin Order Editor extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersEdit
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_OrdersEdit_Block_Adminhtml_Sales_Order_Totals extends Mage_Adminhtml_Block_Widget//Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    protected $_totals;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mageworx/ordersedit/totals.phtml');
    }

    /**
     * Format total value based on order currency
     *
     * @param   Varien_Object $total
     * @return  string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            return $this->helper('adminhtml/sales')->displayPrices(
                $this->getOrder(),
                $total->getBaseValue(),
                $total->getValue()
            );
        }
        return $total->getValue();
    }

    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        $this->_totals = array();
        $this->_totals['subtotal'] = new Varien_Object(array(
            'code'      => 'subtotal',
            'value'     => $this->getSource()->getSubtotal(),
            'base_value'=> $this->getSource()->getBaseSubtotal(),
            'label'     => $this->helper('sales')->__('Subtotal')
        ));

        /**
         * Add shipping
         */
        if (!$this->getSource()->getIsVirtual() && ((float) $this->getSource()->getShippingAmount() || $this->getSource()->getShippingDescription()))
        {
            $this->_totals['shipping'] = new Varien_Object(array(
                'code'      => 'shipping',
                'value'     => $this->getSource()->getShippingAmount(),
                'base_value'=> $this->getSource()->getBaseShippingAmount(),
                'label' => $this->helper('sales')->__('Shipping & Handling')
            ));
        }

        /**
         * Add discount
         */
        if (((float)$this->getSource()->getDiscountAmount()) != 0) {
            if ($this->getSource()->getDiscountDescription()) {
                $discountLabel = $this->helper('sales')->__('Discount (%s)', $this->getSource()->getDiscountDescription());
            } else {
                $discountLabel = $this->helper('sales')->__('Discount');
            }
            $this->_totals['discount'] = new Varien_Object(array(
                'code'      => 'discount',
                'value'     => $this->getSource()->getDiscountAmount(),
                'base_value'=> $this->getSource()->getBaseDiscountAmount(),
                'label'     => $discountLabel
            ));
        }

        $this->_totals['grand_total'] = new Varien_Object(array(
            'code'      => 'grand_total',
            'strong'    => true,
            'value'     => $this->getSource()->getGrandTotal(),
            'base_value'=> $this->getSource()->getBaseGrandTotal(),
            'label'     => $this->helper('sales')->__('Grand Total'),
            'area'      => 'footer'
        ));

        return $this;
    }

    /**
     * Get order totals
     * @return array
     */
    public function getTotals()
    {
        $totals = $this->getData('totals');

        //for shipping incl. tax on "New Totals" block
        if (Mage::helper('tax')->displayShippingPriceIncludingTax()) {
            $totals['shipping']->setValue($this->getSource()->getShippingAddress()->getShippingInclTax());
           // $totals['shipping']->setValue($this->getQuote()->getShippingAddress()->getShippingInclTax());      //SHE       
        }

        $order = $this->getOrder();
        //Brainworx: if rental, total in quote will be empty and we can use the ones form the order
        //causes: An error occured while saving the orderMaximum amount available to refund is € 0,00
        $quote = Mage::getModel('mageworx_ordersedit/edit')->getQuoteByOrder($order);
        //loop thru quote_items - if rentalitem, check sku, set q,
        foreach ($quote->getAllItems() as $quoteitem) {
        	if($quoteitem->getRentalitem()){
        		Mage::log('Rentalitem '.$quoteitem->getQuoteId());
	        	//print_r($item->getData()); //sales_quote_collect_totals_before collect totals reset alles + 
	        	foreach($order->getAllItems() as $orderitem){
	        		//if($orderitem->getProductId()==$quoteitem->getProductId()){ //kan ook op order->quote_item_id
	        		if($orderitem->getQuoteItemId()==$quoteitem->getItemId()){
	        			$quoteitem->setQty($orderitem->getQty());
	        			$quoteitem->setCustomPrice(null);
	        			$quoteitem->setPrice($orderitem->getPrice()); //needed?
	        			$quoteitem->setBasePrice($orderitem->getBasePrice());//needed?
	        			$quoteitem->setPriceInclTax($orderitem->getPriceInclTax());
	        			$quoteitem->setBasePriceInclTax($orderitem->getBasePriceInclTax());
	        			$quoteitem->setTaxAmount($orderitem->getTaxAmount());
	        			$quoteitem->setBaseTaxAmount($orderitem->getBaseTaxAmount());
	        			$quoteitem->setRowTotalInclTax($orderitem->getRowTotalInclTax());
	        			$quoteitem->setBaseRowTotalInclTax($orderitem->getBaseRowTotalInclTax());
	        			$quoteitem->setRowTotal($orderitem->getRowTotal());
	        			$quoteitem->setBaseRowTotal($orderitem->getBaseRowTotal());
	        			$quoteitem->save();
	        			break;
	        		}
	        	}
        	}
        }
        
        //update totals
         
        if($totals['subtotal']->getValue()==0||$totals['subtotal']->getValue()!=$quote->getSubtotal()){
        	Mage::log("Edit of rental order ".$order->getEntityId());
        	
        	$quoteShippingAddress = $quote->getShippingAddress();
        	//$quoteBillingAddress = $quote->getBillingAddress();
        	 try{
        	 	if(isset($totals['subtotal'])){
	        		$totals['subtotal']->setValue($order->getSubtotal());
        	 	}
        	 	if(isset($totals['grand_total'])){
	        		$totals['grand_total']->setValue($order->getGrandTotal());
        	 	}
        	 	if(isset($totals['tax'])){
	        		$totals['tax']->setValue($order->getTaxAmount());
        	 	}
        	  } catch (Exception $e) {
           			 Mage::log('Order edit Total.php An error occured while saving the order' . $e->getMessage());
        		}
        	
        	
        	$quote->setSubtotal($order->getSubtotal()); 
        	$quote->setBaseSubtotal($order->getSubtotal());
        	$quote->setTaxAmount($order->getTaxAmount());
        	$quote->setBaseTaxAmount($order->getBaseTaxAmount());
        	$quote->setBaseGrandTotal($order->getGrandTotal());
        	$quote->setGrandTotal($order->getGrandTotal());
        	$quote->setSubtotalInclTax($order->getSubtotalInclTax());
        	$quote->setBaseSubtotalInclTax($order->getBaseSubtotalInclTax());
        	$quote->save();
        	
        	$quoteShippingAddress->setSubtotal($order->getSubtotal());
        	$quoteShippingAddress->setBaseSubtotal($order->getSubtotal());
        	$quoteShippingAddress->setTaxAmount($order->getTaxAmount());
        	$quoteShippingAddress->setBaseTaxAmount($order->getBaseTaxAmount());
        	$quoteShippingAddress->setBaseGrandTotal($order->getGrandTotal());
        	$quoteShippingAddress->setGrandTotal($order->getGrandTotal());
        	$quoteShippingAddress->setSubtotalInclTax($order->getSubtotalInclTax());
        	$quoteShippingAddress->setBaseSubtotalInclTax($order->getBaseSubtotalInclTax());
        	$quoteShippingAddress->save();
        	 
        }
        
        //tax amount displayed is still not the one from quote
        	
        	//
        $rate = $order->getBaseToOrderRate();
        foreach ($totals as $total) {
            $base = $total->getValue() / $rate;
            $total->setData('base_value', $base);
        }

        return $totals;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getSource()
    {
        return $this->getQuote();
    }
}