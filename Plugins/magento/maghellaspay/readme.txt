INSTALLATION:
1) Upload the files to their corresponding location on the server (ftp/ssh).
2) Clear Magento cache. 
3) Configure the "Viva Payments" payment module.
4) At your Viva Payments backoffice create a new payment source:

Example with URL rewrite disabled:
Success page: index.php/hellaspay/hpay/hpok/
Failure page: index.php/hellaspay/hpay/hpnok/

Example with URL rewrite enabled:
Success page: hellaspay/hpay/hpok/
Failure page: hellaspay/hpay/hpnok/

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
Success URL - as described in the plugin instructions
Failure URL - as described in the plugin instructions
Advanced Configuration - usually no need to make any changes here

Wait until Vivawallet has activated your newly created Payment Source before activating the plugin in your e-commerce platform.