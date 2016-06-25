<?php

$renumber = false; // $renumber = true or false 
  //  true:  when a gridded product has a file upload option, the file
  //    uploaded will be copied to the next "product" with a new 
  //    reference number.  This is something that perhaps is desired if
  //    after upload each product might be modified in a different way
  //    to achieve a different result for each (resized photo for a 
  //    smaller object than a larger one, or some modified document, etc.)
  //  false: when a gridded product has a file upload option, the file
  //    uploaded will be assigned to the "first" product, all other product
  //    added by use of the grid will have the same exact file 
  //    referenced in the records for that product.  A change to the
  //    uploaded file is expected to apply to all product that were added
  //    as part of the add-to-cart of a gridded product.

switch ($_GET['action']) {
    case 'add_product':
        if (isset($_POST['product_id']) && is_array($_POST['product_id']) && (function_exists('zen_product_is_sba') ? !zen_product_is_sba($_POST['products_id']) : true)) {
            
            if ($_SESSION['cart']->display_debug_messages) $messageStack->add_session('header', 'A: FUNCTION ' . __FUNCTION__, 'caution');
// Here can add product attributes grid check and prepare to iterate through the "products" that have been added.
// if is a multi-product add, then capture/process the necessary data to be able to assign each $_POST['products_id'], $_POST['id'],
// and $_POST['cart_quantity'].  $_POST['products_id'] is expected to be relatively the same for each product (ie. the product's number only).
// the 'id' is expected to be all of the attributes associated with the product and 'cart_quantity' will end up being the total
// quantity of a product, ie. carts_quantity times the number entered in the individual field box.  This way the quantity box will
// have a default value if not shown, but if shown then multiples of the selected number of "groups" will be added to the cart.
//  In this section, want to also be sure to add/maintain the 'id's in the order that would be expected without this additional
//  feature so that all future manipulations work out correctly.
            /* Test to see if is a grid related submission/product*/
            /* Do additional prestage work for grid related submission/product*/
            $grid_prod_id = array();
            $grid_id = array();
            $prod_qty = array();
            $grid_add_number = 0;
            
            
            // if SBA is installed then it ws not tracked by SBA otherwise it is PAG
            
            foreach ($_POST['product_id'] as $prid => $qty) {
                $products_id = zen_get_prid($prid);
                
                $option_ref = array();
                
                if (!is_numeric($qty) || $qty < 0) {
                    // adjust quantity when not a value
                    //$_SESSION['non_sub_qty_'.$prid] = $qty;
                    $chk_link = '<a href="' . zen_href_link(zen_get_info_page($products_id), 'cPath=' . (zen_get_generated_category_path_rev(zen_get_products_category_id($products_id))) . '&products_id=' . $products_id) . '">' . zen_get_products_name($products_id) . '</a>';
                    $messageStack->add_session('header', ERROR_CORRECTIONS_HEADING . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . $chk_link . ' ' . PRODUCTS_ORDER_QTY_TEXT . zen_output_string_protected($qty), 'caution');
                    $qty = 0;
                }
                
                if (isset($_POST['id']) && is_array($_POST['id'])) { // This is to fix/setup attribs if needed.
                    foreach ($_POST['id'] as $option => $option_value) {
                        $_POST['attribs'][$prid][$option] = $option_value;
                    }
                }
                
                if (isset($_GET['number_of_uploads']) && $_GET['number_of_uploads'] > 0) {
                    for ($iFile = 1, $nFile = $_GET['number_of_uploads']; $iFile <= $nFile; $iFile++) {
                        if (zen_not_null($_FILES['id']['tmp_name'][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $iFile]]) and ($_FILES['id']['tmp_name'][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $iFile]] != 'none')) {
                            $_POST['attribs'][$prid][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $iFile]] = $iFile . ". ";
                        } else { // No file uploaded -- use previous value
                            $_POST['attribs'][$prid][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $iFile]] = $_POST[TEXT_PREFIX . UPLOAD_PREFIX . $iFile];
                        }
                    }
                }
                
                foreach ($_POST['attribs'][$prid] as $option_id => $value_id) {
                    if (substr($option_id, 0, strlen(TEXT_PREFIX)) == TEXT_PREFIX) {
                        $option_ref[substr($option_id, strlen(TEXT_PREFIX))] = $option_id;
                        $option_id = substr($option_id, strlen(TEXT_PREFIX));
                    } elseif (substr($option_id, 0, strlen(FILE_PREFIX)) == FILE_PREFIX) {
                        $option_ref[substr($option_id, strlen(FILE_PREFIX))] = $option_id;
                        $option_id = substr($option_id, strlen(FILE_PREFIX));
                    } else {
                        $option_ref[$option_id] = $option_id;
                    }
                    $check_attrib = $db->Execute("select pov.products_options_values_name from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov " .
                        "where pa.options_values_id = pov.products_options_values_id " .
                        "and pa.options_id = '" . (int)$option_id . "' " .
                        "and pa.products_id = '" . (int)$products_id . "' " .
                        "and pov.language_id = '" . (int)$_SESSION['languages_id'] . "'");
                    if ($check_attrib->RecordCount() <= 1 && $check_attrib->fields['products_options_values_name'] == '') {
                        unset($_POST['attribs'][$prid][$option_id]);  // Not sure why it matters if the value has a name or not. mc12345678
                    }
                }
                
                if (!is_numeric($_POST['cart_quantity']) || $_POST['cart_quantity'] < 0) {
                    // adjust quantity when not a value
                    $chk_link = '<a href="' . zen_href_link(zen_get_info_page($products_id), 'cPath=' . (zen_get_generated_category_path_rev(zen_get_products_category_id($products_id))) . '&products_id=' . $products_id) . '">' . zen_get_products_name($products_id) . '</a>';
                    $messageStack->add_session('header', ERROR_CORRECTIONS_HEADING . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . $chk_link . ' ' . PRODUCTS_ORDER_QTY_TEXT . zen_output_string_protected($_POST['cart_quantity']), 'caution');
                    $_POST['cart_quantity'] = 0;
                }
                if (is_numeric($qty) && zen_not_null($qty) && $qty > 0) {
                    reset($_POST['attribs'][$prid]);
                    // End result on the side with grid is to set $_POST['id'] = $_POST['attribs'][$prid]
                    // and then move to the next item.
                    if (PRODUCTS_OPTIONS_SORT_ORDER == '0') {
                        $options_order_by = ' order by LPAD(popt.products_options_sort_order,11,"0"), popt.products_options_name';
                    } else {
                        $options_order_by = ' order by popt.products_options_name';
                    }
                    
                    //get the option/attribute list
                    $sql = "select distinct popt.products_options_id, popt.products_options_name, popt.products_options_sort_order,
                              popt.products_options_type, popt.products_options_length, popt.products_options_comment,
                              popt.products_options_size,
                              popt.products_options_images_per_row,
                              popt.products_options_images_style,
                              popt.products_options_rows
              from        " . TABLE_PRODUCTS_OPTIONS . " popt
              left join " . TABLE_PRODUCTS_ATTRIBUTES . " patrib ON (patrib.options_id = popt.products_options_id)
              where patrib.products_id= :products_id:
              and popt.language_id = :languages_id: " .
                        $options_order_by;
                    
                    $sql = $db->bindVars($sql, ':products_id:', $prid, 'integer');
                    $sql = $db->bindVars($sql, ':languages_id:', $_SESSION['languages_id'], 'integer');
                    $products_options_sequence = $db->Execute($sql);
                    
                    $grid_id2 = array();
                    while (!$products_options_sequence->EOF) {
                        $grid_id2[$option_ref[$products_options_sequence->fields['products_options_id']]] = $_POST['attribs'][$prid][$option_ref[$products_options_sequence->fields['products_options_id']]];
                        $products_options_sequence->MoveNext();
                    }
                    
                    $grid_id[] = $grid_id2;
                    $prod_qty[] = $qty * $_POST['cart_quantity'];
                    $grid_prod_id[] = $products_id;
                    $grid_add_number++;
                }
            }
            
            if (sizeof($grid_id) < 1 || sizeof($prod_qty) < 1 || sizeof($grid_prod_id) < 1) {
                $grid_prod_id[0] = null;
                $prod_qty[0] = 0;
                $grid_add_number = 0;
            }
            
            if (sizeof($grid_id) < 1) {
                // no grid item, so make the first data record be null.
                $grid_id[0] = null;
            }
            if (sizeof($grid_id) == 1 && is_null($grid_id[0]) && (!isset($_GET['number_of_uploads']) || (isset($_GET['number_of_uploads']) && $_GET['number_of_uploads'] == 0))) {
                $grid_add_number = 0;
            }
            
            if (sizeof($prod_qty) < 1) {
                $prod_qty[0] = 0;
                $grid_add_number = 0;
            }
            
            if (sizeof($grid_prod_id) < 1) {
                $grid_prod_id[0] = null;
                $grid_add_number = 0;
            }
            
            if (is_array($fileVar) && array_key_exists('file_located', $fileVar)) {
                unset($fileVar['file_located']);
                $fileVar = array();
            } elseif (!is_array($fileVar)) {
                $fileVar = array();
            }
            
            $grid_loop = 0;
            
            while ($grid_loop++ <= $grid_add_number) {
                $_POST['products_id'] = $grid_prod_id[$grid_loop - 1];
                $_POST['id'] = $grid_id[$grid_loop - 1];
                $_POST['cart_quantity'] = $prod_qty[$grid_loop - 1];
                if (isset($_POST['products_id']) && is_numeric($_POST['products_id'])) {
//Loop for each product in the cart
                    if ($_SESSION['cart']->display_debug_messages) $messageStack->add_session('header', 'A2: FUNCTION ' . __FUNCTION__, 'caution');
                    $the_list = '';
                    $adjust_max = 'false';
                    if (isset($_POST['id'])) {
                        foreach ($_POST['id'] as $key => $value) {
                            $check = zen_get_attributes_valid($_POST['products_id'], $key, $value);
                            if ($check == false) {
                                $the_list .= TEXT_ERROR_OPTION_FOR . '<span class="alertBlack">' . zen_options_name($key) . '</span>' . TEXT_INVALID_SELECTION . '<span class="alertBlack">' . ($value == (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID ? TEXT_INVALID_USER_INPUT : zen_values_name($value)) . '</span>' . '<br />';
                            }
                        }
                    }
                    if (!is_numeric($_POST['cart_quantity']) || $_POST['cart_quantity'] < 0) {
                        // adjust quantity when not a value
                        $chk_link = '<a href="' . zen_href_link(zen_get_info_page($_POST['products_id']), 'cPath=' . (zen_get_generated_category_path_rev(zen_get_products_category_id($_POST['products_id']))) . '&products_id=' . $_POST['products_id']) . '">' . zen_get_products_name($_POST['products_id']) . '</a>';
                        $messageStack->add_session('header', ERROR_CORRECTIONS_HEADING . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . $chk_link . ' ' . PRODUCTS_ORDER_QTY_TEXT . zen_output_string_protected($_POST['cart_quantity']), 'caution');
                        $_POST['cart_quantity'] = 0;
                    }
                    $attr_list = array();
                    $attr_dash = array();
                    $attr_id = array();
                    $attr_val = array();
                    
                    $attributes = (isset($_POST['id']) && zen_not_null($_POST['id']) ? $_POST['id'] : null);
                    // to address product with maleable attributes where the attribute
                    // is not stock dependent (text field) product_id needs to reflect
                    // the appropriate designation as built using the appropriate $attributes.
                    //  This would take a refactoring of entered text as if it was absent. mc12345678 01-02-2016
                    
                    // Need to get the file related information into the $attributes related data.
                    if (isset($_GET['number_of_uploads']) && $_GET['number_of_uploads'] > 0) {
                        
                        for ($iFile = 1, $nFile = $_GET['number_of_uploads']; $iFile <= $nFile; $iFile++) {
                            if (zen_not_null($_FILES['id']['tmp_name'][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $iFile]]) and ($_FILES['id']['tmp_name'][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $iFile]] != 'none')) {
                                
                                //  The line below did have $insert_id in place of $iFile on the right side; however, with removal of the
                                //   storage information, that number has been "lost".
                                $attributes[TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $iFile]] = $iFile . ". " /*. $products_options_file->filename*/
                                ;
                            } else { // No file uploaded -- use previous value
                                $attributes[TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $iFile]] = $_POST[TEXT_PREFIX . UPLOAD_PREFIX . $iFile];
                            }
                        }
                    }
                    
                    $product_id = zen_get_uprid($_POST['products_id'], $attributes);
                    
                    $add_max = zen_get_products_quantity_order_max($_POST['products_id']);
                    // to address product with maleable attributes where the attribute
                    // is not stock dependent cart_qty needs to reflect the appropriate number.  mc12345678 01-02-2016
                    
                    $cart_qty = $_SESSION['cart']->get_quantity($product_id);
                    
                    if ($_SESSION['cart']->display_debug_messages) $messageStack->add_session('header', 'B: FUNCTION ' . __FUNCTION__ . ' Products_id: ' . $_POST['products_id'] . ' cart_qty: ' . $cart_qty . ' $_POST[cart_quantity]: ' . $_POST['cart_quantity'] . ' <br>', 'caution');
                    
                    $new_qty = $_POST['cart_quantity']; //Number of items being added (Known to be SBA tracked already)
                    $new_qty = $_SESSION['cart']->adjust_quantity($new_qty, $_POST['products_id'], 'header');

