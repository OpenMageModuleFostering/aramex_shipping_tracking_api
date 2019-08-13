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


class Aramex_Shipping_Model_Carrier_Aramex_Source_DomesticAdditionalServices
{
    public function toOptionArray()
    {        
        $arr[] = array('value'=>'AM10', 'label'=>'Morning delivery');
		$arr[] = array('value'=>'CHST', 'label'=>'Chain Stores Delivery');	
		$arr[] = array('value'=>'CODS', 'label'=>'Cash On Delivery Service');
		$arr[] = array('value'=>'COMM', 'label'=>'Commercial');
		$arr[] = array('value'=>'CRDT', 'label'=>'Credit Card');
		
		$arr[] = array('value'=>'DDP', 'label'=>'DDP - Delivery Duty Paid - For European Use');
		$arr[] = array('value'=>'DDU', 'label'=>'DDU - Delivery Duty Unpaid - For the European Freight');
		$arr[] = array('value'=>'EXW', 'label'=>'Not An Aramex Customer - For European Freight');
		$arr[] = array('value'=>'INSR', 'label'=>'Insurance');
		$arr[] = array('value'=>'RTRN', 'label'=>'Return');
		
		$arr[] = array('value'=>'SPCL', 'label'=>'Special Services');	
		
		
        return $arr;
    }
}
