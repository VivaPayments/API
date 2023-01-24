import unittest
from base import *
from datetime import datetime


class TransactionTestCase(VivaTestClient):

	def setUp(self):
		card = self.test_client.Card.CreateToken(
			os.environ['PUBLIC_KEY'], 4111111111111111, 123, '2019-12-12', 'Igneel64')
		self.card_token = card['result']['Token']

		order = self.test_client.Order.Create(100)
		self.order_code = order['result']['OrderCode']

	def tearDown(self):
		pass

	@unittest.skip("Skipping create.")
	def test_create(self):
		ret = self.test_client.Transaction.Create(
			1000, order_code=self.order_code)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertIsNone(result['Amount'])
		self.assertEqual(result['ErrorCode'], 403)
		self.assertIsNone(result['TransactionId'])
		self.assertIsNone(result['AuthorizationId'])
		self.assertTrue(isinstance(result['TimeStamp'], datetime))

		ret = self.test_client.Transaction.Create(
			1000, order_code=self.order_code, card_token=self.card_token)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(result['Amount'], 10.0)
		self.assertEqual(result['ErrorCode'], 0)
		self.assertIsNotNone(result['TransactionId'])
		self.assertIsNotNone(result['AuthorizationId'])
		self.assertTrue(isinstance(result['TimeStamp'], datetime))

	@unittest.skip("Skipping create_recurring.")
	def test_create_recurring(self):
		initial_transaction = self.test_client.Transaction.Create(
			1000, order_code=self.order_code, card_token=self.card_token, AllowsRecurring=True)
		initial_transaction_id = initial_transaction['result']['TransactionId']

		ret = self.test_client.Transaction.CreateRecurring(
			initial_transaction_id, 1000)
		result = ret['result']

		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(result['Amount'], 10.0)
		self.assertEqual(result['ErrorCode'], 0)
		self.assertNotEqual(result['TransactionId'], initial_transaction_id)
		self.assertIsNotNone(result['AuthorizationId'])
		self.assertTrue(isinstance(result['TimeStamp'], datetime))

		ret = self.test_client.Transaction.CreateRecurring(
			initial_transaction_id, 2000)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(result['Amount'], 20.0)
		self.assertEqual(result['ErrorCode'], 0)
		self.assertNotEqual(result['TransactionId'], initial_transaction_id)
		self.assertIsNotNone(result['AuthorizationId'])
		self.assertTrue(isinstance(result['TimeStamp'], datetime))

	@unittest.skip("Skipping get.")
	def test_get(self):
		ret = self.test_client.Transaction.Get(order_code=self.order_code)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(result['ErrorCode'], 0)
		self.assertFalse(result['Transactions'])
		self.assertIsNone(result['ErrorText'])
		self.assertTrue(isinstance(result['TimeStamp'], datetime))

		transaction = self.test_client.Transaction.Create(
			100, order_code=self.order_code, card_token=self.card_token)
		transaction_id = transaction['result']['TransactionId']

		ret = self.test_client.Transaction.Get(order_code=self.order_code)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(result['ErrorCode'], 0)
		self.assertIsNotNone(result['Transactions'])
		self.assertEqual(result['Transactions'][0]['Order'][
						 'OrderCode'], self.order_code)

		ret = self.test_client.Transaction.Get(date=datetime.now())
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(result['ErrorCode'], 0)
		self.assertIsNotNone(result['Transactions'])

	@unittest.skip("Skipping cancel.")
	def test_cancel(self):
		transaction = self.test_client.Transaction.Create(
			100, order_code=self.order_code, card_token=self.card_token)
		transaction_id = transaction['result']['TransactionId']

		ret = self.test_client.Transaction.Cancel(transaction_id)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertIsNotNone(result['ErrorText'])
		self.assertEqual(result['ErrorCode'], 400)
		self.assertIsNone(result['TransactionId'])

		ret = self.test_client.Transaction.Cancel(transaction_id, amount=10)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertIsNotNone(result['ErrorText'])
		self.assertEqual(result['ErrorCode'], 400)
		self.assertIsNone(result['TransactionId'])

		ret = self.test_client.Transaction.Cancel(transaction_id, amount=100)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertIsNone(result['ErrorText'])
		self.assertEqual(result['ErrorCode'], 0)

		ret = self.test_client.Transaction.Cancel(transaction_id, amount=100)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertEqual(result['ErrorText'], 'Non reversible transaction')
		self.assertEqual(result['ErrorCode'], 403)

	@unittest.skip("Skipping OCT.")
	def test_original_credit_transaction(self):
		transaction = self.test_client.Transaction.Create(
			100, order_code=self.order_code, card_token=self.card_token)
		transaction_id = transaction['result']['TransactionId']
		ret = self.test_client.Transaction.OriginalCreditTransaction(
			transaction_id, 10)
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertTrue(isinstance(result['TimeStamp'], datetime))
		self.assertEqual(result['ErrorCode'], 403)
