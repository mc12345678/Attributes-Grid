Product Attribute Grid
Version 1.3.4
ZenCart 1.5
ZenCart 1.5.4, 1.5.5

Support forum:
http://www.zen-cart.com/forum/showthread.php?p=490491


The module is designed to set out TWO attributes within a grid format. For example, if an item has both colour and size, the grid is populated with quantity fields so a number of products can be added at the same time.

This module has also been designed to run along side STOCK BY ATTRIBUTES.

This is the ONLY stock module it is designed to work with.
http://www.zen-cart.com/index.php?main_page=product_contrib_info&products_id=310


==============================
INSTALLATION
==============================

1) Backup, BACKUP!!! No responsibility is taken for any loss of data.


2) Change the name of /YOUR_TEMPLATE/ folder to match your template.


3) Upload files to your store directory. Merge with existing files as necessary.


4) Now, navigate to a product on your Store's front end. This will auto-install the module for you.


5) Login to your admin, go to 'Catalog' > 'Option Name Manager' and setup your custom option names (ie. Color, Size). Be sure to select 'Grid' type.
   NOTE: Attributes appear in SORT_ORDER, so the lowest sort number is at the LEFT of the grid, and the highest sort number is on the TOP of the grid.


6) Go to 'Option Values' and setup values for your custom option names (ie. Red, White, Blue).


7) Go to'Attribute Controller' and setup your product attributes as desired:

	A) If you want a single set of options with a quantity box, just add a single option.

		Red     [__]
		White   [__]
		Blue    [__]

	B) If you want to cross-grid your attributes, add two options.

			Small	Medium	Large
		Red	[__]	[__]	[__]
		White	[__]	[__]	[__]
		Blue	[__]	[__]	[__]

	NOTE: Attributes appear in SORT_ORDER, so the lowest sort number is at the LEFT of the grid, and the highest sort number is on the TOP of the grid.



==============================
AFFECTED FILES
==============================

Files included in this install are:

	includes/modules/YOUR_TEMPLATE/attributes.php
	includes/auto_loaders/config.attributes_grid_products.php
	includes/classes/observers/class.attributes_grid_products.php
	includes/extra_cart_actions/attributes_grid_products_extra_cart.php
	includes/languages/english/extra_definitions/absolute_attribute_grid.php



==============================
CHANGELOG
==============================

1.3.4 12/19/16 - mc12345678 McNumbers Ware:
          Changes made to support SBA back in version 1.3.2 prevented proper operation if Stock By Attributes was not
	  installed.  This has been corrected in this version.
	  Modified the included attributes.php file to incorporate changes made to Stock By Attributes and Attribute Image Swap.

1.3.3 11/17/16 - mc12345678 McNumbers Ware:
        - Modify the grid option name constant to the designation set by Edit Orders
	  to resolve issues identified such as identified at: 
	  https://www.zen-cart.com/showthread.php?85649-Product-Attribute-Grid&p=1243256#post1243256

1.3.2 11/04/16 - mc12345678 McNumbers Ware:
        - Correct visual display when any one or more option name has only one option value.
	- If an internal id is blank, then when generating the object id for javascript assign a zero (0).
	- Add code associated with SBA to continue compatibility.
	- Remove some code from the includes/modules/YOUR_TEMPLATE/attributes.php to restore to original/SBA related.
	- Added possible functionality to provide a contact us line/item.
	- Added ability to hide price based on user's requiring to be logged in.
	- Added ability to obscure the actual number of available product greater than
	   some chosen value.
	- Incorporated possible use of displaying price at grid box instead of at option name's option value.
	- Improved product processing to allow all ZC default attribute types to be used.
	- For uploading files, it is possible to associate a single file for all "variants" or
	   to provide one file for each product variation (grid option).
	
1.3.1 04/06/16 - mc12345678 McNumbers Ware
        - Remove code that caused grid to appear twice when a grid contained two attributes.

1.3   04/03/16 - mc12345678 McNumbers Ware
        - Operate with ZC 1.5.3+.
	- Integrate with attributes.php by observer.
	- Grid option name type is now stored in the database instead of forced to be some huge number.
	- Renamed some file for easier recognition in future upgrades/modifications.

1.2 - 11/02/12 - JT Website Design
	- allow single column grid
	- upgrade for compatibility to zc 1.5
	- remove unnecessary files
	- cleanup installation instructions

1.1.2 - 02/08/12 - JT Website Design
	- fix radio button display and pricing display

1.1.1 - Absolute Solutions
	- New Option Type added to admin - GRID
	- 1 or 2 attributes may be added as GRID type. If only 1, QTY column is automatically added.
	- Module now works with Attribute Stock Quantity module (not included in zip)
	- Out of stock image replaces input field when stock is controlled and admin switches are set to "hide add to cart when out of stock"

1.0.1 - Absolute Solutions
	- Original release



==============================
CREDITS & DONATIONS
==============================

This module has been created by Absolute Solutions - www.absolute-solutions.co.uk
and updated by McNumbers Ware: mc12345678.com

Zen Cart Forum member :: Absolute

This module was designed and is maintained by Absolute-Solutions.co.uk.  If you feel it is an asset to your store, and would like to make a donation, payments can be sent using PayPal to paypal@absolute-solutions.co.uk.

Additional support can be arranged through Absolute Solutions, and will be charged at our standard hourly rate, with no minimum fee.  Please contact zen.mods@absolute-solutions.co.uk


Thanks go to John K Moore of LodestarIT whose generous support ensured the release of version 1.1.1 of this module.

If you want to see another feature added to this module, please show your appreciation, as John did.
