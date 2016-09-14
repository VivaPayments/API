from datetime import datetime


class Card(object):

	def __init__(self, config):
		self.config = config

	def CreateToken(self, public_key, cc_id, cvc, expiration_date, cardholder_name):
		"""Function used to generate a credit card token.

		Args:
				public_key (str): The user's public API key.
				cc_id (int): The credit card number.
				cvc (int): The credit card CVC/CVV.
				expiration_date (str) or (datetime): The credit card's 
						expiration date.
				cardholder_name: The cardholder name of the card. 

		"""
		if isinstance(expiration_date, datetime):
			expiration_date = date.strftime('%Y-%m-%d')
		elif expiration_date:
			expiration_date = expiration_date[:10]

		params = {
			'key': public_key
		}
		headers = {
			'NativeCheckoutVersion': '230'
		}
		data = {
			'Number': cc_id,
			'CVC': cvc,
			'ExpirationDate': expiration_date,
			'CardHolderName': cardholder_name
		}
		return self.config.call_api('POST', 'cards', params=params, data=data)

	def CheckInstallments(self, cc_id):
		"""Function used to check the installments limit of a credit card.

		Args:
				cc_id (int): The credit card number to be checked. 

		"""
		headers = {
			'CardNumber': cc_id
		}
		return self.config.call_api('GET', 'cards/installments', headers=headers)
