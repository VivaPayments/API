import unittest
from base import *
from datetime import datetime


class OrderTestCase(VivaTestClient):

	@unittest.skip("Skipping create.")
	def test_create(self):
		ret = self.test_client.Order.Create(100)
		result = ret['result']
		self.assertTrue(isinstance(result['TimeStamp'], datetime))
		self.assertTrue(result['OrderCode'])
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(result['ErrorCode'], 0)
		self.assertIsNone(result['ErrorText'])

	@unittest.skip("Skipping get.")
	def test_get(self):
		order = self.test_client.Order.Create(100)
		order_code = order['result']['OrderCode']
		ret = self.test_client.Order.Get(order_code)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(ret['result'].keys(), ['RequestAmount', 'MerchantTrns', 'OrderCode',
												'Tags', 'StateId', 'ExpirationDate', 'SourceCode', 'RequestLang', 'CustomerTrns'])

	@unittest.skip("Skipping cancel.")
	def test_cancel(self):
		order = self.test_client.Order.Create(100)
		order_code = order['result']['OrderCode']
		ret = self.test_client.Order.Cancel(order_code)
		result = ret['result']
		self.assertTrue(isinstance(result['TimeStamp'], datetime))
		self.assertEqual(result['OrderCode'], order_code)
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(result['ErrorCode'], 0)
		self.assertIsNone(result['ErrorText'])

	@unittest.skip("Skipping update.")
	def test_update(self):
		order = self.test_client.Order.Create(100)
		order_code = order['result']['OrderCode']
		ret = self.test_client.Order.Update(order_code, amount=40000)
		self.assertEqual(ret['status_code'], 200)
		self.assertIsNone(ret['result'])
