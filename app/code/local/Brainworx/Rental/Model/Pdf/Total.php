<?php
/**
 * Custom override of Sales Order Total PDF model
 *
 * @package    Brainworx_Rental
 * @author     Stijn Heylen
 */
class Brainworx_Rental_Model_Pdf_Total extends Mage_Tax_Model_Sales_Pdf_Grandtotal
{
    

    /**
     * Get array of arrays with tax information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getFullTaxInfo()
    {
        $fontSize       = $this->getFontSize() ? $this->getFontSize() : 7;
        $taxClassAmount = $this->_getCalculatedTaxes();

        if (!empty($taxClassAmount)) {
            foreach ($taxClassAmount as &$tax) {
            	//SHE updated round percent
                $percent          = $tax['percent'] ? ' (' . round($tax['percent'],1). '%)' : '';
                $tax['amount']    = $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($tax['tax_amount']);
                $tax['label']     = $this->_getTaxHelper()->__($tax['title']) . $percent . ':';
                $tax['font_size'] = $fontSize;
            }
        } else {
            $fullInfo = $this->_getFullRateInfo();
            $tax_info = array();

            if ($fullInfo) {
                foreach ($fullInfo as $info) {
                    if (isset($info['hidden']) && $info['hidden']) {
                        continue;
                    }

                    $_amount = $info['amount'];

                    foreach ($info['rates'] as $rate) {
                    	//SHE updated round percent
                        $percent = $rate['percent'] ? ' (' . round($rate['percent'],1). '%)' : '';

                        $tax_info[] = array(
                            'amount'    => $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($_amount),
                            'label'     => $this->_getTaxHelper()->__($rate['title']) . $percent . ':',
                            'font_size' => $fontSize
                        );
                    }
                }
            }
            $taxClassAmount = $tax_info;
        }

        return $taxClassAmount;
    }
}
