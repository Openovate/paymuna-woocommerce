Paymuna Payment Gateway Plugin for WooCommerce
==============================================

## Prerequisites
Latest Version Tested:
- Wordpress v4.8.3
- WooCommerce v3.2.3

You must have a Paymuna account to use this plugin. Having an account is free, so [go ahead and signup for a Paymuna account](http://paymuna.com/).

## Server Requirements
- [Wordpress](https://wordpress.org/about/requirements/) >= 4.8.3 (Older versions will work but this plugin wasn't tested to these older versions.)
- [WooCommerce](https://docs.woocommerce.com/document/server-requirements/) >= 3.2.3
- [PHP](http://php.net/) >= 5.6 (This plugin is tested with 7.1 version)
- cURL (Once you have PHP installed, it will be basically included on it.)

## Installation
### Manual

Once you have downloaded the zip file containing the Paymuna plugin, head over to your **Wordpress Administration Panels > Plugins > Add New > Upload Plugin**.

![Upload Plugin](https://i.imgur.com/W1h7cXQ.png)

A file dialog will show and select the downloaded zip file and upload it.

![Uploading](https://i.imgur.com/ceZBXJ4.png)

Once it finished uploading, activate the plugin by clicking **Active Plugin** button.

To verify that the plugin was successfully installed, go to **Wordpress Administration Panels > Plugins > Installed Plugins** and the Paymuna plugin should show up there.

![Activate Success](https://i.imgur.com/p1YYpIG.png)

## Configuration

Before you move on configuring Paymuna make sure you already have a Checkout Template in order for you to have an **API Token**, **API Secret**, and **Checkout Reference**. If you don't have the following, head over to [Paymuna](http://paymuna.com) to generated those.

Once you have the necessary credentials, head over to **WooCommerce > Settings > Checkout > Paymuna** and start configuring your Paymuna Payment.

![Configuration](https://i.imgur.com/ajVlXrm.png)

In order for everything to work, fill out the necessary fields in the **Credentials** section.

![Credentials](https://i.imgur.com/S12EzvG.png)

Once everything is setup, save the changes and Paymuna should be shown as a payment option in the **Checkout** page.
