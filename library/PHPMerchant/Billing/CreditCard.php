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
 * PHPMerchant_Billing_CreditCard
 * 
 * @package PHPMerchant
 * @author Ramon Torres
 * @copyright 2009 Ramon Torres
 * @license http://www.opensource.org/licenses/mit-license.php
 * @access public
 */
class PHPMerchant_Billing_CreditCard {

	const TYPE_VISA        = 'visa';
	const TYPE_MASTER_CARD = 'master_card';
	const TYPE_AMEX        = 'amex';
	const TYPE_DISCOVER    = 'discover';

	/**
	 * Credit card patterns
	 *
	 * @var array
	 */
	protected static $_cardsPatterns = array(
		self::TYPE_VISA        => '/^4[0-9]{12}([0-9]{3})?$/',
		self::TYPE_MASTER_CARD => '/^5[1-5][0-9]{14}$/',
		self::TYPE_AMEX        => '/^3[4|7][0-9]{13}$/',
		self::TYPE_DISCOVER    => '/^(?:6011|644[0-9]|65[0-9]{2})[0-9]{12}$/'
	);

	public $firstName;
	public $lastName;
	public $number;
	public $month;
	public $year;
	public $verificationValue;

	/**
	 * Stores the validation errors
	 *
	 * @var array
	 */
	protected $_errors = array();

	/**
	 * Credit card type
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Constructor
	 *
	 * @param array $data
	 */
	public function __construct($data = array()) {
		if (count($data) > 0) {
			$this->firstName = isset($data['first_name']) ? $data['first_name'] : null;
			$this->lastName  = isset($data['last_name']) ? $data['last_name'] : null;
			$this->number    = isset($data['number']) ? $data['number'] : null;
			$this->month     = isset($data['month']) ? $data['month'] : null;
			$this->year      = isset($data['year']) ? $data['year'] : null;
			$this->verificationValue = isset($data['verification_value']) ? $data['verification_value'] : null;
			$this->type      = isset($data['type']) ? $data['type'] : null;
		}
	}

	/**
	 * Validates the credit card information.
	 *
	 * @return boolean
	 */
	public function isValid() {
		$this->_errors = array();

		if (empty($this->type)) {
			$this->_errors['type'] = 'Type is required';
		}

		if (empty($this->firstName)) {
			$this->_errors['first_name'] = 'First Name cannot be empty';
		}

		if (empty($this->lastName)) {
			$this->_errors['last_name'] = 'Last Name cannot be empty';
		}

		if (!$this->validMonth()) {
			$this->_errors['month'] = 'Month is not a valid month';
		}

		if (!$this->validYear()) {
			$this->_errors['year'] = 'Year is not a valid year';
		} else if ($this->isExpired()) {
			$this->_errors['year'] = 'Year expired';
		}

		if (!$this->validNumber()) {
			$this->_errors['number'] = 'Number is not a valid credit card number';
		} else if (!isset($this->_errors['type']) && $this->numberMatchesType()) {
			$this->_errors['type'] = 'Type is not the correct card type';
		}

		return count($this->_errors) == 0;
	}

	/**
	 * Returns the validation errors
	 * 
	 * @return array
	 */
	public function errors() {
		return $this->_errors;
	}

	/**
	 * Validates the Credit Card number.
	 * 
	 * @return boolean
	 */
	public function validNumber() {
		return strlen($this->number) > 12 && self::_luhn($this->number);
	}

	/**
	 * Validates the expiration year.
	 * 
	 * @return boolean
	 */
	public function validYear() {
		$nowYear = date('Y');
		return in_array($this->year, range($nowYear, $nowYear+20));
	}

	/**
	 * Validates the expiration month.
	 * 
	 * @return boolean
	 */
	public function validMonth() {
		return in_array($this->month, range(1, 12));
	}

	/**
	 * Validates the expiration date of the Credit Card.
	 * 
	 * @return boolean
	 */
	public function isExpired() {
		$expires = (integer)sprintf("%d%02d", $this->year, $this->month);
		$today   = (integer)date('Ym');
		return $expires < $today;
	}

	/**
	 * Returns true if the credit card number matches the type.
	 * 
	 * @return boolean
	 */
	public function numberMatchesType() {
		return self::detectTypeFromNumber($this->number) == $this->type;
	}

	/**
	 * Verifies a number using the Luhn algorithm
	 *
	 * @link http://en.wikipedia.org/wiki/Luhn_algorithm
	 * @param string $number
	 * @return boolean
	 */
	protected static function _luhn($number) {
		$digits = array_reverse(str_split($number));
		$totalDigits = count($digits);

		$sum = 0;
		$alternate = false;
		for ($i=0; $i<$totalDigits; $i++) {
			$digit = $digits[$i];
			if ($alternate) {
				$digit = $digit * 2;
				if ($digit > 9) {
					$digit = $digit - 9;
				}
			}

			$sum += $digit;
			$alternate = !$alternate;
		}

		return ($sum % 10 == 0);
	}

	/**
	 * Given a number this function detects the Credit Card type
	 * 
	 * @param string $number
	 * @return string
	 */
	public static function detectTypeFromNumber($number) {
		foreach (self::$_cardsPatterns as $type => $pattern) {
			if (preg_match($pattern, $number) == 1) {
				return $type;
			}
		}

		return null;
	}

	/**
	 * Returns a string representing the object 
	 *
	 * @link http://us2.php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
	 * @return string
	 */
	public function __toString() {
		return sprintf(
			"Credit Card - %s %s - %s Exp: %02d/%d VV: %d Type: %s",
			$this->firstName,
			$this->lastName,
			$this->number,
			$this->month,
			$this->year,
			$this->verificationValue,
			$this->type
		);
	}
}
