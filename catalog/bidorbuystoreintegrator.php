<?php

/**
 * Copyright (c) 2014, 2015, 2016 Bidorbuy http://www.bidorbuy.co.za
 * This software is the proprietary information of Bidorbuy.
 *
 * All Rights Reserved.
 * Modification, redistribution and use in source and binary forms, with or without modification
 * are not permitted without prior written approval by the copyright holder.
 *
 * Vendor: EXTREME IDEA LLC http://www.extreme-idea.com
 */
use com\extremeidea\bidorbuy\storeintegrator\core as bobsi;

// if we make a post, zencart tries to check
//
// $mainPage = isset($_GET['main_page']) ? $_GET['main_page'] : FILENAME_DEFAULT;
// if (!in_array($mainPage, $csrfBlackList)) ... redirect to index page.
//
// So, we need to ensure the 'index' is a part of $csrfBlackList before we include '/includes/application_top.php'.

$csrfBlackListCustom[] = 'index';
require_once(dirname(__FILE__) . '/includes/application_top.php');

require_once(dirname(__FILE__) . '/includes/modules/bidorbuystoreintegrator/factory.php');

$shipmentClasses = null;
$paymentModules = null;

$action = (isset($_REQUEST['action'])) ? zen_sanitize_string($_REQUEST['action']) : false;
if (zen_not_null($action)) {
    switch ($action) {
        case 'export':
            bobsi_export();
            break;
        case 'download':
            bobsi_download();
            break;
        case 'downloadl':
            bobsi_downloadl();
            break;
        case 'version':
            bobsi_version();
            break;
        default:
            zen_exit();
            break;
    }
}

function bobsi_export() {
    $token = isset($_REQUEST[bobsi\Settings::paramToken]) ? $_REQUEST[bobsi\Settings::paramToken] : false;
    $productsIds = isset($_REQUEST[bobsi\Settings::paramIds]) ? $_REQUEST[bobsi\Settings::paramIds] : false;

    $exportConfiguration = array(
        bobsi\Settings::paramIds => $productsIds,
        bobsi\Tradefeed::settingsNameExcludedAttributes => array('Width', 'Height', 'Length'),
        bobsi\Settings::paramCallbackGetProducts => 'bobsi_get_all_products',
        bobsi\Settings::paramCallbackGetBreadcrumb => 'bobsi_get_breadcrumb',
        bobsi\Settings::paramCallbackExportProducts => 'bobsi_export_products',
        bobsi\Settings::paramExtensions => getPaymentModules(),

        bobsi\Settings::paramCategories => bobsi_get_export_categories_ids(bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getExcludeCategories()),

    );

    bobsi\StaticHolder::getBidorbuyStoreIntegrator()->export($token, $exportConfiguration);
}

function bobsi_download() {
    $token = isset($_REQUEST[bobsi\Settings::paramToken]) ? $_REQUEST[bobsi\Settings::paramToken] : false;
    bobsi\StaticHolder::getBidorbuyStoreIntegrator()->download($token);
}

function bobsi_downloadl() {
    $token = isset($_REQUEST[bobsi\Settings::paramToken]) ? $_REQUEST[bobsi\Settings::paramToken] : false;
    bobsi\StaticHolder::getBidorbuyStoreIntegrator()->downloadl($token);
}

function bobsi_version() {
    $token = isset($_REQUEST[bobsi\Settings::paramToken]) ? $_REQUEST[bobsi\Settings::paramToken] : false;
    $phpinfo = isset($_REQUEST['phpinfo']) ? $_REQUEST['phpinfo'] : 'n';
    bobsi\StaticHolder::getBidorbuyStoreIntegrator()->showVersion($token, 'y' == $phpinfo);
}

function bobsi_get_export_categories_ids($ids = array()) {
    $categories = zen_get_categories();

    $categories_ids = array();
    if (!empty($categories)) {
        foreach ($categories as $cat) {
            $categories_ids[] = $cat['id'];
        }
    } else {
        return true;
    }
    return array_values(array_diff($categories_ids, $ids));
}

