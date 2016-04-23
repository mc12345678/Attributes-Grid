<?php

/**
 * Description of class.attributes_grid_products: This class is used to support order information related to Attribute Grid.  This way reduces the modifications of the includes/modules/attributes.php file to nearly nothing.
 *
 * @property array $_stock_values The results of querying on the database for the stock remaining and other associated information.
 * @author mc12345678
 * File is based on use in ZC 1.5.5 and the distrbituted attributes.php file
 * Attributes Grid compatible with Stock By Attributes mc12345678
 */
class attributes_grid_products extends base {

  //
  private $_stock_values;
  
  private $_products_options_names_count;

  private $_products_options_names_current;
  
  private $_attrib_grid;
  
  private $_contact_us_value; // Used to display the contact us link.

  
  /*
   * This is the observer for the includes/classes/order.php file to support Stock By Attributes when the order is being processed at the end of the purchase.
   */
  function __construct() {
    //global $zco_notifier;
    
    $attachNotifier = array();
    $attachNotifier[] = 'NOTIFY_ATTRIBUTES_MODULE_DEFAULT_SWITCH'; // Keep
    $attachNotifier[] = 'NOTIFY_ATTRIBUTES_MODULE_START_OPTIONS_LOOP'; // Keep
    $attachNotifier[] = 'NOTIFY_ATTRIBUTES_MODULE_OPTION_BUILT'; // keep

//    $zco_notifier->attach($this, $attachNotifier); 
    $this->attach($this, $attachNotifier);
    
    $this->_products_options_names_current = 0; // Initialize this variable to 0.
    $this->_attrib_grid = '';
    $this->_contact_us_value = array(149); // 8
  }  


// $zco_notifier->notify('NOTIFY_ATTRIBUTES_MODULE_OPTION_BUILT', $products_options_names->fields, $options_name, $options_menu, $options_comment, $options_comment_position, $options_html_id, $options_attributes_image);
  /*
   * 'NOTIFY_ATTRIBUTES_MODULE_OPTION_BUILT'
   */
  function updateNotifyAttributesModuleOptionBuilt(&$callingClass, $notifier, $products_options_names_fields,
                                                   &$options_name, &$options_menu, &$options_comment,
                                                   &$options_comment_position, &$options_html_id,
                                                   &$options_attributes_image) {

    // if at the last option name, then no further processing above and want to reset the
    // counter so that on the next use on this session it is zero.
    if ($this->_products_options_names_current == $this->_products_options_names_count) {
      $this->_products_options_names_current = 0;
    }
  }

