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

/**
 * Register module into admin menu system
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

use com\extremeidea\bidorbuy\storeintegrator\core as bobsi;

require_once(dirname(__FILE__) . '/../../../../includes/modules/bidorbuystoreintegrator/factory.php');

if (function_exists('zen_register_admin_page')) {
    if (!zen_page_key_exists(bobsi\Version::$id)) {
        zen_register_admin_page(bobsi\Version::$id, 'BOX_TOOLS_BOBSI', 'FILENAME_BOBSI_ADM', '', 'tools', 'Y', 10);

        //Add default settings into DB
        zen_db_perform(TABLE_CONFIGURATION, array(
            'configuration_title' => bobsi\Settings::name,
            'configuration_key' => strtoupper(bobsi\Settings::name),
            'configuration_value' => bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->serialize(true),
            'date_added' => 'now()'
        ));
    }
}