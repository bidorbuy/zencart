# ZenCart bidorbuy Store Integrator

### Compatibility


| Product | PHP version  | Platform |
| ------- | --- | --- |
|Store Integrator-2.0.10 |5.4| ✓ ZenCart  1.5.1|
|Store Integrator-2.0.9 |5.4| ✓ ZenCart  1.5.1|
|Store Integrator-2.0.8 |5.4| ✓ ZenCart  1.5.1|
|Store Integrator-2.0.7 |5.3| ✓ ZenCart  1.5.1|
|Store Integrator-2.0.6 |5.3| ✓ ZenCart  1.5.1|

### Description

The bidorbuy Store Integrator allows you to get products from your online store listed on bidorbuy quickly and easily.
Expose your products to the bidorbuy audience - one of the largest audiences of online shoppers in South Africa Store updates will be fed through to bidorbuy automatically, within 24 hours so you can be sure that your store is in sync within your bidorbuy listings. All products will appear as Buy Now listings. There is no listing fee just a small commission on successful sales. View [fees](https://support.bidorbuy.co.za/index.php?/Knowledgebase/Article/View/22/0/fee-rate-card---what-we-charge). Select as many product categories to list on bidorbuy as you like. No technical requirements necessary.

To make use of this plugin, you'll need to be an advanced seller on bidorbuy.
 * [Register on bidorbuy](https://www.bidorbuy.co.za/jsp/registration/UserRegistration.jsp?action=Modify)
 * [Apply to become an advanced seller](https://www.bidorbuy.co.za/jsp/seller/registration/UserSellersRequest.jsp)
 * Once you integrate with bidorbuy, you will be contacted by a bidorbuy representative to guide you through the process.

### System requirements

Minimum PHP version required: 5.4

PHP extensions: curl, mbstring

### Installation

1. Connect to your FTP server. 
2. Unzip the extension on your local machine.
3. Navigate to Catalog folder.
4. Rename YOUR_ADMIN folder as your admin catalog of your ZenCart installation (admincp by default).
5. Copy all files from Catalog folder to your Root folder.

### Uninstallation

1. Open AdminCP > Tools > Install SQL patches.
2. Upload the uninstall.sql and press Upload button.
3. Remove all files of plugin installation (via FTP).

### Upgrade

1. Remove all old files of previous installation:

* Root folder > includes > modules > bidorbuystoreintegrator;
* Root folder > bidorbuystoreintegrator.php;
* YOUR_ADMIN folder > images > bidorbuystoreintegrator;
* YOUR_ADMIN folder > includes > extra_datafiles > bidorbuystoreintegrator.php;
* YOUR_ADMIN folder > includes > functions > extra_functions > reg_bidorbuystoreintegrator.php;
* YOUR_ADMIN folder > includes > modules > bidorbuystoreintegrator;
* YOUR_ADMIN folder > bidorbuystoreintegrator.php.

2. Re-install the archive. Please look through the installation chapter.

### Configuration

1. Log in to control panel as administrator.
2. Navigate to Tools > bidorbuy Store Integrator.
3. Set the export criteria.
4. Press the `Save` button.
5. Press the `Export` button.
6. Press the `Download` button.
7. Share Export Links with bidorbuy.
8. To display BAA fields on the setting page add '?baa=1' to URL in address bar.
