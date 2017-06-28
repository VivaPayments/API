INSTALLATION:
1) Save the files to a zipped package (vm_plugin_vivawallet.zip).
2) Install the [vm_plugin_vivawallet.zip] plugin through Joomla extension manager (Install).
3) Activate the [VM - Payment, Vivawallet] plugin through Joomla extension manager (Manage). 
4) Add the new payment method (Virtuemart/Shop/Payment Methods) and set parameters / configuration.
5) Set a new source in the Vivawallet backoffice with following success / fail links:

Success page: 
index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&bnkact=success

Failure page: 
index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&bnkact=fail

-An optinal webhook URL can be set in your Vivawallet backoffice -> Settings/API Access/Webhooks, this assures the order will be handled
correctly in case the customer does not return to the estore after the transaction:
WEBHOOK URL: http(s)://(www.)your_domain.com/index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&bnkact=webhook
--------------------------------
Optional multilanguage support.
-In case you use English and Greek languages and the URLs contain the language code, create two
payment options with this payment module, one for English [en] and one for Greek[el]. 
-Create 2 sources in the HellasPay backoffice. 

English URLs:

Success page: 
index.php/en/?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&bnkact=success

Failure page: 
index.php/en/?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&bnkact=fail
---------------
Greek URLs:

Success page: 
index.php/el/?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&bnkact=success

Failure page: 
index.php/el/?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&bnkact=fail

----------------------------------------------------------------------------------------------------------

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
