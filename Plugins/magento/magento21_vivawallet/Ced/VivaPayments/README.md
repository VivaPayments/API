Copyright 2017  Vivawallet.com
http://www.vivawallet.com

INSTALLATION:
1 - unzip the package in your Magento installation, only new files will be added to the app/code/Ced/VivaPayments folder
2 - enable module: bin/magento module:enable --clear-static-content Ced_VivaPayments
3 - upgrade database: bin/magento setup:upgrade
4 - re-run compile command: bin/magento cache:flush
5 - re-run compile command: bin/magento setup:di:compile

In order to deactivate the module bin/magento module:disable --clear-static-content Ced_VivaPayments
In order to update static files: bin/magento setup:static-content:deploy

Important: make sure that php path is correct in bin/magento file

---------------------------------------------------------------------------

Vivawallet setup:
You can find your Merchant ID and API Key when you login your business account under Settings - API Access.

To connect the plugin with your e-commerce platform and Vivawallet you would have to create a new Payment Source in your Vivawallet business account, use the generated source code (usually a four digit number) in the plugin settings.

You can create a new Payment Source from the menu My Sales - Payment Sources - New Website/App.
Code - use this code in your plugin
Source Name - provide a logic name here
Linked Wallet - link the payment source to the wallet you want to use with it
Protocol - in case your e-commerce platform uses SSL on the checkout select https, otherwise use https
Integration method - redirection
Company Logo - your png company logo to display on the Vivawallet payment page
Success URL - http(s)://(www.)yourdomain.com/vivapayments/viva/callback/
Failure URL - http(s)://(www.)yourdomain.com/vivapayments/viva/callback/
Advanced Configuration - usually no need to make any changes here

Wait until Vivawallet has activated your newly created Payment Source before activating the plugin in your e-commerce platform.
