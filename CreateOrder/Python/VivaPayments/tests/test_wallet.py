import os
import unittest
import urllib2
from base import *


class TransactionTestCase(VivaTestClient):

	@unittest.skip("Skipping balance_transfer.")
	def test_balance_transfer(self):
		with self.assertRaises(urllib2.HTTPError):
			self.test_client.Wallet.BalanceTransfer(
				os.environ['WALLET_ID'], 1000, target_wallet='587200706057')