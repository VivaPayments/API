1. Open a merchant account at vivawallet.com, upload the files from the package.
-------------------------------------------------------------------------------
2. Backup your database and execute following sql statements (with phpmyadmin for example):
INSERT INTO `cscart_payment_processors` (`processor`, `processor_script`, `processor_template`, `admin_template`, `callback`, `type`) VALUES 
('Viva Payments', 'hellaspay.php', 'hellaspay.tpl', 'hellaspay.tpl', 'N', 'P');

INSERT INTO `cscart_language_values` (`lang_code` ,`name` ,`value`) VALUES 
('EN', 'hellaspay_instalments', 'Instalments'), 
('EL', 'hellaspay_instalments', 'Δόσεις');

INSERT INTO `cscart_language_values` (`lang_code` ,`name` ,`value`) VALUES 
('EN', 'hellaspay_no_instalments', 'No Instalments'), 
('EL', 'hellaspay_no_instalments', 'Χωρίς Δόσεις');

INSERT INTO `cscart_language_values` (`lang_code` ,`name` ,`value`) VALUES 
('EN', 'hellaspay_instalments_step', 'Instalment steps'), 
('EL', 'hellaspay_instalments_step', 'Instalment steps');

INSERT INTO `cscart_language_values` (`lang_code` ,`name` ,`value`) VALUES 
('EN', 'hellaspay_instalments_start', 'First instalment'), 
('EL', 'hellaspay_instalments_start', 'First instalment');

INSERT INTO `cscart_language_values` (`lang_code` ,`name` ,`value`) VALUES 
('EN', 'hellaspay_transaction_fail', 'Transaction failed or has been cancelled.'), 
('EL', 'hellaspay_transaction_fail', 'Transaction failed or has been cancelled');

INSERT INTO `cscart_language_values` (`lang_code` ,`name` ,`value`) VALUES 
('EN', 'text_hellaspay_notice', '<b>Note:</b> Please set the following information to payment source:<br />Success URL: <u>[success_url]</u><br /> Fail URL: <u>[failure_url]</u><br /><br />'), 
('EL', 'text_hellaspay_notice', '<b>Note:</b> Please set the following information to payment source:<br />Success URL: <u>[success_url]</u><br /> Fail URL: <u>[failure_url]</u><br /><br />');

DROP TABLE IF EXISTS `hellaspaydata`;
CREATE TABLE IF NOT EXISTS `hellaspaydata` (
  `hp_id` int(11) NOT NULL AUTO_INCREMENT,
  `hp_oid` int(11) NOT NULL,
  `hp_code` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`hp_id`)
) ENGINE=InnoDB;
-------------------------------------------------------------------------------
3. Create a new source in the Viva Payments backoffice and specify success / fail URLs.
-------------------------------------------------------------------------------
4. Copy the file to your store root (no files are overwritten), copy the files that are inside the skins/basic
also to your used template (skins/yourusedtemplate).
-------------------------------------------------------------------------------
5. Add a the new payment method through your store admin, 
a. select from dropdown template -hellaspay.tpl-
b. select from dropdown processor -Viva Payments-
c. select payment category -Internet Payments- (if available)
d. add a title
e. in the configure tab, specify an instalment logic (optional)
f. save settings
-------------------------------------------------------------------------------
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