<?php
/**
 * PHP Merchant
 *
 * Copyright (C) 2009 Ramon Torres
 *
 * This Software is released under the MIT License.
 * See license.txt for more details.
 *
 * @author Ramon Torres
 * @copyright Copyright (c) 2009 Ramon Torres
 * @license http://www.opensource.org/licenses/mit-license.php
 * @version $Id$
 */

/**
 * PHPMerchant_Billing_Gateway
 * 
 * @package PHPMerchant
 * @author Ramon Torres
 * @copyright 2009 Ramon Torres
 * @license http://www.opensource.org/licenses/mit-license.php
 * @access public
 */
abstract class PHPMerchant_Billing_Gateway {

	/**
	 * Stores the configuration
	 *
	 * @var array
	 */
	protected $_options = array(
		'test' => false
	);

	abstract public function purchase($money, PHPMerchant_Billing_CreditCard $creditcard, $options = array());
	abstract public function authorize($money, PHPMerchant_Billing_CreditCard $creditcard, $options = array());
	abstract public function capture($money, $authorization, $options = array());
	abstract public function void($identification, $options = array());
	abstract public function credit($money, $identification, $options = array());

	/**
	 * Constructor
	 *
	 * @param array $options 
	 */
	public function __construct($options = array()) {
		$this->_options = array_merge($this->_options, $options);
	}

	/**
	 * Returns true if the transaction is a test
	 *
	 * @return boolean
	 */
	public function isTest() {
		return $this->_options['test'];
	}

	/**
	 * undocumented function
	 *
	 * @param string $options 
	 * @param string $required 
	 * @return void
	 * @throws PHPMerchant_Gateway_Exception
	 */
	protected static function requires($options, $required) {
		if (!is_array($required)) {
			$required = array($required);
		}

		foreach ($required as $k) {
			if (!isset($options[$k])) {
				require_once 'Gateway/Exception.php';
				throw new PHPMerchant_Gateway_Exception(sprintf(
					"Required option '%s' is missing", $k
				));
			}
		}
	}
}
