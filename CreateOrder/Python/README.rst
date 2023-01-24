VivaPayments Python API wrapper
===============================

.. figure:: https://www.vivawallet.com/App_Themes/VivaWallet/Resources/img/viva-logo.png
   :alt: N\|Solid

   N\|Solid

Installation
~~~~~~~~~~~~

.. code:: bash

    python setup.py install

PyPI will be up soon :)

Usage
~~~~~

.. code:: sh

    from VivaPayments import client
    viva_client = client.Client('MERCHANT_ID', 'API_KEY')
    #for production do client.Client('MERCHANT_ID', 'API_KEY', 'production')

With viva\_client you can call any model you require and the desired
action. The tests folder can serve as an initial guideline for your API
calls.

.. code:: sh

    #example
    order = viva_client.Order.Create(100)
    order_code = order['result']['OrderCode']

Models
~~~~~~

The Python wrapper currently supports the listed calls.

-  Card (CreateToken, CheckInstallments)
-  Order(Create, Cancel, Get, Update)
-  Source(Add)
-  Transaction(Get, Create, CreateRecurring, Cance,
   OriginalCreditTransaction)
-  Wallet(BalanceTransfer)

For some of the calls you need special permissions from Viva so consult
the wiki before using.

Testing
~~~~~~~

.. code:: sh

    python setup.py test

For the tests to pass you need to set up the followiwng enviroment
variables.: \* TEST\_MERCHANT (Your demo merchant ID) \* TEST\_KEY (Your
demo API Key) \* WALLET\_ID (Your Viva wallet ID)

License
-------

MIT

Documentation
~~~~~~~~~~~~~

Code is clearly documented inside the module at the moment but will be
officially documented after the PyPI release.

For more information about the API usage refer to `Viva wiki`_.

.. _Viva wiki: https://github.com/VivaPayments/API/wiki