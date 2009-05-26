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

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);

/*
 * Appends library/ tot he include paths
 */
define('LIBRARY_PATH', dirname(dirname(__FILE__)).DS.'library');
set_include_path(get_include_path() . PS . LIBRARY_PATH);

/**
 * Autoloader
 *
 * @param string $class
 * @return void
 */
function __autoload($class) {
	$file = LIBRARY_PATH . DS .str_replace('_', DS, $class) . '.php';
	require $file;
}
