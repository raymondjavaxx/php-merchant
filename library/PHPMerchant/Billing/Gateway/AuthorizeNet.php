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
 * PHPMerchant_Billing_Gateway_AuthorizeNet
 *
 * Authorize.Net Payment Gateway
 * 
 * @package PHPMerchant
 * @author Ramon Torres
 * @copyright 2009 Ramon Torres
 * @license http://www.opensource.org/licenses/mit-license.php
 * @access public
 */
class PHPMerchant_Billing_Gateway_AuthorizeNet extends PHPMerchant_Billing_Gateway {

	const API_VERSION = '3.1';

	/**
	 * Response Codes
	 *
	 * @link http://developer.authorize.net/guides/AIM/Transaction_Response/Fields_in_the_Payment_Gateway_Response.htm
	 */
	const RESPONSE_CODE_APPROVED = 1;
	const RESPONSE_CODE_DECLINED = 2;
	const RESPONSE_CODE_ERROR = 3;
	const RESPONSE_CODE_HELD = 4;

	const TEST_URL = 'https://test.authorize.net/gateway/transact.dll';
	const LIVE_URL = 'https://secure.authorize.net/gateway/transact.dll';

	/**
	 * undocumented function
	 *
	 * @param mixed $money 
	 * @param PHPMerchant_Billing_CreditCard $creditcard 
	 * @param array $options 
	 * @return PHPMerchant_Billing_Response
	 */
	public function purchase($money, PHPMerchant_Billing_CreditCard $creditcard, $options = array()) {
		return $this->_commit('AUTH_CAPTURE', $this->_buildAuthorizeOrPurchaseRequest($money, $creditcard, $options));
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
		return $this->_commit('AUTH_ONLY', $this->_buildAuthorizeOrPurchaseRequest($money, $creditcard, $options));
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
		$request = array(
			'x_trans_id' => $authorization,
			'x_amount'   => $this->_formatMoney($money)
		);

		return $this->_commit('PRIOR_AUTH_CAPTURE', $request);
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::void()
	 * 
	 * @param string $authorization
	 * @param array $options
	 * @return PHPMerchant_Billing_Response
	 */
	public function void($authorization, $options = array()) {
		$request = array(
			'x_trans_id' => $authorization
		);

		return $this->_commit('VOID', $request);
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
		self::requires($options, 'card_number');

		$request = array(
			'x_trans_id' => $identification,
			'x_card_num' => $options['card_number'],
			'x_amount'   => $this->_formatMoney($money)
		);

		return $this->_commit('CREDIT', $request);
	}

	/**
	 * PHPMerchant_Billing_Gateway_PayPal::_commit()
	 * 
	 * @param mixed $action
	 * @param mixed $request
	 * @return PHPMerchant_Billing_Response
	 */
	protected function _commit($action, $request) {
		$request = array_merge(array(
            'x_login'          => $this->_options['login'],
            'x_tran_key'       => $this->_options['password'],
            'x_version'        => self::API_VERSION,
            'x_type'           => $action,
            'x_delim_data'     => 'TRUE',
            'x_delim_char'     => '|',
            'x_relay_response' => 'FALSE',
		), $request);

		$response = PHPMerchant_Utils::httpPost($this->_getEndpointUrl(), $request);
		$response = explode('|', $response);

		$success = in_array($response[0], array(self::RESPONSE_CODE_APPROVED, self::RESPONSE_CODE_HELD));

		// Response Reason Text (ORDER: 4)
		$message = $response[3];
 
		$responseObject = new PHPMerchant_Billing_Response($success, $message, $response, array(
			'test'          => $this->isTest(),
			'authorization' => $response[6], // Transaction ID (ORDER: 7)
			'avs_result'    => empty($response[5])  ? null : $response[5], // AVS Response (ORDER: 6)
			'cvv_result'    => empty($response[38]) ? null : $response[38] // Card Code Response (ORDER: 39)
		)); 

		return $responseObject;
	}

	protected function _buildAuthorizeOrPurchaseRequest($money, $creditcard, $options) {
		$request = array(
			'x_first_name' => $creditcard->firstName,
			'x_last_name'  => $creditcard->lastName,
			'x_card_num'   => $creditcard->number,
			'x_exp_date'   => sprintf('%02d-%d', $creditcard->month, $creditcard->year),
			'x_amount'     => $this->_formatMoney($money)
		);

		return $request;
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
	 * PHPMerchant_Billing_Gateway_AuthorizeNet::_formatMoney()
	 * 
	 * @param integer $money
	 * @return string
	 */
	protected function _formatMoney($money) {
		return sprintf("%.2f", $money / 100); 
	}
}
