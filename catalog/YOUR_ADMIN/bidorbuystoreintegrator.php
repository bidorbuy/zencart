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

require('includes/application_top.php');
require_once(dirname(__FILE__) . '/../includes/modules/bidorbuystoreintegrator/factory.php');

$action = (isset($_POST['action'])) ? $_POST['action'] : false;

/***************** ADMIN FORM ACTIONS ****************/

// Reset Tokens action
if (isset($_POST[bobsi\Settings::nameActionReset]) && $action === 'reset_tokens') {
    bobsi\StaticHolder::getBidorbuyStoreIntegrator()->processAction(bobsi\Settings::nameActionReset);

    //Write into DB
    zen_db_perform(TABLE_CONFIGURATION, array(
        'configuration_value' => bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->serialize(true),
        'last_modified' => 'now()'),
        'update', ' configuration_key = "' . strtoupper(bobsi\Settings::name) . '"'
    );

    $messageStack->add('Success: The tokens updated.', 'success');
}

// Save settings to DB
if (!empty($_POST) && empty($_POST[bobsi\Settings::nameLoggingFormAction]) && $action === 'save') {
    if (bobsi\Settings::validateNameFileName($_POST['filename'])) {
        bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->unserialize(serialize($_POST));

        //Write into DB
        zen_db_perform(TABLE_CONFIGURATION, array(
            'configuration_value' => bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->serialize(true),
            'last_modified' => 'now()'),
            'update', ' configuration_key = "' . strtoupper(bobsi\Settings::name) . '"'
        );
        $messageStack->add('Settings successfully saved!', 'success');
    } else {
        $messageStack->add('Warning: Incorrect Export Filename. <br> 16 characters max. Must start with a letter. Can contain letters, digits, `-` and `_`', 'error');
    }
}

//Log files actions: Download, Remove
if (isset($_POST[bobsi\Settings::nameLoggingFormAction])) {

    $data = array(bobsi\Settings::nameLoggingFormFilename =>
        (isset($_POST[bobsi\Settings::nameLoggingFormFilename]))
            ? $_POST[bobsi\Settings::nameLoggingFormFilename]
            : '');
    $result = bobsi\StaticHolder::getBidorbuyStoreIntegrator()->processAction($_POST[bobsi\Settings::nameLoggingFormAction], $data);
    foreach ($result as $item) {
        $messageStack->add($item, 'success');
    }
}

$settings = (array)bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings();
$settings = array_shift($settings);
$wordings = bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getDefaultWordings();

$baa = isset($_REQUEST['baa']) ? $_REQUEST['baa'] : false;

/* feature 3909 */
$warnings = array_merge(
    bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getWarnings(),
    bobsi\StaticHolder::getWarnings()->getBusinessWarnings()
);

