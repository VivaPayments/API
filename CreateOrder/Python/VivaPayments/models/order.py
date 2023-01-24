

class Order(object):
	"""Class to handle order API calls"""
	def __init__(self, config):
		self.config = config

	def Create(self, amount, **kwargs):
		"""Function used to create a new order.
		
		Args:
			amount (int): Credit amount for the order.

		"""
		data = {
			'Amount': amount
		}
		return self.config.call_api('POST', 'orders', data = data, optional_parameters = kwargs)

	def Cancel(self, order_code, **kwargs):
		"""Function used to create a new order.
		
		Args:
			order_code (str): Code of the order to be cancelled.

		"""
		url = 'orders/{}'.format(order_code)
		return self.config.call_api('DELETE', url, optional_parameters = kwargs)

	def Get(self, order_code):
		"""Function used to get a specific order and info.

		Args:
			order_code (str): Code of the order to aquire info.

		"""
		url = 'orders/{}'.format(order_code)
		return self.config.call_api('GET', url)

	def Update(self, order_code, amount = None, is_canceled = None, disable_paid_state = None, expiration_date = None):
		"""Function used to update specific order.

		Args:
			order_code (str): Code of the order to be updated.
			amount (int): Defaults to None. The new amount of the order.
			is_canceled (bool): Defaults to None. Changes 
				the canceled state of the order.
			disabled_paid_state (bool): Defaults to None. Enables
				multiple payments for an order
			expiration_date (str): Defaults to None. If passed
				changes the expiration date of the order.

		"""
		url = 'orders/{}'.format(order_code)
		data = {
			'Amount': amount,
			'IsCanceled': is_canceled,
			'DisablePaidState': disable_paid_state,
			'ExpirationDate': expiration_date
		}
		return self.config.call_api('PATCH', url, data = data)