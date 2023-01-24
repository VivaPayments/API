import os
import unittest
from base import *


class CardTestCase(VivaTestClient):

	@unittest.skip("Skipping CreateToken.")
	def test_create_token(self):
		ret = self.test_client.Card.CreateToken(os.environ['PUBLIC_KEY'], 4111111111111111, 123, '2019-12-12', 'Igneel64')
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(len(ret['result']['Token']), 352)

	@unittest.skip("Skipping check_installments.")
	def test_check_installments(self):
		ret = self.test_client.Card.CheckInstallments(4111111111111111)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(result.keys(), ['MaxInstallments'])