  /*
   * 'NOTIFY_ATTRIBUTES_MODULE_START_OPTIONS_LOOP'
   */
  function updateNotifyAttributesModuleStartOptionsLoop(&$callingClass, $notifier, $i, &$products_options_fields){
    global $db, $options_menu_images, $products_options_array, $products_options_names,
           $PWA_STOCK_QTY;

    $this->_products_options_names_current++;
    
    if ($this->_products_options_names_current == 1) {
      global $currencies;

      $show_attribute_stock_left = true;
      $show_price_with_option_value = false;

      /****************************************************
      /* Absolute-Solutions.co.uk Edit
      /*
      /* Attributes Grid format
      /* 1 of 2
      /****************************************************/
// PRODUCTS_OPTIONS_TYPE_GRID is the ID for the GRID option
      if (!defined('PRODUCTS_OPTIONS_TYPE_GRID')) {// || !defined('CONFIG_ATTRIBUTE_OPTION_GRID_INSTALLED') || CONFIG_ATTRIBUTE_OPTION_GRID_INSTALLED != 'true') {
        $products_options_types_name = 'Grid';

        $sql = "SELECT products_options_types_name, products_options_types_id FROM " . TABLE_PRODUCTS_OPTIONS_TYPES . " WHERE 
            products_options_types_name = :products_options_types_name: AND products_options_types_name != '';";
        $sql = $db->bindVars($sql, ':products_options_types_name:', $products_options_types_name, 'string');
        $result = $db->Execute($sql);

        if (!$result->EOF && $result->RecordCount() > 0) {
          // Is found, reassign $resultGID to found value.
          $resultGID = $result->fields['products_options_types_id'];
          // $result = true; // Reassign the variable to true to indicate that successfully found a previous $products_options_types_name.
        } else {
          $sql = "SELECT pot.products_options_types_id, pot.products_options_types_name
                  FROM ".TABLE_PRODUCTS_OPTIONS_TYPES." pot  
                  order by pot.products_options_types_id desc limit 1";
          $result = $db->Execute($sql);

          if (!$result->EOF && $result->RecordCount() > 0)
          {
            $resultGID = $result->fields['products_options_types_id'] + 1;
          } else { // Apparently the table for products_options_types_id has been cleared.
            $resultGID = 6; // Assign the next products_options_types_id after the default ZC products_options_types_id.
          }

          $sql = "INSERT INTO ".TABLE_PRODUCTS_OPTIONS_TYPES." (`products_options_types_id`, `products_options_types_name`) 
                  VALUES (:resultGID:, :products_options_types_name:);";
          $sql = $db->bindVars($sql, ':resultGID:', $resultGID, 'integer');
          $sql = $db->bindVars($sql, ':products_options_types_name:', $products_options_types_name, 'string');

          $result = $db->Execute($sql); // Returns true if successful insert and false if not.
        }

        if($result !== false /*&& $result->fields['products_options_types_name'] !=  ''*/ ){
// PRODUCT_TYPE_ATTRIBUTE_OPTION_GRID was the "old" version.
          $configuration_title = 'Selection list product option type (Grid)';
          $configuration_key = 'PRODUCTS_OPTIONS_TYPE_GRID';
          $configuration_value = $resultGID;
          $configuration_description = 'Numeric value of the grid product option type used for SBA';
          $configuration_group_id = 6;
          $sort_order = 0;
          $last_modified = 'NULL';
          $date_added = 'now()';
          $use_function = 'NULL';
          $set_function = 'NULL';


          $sql = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value,
      configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
        
      VALUES
      (:configuration_title:, :configuration_key:, :configuration_value:, 
        :configuration_description:,
        :configuration_group_id:, :sort_order:, :last_modified:, :date_added:, :use_function:, :set_function:);";
          $sql = $db->bindVars($sql, ':configuration_title:', $configuration_title, 'string');
          $sql = $db->bindVars($sql, ':configuration_key:', $configuration_key, 'string');
          $sql = $db->bindVars($sql, ':configuration_value:', $configuration_value, 'integer');
          $sql = $db->bindVars($sql, ':configuration_description:', $configuration_description, 'string');
          $sql = $db->bindVars($sql, ':configuration_group_id:', $configuration_group_id, 'integer');
          $sql = $db->bindVars($sql, ':sort_order:', $sort_order, 'integer');
          $sql = $db->bindVars($sql, ':last_modified:', $last_modified, (($last_modified == 'now()' || $last_modified == 'NULL') ? 'noquotestring' : 'string'));
          $sql = $db->bindVars($sql, ':date_added:', $date_added, (($date_added == 'now()' || $date_added == 'NULL') ? 'noquotestring' : 'string'));
          $sql = $db->bindVars($sql, ':use_function:', $use_function, (($use_function == 'now()' || $use_function == 'NULL') ? 'noquotestring' : 'string'));
          $sql = $db->bindVars($sql, ':set_function:', $set_function, (($set_function == 'now()' || $set_function == 'NULL') ? 'noquotestring' : 'string'));

          $db->Execute($sql);
        }

        define('PRODUCTS_OPTIONS_TYPE_GRID', $resultGID);
      }

      $sql     = "select patrib.options_id, popt.products_options_type 
                from " . TABLE_PRODUCTS_OPTIONS . " popt 
                left join " . TABLE_PRODUCTS_ATTRIBUTES . " patrib ON (popt.products_options_id = patrib.options_id)
                where patrib.products_id = :products_id: 
                and popt.language_id = :languages_id: 
                group by patrib.options_id";

      $sql = $db->bindVars($sql, ':products_id:', $_GET['products_id'], 'integer');
      $sql = $db->bindVars($sql, ':languages_id:', $_SESSION['languages_id'], 'integer');
      $pr_attr_sba           = $db->Execute($sql);
      $check_attributes_count    = $pr_attr_sba->RecordCount();
      $pr_attr = $pr_attr_sba;
      $pr_attr->fields = $pr_attr_sba->fields;
      $pr_attr->fields['total']  = $check_attributes_count; //Fix for template checks

      $check_grid = false;
      while(!$pr_attr_sba->EOF) {
        switch($pr_attr_sba->fields['products_options_type']) {
          case PRODUCTS_OPTIONS_TYPE_GRID:  //GRID exists
            $check_grid = true;

            break 2; // Exits the switch and the while loop.  No need to stay in the loop if already identified that the grid attribute exists.
        }
        $pr_attr_sba->MoveNext();
      }

      $show_only_grid = false;

      if ($check_grid) {
        if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
          $options_order_by= ' order by LPAD(popt.products_options_sort_order,11,"0")';
        } else {
          $options_order_by= ' order by popt.products_options_name';
        }
        $sql = "select distinct popt.products_options_id, popt.products_options_name, popt.products_options_sort_order, popt.products_options_comment
          from    " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
          where    patrib.products_id = :products_id:
          and    patrib.options_id = popt.products_options_id
          and     popt.products_options_type = :products_options_type:
          and    popt.language_id = :languages_id: " .
            $options_order_by;

        $sql = $db->bindVars($sql, ':products_id:', $_GET['products_id'], 'integer');
        $sql = $db->bindVars($sql, ':products_options_type:', PRODUCTS_OPTIONS_TYPE_GRID, 'integer');
        $sql = $db->bindVars($sql, ':languages_id:', $_SESSION['languages_id'], 'integer');
        $products_options_names2 = $db->Execute($sql);

        $show_only_grid = ($check_attributes_count == $products_options_names2->RecordCount());

        for($d=0; $d < 2; $d++) {
          $rd = ($rd != 'V' ? 'V' : 'H');
          if ($products_options_names2->RecordCount() == $d) {
            $grid_records[$rd] = array(  'id'    =>  '',
                'name'    =>  TEXT_ATTRIBUTE_GRID_QTY,
                'comment'  =>  '',
                'options'  =>  array(
                    array(
                        'id'      =>  '',
                        'name'      =>  '',
                        'price'      =>  '0',
                        'price_prefix'  =>  '',
                        'weight'    =>  '0',
                        'weight_prefix'  =>  '',
                        'discounted'  =>  '0',
                        'image'      =>  '',
                        'base_price'  =>  '0',
                        'required'    =>  '0',
                        'display'    =>  '1'
                    )
                ),
                'images'  =>  false
            );
          } else {
            $grid_records[$rd] = array(
                'id'    =>  $products_options_names2->fields['products_options_id'],
                'name'    =>  $products_options_names2->fields['products_options_name'],
                'comment'  =>  $products_options_names2->fields['products_options_comment'],
                'options'  =>  array(),
                'images'  =>  false
            );
            if ( PRODUCTS_OPTIONS_SORT_BY_PRICE =='1' ) {
              $order_by= ' order by LPAD(pa.products_options_sort_order,11,"0"), pov.products_options_values_name';
            } else {
              $order_by= ' order by LPAD(pa.products_options_sort_order,11,"0"), pa.options_values_price';
            }
            $sql = "select  pov.products_options_values_id, pov.products_options_values_name,
          pa.*
          from      " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
          where     pa.products_id = '" . (int)$_GET['products_id'] . "'
          and       pa.options_id = '" . (int)$products_options_names2->fields['products_options_id'] . "'
          and       pa.options_values_id = pov.products_options_values_id
          and       pov.language_id = '" . (int)$_SESSION['languages_id'] . "' " .
                $order_by;

            $products_options2 = $db->Execute($sql);

            while (!$products_options2->EOF) {
              $grid_records[$rd]['options'][] = array(
                  'id'      =>    $products_options2->fields['products_options_values_id'],
                  'name'      =>  $products_options2->fields['products_options_values_name'],
                  'price'      =>  $products_options2->fields['options_values_price'],
                  'price_prefix'  =>  $products_options2->fields['price_prefix'],
                  'weight'    =>  $products_options2->fields['products_attributes_weight'],
                  'weight_prefix'  =>  $products_options2->fields['products_attributes_weight_prefix'],
                  'discounted'  =>  $products_options2->fields['attributes_discounted'],
                  'image'      =>  $products_options2->fields['attributes_image'],
                  'base_price'  =>  $products_options2->fields['attributes_base_price_included'],
                  'required'    =>  $products_options2->fields['attributes_required'],
                  'display'    =>  $products_options2->fields['display_only']
              );
              
              if (in_array($grid_records[$rd]['options'][sizeof($grid_records[$rd]['options'])-1]['id'], $this->_contact_us_value)) {
                $grid_records[$rd]['options'][sizeof($grid_records[$rd]['options']) - 1]['price'] = '<a href="' . zen_href_link(FILENAME_ASK_A_QUESTION, 'products_id=' . $_GET['products_id']) . '" >' . TEXT_ATTRIBUTE_GRID_CONTACT_US . '</a>';
//                $grid_records[$rd]['options'][sizeof($grid_records[$rd]['options']) - 1]['name'] = '<a href="' . zen_href_link(FILENAME_CONTACT_US) . '" >' . TEXT_ATTRIBUTE_GRID_CONTACT_US . '</a>';
              }

              if (zen_not_null($products_options2->fields['attributes_image'])) {
                $grid_records[$rd]['images'] = true;
              }

              $products_options2->MoveNext();
            }
          }

          if (!$products_options_names2->EOF) {
            $products_options_names2->MoveNext();
          }
        }

        $grh_size = sizeof($grid_records['H']['options']);
        $grv_size = sizeof($grid_records['V']['options']);

        $attribute_stock_controlled = (defined('TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK') && $show_only_grid);
        $show_attribute_stock = ($attribute_stock_controlled && zen_get_show_product_switch($_GET['products_id'], 'quantity') == 1);
        $show_attribute_out_of_stock = ($attribute_stock_controlled && SHOW_PRODUCTS_SOLD_OUT_IMAGE == '1');
        
        if (($grh_size > 1) || zen_not_null($grid_records['H']['options'][0]['name'])) {
          $top_rowspan = 1;
        } else {
          $top_rowspan = 1;
        }

        if (($grv_size > 1) || zen_not_null($grid_records['V']['options'][0]['name'])) {
          $top_colspan = 2;
        } else {
          $top_colspan = 1;
        }

        // BOF This is the start of the headers for the table.
        $this->_attrib_grid = '<table id="attrib-grid-table">'."\n" .
            '<tr>'."\n".
            '  <td colspan="1" rowspan="'.($top_rowspan).'" ' .($grh_size > 1 ? '' : 'id="attrib-grid-opt-nameV"') . '>'.($grh_size > 1 ? '' : $grid_records['V']['name']);
        $this->_attrib_grid .= (zen_not_null($grid_records['V']['comment']) ? 
        '<br />' . '<span id="attrib-grid-opt-commentV">'.$grid_records['V']['comment'].'</span>' 
        : 
        '');

        $this->_attrib_grid .='</td>'."\n";
        // BOF (Qty Avail) Left of Quantity display
        $this->_attrib_grid .= ($show_price_with_option_value == true ? '' : '<td>Price</td>');
        $this->_attrib_grid .= ($show_attribute_stock_left == true ? (($show_attribute_stock == true && $show_attribute_stock_left == true) ? 
          '<td id="attrib-grid-opt-availH" class="attrib-grid-opt-avail-headerH">Qty Avail</td>': '') : '').
            '<td colspan="'.(sizeof($grid_records['H']['options'])-($grh_size > 1 ? 0: 1)).'" id="attrib-grid-opt-nameH">'.$grid_records['H']['name'].(zen_not_null($grid_records['H']['comment']) ? '<br /><span id="attrib-grid-opt-commentH">'.$grid_records['H']['comment'].'</span>' : '').'</td>'."\n".
             ($show_attribute_stock_left == false ? (($show_attribute_stock == true && $show_attribute_stock_left == false && $grh_size == 1) ? 
          '<td id="attrib-grid-opt-availH" class="attrib-grid-opt-avail-headerH">Qty Avail</td>': '') : '') .
            '</tr>'."\n". ($grh_size > 1 ?
            '<tr>' .
            '  <td id="attrib-grid-opt-nameV">' . $grid_records['V']['name'] . 
            '  </td>'."\n" : ''); //JT modification
        // EOF This is the end of the headers for the table.

        for($grh=0; $grh < $grh_size; $grh++) {
          if (($grh_size > 1) && zen_not_null($grid_records['H']['options'][$grh]['name'])) {
            $this->_attrib_grid .= '<td class="attrib-grid-hHeader" id="attrib-grid-hHeader-'.$grid_records['H']['options'][$grh]['id'].'">'.$grid_records['H']['options'][$grh]['name'];

            // Show price based on store settings - JT modification
            if (!in_array($grid_records['H']['options'][$grh]['id'], $this->_contact_us_value)) {
              $attrib_h_price = $grid_records['H']['options'][$grh]['price_prefix'].$currencies->format($grid_records['H']['options'][$grh]['price']);
              $attrib_h_store_price = $this->price_display_logged_in($grid_records['H']['options'][$grh]['price_prefix'].$currencies->format($grid_records['H']['options'][$grh]['price']));
            } else {
              $attrib_h_price = $grid_records['H']['options'][$grh]['price'] + 1;
              $attrib_h_store_price = $grid_records['H']['options'][$grh]['price'];
            }
            
            if ($attrib_h_price == $attrib_h_store_price) {
              if ($show_price_with_option_value == true) {
                $this->_attrib_grid .= ($grid_records['H']['options'][$grh]['price'] != 0 ? '<br /><span class="attrib-grid-header-price">'. $attrib_h_price .'</span>' : '<br /><span class="attrib-grid-header-price">' . '</span>');
              } else {
                $this->_attrib_grid .= ($grid_records['H']['options'][$grh]['price'] != 0 ? '</td><td class="attrib-grid-header-price">'. $grid_records['H']['options'][$grh]['price_prefix'].$currencies->format($grid_records['H']['options'][$grh]['price']) : '</td><td class="attrib-grid-header-price">' );
              }
            } else {
              if ($show_price_with_option_value == true) {
                $this->_attrib_grid .= ($grid_records['H']['options'][$grh]['price'] != 0 ? '<br /><span class="attrib-grid-header-price"><a href="'.zen_href_link(FILENAME_LOGIN).'">' . $attrib_h_store_price . '</a></span>' : '<br /><span class="attrib-grid-header-price"><a href="'.zen_href_link(FILENAME_LOGIN).'">' . $attrib_h_store_price . '</a></span>');
              } else {
                $this->_attrib_grid .= ($grid_records['H']['options'][$grh]['price'] != 0 ? '</td><td class="attrib-grid-header-price"><a href="'.zen_href_link(FILENAME_LOGIN).'">' . $attrib_h_store_price . '</a>' : '</td><td class="attrib-grid-header-price"><a href="'.zen_href_link(FILENAME_LOGIN).'">' . $attrib_h_store_price . '</a>');
              }
            }
            // End show price

            $this->_attrib_grid .= '</td>';
          }
        }

        if (($grh_size > 1) /*|| zen_not_null($grid_records['H']['options'][0]['name'])*/) {
          $this->_attrib_grid .= '</tr>'."\n";
        }

        for($grv=0; $grv < $grv_size; $grv++) {
          $this->_attrib_grid .= '<tr>'."\n";
          if (($grv_size > 1) && zen_not_null($grid_records['V']['options'][$grv]['name'])) {
            $this->_attrib_grid .= '  <td class="attrib-grid-vHeader" id="attrib-grid-vHeader-'.$grid_records['V']['options'][$grv]['id'].'">'.$grid_records['V']['options'][$grv]['name'];

            // Show price based on store settings - JT modification
            if (!in_array($grid_records['V']['options'][$grv]['id'], $this->_contact_us_value)) {
              $attrib_v_price = $grid_records['V']['options'][$grv]['price_prefix'].$currencies->format($grid_records['V']['options'][$grv]['price']);
              $attrib_v_store_price = $this->price_display_logged_in($grid_records['V']['options'][$grv]['price_prefix'].$currencies->format($grid_records['V']['options'][$grv]['price']));
            } else {
              $attrib_v_price = $grid_records['V']['options'][$grv]['price'] + 1;
              $attrib_v_store_price = $grid_records['V']['options'][$grv]['price'];
            }
            
            if ($attrib_v_price == $attrib_v_store_price) {
              if ($show_price_with_option_value == true) {
                $this->_attrib_grid .= ($grid_records['V']['options'][$grv]['price'] != 0 ? '<br /><span class="attrib-grid-header-price">'. $attrib_v_price .'</span>' : '<br /><span class="attrib-grid-header-price">' . '</span>');
              } else {
                $this->_attrib_grid .= ($grid_records['V']['options'][$grv]['price'] != 0 ? '</td><td class="attrib-grid-header-price">'. $attrib_v_price : '</td><td class="attrib-grid-header-price">' );
              }
            } else {
              if ($show_price_with_option_value == true) {
                $this->_attrib_grid .= ($grid_records['V']['options'][$grv]['price'] != 0 ? '<br /><span class="attrib-grid-header-price"><a href="'.zen_href_link(FILENAME_LOGIN).'">' . $attrib_v_store_price . '</a></span>' : '<br /><span class="attrib-grid-header-price"><a href="'.zen_href_link(FILENAME_LOGIN).'">' . $attrib_v_store_price . '</a></span>');
              } else {
                $this->_attrib_grid .= ($grid_records['V']['options'][$grv]['price'] != 0 ? '</td><td class="attrib-grid-header-price"><a href="'.zen_href_link(FILENAME_LOGIN).'">' . $attrib_v_store_price . '</a>' : '</td><td class="attrib-grid-header-price"><a href="'.zen_href_link(FILENAME_LOGIN).'">' . $attrib_v_store_price . '</a>');
              }
            }
            // End show price

            $this->_attrib_grid .= '</td>'."\n";
          }

          $attribute_stock_controlled = (defined('TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK') && $show_only_grid);
          $show_attribute_stock = ($attribute_stock_controlled && (zen_get_show_product_switch($_GET['products_id'], 'quantity') == 1 || defined('STOCK_SHOW_ATTRIB_LEVEL_STOCK') && STOCK_SHOW_ATTRIB_LEVEL_STOCK == 'true')); // Displays quantity of attributes if SBA setup to show them or if the product type supports showing the total quantity of product in stock.
          $show_attribute_out_of_stock = ($attribute_stock_controlled && SHOW_PRODUCTS_SOLD_OUT_IMAGE == '1');

          for($grh=0; $grh < $grh_size; $grh++) {
            $attributes_ids = array(  $grid_records['H']['id']  =>  $grid_records['H']['options'][$grh]['id'],
                $grid_records['V']['id']  =>  $grid_records['V']['options'][$grv]['id']
            );
            $products_attribs_id = zen_get_uprid((int)$_GET['products_id'], $attributes_ids);
            $attribute_stock = 'notset';

            if ($attribute_stock_controlled && ($show_attribute_out_of_stock || $show_attribute_stock)) {
              $out_of_stock_button = zen_image_button('attribute_out_of_stock.gif', ALT_ATTRIBUTE_GRID_OUT_OF_STOCK);
              $stock_check_array = array();
              if (zen_not_null($grid_records['H']['options'][$grh]['name'])) $stock_check_array[] = $grid_records['H']['options'][$grh]['id'];
              if (zen_not_null($grid_records['V']['options'][$grv]['name'])) $stock_check_array[] = $grid_records['V']['options'][$grv]['id'];
              $attribute_stock = zen_get_products_stock($_GET['products_id'], $stock_check_array);
            }

            $this->_attrib_grid .= '<td class="attrib-grid-cell" id="attrib-grid-cell-'.$grid_records['H']['options'][$grh]['id'].'-'.$grid_records['V']['options'][$grv]['id'].'">';

            switch(true) {
              case (in_array($grid_records['H']['options'][$grh]['id'], $this->_contact_us_value)):
                $this->_attrib_grid .= $grid_records['H']['options'][$grh]['price'];
                break;
              case (in_array($grid_records['V']['options'][$grv]['id'], $this->_contact_us_value)):
                $this->_attrib_grid .= $grid_records['V']['options'][$grv]['price'];
                break;
              case ($show_attribute_out_of_stock && (float)$attribute_stock <= 0.0):
                $this->_attrib_grid .=  $out_of_stock_button;
                break;
              default:
                $attribute_stock_text = $this->price_display_logged_in($attribute_stock);
                if ($show_attribute_stock == true && $show_attribute_stock_left == true) {
                  $this->_attrib_grid .= ' <span class="attrb-stock-left">'.
                     (($attribute_stock_text == $attribute_stock/*($_SESSION['customer_id'] || CUSTOMERS_APPROVAL == '0'*/) ?
                     sprintf(TEXT_ATTRIBUTE_GRID_IN_STOCK, ($attribute_stock > 5 ? ">5" : $attribute_stock)) 
                     : '<a href="'.zen_href_link(FILENAME_LOGIN).'"> Login </a>')
                          .'</span>' . ($grh_size > 1 ? '' : '</td><td>' );
                }
                $this->_attrib_grid .=  zen_draw_input_field('product_id['.$products_attribs_id.']', '', 'size="3" value="0"').
                    zen_draw_hidden_field('attribs['.$products_attribs_id.']['.$grid_records['H']['id'].']', $grid_records['H']['options'][$grh]['id']).
                    zen_draw_hidden_field('attribs['.$products_attribs_id.']['.$grid_records['V']['id'].']', $grid_records['V']['options'][$grv]['id']);

                if ($show_attribute_stock == true && $show_attribute_stock_left == false) {
                  $this->_attrib_grid .= ($grh_size > 1 ? '' : '</td><td>' ) . ' <span class="attrb-stock-right">'.
                    (($attribute_stock_text == $attribute_stock) ?
                    sprintf(TEXT_ATTRIBUTE_GRID_IN_STOCK, ($attribute_stock > 5 ? ">5" : $attribute_stock))
                    : '<a href="' . zen_href_link(FILENAME_LOGIN) . '"> Login </a>')
                    .'</span>';
                }
                break;
            }
            $this->_attrib_grid .= '</td>';
          }

          if ($grid_records['V']['images'] == true) {
            $this->_attrib_grid .= '<td class="attrib-grid-vImages" id="attrib-grid-vImages-'.$grid_records['V']['options'][$grv]['id'].'">';
            if (zen_not_null($grid_records['V']['options'][$grv]['image'])) {
              $this->_attrib_grid .= zen_image(DIR_WS_IMAGES . $grid_records['V']['options'][$grv]['image']);
            }
            $this->_attrib_grid .= '</td>';
          }
          $this->_attrib_grid .= '</tr>'."\n";
        }

        if ($grid_records['H']['images'] == true) {
          $this->_attrib_grid .= '<tr>'."\n" .
              '  <td></td>';
          for($grh=0; $grh < $grh_size; $grh++) {
            $this->_attrib_grid .= '<td class="attrib-grid-hImages" id="attrib-grid-hImages-'.$grid_records['H']['options'][$grh]['id'].'">';
            if (zen_not_null($grid_records['H']['options'][$grh]['image'])) {
              $this->_attrib_grid .= zen_image(DIR_WS_IMAGES . $grid_records['H']['options'][$grh]['image']);
            }
            $this->_attrib_grid .= '</td>';
          }

          if ($grid_records['V']['images'] == true) {
            $this->_attrib_grid .= '  <td></td>';
          }

          $this->_attrib_grid .= '</tr>';
        }
        $this->_attrib_grid .= '</table>'."\n";
      }
    }
  }


  /*
   * 'NOTIFY_ATTRIBUTES_MODULE_DEFAULT_SWITCH';
   */
  function updateNotifyAttributesModuleDefaultSwitch(&$callingClass, $notifier, $products_options_names_fields, &$options_name, &$options_menu, &$options_comment, &$options_comment_position, &$options_html_id){

    switch (true) {
/****************************************************
/* Absolute-Solutions.co.uk Edit
/*
/* Attributes Grid format
/* 2 of 2
/****************************************************/
      case ($products_options_names_fields['products_options_type'] == PRODUCTS_OPTIONS_TYPE_GRID): // GRID LAYOUT
        //Only show on the first attribute
        if (zen_not_null($this->_attrib_grid)) {
          $options_html_id[] = 'grid-attrib-' . $products_options_names_fields['products_options_id'];;
          $options_name[] = '';
          $options_menu[] = $this->_attrib_grid;
          $options_comment[] = '';
          $options_comment_position[] = '';
          // Then remove the attribute grid so it isn't shown twice
          $this->_attrib_grid = '';
        }
        break;
/****************************************************
/* Absolute-Solutions.co.uk Edit
/*
/* Attributes Grid format
/* END of 2 of 2
/****************************************************/

      default:
        break;
    }
  }

  /*
   * Return the alternate text if prices should not be shown otherwise return the text for the price.
   */
  function price_display_logged_in($price_text) {
    // 0 = normal shopping
    // 1 = Login to shop
    // 2 = Can browse but no prices
    // verify display of prices
    switch (true) {
      case (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == ''):
        // customer must be logged in to browse
        return '';
        break;
      case (CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == ''):
        // customer may browse but no prices
        return TEXT_LOGIN_FOR_PRICE_PRICE;
        break;
      case (CUSTOMERS_APPROVAL == '3' and TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM != ''):
        // customer may browse but no prices
        return TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM;
        break;
      case ((CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and CUSTOMERS_APPROVAL_AUTHORIZATION != '3') and $_SESSION['customer_id'] == ''):
        // customer must be logged in to browse
        return TEXT_AUTHORIZATION_PENDING_PRICE;
        break;
      case ((CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and CUSTOMERS_APPROVAL_AUTHORIZATION != '3') and $_SESSION['customers_authorization'] > '0'):
        // customer must be logged in to browse
        return TEXT_AUTHORIZATION_PENDING_PRICE;
        break;
      case ((int)$_SESSION['customers_authorization'] == 2):
        // customer is logged in and was changed to must be approved to see prices
        return TEXT_AUTHORIZATION_PENDING_PRICE;
        break;
      default:
        // proceed normally
        break;
    }

// show case only
    if (STORE_STATUS != '0') {
      if (STORE_STATUS == '1') {
        return '';
      }
    }

    return $price_text;
  }

  /*
   * Generic function that is activated when any notifier identified in the observer is called but is not found in one of the above previous specific update functions is encountered as a notifier.
   */
  function update(&$callingClass, $notifier, $paramsArray) {
    global $db;

    if ($notifier == 'NOTIFY_ATTRIBUTES_MODULE_START_OPTIONS_LOOP') {
      global $products_options_fields;
      $this->updateNotifyAttributesModuleStartOptionsLoop($callingClass, $notifier, $paramsArray, $products_options_fields);
    }
    
    if ($notifier == 'NOTIFY_ATTRIBUTES_MODULE_OPTION_BUILT') {
      global $options_name, $options_menu, $options_comment,
             $options_comment_position, $options_html_id, $options_attributes_image; 
      
      $this->updateNotifyAttributesModuleOptionBuilt($callingClass, $notifier, $paramsArray,
                                                   $options_name, $options_menu, $options_comment,
                                                   $options_comment_position, $options_html_id,
                                                   $options_attributes_image);
    }

    if ($notifier == 'NOTIFY_ATTRIBUTES_MODULE_DEFAULT_SWITCH') {
      global $options_name, $options_menu, $options_comment, $options_comment_position, $options_html_id;
      
      $this->updateNotifyAttributesModuleDefaultSwitch($callingClass, $notifier, $paramsArray, $options_name, $options_menu, $options_comment, $options_comment_position, $options_html_id);
    }
  } //end update function - mc12345678
} //end class - mc12345678
