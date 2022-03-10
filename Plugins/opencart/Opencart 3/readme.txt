INSTALLATION:
1a) Extract the vivawallet.ocmod.zip and upload the files from the upload directory to their corresponding location on the server with ftp/ssh.
1b) Or use the extension installer, click the Upload button and select the vivawallet.ocmod.zip package.
2. Overwrite files/folders as necessary (no core opencart files will be overwritten)
3. Login to the Open Cart admin section and go to Extensions > Payments
4. Find Viva Wallet Smart Checkout in the list of extensions
5. Click "Install" and then "Edit" the payment module settings
6. At your Vivawallet backoffice create a new [payment source](https://developer.vivawallet.com/getting-started/create-a-payment-source/payment-source-for-plugins/):

Vivawallet setup:
You can [find your Merchant ID and API Key](https://developer.vivawallet.com/getting-started/find-your-merchant-id-and-api-key/) when you login your business account under Settings - API Access.

To connect the plugin with your e-commerce platform and Vivawallet you would have to create a new [Payment Source](https://developer.vivawallet.com/getting-started/create-a-payment-source/payment-source-for-plugins/) in your Vivawallet business account, use the generated source code (usually a four digit number) in the plugin settings.

Please refer to [developer portal](https://developer.vivawallet.com/plugins/opencart) for more information.
