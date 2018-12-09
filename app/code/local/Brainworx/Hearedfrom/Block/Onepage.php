<?php

class Brainworx_Hearedfrom_Block_Onepage extends Mage_Checkout_Block_Onepage
{
    public function getSteps()
    {
        $steps = array();

        if (!$this->isCustomerLoggedIn()) {
            $steps['login'] = $this->getCheckout()->getStepData('login');
        }

        $stepCodes = array('patient','billing', 'shipping_method', 'shipping', 'review');

        foreach ($stepCodes as $step) {
            $steps[$step] = $this->getCheckout()->getStepData($step);
        }
        
        return $steps;
    }

    /**
     * Get active step
     *
     * @return string
     */
    public function getActiveStep()
    {
    	return $this->isCustomerLoggedIn() ? 'patient' : 'login';
    }
}