<?php
class Brainworx_Rental_Model_SimpleOutput
{
public function basicText()
{
	$todayStartOfDayDate  = Mage::app()->getLocale()->date()
	->setTime('00:00:00')
	->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    echo $todayStartOfDayDate + 'this is some text from the simple output model inside the basic text function';
}
}