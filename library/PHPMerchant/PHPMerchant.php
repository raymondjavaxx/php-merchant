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

/*
 * Include common classes. If you used an autoloader
 * you might not have to include this file
 */

require_once 'Utils.php';
require_once 'Billing/CreditCard.php';
require_once 'Billing/Response.php';
require_once 'Billing/Gateway.php';
require_once 'Billing/Gateway/Exception.php';
