<?php
	class Aramex_Core_Helper_Data
	{
		public function __(){}
		public function getEmails($configPath,$storeId)
		{
			$data = Mage::getStoreConfig($configPath,$storeId);
			if (!empty($data)) {
				return explode(',', $data);
			}
			return false;
		}		 
	}