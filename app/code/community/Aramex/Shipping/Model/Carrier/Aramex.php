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
 * @category    Mage
 * @package     Mage_Usa
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Aramex_Shipping_Model_Carrier_Aramex extends Mage_Usa_Model_Shipping_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code 				= 'aramex';
    protected $_request 			= null;
    protected $_result 				= null;
    protected $_defaultGatewayUrl 	= null;
	
	function __construct()
	{
		$this->_defaultGatewayUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'wsdl/Tracking.wsdl';
	}
	
	protected function _doShipmentRequest(Varien_Object $request)
	{
		return $result;
	}
	
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag($this->_activeFlag)) {
            return false;
        }

        $this->setRequest($request);

        $this->_result = $this->_getQuotes();

        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }
	
    public function setRequest(Mage_Shipping_Model_Rate_Request $request) {
        $this->_request = $request;

        $r = new Varien_Object();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        } else {
            $r->setService('ALL');
        }

        if ($request->getAramexUserid()) {
            $userId = $request->getAramexUserid();
        } else {
            $userId = $this->getConfigData('userid');
        }
        $r->setUserId($userId);

        if ($request->getAramexContainer()) {
            $container = $request->getAramexContainer();
        } else {
            $container = $this->getConfigData('container');
        }
        $r->setContainer($container);

        if ($request->getAramexSize()) {
            $size = $request->getAramexSize();
        } else {
            $size = $this->getConfigData('size');
        }
        $r->setSize($size);

        if ($request->getAramexMachinable()) {
            $machinable = $request->getAramexMachinable();
        } else {
            $machinable = $this->getConfigData('machinable');
        }
        $r->setMachinable($machinable);

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig('shipping/origin/postcode'));
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        $r->setDestCountryId($destCountry);


        $countries = Mage::getResourceModel('directory/country_collection')
                        ->addCountryIdFilter($destCountry)
                        ->load()
                        ->getItems();
        $country = array_shift($countries);
        $countryName = $country->getName();

        $r->setDestCountryName($countryName);

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
        $r->setWeightPounds(floor($weight));
        $r->setWeightOunces(round(($weight - floor($weight)) * 16, 1));
        if ($request->getFreeMethodWeight() != $request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $r->setValue($request->getPackageValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

        $this->_rawRequest = $r;

        return $this;
    }
	
    public function getCode($type, $code='') {
        return false;
    }
	
    protected function _getQuotes() {
        return false;
    }
	
    public function getResult() {
        return $this->_result;
    }
	
    protected function _setFreeMethodRequest($freeMethod) {
        $r = $this->_rawRequest;

        $weight = $this->getTotalNumOfBoxes($r->getFreeMethodWeight());
        $r->setWeightPounds(floor($weight));
        $r->setWeightOunces(round(($weight - floor($weight)) * 16, 1));
        $r->setService($freeMethod);
    }
	
    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }
        return $arr;
    }
	
    /**
     * Return array of authenticated information
     *
     * @return array
     */
    protected function _getAuthDetails()
    {
		return array(
						'ClientInfo'  			=> array(
												'AccountCountryCode'	=> Mage::getStoreConfig('aramexsettings/settings/account_country_code'),
												'AccountEntity'		 	=> Mage::getStoreConfig('aramexsettings/settings/account_entity'),
												'AccountNumber'		 	=> Mage::getStoreConfig('aramexsettings/settings/account_number'),
												'AccountPin'		 	=> Mage::getStoreConfig('aramexsettings/settings/account_pin'),
												'UserName'			 	=> Mage::getStoreConfig('aramexsettings/settings/user_name'),
												'Password'			 	=> Mage::getStoreConfig('aramexsettings/settings/password'),
												'Version'			 	=> 'v1.0'
												)
					);
    }

    public function getTracking($trackings) {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }

        $this->_getXmlTracking($trackings);

        return $this->_result;
    }

    protected function setTrackingReqeust() {
        $r = new Varien_Object();

        $userId = $this->getConfigData('userid');
        $r->setUserId($userId);

        $this->_rawTrackRequest = $r;
    }

    protected function _getXmlTracking($trackings) {
        $r = $this->_rawTrackRequest;

        foreach ($trackings as $tracking) {
            $this->_parseXmlTrackingResponse($tracking);
        }
    }

    protected function _parseXmlTrackingResponse($trackingvalue) {
        $resultArr = array();

        if (!$this->_result) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }
        $defaults = $this->getDefaults();

        //$url = 'http://localhost:8080/soap_test/wsdl/Tracking.wsdl';
        //if (!$url) {
        $url = $this->_defaultGatewayUrl;
        //}
		
        $clientAramex = new SoapClient($url);
		$aramexParams = $this->_getAuthDetails();

		$aramexParams['Transaction'] 	= array('Reference1' => '001' );
		$aramexParams['Shipments'] 		= array($trackingvalue);

        $_resAramex = $clientAramex->TrackShipments($aramexParams);
	
		if(is_object($_resAramex) && !$_resAramex->HasErrors){
				$tracking = Mage::getModel('shipping/tracking_result_status');
				$tracking->setCarrier('aramex');
				$tracking->setCarrierTitle($this->getConfigData('title'));
				$tracking->setTracking($trackingvalue);
				if(!empty($_resAramex->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult)){
					$tracking->setTrackSummary($this->getTrackingInfoTable($_resAramex->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult));
				} else {
					$tracking->setTrackSummary('Unable to retrieve quotes, please check if the Tracking Number is valid or contact your administrator.');
				}
				$this->_result->append($tracking);
		} else {
				$errorMessage = '';
				foreach($_resAramex->Notifications as $notification){
					$errorMessage .= '<b>' . $notification->Code . '</b>' . $notification->Message;
				}
				$error = Mage::getModel('shipping/tracking_result_error');
				$error->setCarrier('aramex');
				$error->setCarrierTitle($this->getConfigData('title'));
				$error->setTracking($trackingvalue);
				$error->setErrorMessage($errorMessage);
				$this->_result->append($error);			
		}
    }

    public function getResponse() {
        $statuses = '';
        if ($this->_result instanceof Mage_Shipping_Model_Tracking_Result) {
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking) {
                    if ($data = $tracking->getAllData()) {
                        if (!empty($data['track_summary'])) {
                            $statuses .= Mage::helper('usa')->__($data['track_summary']);
                        } else {
                            $statuses .= Mage::helper('usa')->__('Empty response');
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = Mage::helper('usa')->__('Empty response');
        }
        return $statuses;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getTrackingInfoTable($HAWBHistory) {

        $_resultTable = '<table summary="Item Tracking"  class="data-table">';
        $_resultTable .= '<col width="1">
                          <col width="1">
                          <col width="1">
                          <col width="1">
                          <thead>
                          <tr class="first last">
                          <th>Location</th>
                          <th>Action Date/Time</th>
                          <th class="a-right">Tracking Description</th>
                          <th class="a-center">Comments</th>
                          </tr>
                          </thead><tbody>';

        foreach ($HAWBHistory as $HAWBUpdate) {

            $_resultTable .= '<tr>
                <td>' . $HAWBUpdate->UpdateLocation . '</td>
                <td>' . $HAWBUpdate->UpdateDateTime . '</td>
                <td>' . $HAWBUpdate->UpdateDescription . '</td>
                <td>' . $HAWBUpdate->Comments . '</td>
                </tr>';
        }
        $_resultTable .= '</tbody></table>';

        return $_resultTable;
    }
}

?>