<?php

/**
 * Autoloader array for attributes grid  functionality. Makes sure that products with attributes grid is instantiated at the
 * right point of the Zen Cart initsystem.
 * 
 * @package     attributes_grid_products
 * @author      mc12345678 
 * @copyright   Copyright 2008-2013 mc12345678
 * @copyright   Copyright 2003-2007 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @link        http://www.zen-cart.com/
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: config.user_tracking.php xxxx 2013-06-28 20:31:10Z conor $
 *
 * Stock by Attributes 1.5.4
 */

 $autoLoadConfig[0][] = array(
	'autoType' => 'class',
	'loadFile' => 'observers/class.attributes_grid_products.php'
	);
 $autoLoadConfig[199][] = array(
	'autoType' => 'classInstantiate',
	'className' => 'attributes_grid_products',
	'objectName' => 'attributes_grid_products_observe'
	); 
?>