This php example uses an existing demo account that has the return URLs (see source.docx) defined as :

- http://localhost/phpExample/success.php
- http://localhost/phpExample/fail.php

To run the code open the file create_order.php from a web browser. It will automatically create an order for 1 euro (100cents) and redirect you directly to the demo viva checkout page.

To make a successfull payment use the credit card 4111 1111 1111 1111. After a successful transaction you are redirected to the the success.php page, where you can cancel your transaction by clicking the link 'refund transaction' (refund.php). Transactions that are refunded in the same business day are cancelled (void, total amount only). From the next business day onwards transactions can be refunded which includes partial refunds.

To make a recuring transaction (AllowRecurring is already set to true when creating an order in the create_order.php page) check the checkbox "Αποδέχομαι επαναλαμβανόμενες χρεώσεις..." and the link "do transaction 1.20 euro" will appear in the success.php page. Clicking this link simply make a recurring payment of 1.20 euro using the transaction details of your original transaction (doTransaction.php).

