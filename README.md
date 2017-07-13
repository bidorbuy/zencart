# ZenCart bidorbuy Store Integrator

### Compatibility

 ZenCart 1.5

### Description

The bidorbuy Store Integrator allows you to get products from your online store listed on bidorbuy quickly and easily.
Expose your products to the bidorbuy audience - one of the largest audiences of online shoppers in South Africa Store updates will be fed through to bidorbuy automatically, within 24 hours so you can be sure that your store is in sync within your bidorbuy listings. All products will appear as Buy Now listings. There is no listing fee just a small commission on successful sales. View fees (http://support.bidorbuy.co.za/index.php?/Knowledgebase/Article/View/22/0/fee-rate-card---what-we-charge). Select as many product categories to list on bidorbuy as you like. No technical requirements necessary.

To make use of this plugin, you'll need to be an advanced seller on bidorbuy.
 * Register on bidorbuy (https://www.bidorbuy.co.za/jsp/registration/UserRegistration.jsp?action=Modify)
 * Apply to become an advanced seller (https://www.bidorbuy.co.za/jsp/seller/registration/UserSellersRequest.jsp)
Once you integrate with bidorbuy, you will be contacted by a bidorbuy representative to guide you through the process.

### System requirements

Minimum PHP version required: 5.3.0.

PHP extensions: curl, mbstring.

### Installation

1. Connect to your FTP server. 
2. Unzip the extension on your local machine.
3. Navigate to Catalog folder.
4. Rename YOUR_ADMIN folder as your admin catalog of your zenCart installation (admincp by default).
5. Copy all files from Catalog folder to your Root folder.

### Uninstallation

1. Open AdminCP> Tools > Install SQL patches.
2. Upload the uninstall.sql and press Upload button.
3. Remove all files of plugin installation (via FTP).

### Upgrade

Remove all old files of previous installation:
1. Root folder-->`includes` folder-->`modules`-->`bidorbuystoreintegrator`;
2. Root folder-->`bidorbuystoreintegrator.php`;
3. YOUR_ADMIN folder-->`images`-->`bidorbuystoreintegrator`;
4. YOUR_ADMIN folder-->`includes`-->`extra_datafiles`-->`bidorbuystoreintegrator.php`;
5. YOUR_ADMIN folder-->`includes`-->`functions`-->`extra_functions`-->`reg_bidorbuystoreintegrator.php`;
6. YOUR_ADMIN folder-->`includes`-->modules-->`bidorbuystoreintegrator`;
5. YOUR_ADMIN folder-->`bidorbuystoreintegrator.php`.

Re-install the archive. Please look through the installation chapter.

### Configuration

1. Log in to control panel as administrator.
2. Navigate to Tools >bidorbuy Store Integrator.
3. Set the export criteria.
4. Press the `Save` button.
5. Press the `Export` button.
6. Press the `Download` button.
7. Share Export Links with bidorbuy.
8. To display BAA fields on the setting page add '?baa=1' to URL in address bar.

### Changelog

#### 2.0.7
* Fixed error in query (1292): Incorrect datetime value: '0000-00-00 00:00:00' for column 'row_modified_on' at row 1.
* Fixed error in query (1055): Expression #1 of SELECT list is not in GROUP BY clause and contains nonaggregated column.
* Fixed issue when "$this->dbLink->execute" hides the real error messages.
* Fixed issue when bobsi tables are created always with random charset instead of utf8_unicode_ci.
* Fixed issue when export process is interrupted by zlib extension.

_[Updated on June 06, 2017]_

#### 2.0.6
* Added a flag to display BAA fields (to display BAA fields on the setting page add '?baa=1' to URL in address bar).
* Added an appropriate warning on the Store Integrator setting page about EOL(End-of-life) of export non HTTP URL to the tradefeed file.

_[Updated on March 07, 2017]_

#### 2.0.5
* Added support of multiple images.
* Added support of images from product description.
* Added the possibility to open PHP info from store Integrator settings page.

 _[Updated on December 19, 2016]_

#### 2.0.4
* Added additional improvements for Store Integrator Settings page.
* Added new feature: if product has weight attribute, the product name should contain this attribute value.
* Fixed an issue when Store Integrator cuts the long name of categories in Export Criteria section.

 _[Updated on November 18, 2016]_

#### 2.0.3
* Fixed an issue when tradefeed is invalid to being parsed with Invalid byte 1 of 1-byte UTF-8 sequence.
* Fixed an issue of empty XML after changing the settings.
* Fixed an issue when it is impossible to download log after its removal.
* Fixed an issue when extra character & added to the export URL.
* Corrected the export link length: it was too long.
* Added an error message if "mysqli" extension is not loaded.

_[Updated on October 28, 2016]_

#### 2.0.2
* Added warning in case if 'readfile' function is disabled.
* The PHP version has changed to 5.3.0.

_[Updated on August 31, 2016]_

#### 2.0.1
* Added the ability to display the plugin version.
 
_[Updated on April 25, 2016]_

#### 2.0.0
* Enhancements and bugs fixes.
 
_[Updated on September 15, 2015]_

#### 1.0
* First release.

_[Released on July 28, 2014]_

