

class Source(object):
	"""Class to handle source API calls"""
	def __init__(self, config):
		self.config = config

	def Add(self, name, source_code, domain, is_secure, path_fail, path_sucess):
		"""Function used to add a new source for sales groups.

		Args:
			name (str): The name to be used for the new source.
			source_code (str): A unique code to be used for the new source.
			domain (str): The primary domain for the new source.
			is_secure (bool): Value indicating id the protocol is http or https.
			path_fail (str): The relative path the client will end up after
				a failed transaction.
			path_success (str): The relative path the client will end up after
				a successful transaction.

		"""
		data = {
			'Name': name,
			'SourceCode': source_code,
			'Domain': domain,
			'IsSecure': is_secure,
			'PathFail': path_fail,
			'PathSuccess': path_sucess
		}
		return self.config.call_api('POST', 'Sources', data = data)
