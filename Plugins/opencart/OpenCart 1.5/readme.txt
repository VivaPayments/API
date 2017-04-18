1. Copy the contents of this package to your opencart installation keeping the folder structure.
2. Overwrite files/folders as necessary (no core opencart files will be overwritten)
3. Login to the Open Cart admin section and go to Extensions > Payments
4. Find Viva Payments in the list of extensions
5. Click "Install" and then "Edit" the payment module settings
6. At your Viva Payments backoffice create a new payment source:

Success path: index.php?route=payment/hellaspay/callback&success
Fail path: index.php?route=payment/hellaspay/callback&fail

--------------------------------------------------------------------------------------------------
URL NOTE (FOR JOOMLA! MIJOSHOP USERS ONLY):

USING SEF URLs:
Success path: component/mijoshop/payment/hellaspay/callback?success
Fail path: component/mijoshop/payment/hellaspay/callback?fail

NOT USING SEF URLs:
Success path: index.php?option=com_mijoshop&route=payment/hellaspay/callback&success
Fail path: index.php?option=com_mijoshop&route=payment/hellaspay/callback&fail

MIJOSHOP URLs can differ from the given example based on Joomla! configuration, check before using
above URLs if they do not generate errors or 404 page not found errors.
--------------------------------------------------------------------------------------------------
