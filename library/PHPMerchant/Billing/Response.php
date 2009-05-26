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
 * PHPMerchant_Billing_Response
 * 
 * @package PHPMerchant
 * @author Ramon Torres
 * @copyright 2009 Ramon Torres
 * @license http://www.opensource.org/licenses/mit-license.php
 * @access public
 */
class PHPMerchant_Billing_Response {

	public $test;
	public $success;
	public $message;
	public $authorization;

	public $params = array();
	public $cvvResult = array();
	public $avsResult = array();

	/**
	 * Descriptions for Address Verification Service (AVS) codes
	 *
	 * @link http://en.wikipedia.org/wiki/Address_Verification_System
	 * @var array
	 */
	protected static $_avsMessages = array(
		'A' => 'Street address matches, but 5-digit and 9-digit postal code do not match.',
		'B' => 'Street address matches, but postal code not verified.',
		'C' => 'Street address and postal code do not match.',
		'D' => 'Street address and postal code match.',
		'E' => 'AVS data is invalid or AVS is not allowed for this card type.',
		'F' => 'Card member’s name does not match, but billing postal code matches.',
		'G' => 'Non-U.S. issuing bank does not support AVS.',
		'H' => 'Card member’s name does not match. Street address and postal code match.',
		'I' => 'Address not verified.',
		'J' => 'Card member’s name, billing address, and postal code match. Shipping information verified and chargeback protection guaranteed through the Fraud Protection Program.',
		'K' => 'Card member’s name matches but billing address and billing postal code do not match.',
		'L' => 'Card member’s name and billing postal code match, but billing address does not match.',
		'M' => 'Street address and postal code match.',
		'N' => 'Street address and postal code do not match.',
		'O' => 'Card member’s name and billing address match, but billing postal code does not match.',
		'P' => 'Postal code matches, but street address not verified.',
		'Q' => 'Card member’s name, billing address, and postal code match. Shipping information verified but chargeback protection not guaranteed.',
		'R' => 'System unavailable.',
		'S' => 'U.S.-issuing bank does not support AVS.',
		'T' => 'Card member’s name does not match, but street address matches.',
		'U' => 'Address information unavailable.',
		'V' => 'Card member’s name, billing address, and billing postal code match.',
		'W' => 'Street address does not match, but 9-digit postal code matches.',
		'X' => 'Street address and 9-digit postal code match.',
		'Y' => 'Street address and 5-digit postal code match.',
		'Z' => 'Street address does not match, but 5-digit postal code matches.'
	);

	protected static $_cvvMessages = array(
		'D'  =>  'Suspicious transaction',
		'I'  =>  'Failed data validation check',
		'M'  =>  'Match',
		'N'  =>  'No Match',
		'P'  =>  'Not Processed',
		'S'  =>  'Should have been present',
		'U'  =>  'Issuer unable to process request',
		'X'  =>  'Card does not support verification'
	);

	/**
	 * Constructor
	 * 
	 * @param boolean $success
	 * @param string $message
	 * @param array $params
	 * @param array $options
	 * @return void
	 */
	public function __construct($success, $message, $params = array(), $options = array()) {
		$this->success = $success;
		$this->message = $message;
		$this->test = isset($options['test']) ? $options['test'] : false;
		$this->params = $params;

		if (isset($options['authorization'])) {
			$this->authorization = $options['authorization'];			
		}

		if (isset($options['avs_result'])) {
			$this->avsResult = self::_normalizeAvsResult($options['avs_result']);
		}

		if (isset($options['cvv_result'])) {
			$this->cvvResult = self::_normalizeCvvResult($options['cvv_result']);
		}
	}

	/**
	 * PHPMerchant_Billing_Response::_normalizeAvsResult()
	 * 
	 * @param mixed $avsResult
	 * @return array
	 */
	protected static function _normalizeAvsResult($avsResult) {
		$normalizedResult = $avsResult;
		if (!isset($normalizedResult['message'])) {
			$normalizedResult['message'] = self::$_avsMessages[$avsResult['code']];
		}

		return $normalizedResult;
	}

	/**
	 * PHPMerchant_Billing_Response::_normalizeCvvResult()
	 * 
	 * @param mixed $avsResult
	 * @return array
	 */
	protected static function _normalizeCvvResult($cvvResult) {
		$normalizedResult = $cvvResult;
		if (!isset($normalizedResult['message'])) {
			$normalizedResult['message'] = self::$_cvvMessages[$cvvResult['code']];
		}

		return $normalizedResult;
	}
}
