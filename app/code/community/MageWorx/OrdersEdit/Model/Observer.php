<?php
/**
 * MageWorx
 * Admin Order Editor extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersEdit
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_OrdersEdit_Model_Observer
{

    /** Before edit order set old price
     *
     * @param $observer
     * @return $this
     */
    public function convertOrderItemToQuoteItem($observer)
    {
        $helper = $this->getMwHelper();
        if (!$helper->isEnabled()) {
            return $this;
        }

        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        $quoteItem = $observer->getEvent()->getQuoteItem();

        // fix for magento 1620-1700:
        $shippingAddress = $quoteItem->getQuote()->getShippingAddress();
        if ($shippingAddress) {
            $shippingAddress->setSameAsBilling(0);
        }

        // KeepPurchasePrice
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        $orderItem = $observer->getEvent()->getOrderItem();
        $storeId = $orderItem->getOrder()->getStoreId();
        $store = Mage::app()->getStore($storeId);


        $oldQuoteItemId = $orderItem->getQuoteItemId();

        $oldPrice = $orderItem->getPrice();
        if (Mage::helper('tax')->priceIncludesTax($store)) {
            $oldPrice = $orderItem->getOriginalPrice();
        }

        /** @var Mage_Core_Model_Resource $coreResource */
        $coreResource = Mage::getSingleton('core/resource');
        $read = $coreResource->getConnection('core_read');

        if ($orderItem->getProductType() != 'bundle' && $oldQuoteItemId > 0) {
            $select = $read->select()
                ->from($coreResource->getTableName('sales_flat_quote_item'), 'original_custom_price')
                ->where('item_id = ?', $oldQuoteItemId);
            $originalCustomPrice = $read->fetchOne($select);
            if ($originalCustomPrice) {
                $oldPrice = $originalCustomPrice;
            }
        }

        if ($orderItem->getProductType() == 'configurable') {
            $productId = $orderItem->getProductId();
            $itemPrice = $quoteItem->getParentItem()->getProduct()->getPriceModel()->getFinalPrice(1, $quoteItem->getParentItem()->getProduct());
            $items = $quoteItem->getQuote()->getItemsCollection();
            foreach ($items as $item) {
                if ($item->getProduct()->getId() == $productId && !$item->getApplyPriceFlag()) {
                    if ($oldPrice != $itemPrice) {
                        $item->setCustomPrice($oldPrice)->setOriginalCustomPrice($oldPrice);
                    }
                    $item->setApplyPriceFlag(true); // mark item
                }
            }
            return $this;
        } elseif ($orderItem->getProductType() == 'bundle') {
            // prepare bundle old price
            if (!$oldQuoteItemId) {
                return $this;
            }
            if ($quoteItem->getParentItem()) $quoteItem = $quoteItem->getParentItem();
            $select = $read->select()
                ->from($coreResource->getTableName('sales_flat_quote_item'), array('product_id', 'price', 'original_custom_price', 'price_incl_tax'))
                ->where('parent_item_id = ?', $oldQuoteItemId);
            $children = $read->fetchAll($select);
            if (!$children) {
                return $this;
            }
            $orderChildren = array();
            foreach ($children as $child) {
                $orderChildren[$child['product_id']] = $child;
            }

            // foreach all children and apply old price
            $children = $quoteItem->getChildren();
            if (!$children) {
                return $this;
            }
            foreach ($children as $child) {
                if (isset($orderChildren[$child->getProductId()])) {
                    $orderChild = $orderChildren[$child->getProductId()];
                    if (Mage::helper('tax')->priceIncludesTax($store)) {
                        $oldPrice = $orderChild['price_incl_tax'];
                    } else {
                        $oldPrice = $orderChild['price'];
                    }
                    $oldPrice = $orderChild['original_custom_price'] ? $orderChild['original_custom_price'] : $oldPrice;
                    if ($oldPrice != $child->getProduct()->getFinalPrice()) {
                        $child->setCustomPrice($oldPrice)->setOriginalCustomPrice($oldPrice);
                    }
                }
            }
            return $this;
        }

        // simple
        if ($oldPrice != $quoteItem->getProduct()->getFinalPrice()) {
            $quoteItem->setCustomPrice($oldPrice)->setOriginalCustomPrice($oldPrice);
        }

    }

    /** Before edit order collectShippingRates
     *
     * @param $observer
     * @return $this
     */
    public function convertOrderToQuote($observer)
    {
//         $helper = $this->getMwHelper();
//         if (!$helper->isEnabled()) {
//             return $this;
//         }

//         /** @var Mage_Sales_Model_Order $order */
//         $order = $observer->getEvent()->getOrder();
//         /** @var Mage_Sales_Model_Quote $quote */
//         $quote = $observer->getEvent()->getQuote();

//         $billing = $order->getBillingAddress();
//         $shipping = $order->getShippingAddress();

//         // set same_as_billing = yes/no
//         if ($shipping) {
//             if ($billing->getFirstname() == $shipping->getFirstname()
//                 && $billing->getMiddlename() == $shipping->getMiddlename()
//                 && $billing->getSuffix() == $shipping->getSuffix()
//                 && $billing->getCompany() == $shipping->getCompany()
//                 && $billing->getStreet() == $shipping->getStreet()
//                 && $billing->getCity() == $shipping->getCity()
//                 && $billing->getRegion() == $shipping->getRegion()
//                 && $billing->getRegionId() == $shipping->getRegionId()
//                 && $billing->getPostcode() == $shipping->getPostcode()
//                 && $billing->getCountryId() == $shipping->getCountryId()
//                 && $billing->getTelephone() == $shipping->getTelephone()
//                 && $billing->getFax() == $shipping->getFax()
//             ) {
//                 $shipping->setSameAsBilling(1);
//                 Mage::getSingleton('adminhtml/sales_order_create')->getShippingAddress()->setSameAsBilling(1);
//             } else {
//                 Mage::getSingleton('adminhtml/sales_order_create')->setShippingAsBilling(0);
//             }
//         }

//         $store = Mage::getSingleton('adminhtml/session_quote')->getStore();
//         if (Mage::helper('tax')->shippingPriceIncludesTax($store)) {
//             $baseShippingAmount = $order->getBaseShippingInclTax();
//         } else {
//             $baseShippingAmount = $order->getBaseShippingAmount();
//         }
//         Mage::getSingleton('adminhtml/session_quote')->setBaseShippingCustomPrice($baseShippingAmount);

//         // for collectShippingRates
//         $quote->setTotalsCollectedFlag(false);
    }


    /**
     * @param $observer
     */
    public function orderCreateProcessData($observer)
    {
//         $request = $observer->getEvent()->getRequest();
//         if (isset($request['order']['shipping_price'])) {
//             $shippingPrice = $request['order']['shipping_price'];
//             if ($shippingPrice == 'null') {
//                 $shippingPrice = null;
//             } else {
//                 $shippingPrice = floatval($shippingPrice);
//             }
//             Mage::getSingleton('adminhtml/session_quote')->setBaseShippingCustomPrice($shippingPrice);
//         }
//         // if no cancel reset_shipping - recollectShippingRates
//         Mage::getSingleton('adminhtml/sales_order_create')->collectShippingRates();
    }

    /** Edit order set old coupone
     *
     * @param $observer
     * @return $this
     */
    public function quoteCollectTotalsAfter($observer)
    {
        if (!$this->getMwHelper()->isEnabled()) {
            return $this;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        // apply custom shipping price
//         if ($this->getMwHelper()->isShippingPriceEditEnabled() && Mage::app()->getStore()->isAdmin()) {
//             $address = $quote->getShippingAddress();
//             $baseShippingCustomPrice = Mage::getSingleton('adminhtml/session_quote')->getBaseShippingCustomPrice();
//             if ($address && !is_null($baseShippingCustomPrice)) {
//                 if ($address->getShippingMethod()) {

//                     $origBaseShippingInclTax = $address->getBaseShippingInclTax();
//                     $origShippingInclTax = $address->getShippingInclTax();

//                     $address->setBaseTotalAmount('shipping', $baseShippingCustomPrice);
//                     $shippingCustomPrice = $quote->getStore()->convertPrice($baseShippingCustomPrice);
//                     $address->setTotalAmount('shipping', $shippingCustomPrice);

//                     $creditModel = null;
//                     $address->setAppliedTaxesReset(false);

//                     foreach ($address->getTotalCollector()->getCollectors() as $code => $model) {
//                         // for calculate shipping tax
//                         if ($code == 'tax_shipping' || $code == 'tax') {
//                             $model->collect($address);
//                         }
//                         if ($code == 'customercredit') {
//                             $creditModel = $model;
//                         }
//                     }

//                     $address->setGrandTotal((float)$address->getGrandTotal() + ($address->getShippingInclTax() - $origShippingInclTax));
//                     $address->setBaseGrandTotal((float)$address->getBaseGrandTotal() + ($address->getBaseShippingInclTax() - $origBaseShippingInclTax));

//                     // for recollect customer credit and authorizenet in admin
//                     if ($creditModel && $address->getBaseCustomerCreditAmount() > 0) {
//                         $baseCreditLeft = $address->getBaseCustomerCreditAmount();
//                         $creditLeft = $address->getCustomerCreditAmount();
//                         $address->setBaseGrandTotal($address->getBaseGrandTotal() + $baseCreditLeft);
//                         $address->setGrandTotal($address->getGrandTotal() + $creditLeft);
//                         $creditModel->collect($address);
//                     }

//                 }
//             }
//         }
//         Mage::getSingleton('adminhtml/session_quote')->setBaseShippingCustomPrice(null);

        $quote = $this->applyFreeShippingCartRule($quote);

        // apply old coupon_code
        $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrderId();
        if (!$orderId) {
            return $this;
        }

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) {
            return $this;
        }
        if (!$order->getAppliedRuleIds()) {
            return $this;
        }

        if (!$quote->getCouponCode() && !Mage::getSingleton('adminhtml/session_quote')->getCouponCodeIsDeleted() && $order->getCouponCode()) {
            $quote->setCouponCode($order->getCouponCode());

            /** @var Mage_Sales_Model_Quote_Address $address */
            foreach ($quote->getAllAddresses() as $address) {
                $amount = $address->getDiscountAmount();
                if ($amount != 0) {
                    $description = $order->getDiscountDescription();
                    // WTF?!
                    if ($description) {
                        $title = Mage::helper('sales')->__('Discount (%s)', $description);
                    } else {
                        $title = Mage::helper('sales')->__('Discount');
                    }
                    $address->setCouponCode($order->getCouponCode())->setDiscountDescription($description);
                }
            }
        }

        return $this;
    }

    /** Add coupon block after order items block
     *  (for order view page)
     *
     * @param Varien_Event_Observer $observer
     */
    public function insertCouponBlock($observer)
    {

        /** @var Varien_Object $transport */
        $transport = $observer->getTransport();
        /** @var Mage_Core_Block_Abstract $block */
        $block = $observer->getBlock();
        if($block->getType() == 'adminhtml/sales_order_view_items' && $block->getNameInLayout() == 'order_items')
        {
            /** @var string $oldHtml */
            $oldHtml = $transport->getHtml();

            /** @var string $couponsBlockHtml */
            $couponsBlockHtml = Mage::getSingleton('core/layout')
                ->createBlock('mageworx_ordersedit/adminhtml_sales_order_coupons', 'coupons')
                ->toHtml();

            /** @var string $newHtml */
            $newHtml = $oldHtml. $couponsBlockHtml; // append coupon block html
            $transport->setHtml($newHtml);
        }

        return;
    }

    /** Reset all changes of the order from session on load of the order page
     *
     * @param Varien_Event_Observer $observer
     */
    public function resetSessionEditChanges($observer)
    {
        $request = $observer->getEvent()->getControllerAction()->getRequest()->getParams();
        if (isset($request['order_id'])) {
            $orderId = $request['order_id'];
            Mage::helper('mageworx_ordersedit/edit')->resetPendingChanges($orderId);
        }

        return;
    }

    /**
     * Save modified order tax information
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderAfterSave($observer)
    {
        //modified version of Mage_Tax_Model_Observer::salesEventOrderAfterSave()
        $order = $observer->getEvent()->getOrder();

        if ($order->getAppliedTaxIsSaved()) {
            return;
        }

        $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($order->getQuoteId());
        $address = $quote->getShippingAddress();
        $orderTaxes = Mage::getModel('tax/sales_order_tax')->getCollection()->loadByOrder($order);
        foreach ($orderTaxes->getItems() as $orderTax) {
            $orderTax->delete();
        }

        $getTaxesForItems   = $address->getQuote()->getTaxesForItems();
        $taxes              = $address->getAppliedTaxes();

        $ratesIdQuoteItemId = array();
        if (!is_array($getTaxesForItems)) {
            $getTaxesForItems = array();
        }
        foreach ($getTaxesForItems as $quoteItemId => $taxesArray) {
            foreach ($taxesArray as $rates) {
                if (count($rates['rates']) == 1) {
                    $ratesIdQuoteItemId[$rates['id']][] = array(
                        'id'        => $quoteItemId,
                        'percent'   => $rates['percent'],
                        'code'      => $rates['rates'][0]['code']
                    );
                } else {
                    $percentDelta   = $rates['percent'];
                    $percentSum     = 0;
                    foreach ($rates['rates'] as $rate) {
                        $ratesIdQuoteItemId[$rates['id']][] = array(
                            'id'        => $quoteItemId,
                            'percent'   => $rate['percent'],
                            'code'      => $rate['code']
                        );
                        $percentSum += $rate['percent'];
                    }

                    if ($percentDelta != $percentSum) {
                        $delta = $percentDelta - $percentSum;
                        foreach ($ratesIdQuoteItemId[$rates['id']] as &$rateTax) {
                            if ($rateTax['id'] == $quoteItemId) {
                                $rateTax['percent'] = (($rateTax['percent'] / $percentSum) * $delta)
                                    + $rateTax['percent'];
                            }
                        }
                    }
                }
            }
        }

        if (!is_array($taxes)) {
            $taxes = array();
        }
        foreach ($taxes as $id => $row) {
            foreach ($row['rates'] as $tax) {
                if (is_null($row['percent'])) {
                    $baseRealAmount = $row['base_amount'];
                } else {
                    if ($row['percent'] == 0 || $tax['percent'] == 0) {
                        continue;
                    }
                    $baseRealAmount = $row['base_amount'] / $row['percent'] * $tax['percent'];
                }
                $hidden = (isset($row['hidden']) ? $row['hidden'] : 0);
                $data = array(
                    'order_id'          => $order->getId(),
                    'code'              => $tax['code'],
                    'title'             => $tax['title'],
                    'hidden'            => $hidden,
                    'percent'           => $tax['percent'],
                    'priority'          => $tax['priority'],
                    'position'          => $tax['position'],
                    'amount'            => $row['amount'],
                    'base_amount'       => $row['base_amount'],
                    'process'           => $row['process'],
                    'base_real_amount'  => $baseRealAmount,
                );

                $result = Mage::getModel('tax/sales_order_tax')->setData($data)->save();

                if (isset($ratesIdQuoteItemId[$id])) {
                    foreach ($ratesIdQuoteItemId[$id] as $quoteItemId) {
                        if ($quoteItemId['code'] == $tax['code']) {
                            $item = $order->getItemByQuoteItemId($quoteItemId['id']);
                            if ($item) {
                                $data = array(
                                    'item_id'       => $item->getId(),
                                    'tax_id'        => $result->getTaxId(),
                                    'tax_percent'   => $quoteItemId['percent']
                                );
                                Mage::getModel('tax/sales_order_tax_item')->setData($data)->save();
                            }
                        }
                    }
                }
            }
        }

        $order->setAppliedTaxIsSaved(true);
    }

    /**
     * Save mageworx order status history
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderStatusHistoryAfterSave($observer)
    {
        $history = $observer->getEvent()->getStatusHistory();
        $order = $history->getOrder();

        if (!$history->getOrigData()) {
            $orderStatusHistory = Mage::getModel('mageworx_ordersedit/order_status_history');
            $user = Mage::getSingleton('admin/session')->getUser();
            if(is_object($user) && ($adminUserId = $user->getUserId())) {
                $histories = $order->getStatusHistoryCollection(true);
                $creator = $user->getUser();
                foreach ($histories as $historyItem) {
                    $orderStatusHistory->setData('history_id', $historyItem->getEntityId());
                    $orderStatusHistory->setData('creator_admin_user_id', $adminUserId);
                    if (is_object($creator)) {
                        $orderStatusHistory->setData('creator_firstname', $creator->getFirstname());
                        $orderStatusHistory->setData('creator_lastname', $creator->getLastname());
                        $orderStatusHistory->setData('creator_username', $creator->getUsername());
                    }
                    $orderStatusHistory->save();
                    break;
                }
            }
        }
    }

    /**
     * @return MageWorx_OrdersEdit_Helper_Data
     */
    protected function getMwHelper()
    {
        return Mage::helper('mageworx_ordersedit');
    }

    /** Check and apply free shipping shopping cart rule if needed
     * @return Mage_Sales_Model_Quote
     */
    protected function applyFreeShippingCartRule($quote)
    {
        //check if shipping block was edited
        if (!Mage::getSingleton('adminhtml/session')->getShippingEdited()) {
            $appliedRuleIds = explode(',', $quote->getAppliedRuleIds());
            $rules =  Mage::getModel('salesrule/rule')->getCollection()->addFieldToFilter('rule_id' , array('in' => $appliedRuleIds));
            foreach ($rules as $rule) {
                //check and apply free shipping shopping cart rule
//                 if($rule->getSimpleFreeShipping() == Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS) {
//                     $address = $quote->getShippingAddress();
//                     $address->setGrandTotal((float)$address->getGrandTotal() - ($address->getTotalAmount('shipping') + $address->getShippingTaxAmount()));
//                     $address->setBaseGrandTotal((float)$address->getBaseGrandTotal() - ($address->getBaseTotalAmount('shipping') + $address->getBaseShippingTaxAmount()));

//                     $address->setBaseTotalAmount('shipping', 0);
//                     $address->setTotalAmount('shipping', 0);
//                     $address->setBaseShippingInclTax(0);
//                     $address->setShippingInclTax(0);
//                     $address->setBaseShippingTaxAmount(0);
//                     $address->setShippingTaxAmount(0);
//                     break;
//                 }
            }
        }
        Mage::getSingleton('adminhtml/session')->setShippingEdited(false);
        return $quote;
    }
}