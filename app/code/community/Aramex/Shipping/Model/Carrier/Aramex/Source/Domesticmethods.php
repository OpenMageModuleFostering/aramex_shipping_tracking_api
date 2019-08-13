<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Aramex
 * @package     Aramex_Shipping
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Aramex_Shipping_Model_Carrier_Aramex_Source_Domesticmethods
{
    public function toOptionArray()
    {        
        $arr[] = array('value'=>'BLK', 'label'=>'Special: Bulk Mail Delivery');
		$arr[] = array('value'=>'BLT', 'label'=>'Domestic - Bullet Delivery');	
		$arr[] = array('value'=>'CDA', 'label'=>'Special Delivery');
		$arr[] = array('value'=>'CDS', 'label'=>'Special: Credit Cards Delivery');
		$arr[] = array('value'=>'CGO', 'label'=>'Air Cargo (India)');
		
		$arr[] = array('value'=>'COM', 'label'=>'Special: Cheque Collection');
		$arr[] = array('value'=>'DEC', 'label'=>'Special: Invoice Delivery');
		$arr[] = array('value'=>'EMD', 'label'=>'Early Morning delivery');
		$arr[] = array('value'=>'FIX', 'label'=>'Special: Bank Branches Run');
		$arr[] = array('value'=>'LGS', 'label'=>'Logistic Shipment');
		
		$arr[] = array('value'=>'OND', 'label'=>'Overnight (Document)');
		$arr[] = array('value'=>'ONP', 'label'=>'Overnight (Parcel)');
		$arr[] = array('value'=>'P24', 'label'=>'Road Freight 24 hours service');
		$arr[] = array('value'=>'P48', 'label'=>'Road Freight 48 hours service');
		$arr[] = array('value'=>'PEC', 'label'=>'Economy Delivery');
		
		$arr[] = array('value'=>'PEX', 'label'=>'Road Express');
		$arr[] = array('value'=>'SFC', 'label'=>'Surface  Cargo (India)');
		$arr[] = array('value'=>'SMD', 'label'=>'Same Day (Document)');
		$arr[] = array('value'=>'SMP', 'label'=>'Same Day (Parcel)');
		$arr[] = array('value'=>'SPD', 'label'=>'Special: Legal Branches Mail Service');
		
		$arr[] = array('value'=>'SPL', 'label'=>'Special : Legal Notifications Delivery');
		
        return $arr;
    }
	
	public function toKeyArray(){
	    $result  = array();
		$options = $this->toOptionArray();
		foreach($options as $option){
			 $result[$option['value']] = $option['label'];
		}
		return $result;
	}
}
