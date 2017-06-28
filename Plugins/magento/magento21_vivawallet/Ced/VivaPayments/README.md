Copyright 2017  Vivawallet.com<br>
http://www.vivawallet.com<br>
<br>
INSTALLATION:<br>
1 - upload the files to your Magento installation, only new files will be added to the app/code/Ced/VivaPayments folder<br>
2 - enable module: bin/magento module:enable --clear-static-content Ced_VivaPayments<br>
3 - upgrade database: bin/magento setup:upgrade<br>
4 - re-run compile command: bin/magento cache:flush<br>
5 - re-run compile command: bin/magento setup:di:compile<br>
<br>
In order to deactivate the module bin/magento module:disable --clear-static-content Ced_VivaPayments<br>
In order to update static files: bin/magento setup:static-content:deploy<br>
<br>
Important: make sure that php path is correct in bin/magento file<br>
<br>
---------------------------------------------------------------------------<br>
<br>
Vivawallet setup:<br>
You can find your Merchant ID and API Key when you login your business account under Settings - API Access.<br>
<br>
To connect the plugin with your e-commerce platform and Vivawallet you would have to create a new Payment Source in your Vivawallet business account, use the generated source code (usually a four digit number) in the plugin settings.<br>
<br>
You can create a new Payment Source from the menu My Sales - Payment Sources - New Website/App.<br>
Code - use this code in your plugin<br>
Source Name - provide a logic name here<br>
Linked Wallet - link the payment source to the wallet you want to use with it<br>
Protocol - in case your e-commerce platform uses SSL on the checkout select https, otherwise use https<br>
Integration method - redirection<br>
Company Logo - your png company logo to display on the Vivawallet payment page<br>
Success URL - http(s)://(www.)yourdomain.com/vivapayments/viva/callback/<br>
Failure URL - http(s)://(www.)yourdomain.com/vivapayments/viva/callback/<br>
Advanced Configuration - usually no need to make any changes here<br>
<br>
Wait until Vivawallet has activated your newly created Payment Source before activating the plugin in your e-commerce platform.
