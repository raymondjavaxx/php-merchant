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
 * PHPMerchant_Billing_Gateway_PayPal
 * 
 * @package PHPMerchant
 * @author Ramon Torres
 * @copyright 2009 Ramon Torres
 * @license http://www.opensource.org/licenses/mit-license.php
 * @access public
 */
class PHPMerchant_Billing_Gateway_PayPal extends PHPMerchant_Billing_Gateway {

	const TEST_URL = 'https://api-3t.sandbox.paypal.com/nvp';
	const LIVE_URL = 'https://api-3t.paypal.com/nvp';

	/**
	 * undocumented function
	 *
	 * @param mixed $money 
	 * @param PHPMerchant_Billing_CreditCard $creditcard 
	 * @param array $options 
	 * @return PHPMerchant_Billing_Response
	 */
	public function purchase($money, PHPMerchant_Billing_CreditCard $creditcard, $options = array()) {
		return $this->_commit('DoDirectPayment', $this->_buildSaleOrAuthorizationRequest('Sale', $money, $creditcard, $options));
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::authorize()
	 * 
	 * @param mixed $money
	 * @param PHPMerchant_Billing_CreditCard $creditcard
	 * @param array $options
	 * @return PHPMerchant_Billing_Response
	 */
	public function authorize($money, PHPMerchant_Billing_CreditCard $creditcard, $options = array()) {
		return $this->_commit('DoDirectPayment', $this->_buildSaleOrAuthorizationRequest('Authorization', $money, $creditcard, $options));
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::capture()
	 * 
	 * @param integer $money
	 * @param string $authorization
	 * @param array $options
	 * @return PHPMerchant_Billing_Response
	 */
	public function capture($money, $authorization, $options = array()) {
		return $this->_commit('DoCapture', $this->_buildCaptureRequest($money, $authorization, $options));
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::void()
	 * 
	 * @param string $authorization
	 * @param array $options
	 * @return PHPMerchant_Billing_Response
	 */
	public function void($authorization, $options = array()) {
		return $this->_commit('DoVoid', $this->_buildVoidRequest($authorization, $options));
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::credit()
	 * 
	 * @param integer $money
	 * @param string $identification
	 * @param array $options
	 * @return PHPMerchant_Billing_Response
	 */
	public function credit($money, $identification, $options = array()) {
		return $this->_commit('RefundTransaction', $this->_buildCreditRequest($money, $identification, $options));
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::_commit()
	 * 
	 * @param mixed $action
	 * @param mixed $request
	 * @return PHPMerchant_Billing_Response
	 */
	protected function _commit($method, $request) {
		$request = array_merge(array(
            'METHOD'    => $method,
            'VERSION'   => '3.0',
            'USER'      => $this->_options['login'],
            'PWD'       => $this->_options['password'],
            'SIGNATURE' => $this->_options['signature'],
		), $request);

		$response = PHPMerchant_Utils::httpPost($this->_getEndpointUrl(), $request);
		$response = $this->_parseResponse($response);

		$success = ($response['ACK'] == 'Success' || $response['ACK'] == 'SuccessWithWarning');
		$message = isset($response['L_LONGMESSAGE0']) ? $response['L_LONGMESSAGE0'] : $response['ACK'];
 
		$responseObject = new PHPMerchant_Billing_Response($success, $message, $response, array(
			'test' => $this->isTest(),
			'authorization' => self::_authorizationFrom($response),
			'avs_result' => isset($response['AVSCODE']) ? array('code' => $response['AVSCODE']) : null,
			'cvv_result' => isset($response['CVV2MATCH']) ? array('code' => $response['CVV2MATCH']) : null
		)); 

		return $responseObject;
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::_authorizationFrom()
	 * 
	 * @param mixed $response
	 * @return
	 */
	protected static function _authorizationFrom($response) {
		if (isset($response['TRANSACTIONID'])) {
			return $response['TRANSACTIONID'];
		}

		if (isset($response['AUTHORIZATIONID'])) {
			return $response['AUTHORIZATIONID'];
		}

		if (isset($response['REFUNDTRANSACTIONID'])) {
			return $response['REFUNDTRANSACTIONID'];
		}

		return null;
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::_buildSaleOrAuthorizationRequest()
	 * 
	 * @param mixed $action
	 * @param mixed $money
	 * @param mixed $creditcard
	 * @param mixed $options
	 * @return void
	 */
	protected function _buildSaleOrAuthorizationRequest($action, $money, $creditcard, $options) {
		$request = array(
			'PAYMENTACTION'  => $action,
			'AMT'            =>  sprintf("%.2f", $money / 100),
			'CURRENCYCODE'   => isset($options['currency_code']) ? $options['currency_code'] : 'USD',
			'CREDITCARDTYPE' => $this->_creditCardType($creditcard->type),
			'ACCT'           => $creditcard->number,
			'EXPDATE'        => sprintf('%02d%02d', $creditcard->month, $creditcard->year),
			'CVV2'           => $creditcard->verificationValue,
		);

		return $request;
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::_buildCaptureRequest()
	 * 
	 * @param integer $money
	 * @param string $authorization
	 * @param array $options
	 * @return array
	 */
	protected function _buildCaptureRequest($money, $authorization, $options) {
		$request = array(
			'AUTHORIZATIONID' => $authorization,
			'AMT'             =>  sprintf("%.2f", $money / 100),
			'CURRENCYCODE'    => isset($options['currency_code']) ? $options['currency_code'] : 'USD',
			'COMPLETETYPE'    => 'Complete',
			'NOTE'            => isset($options['description']) ? $options['description'] : ''
		);

		return $request;
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::_buildVoidRequest()
	 * 
	 * @param string $authorization
	 * @param array $options
	 * @return array
	 */
	protected function _buildVoidRequest($authorization, $options) {
		$request = array(
			'AUTHORIZATIONID' => $authorization,
			'NOTE'            => isset($options['description']) ? $options['description'] : ''
		);

		return $request;
	}
	
	/**
	 * PHPMerchant_Billing_Gateway_PayPal::_buildCreditRequest()
	 * 
	 * @param integer  $money
	 * @param string $identification
	 * @param array $options
	 * @return array
	 */
	protected function _buildCreditRequest($money, $identification, $options) {
		$request = array(
			'TRANSACTIONID' => $identification,
			'AMT'           =>  sprintf("%.2f", $money / 100),
			'NOTE'          => isset($options['note']) ? $options['note'] : '',
			'REFUNDTYPE'    => 'Partial'
		);

		return $request;
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::_creditCardType()
	 * 
	 * @param string $type
	 * @return string
	 */
	protected function _creditCardType($type) {
		$map = array(
			PHPMerchant_Billing_CreditCard::TYPE_VISA        => 'Visa',
			PHPMerchant_Billing_CreditCard::TYPE_MASTER_CARD => 'MasterCard',
			PHPMerchant_Billing_CreditCard::TYPE_AMEX        => 'Amex',
			PHPMerchant_Billing_CreditCard::TYPE_DISCOVER    => 'Discover'
		);

		return isset($map[$type]) ? $map[$type] : null;
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::getEndpointUrl()
	 * 
	 * @return string
	 */
	protected function _getEndpointUrl() {
		return $this->isTest() ? self::TEST_URL : self::LIVE_URL;
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::_parseResponse()
	 * 
	 * @param mixed $response
	 * @return array
	 */
	protected function _parseResponse($response) {
		$results = array();

		$pairs = explode('&', $response);
		foreach ($pairs as $pair) {
			list($k, $v) = explode('=', $pair);
			$k = trim(urldecode($k));
			$v = trim(urldecode($v));

			$results[$k] = $v;
		}

		return $results;
	}
}