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
 * Test helper
 */
require_once dirname(__FILE__) . '/../../TestHelper.php';

/**
 * PHPMerchant_Billing_CreditCardTest
 * 
 * @package PHPMerchant
 * @author Ramon Torres
 * @copyright 2009 Ramon Torres
 * @version $Id$
 * @access public
 */
class PHPMerchant_Billing_CreditCardTest extends PHPUnit_Framework_TestCase
{
	/**
	 * PHPMerchant_Billing_CreditCardTest::testDetectTypeFromNumber()
	 * 
	 * @return void
	 */
	public function testDetectTypeFromNumber() {
		$result = PHPMerchant_Billing_CreditCard::detectTypeFromNumber('4111111111111111');
		$this->assertEquals(PHPMerchant_Billing_CreditCard::TYPE_VISA, $result);

		$result = PHPMerchant_Billing_CreditCard::detectTypeFromNumber('5105105105105100');
		$this->assertEquals(PHPMerchant_Billing_CreditCard::TYPE_MASTER_CARD, $result);

		$result = PHPMerchant_Billing_CreditCard::detectTypeFromNumber('371449635398431');
		$this->assertEquals(PHPMerchant_Billing_CreditCard::TYPE_AMEX, $result);

		// American Express Corporate
		$result = PHPMerchant_Billing_CreditCard::detectTypeFromNumber('378734493671000');
		$this->assertEquals(PHPMerchant_Billing_CreditCard::TYPE_AMEX, $result);

		// unsupported type
		$result = PHPMerchant_Billing_CreditCard::detectTypeFromNumber('76009244561');
		$this->assertNull($result);
	}
}
