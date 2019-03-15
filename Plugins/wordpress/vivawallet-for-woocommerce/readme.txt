INSTALLATION:
-Save the vivawallet-for-woocommerce directory in a zipped archive (vivawallet-for-woocommerce.zip).
-Install new pluging through wp-admin (Upload - select vivawallet-for-woocommerce.zip) 
-Activate the WooCommerce Vivawallet Payment Gateway
-In Woocommerce - settings - Payment Gateways: select Vivawallet and configure the module
-Create a new source in your Vivawallet backoffice and use the following URLs:

SUCCESS URL: index.php?wc-api=wc_vivawallet&vivawallet=success
FAIL URL: index.php?wc-api=wc_vivawallet&vivawallet=fail

-An optinal webhook URL can be set in your Vivawallet backoffice -> Settings/API Access/Webhooks, this assures the order will be handled
correctly in case the customer does not return to the estore after the transaction:
WEBHOOK URL: http(s)://(www.)your_domain.com/index.php?wc-api=wc_vivawallet&vivawallet=webhook
----------------------------------------------------------------------------------------------------------

Vivawallet setup:
You can find your Merchant ID and API Key when you login your business account under Settings - API Access.

To connect the plugin with your e-commerce platform and Vivawallet you would have to create a new Payment Source in your Vivawallet business account, use the generated source code (usually a four digit number) in the plugin settings.

You can create a new Payment Source from the menu Sales > Online Payments > Websites/Apps - New Website/App.
Code - use this code in your plugin
Source Name - provide a logic name here
Linked Wallet - link the payment source to the account you want to use with it
Protocol - in case your e-commerce platform uses SSL on the checkout select https, otherwise use http
Integration method - redirection
Company Logo - your png company logo to display on the Vivawallet payment page
Success URL - as described in the plugin instructions
Failure URL - as described in the plugin instructions
Advanced Configuration - usually no need to make any changes here

Wait until Vivawallet has activated your newly created Payment Source before activating the plugin in your e-commerce platform.
