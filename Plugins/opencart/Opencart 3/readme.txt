INSTALLATION:
1a) Extract the vivawallet.ocmod.zip and upload the files from the upload directory to their corresponding location on the server with ftp/ssh.
1b) Or use the extension installer, click the Upload button and select the vivawallet.ocmod.zip package.
2. Overwrite files/folders as necessary (no core opencart files will be overwritten)
3. Login to the Open Cart admin section and go to Extensions > Payments
4. Find Vivawallet in the list of extensions
5. Click "Install" and then "Edit" the payment module settings
6. At your Vivawallet backoffice create a new payment source:

Success path: index.php?route=extension/payment/vivawallet/callback&success
Fail path: index.php?route=extension/payment/vivawallet/callback&fail

Use the source code (digit number like 1234) in the payment modules settings. 
--------------------------------------------------------------------------------------------------

Vivawallet setup:
You can find your Merchant ID and API Key when you login your business account under Settings - API Access.

To connect the plugin with your e-commerce platform and Vivawallet you would have to create a new Payment Source in your Vivawallet business account, use the generated source code (usually a four digit number) in the plugin settings.

You can create a new Payment Source from the menu Sales > Online Payments > Websites/Apps - New Website/App.
Code - use this code in your plugin
Source Name - provide a logic name here
Linked Wallet - link the payment source to the wallet you want to use with it
Protocol - in case your e-commerce platform uses SSL on the checkout select https, otherwise use http
Integration method - redirection
Company Logo - your png company logo to display on the Vivawallet payment page
Success URL - as described in the plugin instructions
Failure URL - as described in the plugin instructions
Advanced Configuration - usually no need to make any changes here

Wait until Vivawallet has activated your newly created Payment Source before activating the plugin in your e-commerce platform.
