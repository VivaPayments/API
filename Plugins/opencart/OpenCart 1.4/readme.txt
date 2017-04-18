1. Copy the contents of this package to your opencart installation keeping the folder structure.
2. Overwrite files/folders as necessary (no core opencart files will be overwritten)
3. Login to the Open Cart admin section and go to Extensions > Payments
4. Find Viva Payments in the list of extensions
5. Click "Install" and then "Edit" the payment module settings
6. At your Viva Payments backoffice create a new payment source:

Success: index.php?route=payment/hellaspay/callback&success
Fail: index.php?route=payment/hellaspay/callback&fail
