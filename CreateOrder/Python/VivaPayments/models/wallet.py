

class Wallet(object):
	"""Class to handle wallet API calls"""

	def __init__(self, config):
		self.config = config

	def BalanceTransfer(self, wallet_id, amount, target_wallet=None, person_id=None, description=None):
		"""Function that is used to transfer wallet funds to another wallet or person.

		Args:
				wallet_id (str): The wallet that the money originate from.
				amount (int): The amount to be transfered.
				target_wallet (str): Default to None. The target wallet that the money will be transfered to.
				person_id (str): The person account Id that the money will be transfered to.
				description (str): The reason for transfer.

		"""
		if (target_wallet and person_id) or (not target_wallet and not person_id):
			raise Exception(
				'Balance transfer requires either target wallet or target person ID.')
		if person_id:
			url = 'wallets/{}/balancetransfer?TargetPersonId={}'.format(
				wallet_id, person_id)
		elif target_wallet:
			url = 'wallets/{}/balancetransfer/{}'.format(
				wallet_id, target_wallet)
		data = {
			'Amount': amount,
			'Description': description
		}
		return self.config.call_api('POST', url, data=data)
