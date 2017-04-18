INSTALLATION:
1a) Extract the hellaspay.ocmod.zip and upload the files from the upload directory to their corresponding location on the server with ftp/ssh.
1b) Or use the extension installer, click the Upload button and select the hellaspay.ocmod.zip package.
2. Overwrite files/folders as necessary (no core opencart files will be overwritten)
3. Login to the Open Cart admin section and go to Extensions > Payments
4. Find Viva Payments in the list of extensions
5. Click "Install" and then "Edit" the payment module settings
6. At your Viva Payments backoffice create a new payment source:

Success path: index.php?route=extension/payment/hellaspay/callback&success
Fail path: index.php?route=extension/payment/hellaspay/callback&fail

Use the source code (digit number like 1234) in the payment modules settings. 