// bof: adjust new quantity to be same as current in stock
                    $chk_current_qty = zen_get_products_stock($product_id);
                    $_SESSION['cart']->flag_duplicate_msgs_set = FALSE;
                    
                    if (STOCK_ALLOW_CHECKOUT == 'false' && ($cart_qty + $new_qty > $chk_current_qty)) {
                        $new_qty = $chk_current_qty;
                        $messageStack->add_session('shopping_cart', ($_SESSION['cart']->display_debug_messages ? 'C: FUNCTION ' . __FUNCTION__ . ': ' : '') . WARNING_PRODUCT_QUANTITY_ADJUSTED . zen_get_products_name($_POST['products_id']), 'caution');
                        $_SESSION['cart']->flag_duplicate_msgs_set = TRUE;
                    }
                    // eof: adjust new quantity to be same as current in stock
                    if (($add_max == 1 and $cart_qty == 1)) {
                        // do not add
                        $new_qty = 0;
                        $adjust_max = 'true';
                    } else {
// bof: adjust new quantity to be same as current in stock
                        if (STOCK_ALLOW_CHECKOUT == 'false' && ($new_qty + $cart_qty > $chk_current_qty)) {
                            $adjust_new_qty = 'true';
                            $alter_qty = $chk_current_qty - $cart_qty;
                            $new_qty = ($alter_qty > 0 ? $alter_qty : 0);
                            if (!$_SESSION['cart']->flag_duplicate_msgs_set) {
                                $messageStack->add_session('shopping_cart', ($_SESSION['cart']->display_debug_messages ? 'D: FUNCTION ' . __FUNCTION__ . ': ' : '') . WARNING_PRODUCT_QUANTITY_ADJUSTED . zen_get_products_name($_POST['products_id']), 'caution');
                            }
                        }
// eof: adjust new quantity to be same as current in stock
                        // adjust quantity if needed
                        if (($new_qty + $cart_qty > $add_max) and $add_max != 0) {
                            $adjust_max = 'true';
                            $new_qty = $add_max - $cart_qty;
                        }
                    }
                    if ((zen_get_products_quantity_order_max($_POST['products_id']) == 1 and $_SESSION['cart']->in_cart_mixed($_POST['products_id']) == 1)) {
                        
                        // do not add
                    } else {
                        // process normally
                        // bof: set error message
                        if ($the_list != '') {
                            $messageStack->add('product_info', ERROR_CORRECTIONS_HEADING . $the_list, 'caution');
                        } else {
                            // process normally
                            // iii 030813 added: File uploading: save uploaded files with unique file names
                            $real_ids = isset($_POST['id']) ? $_POST['id'] : "";
                            if (isset($_GET['number_of_uploads']) && $_GET['number_of_uploads'] > 0) {
                                /**
                                 * Need the upload class for attribute type that allows user uploads.
                                 *
                                 */
                                include_once(DIR_WS_CLASSES . 'upload.php');
                                for ($i = 1, $n = $_GET['number_of_uploads']; $i <= $n; $i++) {
                                    if (zen_not_null($_FILES['id']['tmp_name'][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i]]) and ($_FILES['id']['tmp_name'][TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i]] != 'none')) {
                                        if ($grid_loop == 1) {
                                            $products_options_file = new upload('id');
                                            $products_options_file->set_destination(DIR_FS_UPLOADS);
                                            $products_options_file->set_output_messages('session');
                                        }
                                        if ($grid_loop > 1 || $products_options_file->parse(TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i])) {
                                            $products_image_extension = substr($products_options_file->filename, strrpos($products_options_file->filename, '.'));
                                            if ($grid_loop == 1 || $renumber) {
                                                if ($_SESSION['customer_id']) {
                                                    $db->Execute("insert into " . TABLE_FILES_UPLOADED . " (sesskey, customers_id, files_uploaded_name) values('" . zen_session_id() . "', '" . $_SESSION['customer_id'] . "', '" . zen_db_input($products_options_file->filename) . "')");
                                                } else {
                                                    $db->Execute("insert into " . TABLE_FILES_UPLOADED . " (sesskey, files_uploaded_name) values('" . zen_session_id() . "', '" . zen_db_input($products_options_file->filename) . "')");
                                                }
                                                $insert_id = $db->Insert_ID();
                                            }
                                            if ($grid_loop < $grid_add_number) {
                                                // copy the file to another location
                                                // Set a flag that will indicate that it should be copied back
                                                // Earlier need to copy the file back to this location if the flag is set.
                                                // copy();
                                                
                                                if ($grid_loop == 1) {
                                                    $fileVar[$grid_loop][$i]['id'] = $insert_id;
                                                    $fileVar[$grid_loop][$i]['name'] = $products_options_file->filename;
                                                    $fileVar[$grid_loop][$i]['destination'] = $products_options_file->destination;
                                                    $fileVar[$grid_loop][$i]['extension'] = $products_image_extension;
                                                    $products_options_file->filename = $fileVar[$grid_loop][$i]['name'];
                                                } else {
                                                    $fileVar[$grid_loop][$i]['id'] = $fileVar[$grid_loop - 1][$i]['id'];
                                                    if ($renumber) {
                                                        $fileVar[$grid_loop][$i]['id'] = $insert_id;
                                                    }
                                                    $fileVar[$grid_loop][$i]['name'] = $fileVar[$grid_loop - 1][$i]['name'];
                                                    $fileVar[$grid_loop][$i]['destination'] = $fileVar[$grid_loop - 1][$i]['destination'];
                                                    $fileVar[$grid_loop][$i]['extension'] = $fileVar[$grid_loop - 1][$i]['extension'];
                                                    $products_options_file->filename = $fileVar[$grid_loop][$i]['name'];
                                                    $products_image_extension = $fileVar[$grid_loop][$i]['extension'];
                                                    if (!$renumber) {
                                                        $insert_id = $fileVar[$grid_loop][$i]['id'];
                                                    }
                                                }
                                                $fileVar['file_located'] = true;
                                            } else {
                                                // Clear flag
                                                if ($grid_loop == 1) {
                                                } else {
                                                    $fileVar[$grid_loop][$i]['id'] = $fileVar[$grid_loop - 1][$i]['id'];
                                                    if ($renumber) {
                                                        $fileVar[$grid_loop][$i]['id'] = $insert_id;
                                                    }
                                                    $fileVar[$grid_loop][$i]['name'] = $fileVar[$grid_loop - 1][$i]['name'];
                                                    $fileVar[$grid_loop][$i]['destination'] = $fileVar[$grid_loop - 1][$i]['destination'];
                                                    $fileVar[$grid_loop][$i]['extension'] = $fileVar[$grid_loop - 1][$i]['extension'];
                                                    $products_options_file->filename = $fileVar[$grid_loop][$i]['name'];
                                                    $products_image_extension = $fileVar[$grid_loop][$i]['extension'];
                                                    if (!$renumber) {
                                                        $insert_id = $fileVar[$grid_loop][$i]['id'];
                                                    }
                                                }
                                            }
                                            $real_ids[TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i]] = $insert_id . ". " . $products_options_file->filename;
                                            $products_options_file->set_filename("$insert_id" . $products_image_extension);
                                            if ($grid_loop == 1 && !($products_options_file->save())) {
                                                $fileVar[$grid_loop][$i]['failed'] = true;
                                                break;
                                            } else {
                                                if ($grid_loop == 1) {
                                                    $fileVar[$grid_loop][$i]['location'] = $products_options_file;
                                                } elseif ($grid_loop > 1 && array_key_exists('failed', $fileVar[$grid_loop - 1][$i]) && $fileVar[$grid_loop - 1][$i]['failed']) {
                                                    $fileVar[$grid_loop][$i]['failed'] = $fileVar[$grid_loop - 1][$i]['failed'];
                                                    break;
                                                } elseif ($grid_loop > 1 && !array_key_exists('failed', $fileVar[$grid_loop - 1][$i])) {
                                                    $fileVar[$grid_loop][$i]['location'] = $products_options_file;
                                                    copy($fileVar[$grid_loop - 1][$i]['destination'] . $fileVar[$grid_loop - 1][$i]['id'] . $fileVar[$grid_loop - 1][$i]['extension'], $fileVar[$grid_loop][$i]['destination'] . $fileVar[$grid_loop][$i]['id'] . $fileVar[$grid_loop][$i]['extension']);
                                                }
                                            }
                                        } else {
                                            break;
                                        }
                                    } else { // No file uploaded -- use previous value
                                        $real_ids[TEXT_PREFIX . $_POST[UPLOAD_PREFIX . $i]] = $_POST[TEXT_PREFIX . UPLOAD_PREFIX . $i];
                                    }
                                }
                            }
                            
                            $_SESSION['cart']->add_cart($_POST['products_id'], $_SESSION['cart']->get_quantity(zen_get_uprid($_POST['products_id'], $real_ids)) + ($new_qty), $real_ids);
                            // iii 030813 end of changes.
                        } // eof: set error message
                    } // eof: quantity maximum = 1
                    
                    if ($adjust_max == 'true') {
                        $messageStack->add_session('shopping_cart', ERROR_MAXIMUM_QTY . zen_get_products_name($_POST['products_id']), 'caution');
                        if ($_SESSION['cart']->display_debug_messages) $messageStack->add_session('header', 'E: FUNCTION ' . __FUNCTION__ . '<br>' . ERROR_MAXIMUM_QTY . zen_get_products_name($_POST['products_id']), 'caution');
                    }
                    
                    // Want to bypass this entire section if not done with addressing all of the products, though also may need to pull out some of
                    //  the actions so that all products are addressed, but basically do not want to redirect away from this operation until the
                    //  last object has been addressed.  Maybe just need to if around the redirects and leave the add_session information
                    if ($the_list == '') {
                        // no errors
                        // display message if all is good and not on shopping_cart page
                        if (DISPLAY_CART == 'false' && $_GET['main_page'] != FILENAME_SHOPPING_CART && $messageStack->size('shopping_cart') == 0) {
                            $messageStack->add_session('header', ($_SESSION['cart']->display_debug_messages ? 'FUNCTION ' . __FUNCTION__ . ': ' : '') . SUCCESS_ADDED_TO_CART_PRODUCT, 'success');
                            if ($grid_loop == $grid_add_number) {
                                zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
                            }
                        } else {
                            if ($grid_loop == $grid_add_number) {
                                zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
                            }
                        }
                    } else {
                        // errors found with attributes - perhaps display an additional message here, using an observer class to add to the messageStack
                        $_SESSION['cart']->notify('NOTIFIER_CART_OPTIONAL_ATTRIBUTE_ERROR_MESSAGE_HOOK', $_POST, $the_list);
                        $_GET['action'] = '';
                    }
                    
                }
            } // EOF while(grid_loop++ <= $grid_add_number
            
            unset($_POST['products_id']);
            $messageStack->reset();
            unset($_SESSION['cart_errors']);
            $_SESSION['cart']->get_products(false);  //Update all prices now we have added everything
        }
        break;
}