function bobsi_get_breadcrumb($categoryId) {
    $categoryId = ($categoryId === 0) ? '0' : (string)$categoryId;
    //Avoid the problem in Google Chrome with opening tradefeed
    $path = str_replace('&nbsp;', ' ', zen_output_generated_category_path($categoryId));

    return html_entity_decode($path);
}

function bobsi_get_products(&$exportConfiguration = array()) {
    $itemsPerIteration = intval($exportConfiguration[bobsi\Settings::paramItemsPerIteration]);
    $iteration = intval($exportConfiguration[bobsi\Settings::paramIteration]);
    $categoryId = $exportConfiguration[bobsi\Settings::paramCategoryId];

    //zen_get_categories_products_list() accumulates products from each iterated category. We should clear the variable to avoid the problem.
    global $categories_products_id_list;
    $categories_products_id_list = array();

    $products_in_cat = zen_get_categories_products_list($categoryId, false, false);

    $active_products = array();
    foreach ($products_in_cat as $pid => $cid) {
        if (zen_products_lookup($pid, 'products_status') === '1') {
            $active_products[] = $pid;
        }
    }

    $products_slice = array_slice($active_products, $itemsPerIteration * $iteration, $itemsPerIteration);

    return $products_slice;
}

function bobsi_get_all_products() {
    // zen_get_categories_products_list() accumulates products from each iterated category. We should clear the variable to avoid the problem.
    global $categories_products_id_list;
    $categories_products_id_list = array();

    $active_products = array();

    $allCategories = zen_get_categories();
    foreach ($allCategories as $category) {
        $products_in_cat = zen_get_categories_products_list($category['id'], false, false);

        foreach ($products_in_cat as $pid => $cid) {
            if (zen_products_lookup($pid, 'products_status') === '1') {
                $active_products[] = $pid;
            }
        }
    }

    return array_unique($active_products);
}

