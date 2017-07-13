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

require_once(dirname(__FILE__) . '/vendor/autoload.php');

bobsi\StaticHolder::getBidorbuyStoreIntegrator()->init(
    STORE_NAME, STORE_OWNER_EMAIL_ADDRESS,
    PROJECT_VERSION_NAME . ' ' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR,
    constant(strtoupper(bobsi\Settings::name))
);