foreach ($warnings as $warning) {
    $messageStack->add($warning, 'error');
}
/* */
?>

    <!--***************** ADMIN VIEW ****************-->

    <!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html <?php echo HTML_PARAMS; ?>>
    <head>
        <!--Fix IE defect ui -->
        <meta http-equiv="X-UA-Compatible" content="IE=8">
        <!-- End Fix -->
        <meta http-equiv="X-UA-Compatible" content="IE=8">
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
        <title><?php echo TITLE; ?></title>
        <link rel="stylesheet" type="text/css" href="includes/modules/bidorbuystoreintegrator/assets/css/styles.css">
        <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
        <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
        <script language="javascript" src="includes/menu.js"></script>
        <script language="javascript" src="includes/general.js"></script>
        <script language="javascript" src="includes/modules/bidorbuystoreintegrator/assets/js/jquery.js"></script>
        <script language="javascript"
                src="../includes/modules/bidorbuystoreintegrator/vendor/com.extremeidea.bidorbuy/storeintegrator-core/assets/js/admin.js"></script>
        <script language="javascript" src="includes/modules/bidorbuystoreintegrator/assets/js/admin_aux.js"></script>
        <script type="text/javascript">

            function init() {
                cssjsmenu(\'navbar\');
                if (document.getElementById) {
                    var kill = document.getElementById(\'hoverJS\');
                    kill.disabled = true;
                }
            }

        </script>
    </head>
    <body onLoad="init()" class="zencart">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>


    <div id="bobsi-admin-header">
        <div id="bobsi-icon-trade-feed" style="background-image: url('images/bidorbuystoreintegrator/bidorbuy.png');">

        </div>
        <h1><?php echo bobsi\Version::$name; ?></h1>

        <div id="bobsi-adv">
            <!-- BEGIN ADVERTPRO CODE BLOCK -->
            <script type="text/javascript">
                document.write('<scr' + 'ipt src="http://nope.bidorbuy.co.za/servlet/view/banner/javascript/zone?zid=153&pid=0&random=' + Math.floor(89999999 * Math.random() + 10000000) + '&millis=' + new Date().getTime() + '&referrer=' + encodeURIComponent(document.location) + '" type="text/javascript"></scr' + 'ipt>');
            </script>
            <!-- END ADVERTPRO CODE BLOCK -->
        </div>
    </div>


    <?php echo zen_draw_form('export', FILENAME_BOBSI_ADM, '', 'post', 'enctype="multipart/form-data" id="form"') . zen_hide_session_id() . zen_draw_hidden_field('action', 'save'); ?>

    <fieldset>
        <!-- EXPORT CONFIGURATION TABLE  -->
        <div class="postbox-item postbox-left">
            <table class="<?php echo bobsi\Settings::nameExportConfiguration; ?>" cellspacing="0"
                   cellpadding="0">
                <tr>
                    <td class="formAreaTitle">
                        <h4 class="subheader"><?php echo $wordings[bobsi\Settings::nameExportConfiguration][bobsi\Settings::nameWordingsTitle]; ?></h4>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table border="0" cellpadding="2" cellspacing="0" class="inner-box">
                            <tbody>
                            <!-- FILENAME -->
                            <tr>
                                <?php $name = bobsi\Settings::nameFilename; ?>
                                <td class="main mainLabel hastip">
                                    <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                                </td>
                                <td class="main">
                                    <?php echo zen_draw_input_field($name, $settings[$name]); ?>
                                    <p class="tip"> <?php echo $wordings[$name][bobsi\Settings::nameWordingsDescription]; ?></p>
                                </td>
                            </tr>

                            <!-- COMPRESS LIBRARY -->
                            <tr>
                                <?php $name = bobsi\Settings::nameCompressLibrary; ?>
                                <td class="main mainLabel hastip">
                                    <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                                </td>
                                <td class="main">
                                    <select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
                                        <?php $libs = bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getCompressLibraryOptions();

                                        $options = '';
                                        foreach ($libs as $lib => $info) {
                                            $options .= '<option value="' . $lib . '" ';
                                            if (bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getCompressLibrary() == $lib) {
                                                $options .= ' selected="selected"';
                                            }
                                            $options .= '>' . ucfirst($lib) . '</option>';
                                        }
                                        echo $options;
                                        ?>
                                    </select>

                                    <p class="tip"> <?php echo $wordings[$name][bobsi\Settings::nameWordingsDescription]; ?></p>
                                </td>
                            </tr>

                            <!-- MIN QUANTITY -->
                            <tr>
                                <?php $name = bobsi\Settings::nameDefaultStockQuantity; ?>
                                <td class="main mainLabel hastip">
                                    <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                                </td>
                                <td class="main">
                                    <?php echo zen_draw_input_field($name, $settings[$name], '', false, 'number'); ?>
                                    <p class="tip"> <?php echo $wordings[$name][bobsi\Settings::nameWordingsDescription]; ?></p>
                                </td>
                            </tr>



                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <!-- EXPORT CRITERIA TABLE  -->
        <div class="postbox-item postbox-right">
            <table class="<?php echo bobsi\Settings::nameExportCriteria; ?>" cellpadding="0"
                   cellspacing="0">
                <tr>
                    <td class="formAreaTitle">
                        <h4 class="subheader"><?php echo $wordings[bobsi\Settings::nameExportCriteria][bobsi\Settings::nameWordingsTitle]; ?></h4>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table border="0" cellpadding="2" cellspacing="2">
                            <tbody>
                            <!--EXPORT PRODUCTS WITH QUANTITY MORE THAN -->
                            <tr>
                                <?php $name = bobsi\Settings::nameExportQuantityMoreThan; ?>
                                <td class="main mainLabel" colspan="2">
                                    <table border="0" cellpadding="0" cellspacing="0" class="inner-box">
                                        <tr>
                                            <td class="hastip">
                                                <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                                            </td>
                                            <td>
                                                <?php echo zen_draw_input_field($name, (string)$settings[$name], '', false, 'number'); ?>
                                                <p class="tip"> <?php echo $wordings[$name][bobsi\Settings::nameWordingsDescription]; ?></p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- INCLUDE/EXCLUDE CATS -->
                            <tr>
                                <?php $name = bobsi\Settings::nameExcludeCategories; ?>
                                <td>
                                    <?php echo getCategories($name);
                                    ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <?php echo zen_draw_hidden_field(bobsi\Settings::nameTokenDownload, bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getTokenDownload()); ?>
        <?php echo zen_draw_hidden_field(bobsi\Settings::nameTokenExport, bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getTokenExport()); ?>

        <!--SAVE BUTTON-->
        <div class="item-button"><?php echo zen_draw_input_field('save', 'Save', ' id="submit" ', false, 'submit'); ?></div>
    </fieldset>
    <!-- DEBUG-->
    <div class="postbox logfiles postbox-item postbox-long">
        <h4 class="subheader">Debug</h4>

        <?php if ($baa == 1) : ?>

        <div class="inner-box">
            <h4> <span>Basic Access Authentication</span></h4>
            <span>(if necessary)</span>
            <h4>
                <span style="color: red">
                    Do not enter username or password of ecommerce platform, please read carefully about this kind of authentication!
                </span> 
            </h4>
        </div>
        <table class="inner-box">
            <!-- USERNAME -->
            <tr>
                <?php $name = bobsi\Settings::nameUsername; ?>
                <td class="main mainLabel hastip">
                    <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                </td>
                <td class="main">
                    <?php echo zen_draw_input_field($name, $settings[$name]); ?>
                    <p class="tip"> <?php echo $wordings[$name][bobsi\Settings::nameWordingsDescription]; ?></p>
                </td>
            </tr>

            <!-- PASSWORD -->
            <tr>
                <?php $name = bobsi\Settings::namePassword; ?>
                <td class="main mainLabel hastip">
                    <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                </td>
                <td class="main">
                    <?php echo zen_draw_password_field($name, $settings[$name]); ?>
                    <p class="tip"> <?php echo $wordings[$name][bobsi\Settings::nameWordingsDescription]; ?></p>
                </td>
            </tr>

            <?php else: ?>

                <input type="hidden" name="<?= bobsi\Settings::nameUsername ?>"
                       value="<?= $settings[bobsi\Settings::nameUsername] ?>"/>
                <input type="hidden" name="<?= bobsi\Settings::namePassword ?>"
                       value="<?= $settings[bobsi\Settings::namePassword] ?>"/>

            <?php endif; ?>
        </table>
        <table class="inner-box">
            <tr>
                <td><br><h4>Logs</h4></td>
            </tr>
            <!-- EMAIL ADRESSESSES -->
            <tr>
                <?php $name = bobsi\Settings::nameEmailNotificationAddresses; ?>
                <td class="main mainLabel hastip">
                    <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                </td>
                <td class="main">
                    <?php echo zen_draw_input_field($name, $settings[$name]); ?>
                    <p class="tip"> <?php echo $wordings[$name][bobsi\Settings::nameWordingsDescription]; ?></p>
                </td>
            </tr>

            <!-- TURN ON/OFF EMAIL -->
            <tr>
                <?php $name = bobsi\Settings::nameEnableEmailNotifications; ?>
                <td class="main mainLabel">
                    <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                </td>
                <td align="left">
                    <?php echo zen_draw_checkbox_field($name, $settings[$name], (bool)$settings[$name]); ?>
                    <p class="tip"> <?php echo $wordings[$name][bobsi\Settings::nameWordingsDescription]; ?></p>
                </td>
            </tr>

            <!-- LOGGING LEVEL -->
            <tr>
                <?php $name = bobsi\Settings::nameLoggingLevel; ?>
                <td class="main mainLabel hastip">
                    <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                </td>
                <td class="main">
                    <select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
                        <?php $levels = bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getLoggingLevelOptions();

                        $options = '';
                        foreach ($levels as $level) {
                            $options .= '<option value="' . $level . '" ';
                            if (bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getLoggingLevel() == $level) {
                                $options .= ' selected="selected"';
                            }
                            $options .= '>' . ucfirst($level) . '</option>';
                        }
                        echo $options;
                        ?>
                    </select>

                    <p class="tip"> <?php echo $wordings[$name][bobsi\Settings::nameWordingsDescription]; ?></p>
                </td>
            </tr>
        </table>

        <!--END FORM -->
        </form>
        <!--LOGS-->
        <fieldset>
            <?php echo bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getLogsHtml(); ?>
        </fieldset>
    </div>


    <!--LINKS-->
    <?php echo zen_draw_form('bobsi-export-links', FILENAME_BOBSI_ADM, '', 'post', ' id="bobsi-export-links" enctype="multipart/form-data"') . zen_hide_session_id() . zen_draw_hidden_field('action', 'reset_tokens'); ?>

    <div class="postbox links postbox-item postbox-long">
        <input class="bobsi-input" type="hidden"
               id="<?php echo bobsi\Settings::nameActionReset; ?>"
               name="<?php echo bobsi\Settings::nameActionReset; ?>"
               value="1"/>

        <h4 class="subheader"><?php echo $wordings[bobsi\Settings::nameExportLinks][bobsi\Settings::nameWordingsTitle]; ?></h4>

        <fieldset>
            <?php $link_tooltip = 'Click to select';
            $base = (ENABLE_SSL_CATALOG != 'false' && ENABLE_SSL_CATALOG != false) ?
                HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG :
                HTTP_CATALOG_SERVER . DIR_WS_CATALOG;

            $export_link = $base . 'bidorbuystoreintegrator.php?action=export&' . bobsi\Settings::paramToken . '=' . bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getTokenExport();
            $download_link = $base . 'bidorbuystoreintegrator.php?action=download&' . bobsi\Settings::paramToken . '=' . bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getTokenDownload();
            $phpInfo_link = $base . 'bidorbuystoreintegrator.php?action=version&' . bobsi\Settings::paramToken . '=' . bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getTokenDownload().'&phpinfo=y';
            ?>
            <table id="module" class="form-table export-links">
                <tbody>
                <!-- EXPORT LINK -->
                <tr>

                    <?php $name = bobsi\Settings::nameExportUrl; ?>
                    <td class="main mainLabel">
                        <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                    </td>
                    <td class="main hastip">
                        <?php echo zen_draw_input_field($name, $export_link, ' class="bobsi-url" readonly id="' . bobsi\Settings::nameTokenExport . '"'); ?>
                    </td>
                    <td class="narrow button-section right">
                        <p class="tip">
                            <?php echo $link_tooltip; ?>
                        </p>
                        <?php echo zen_draw_input_field('export_button', 'Launch',
                            ' class="button" onclick="window.open(\'' . $export_link . '\',\'_blank\');"', false, 'button'); ?>
                        <?php echo zen_draw_input_field('copy', 'Copy', ' class="button copy-button" ', false, 'button'); ?>
                    </td>
                </tr>

                <!-- DOWNLOAD LINK -->
                <tr>

                    <?php $name = bobsi\Settings::nameDownloadUrl; ?>
                    <td class="main mainLabel">
                        <?php echo $wordings[$name][bobsi\Settings::nameWordingsTitle]; ?>
                    </td>
                    <td class="main hastip">
                        <?php echo zen_draw_input_field($name, $download_link, ' class="bobsi-url" readonly id="' . bobsi\Settings::nameTokenDownload . '"'); ?>
                    </td>
                    <td class="narrow button-section right">
                        <p class="tip">
                            <?php echo $link_tooltip; ?>
                        </p>
                        <?php echo zen_draw_input_field('export_button', 'Launch',
                            ' class="button" onclick="window.open(\'' . $download_link . '\',\'_blank\');"', false, 'button'); ?>
                        <?php echo zen_draw_input_field('copy', 'Copy', ' class="button copy-button" ', false, 'button'); ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="3" class="right-text">
                        <?php echo zen_draw_input_field('reset_tokens',
                            $wordings[bobsi\Settings::nameButtonReset][bobsi\Settings::nameWordingsTitle],
                            ' class="button" ', false, 'submit'); ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    </form>

    <div class="postbox postbox-item postbox-long">
        <h4 class="subheader">Version</h4>
        <div class="inner-box">
            <a href="<?php echo $phpInfo_link?>" target="_blank">@See PHP information</a><br>
            <?php echo bobsi\Version::getLivePluginVersion(); ?>
        </div>
    </div>

    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>

    </body>
    </html>

<?php

require(DIR_WS_INCLUDES . 'application_bottom.php');

/*
 * Aux functions
 */
function getCategories($name) {
    $export_categories = bobsi\StaticHolder::getBidorbuyStoreIntegrator()->getSettings()->getExcludeCategories();

    $cats = zen_get_category_tree('0', '', '0');

    $included_categories = '<select id="bobsi-inc-categories" class="bobsi-categories-select" name="bobsi_inc_categories[]" multiple="multiple" size="9">';
    $excluded_categories = '<select id="bobsi-exc-categories" class="bobsi-categories-select" name="' . $name . '[]" multiple="multiple" size="9">';

    foreach ($cats as $category) {
        $t = '<option  value="' . $category['id'] . '">' . $category['text'] . '</option>';
        if (in_array($category['id'], $export_categories)) {
            $excluded_categories .= $t;
        } else {
            $included_categories .= $t;
        }
    }
    $included_categories .= '</select>';
    $excluded_categories .= '</select>';

    $html[] = '<table cellspacing="4" cellpadding="2" width=100%><tr><td><label for="bobsi-inc-categories">Included Categories</label></td>
                    <td></td><td><label for="bobsi-exc-categories">Excluded Categories</label></td></tr>';
    $html[] = '<tr><td>' . $included_categories . '</td>';
    $html[] = '<td class="buttons-item">
                    <p class="submit"><button name="include" id="include" class="button" type="button">< Include</button></p>
                    <p class="submit"><button name="exclude" id="exclude" class="button" type="button">> Exclude</button></p>
                </td>';
    $html[] = '<td>' . $excluded_categories . '</td></tr></table>';

    return implode($html);
}

?>