function bobsi_export_products($productsIds, $exportConfiguration = array()) {
    global $db;

    $exportQuantityMoreThan = bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getExportQuantityMoreThan();
    $defaultStockQuantity = bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getDefaultStockQuantity();

    $exportProducts = array();
    foreach ($productsIds as $productId) {
        $categoriesMatching = in_array(zen_get_products_category_id($productId), $exportConfiguration[bobsi\Settings::paramCategories]);

        if (bobsi_calc_product_quantity($productId, $defaultStockQuantity) > $exportQuantityMoreThan AND $categoriesMatching) {

            //Attrs and variations
            $variations = array();
            $sortVariations = array();
            $readonly = array();

            if (zen_has_product_attributes($productId)) {

                $sql = "select
                products_id,
                products_attributes_id,
                options_id,
                products_options_name,
                options_values_id,
                products_options_values_name,
                options_values_price,
                price_prefix,
                attributes_discounted,
                attributes_price_factor,
                attributes_price_factor_offset,
                p.attributes_default,
                attributes_image,
                pd.products_options_sort_order,
                products_options_type
             from " . TABLE_PRODUCTS_ATTRIBUTES . " p, " .
                    TABLE_PRODUCTS_OPTIONS . " pd, " .
                    TABLE_PRODUCTS_OPTIONS_VALUES . " pdd
             where    p.products_id = '" . $productId . "'
                and      pd.products_options_id = p.options_id
                and      pdd.products_options_values_id = p.options_values_id
             ;";

                $res = $db->Execute($sql);

                while (!$res->EOF) {

                    $sortVariations[$res->fields['options_id']][] = array(
                        'id' => $res->fields['products_attributes_id'],
                        'options_id' => $res->fields['options_id'],
                        'options_values_id' => $res->fields['options_values_id'],
                        'attr_title' => $res->fields['products_options_name'],
                        'name' => $res->fields['products_options_values_name'],
                        'adjustment' => floatval($res->fields['options_values_price']) *
                            ($res->fields['price_prefix'] === '-' ? -1 : 1),
                        'discounted' => $res->fields['attributes_discounted'],
                        'factor' => floatval($res->fields['attributes_price_factor']),
                        'offset' => floatval($res->fields['attributes_price_factor_offset']),
                        'image' => DIR_WS_IMAGES . $res->fields['attributes_image'],
                        'sort_order' => $res->fields['products_options_sort_order'],
                        'type' => $res->fields['products_options_type'],
                        'is_default' => $res->fields['attributes_default']
                    );
                    $res->MoveNext();
                }

                //Handle checkboxes (type=3) and ReadOnly (type=5) attr types
                $temp = $sortVariations;

                foreach ($temp as $id => $values) {
                    if ($values[0]['type'] === '3') {
                        //Get combinations (power set) of checkboxes values
                        $combinations = bobsi_power_set($values, 2);
                        foreach ($combinations as $comb) {
                            $sortVariations[$id][] = $comb;
                        }
                        //Add dummy value: if no one checkbox is checked
                        $sortVariations[$id][] = array(
                            'id' => '' . $id,
                            'attr_title' => $values[0]['attr_title'],
                            'name' => '',
                            'adjustment' => 0,
                            'discounted' => '0',
                            'factor' => floatval(0),
                            'offset' => floatval(0),
                            'image' => '/',
                            'sort_order' => '0',
                            'type' => $values[0]['type']
                        );
                        //ReadOnly attrs don't take a part in variations.
                        //We should add all of them into each variation
                    } elseif ($values[0]['type'] === '5') {
                        $readonly[] = $values;
                        unset($sortVariations[$id]);

                        //Attr File cann't be exported
                    } elseif ($values[0]['type'] === '4') {
                        unset($sortVariations[$id]);
                    }

                }

                $variations = bobsi_array_cartesian($sortVariations);
            }

            //If variation available - process it as independent product
            if (!empty($variations) || !empty($readonly)) {
                foreach ($variations as $variation) {
                    $p = bobsi_build_export_product($productId, array_merge($variation, $readonly));
                    if (intval($p[bobsi\Tradefeed::nameProductPrice]) > 0) {
                        $exportProducts[] = $p;
                    } else {
                        bobsi\StaticHolder::getBidorbuyStoreIntegrator()->logInfo('Product price <= 0, skipping, product id: ' . $productId);
                    }
                }
            } else {
                $p = bobsi_build_export_product($productId);
                if (intval($p[bobsi\Tradefeed::nameProductPrice]) > 0) {
                    $exportProducts[] = $p;
                } else {
                    bobsi\StaticHolder::getBidorbuyStoreIntegrator()->logInfo('Product price <= 0, skipping, product id: ' . $productId);
                }
            }
        } else {
            bobsi\StaticHolder::getBidorbuyStoreIntegrator()->logInfo('Product does not satisfy published requirements, product id: ' . $productId);
        }
    }

    return $exportProducts;
}

