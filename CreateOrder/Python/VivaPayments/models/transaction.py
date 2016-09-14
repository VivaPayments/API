from datetime import datetime


class Transaction(object):
	"""Class to handle transaction API calls"""

	def __init__(self, config):
		self.config = config

	def Get(self, transaction_id=None, date=None, order_code=None):
		"""Function to get transactions based on function arguments.

		Args:
			transaction_id (str): Defaults to None. Passing transaction_id
				will search for the specific transaction info.
			date (str) or (datetime): Defaults to None. Passing date will
				result in all the transactions made this date. Date can be
				either string or datetime object.
			order_code (str): Defaults to None. Passing order_code will
				return all the transactions based on the order code. 

		"""
		if transaction_id:
			url = 'transactions/{}'.format(transaction_id)
		else:
			url = 'transactions/'

		if isinstance(date, datetime):
			date = date.strftime('%Y-%m-%d')
		elif date:
			date = date[:10]

		params = {
			'date': date,
			'ordercode': order_code
		}

		return self.config.call_api('GET', url, params=params)

	def Create(self, amount, order_code = None, card_token = None, **kwargs):
		"""Function used to generate a transaction based on order code and token.

		Args:
			amount (int): Transaction amount.
			order_code (str): The order code that the transaction will be based on.
			card_token (str): The credit card token created by passing the credit
				card to the API.

		"""
		url = 'transactions'
		data = {
			'Amount': amount,
			'OrderCode': order_code,
			'CreditCard': {
				'Token': card_token
			}
		}
		return self.config.call_api('POST', url, data = data, optional_parameters = kwargs)

	def CreateRecurring(self, transaction_id, amount, **kwargs):
		"""Function used to generate a recurring transaction based on specific transaction id.

		Args:
			transaction_id (str): The first transaction_id that was marked as recurring.
			amount (int): Transaction amount.

		"""
		url = 'transactions/{}'.format(transaction_id)
		data = {
			'Amount': amount
		}
		return self.config.call_api('POST', url, data = data, optional_parameters = kwargs)

	def Cancel(self, transaction_id, amount = None, action_user = None):
		"""Function used to cancel a transaction based on specific transaction id.

		Args:
			transaction_id (str): The transaction_id that will be cancelled.
			amount (int): Defaults to None. Transaction amount.
			action_user (str): Defaults to None. The user that initiated this action,
				could be used for logging purposes.

		"""  
		url = 'transactions/{}'.format(transaction_id)
		params = {
			'amount': amount,
			'actionuser': action_user
		}
		return self.config.call_api('DELETE', url, params = params)

	def OriginalCreditTransaction(self, transaction_id, amount, **kwargs):
		"""Function used to directly pay an amount to a card(refer to the docs).

		Args:
			transaction_id (str): The transaction that the original payment was done.
			amount (int): Amount to be paid.

		"""        
		url = 'transactions/{}'.format(transaction_id)
		params = {
			'Amount': amount,
			'ServiceId': 6
		}
		return self.config.call_api('DELETE', url, params = params, optional_parameters = kwargs)





