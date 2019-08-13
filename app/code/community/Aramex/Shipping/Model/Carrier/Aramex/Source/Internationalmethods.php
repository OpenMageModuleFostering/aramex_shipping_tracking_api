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
 * @package     Aramex_Shipment
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Aramex_Shipping_Model_Carrier_Aramex_Source_Internationalmethods
{
    public function toOptionArray()
    {        
        $arr[] = array('value'=>'DPX', 'label'=>'Value Express Parcels');
		$arr[] = array('value'=>'EDX', 'label'=>'Economy Document Express');
		$arr[] = array('value'=>'EPX', 'label'=>'Economy Parcel Express');
		$arr[] = array('value'=>'GDX', 'label'=>'Ground Document Express');
		$arr[] = array('value'=>'GPX', 'label'=>'Ground Parcel Express');
		
		$arr[] = array('value'=>'IBD', 'label'=>'International defered');
		$arr[] = array('value'=>'PDX', 'label'=>'Priority Document Express');
		$arr[] = array('value'=>'PLX', 'label'=>'Priority Letter Express (<.5 kg Docs)');
		$arr[] = array('value'=>'PPX', 'label'=>'Priority Parcel Express');
		
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