function &bobsi_build_export_product(&$product_id, $variations = array()) {
    global $db;

    // Create the plane array of variations (it's necessary if the product has checkboxes)
    $newOrderOfVariations = array();
    foreach ($variations as $variation) {
        if (isset($variation[0])) {
            foreach ($variation as $checkboxValue) {
                $newOrderOfVariations[] = $checkboxValue;
            }
        } else {
            $newOrderOfVariations[] = $variation;
        }
    }
    $variations = $newOrderOfVariations;

    bobsi_sort_variations($variations);
    $exportedProduct = array();
    $attrs = array();

    $attrs[] = array('name' => 'Brand', 'value' => zen_get_products_manufacturers_name($product_id));

    $sku = $product_id;
    $variationImages = array();
    $options_array = array();
    $added_change = 0;
    $added_weight = 0;
    if (!empty($variations)) {
        $sku .= '-';
        foreach ($variations as $variation) {
            $sku .= $variation['id'];
            $attrs[] = array('name' => $variation['attr_title'], 'value' => $variation['name']);

            if (defined('DIR_WS_IMAGES') && $variation['image'] !== DIR_WS_IMAGES) {
                $variationImages[] = $variation['image'];
            }

            $options_array[$variation['options_id']] = $variation['options_values_id'];
        }

        // Calculate price change which is set in product attributes
        $shoppingCart = new shoppingCart();
        $shoppingCart->contents[$product_id]['attributes'] = $options_array;
        $added_change = $shoppingCart->attributes_price($product_id);
        $added_weight = $shoppingCart->attributes_weight($product_id);
    }

    $exportedProduct[bobsi\Tradefeed::nameProductId] = $product_id;
    $exportedProduct[bobsi\Tradefeed::nameProductName] = zen_get_products_name($product_id);
    $exportedProduct[bobsi\Tradefeed::nameProductCode] = $sku;

    //Wow! One product = one master category!
    $exportedProduct[bobsi\Tradefeed::nameProductCategory] = bobsi_get_breadcrumb(zen_get_products_category_id($product_id));

    if (zen_products_lookup($product_id, '`product_is_free`') != '1') {
        //This price includes taxes only
        $priceWithoutReduct = floatval(zen_products_lookup($product_id, '`products_price`'));

        $product_tax_class = zen_products_lookup($product_id, '`products_tax_class_id`');
        $priceWithoutReduct += zen_calculate_tax($priceWithoutReduct, zen_get_tax_rate($product_tax_class));

        //This price includes taxes & discounts
        $special_price = zen_get_products_special_price($product_id, false);
        $priceFinal = floatval($special_price ? $special_price : $priceWithoutReduct);

        $priceWithoutReduct += $added_change;
        $priceFinal += $added_change;

        if (($priceFinal < $priceWithoutReduct)) {
            $exportedProduct[bobsi\Tradefeed::nameProductPrice] = $priceFinal;
            $exportedProduct[bobsi\Tradefeed::nameProductMarketPrice] = $priceWithoutReduct;
        } else {
            $exportedProduct[bobsi\Tradefeed::nameProductPrice] = $priceFinal;
            $exportedProduct[bobsi\Tradefeed::nameProductMarketPrice] = '';
        }
    }

    //SHIPMENT: Virtual product hasn't shipping classes
    if (!zen_get_products_virtual($product_id)) {
        $exportedProduct[bobsi\Tradefeed::nameProductShippingClass] =
            (!zen_get_product_is_always_free_shipping($product_id)) ?
                getShippingModules() : 'FREE SHIPPING!';
    }

    $exportedProduct[bobsi\Tradefeed::nameProductDescription] = zen_get_products_description($product_id);
    $exportedProduct[bobsi\Tradefeed::nameProductCondition] = bobsi\Tradefeed::conditionNew;
    $exportedProduct[bobsi\Tradefeed::nameProductAvailableQty] = bobsi_calc_product_quantity($product_id, bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getDefaultStockQuantity());

    //There are no length, width, height in zen-cart
    $weight = zen_products_lookup($product_id, '`products_weight`') + $added_weight + 0;
    if ($weight) {
        $attrs[] = array(
            'name' => bobsi\Tradefeed::nameProductAttrShippingWeight,
            'value' => number_format($weight, 2, '.', '') . (defined('TEXT_PRODUCT_WEIGHT_UNIT') ? TEXT_PRODUCT_WEIGHT_UNIT : '')
        );
    }

    $exportedProduct[bobsi\Tradefeed::nameProductAttributes] = $attrs;

    // IMAGES
    $images = array();

    // Dummy image. If product image === sample image it means product hasn't own image. Let's get image of variations!
    $dummy_img = simplexml_load_string(zen_get_products_image(0));

    // $img_attr = strval(reset(array_filter($var_image)));
    if (($imgHtml = simplexml_load_string(zen_get_products_image($product_id))) &&
        (strval($imgHtml->attributes()->src) != strval($dummy_img->attributes()->src))
    ) {
        $image = strval($imgHtml->attributes()->src);
    }

    $exportedProduct[bobsi\Tradefeed::nameProductImages] = array();
    $exportedProduct[bobsi\Tradefeed::nameProductImageURL] = $image ? zen_href_link($image, '', 'SSL', false, false, true) : '';

    if (isset($image) && !empty($image)) {
        $images = bobsi_get_additional_product_images($product_id, str_replace(DIR_WS_IMAGES, '', $image));
        array_unshift($images, $image);
    }

    // taken from includes/modules/attributes.php
    $sql = "select distinct popt.products_options_id, popt.products_options_name, popt.products_options_sort_order,
                              popt.products_options_type, popt.products_options_length, popt.products_options_comment,
                              popt.products_options_size,
                              popt.products_options_images_per_row,
                              popt.products_options_images_style,
                              popt.products_options_rows
              from        " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
              where           patrib.products_id='" . (int)$product_id . "'
              and             patrib.options_id = popt.products_options_id
              and             popt.language_id = '" . (int)$_SESSION['languages_id'] . "' ";

    $products_options_names = $db->Execute($sql);

    while (!$products_options_names->EOF) {
        $sql = "select    pov.products_options_values_id,
                        pov.products_options_values_name,
                        pa.*
              from      " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
              where     pa.products_id = '" . (int)$product_id . "'
              and       pa.options_id = '" . (int)$products_options_names->fields['products_options_id'] . "'
              and       pa.options_values_id = pov.products_options_values_id
              and       pov.language_id = '" . (int)$_SESSION['languages_id'] . "' ";

        $products_options = $db->Execute($sql);

        // collect attribute image if it exists and to be drawn in table below
        if ($products_options_names->fields['products_options_images_style'] == '0' or ($products_options_names->fields['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE or $products_options_names->fields['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT or $products_options_names->fields['products_options_type'] == '0')) {
            if ($products_options->fields['attributes_image'] != '') {
                $images[] = DIR_WS_IMAGES . $products_options->fields['attributes_image'];
            }
        }

        $products_options_names->MoveNext();
    }

    foreach (array_merge($images, $variationImages) as $currentImage) {
        $exportedProduct[bobsi\Tradefeed::nameProductImages][] = zen_href_link($currentImage, '', 'SSL', false, false, true);
    }

    return $exportedProduct;
}

/*
 * Aux functions
 */
function bobsi_calc_product_quantity($product) {
    $qty = intval(zen_get_products_stock($product));
    return $qty;
}

function getPaymentModules() {
    global $paymentModules;

    if ($paymentModules != null && gettype($paymentModules) === 'string') {
        return $paymentModules;
    }

    return explode(',', getModulesData('MODULE_PAYMENT_INSTALLED'));
}

function getShippingModules() {
    global $shipmentClasses;

    if ($shipmentClasses != null && gettype($shipmentClasses) === 'string') {
        return $shipmentClasses;
    }

    return getModulesData('MODULE_SHIPPING_INSTALLED');
}

function getModulesData($key) {
    $installedModules = explode(';', zen_get_configuration_key_value($key));
    $modules = array();

    foreach ($installedModules as $file) {
        $name = basename($file, '.php');
        $path = ($key === 'MODULE_SHIPPING_INSTALLED') ? 'shipping/' : 'payment/';

        if (file_exists(DIR_WS_MODULES . $path . $file)) {
            require_once(DIR_WS_MODULES . $path . $file);
            require_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/' . $path . $file);
            $moduleObj = new $name;

            if (($moduleObj->enabled === true || $moduleObj->enabled === null) && $name != 'freeshipper') {

                $keys = $moduleObj->keys();
                foreach ($keys as $value) {

                    if (strpos($value, 'STATUS') && constant($value) === 'True') {
                        $modules[] = $moduleObj->title . (isset($moduleObj->codeVersion) ? ' ' . strval($moduleObj->codeVersion) : '');
                    }
                }
            }
        }
    }

    return implode(', ', $modules);
}

/** This code was taken from ZenCart core. See includes/modules/additional_images.php
 *
 * @param type $product_id
 * @param type $products_image
 *
 * @return array Array with relative URLs
 */
function bobsi_get_additional_product_images($product_id, $products_image) {
    $images_array = array();

    $flag_show_product_info_additional_images = zen_get_show_product_switch($product_id, 'additional_images');

    // do not check for additional images when turned off
    if ($products_image != '' && $flag_show_product_info_additional_images != 0) {
        // prepare image name
        $products_image_extension = substr($products_image, strrpos($products_image, '.'));
        $products_image_base = str_replace($products_image_extension, '', $products_image);

        // if in a subdirectory
        if (strrpos($products_image, '/')) {
            $products_image_match = substr($products_image, strrpos($products_image, '/') + 1);
            //echo 'TEST 1: I match ' . $products_image_match . ' - ' . $file . ' -  base ' . $products_image_base . '<br>';
            $products_image_match = str_replace($products_image_extension, '', $products_image_match) . '_';
            $products_image_base = $products_image_match;
        }

        $products_image_directory = str_replace($products_image, '', substr($products_image, strrpos($products_image, '/')));
        if ($products_image_directory != '') {
            $products_image_directory = DIR_WS_IMAGES . str_replace($products_image_directory, '', $products_image) . "/";
        } else {
            $products_image_directory = DIR_WS_IMAGES;
        }

        // Check for additional matching images
        $file_extension = $products_image_extension;
        $products_image_match_array = array();
        if ($dir = @dir($products_image_directory)) {
            while ($file = $dir->read()) {
                if (!is_dir($products_image_directory . $file)) {
                    if (substr($file, strrpos($file, '.')) == $file_extension) {
                        if (preg_match('/\Q' . $products_image_base . '\E/i', $file) == 1) {
                            if ($file != $products_image) {
                                if ($products_image_base . str_replace($products_image_base, '', $file) == $file) {
                                    //  echo 'I AM A MATCH ' . $file . '<br>';
                                    $images_array[] = $products_image_directory . $file;
                                } else {
                                    //  echo 'I AM NOT A MATCH ' . $file . '<br>';
                                }
                            }
                        }
                    }
                }
            }
            if (sizeof($images_array)) {
                sort($images_array);
            }
            $dir->close();
        }
    }

    return $images_array;
}

function bobsi_array_cartesian($input) {
    $result = array();

    while (list($key, $values) = each($input)) {
        // If a sub-array is empty, it doesn't affect the cartesian product
        if (empty($values)) {
            continue;
        }

        // Special case: seeding the product array with the values from the first sub-array
        if (empty($result)) {
            foreach ($values as $value) {
                $result[] = array($key => $value);
            }
        } else {
            // Second and subsequent input sub-arrays work like this:
            //   1. In each existing array inside $product, add an item with
            //      key == $key and value == first item in input sub-array
            //   2. Then, for each remaining item in current input sub-array,
            //      add a copy of each existing array inside $product with
            //      key == $key and value == first item in current input sub-array

            // Store all items to be added to $product here; adding them on the spot
            // inside the foreach will result in an infinite loop
            $append = array();
            foreach ($result as &$product) {
                // Do step 1 above. array_shift is not the most efficient, but it
                // allows us to iterate over the rest of the items with a simple
                // foreach, making the code short and familiar.
                $product[$key] = array_shift($values);

                // $product is by reference (that's why the key we added above
                // will appear in the end result), so make a copy of it here
                $copy = $product;

                // Do step 2 above.
                foreach ($values as $item) {
                    $copy[$key] = $item;
                    $append[] = $copy;
                }

                // Undo the side effecst of array_shift
                array_unshift($values, $product[$key]);
            }

            // Out of the foreach, we can add to $results now
            $result = array_merge($result, $append);
        }
    }

    return $result;
}

function bobsi_sort_variations(&$variations) {
    $order = array();
    foreach ($variations as $key => $values) {
        $order[$key] = $values['sort_order'];
    }
    asort($order);

    $sorted_vars = array();
    foreach ($order as $key => $value) {
        $sorted_vars[$key] = $variations[$key];
    }

    $variations = $sorted_vars;

}

/**
 * Returns the power set of a one dimensional array,
 * a 2-D array.
 * array(a,b,c) ->
 * array(array(a),array(b),array(c),array(a,b),array(b,c),array(a,b,c))
 */
function bobsi_power_set($in, $minLength = 1) {
    $count = count($in);
    $members = pow(2, $count);
    $result = array();
    for ($i = 0; $i < $members; $i++) {
        $b = sprintf("%0" . $count . "b", $i);
        $out = array();
        for ($j = 0; $j < $count; $j++) {
            if ($b{$j} == '1') $out[] = $in[$j];
        }
        if (count($out) >= $minLength) {
            $result[] = $out;
        }
    }
    return $result;
}
