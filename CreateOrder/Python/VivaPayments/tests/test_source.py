import unittest
from base import *


class SourceTestCase(VivaTestClient):

	@unittest.skip("Skipping add.")
	def test_add(self):
		ret = self.test_client.Source.Add(
			'Test Source', 'test1', 'mydomain.com', False, 'site/failure.py', 'site/success.py')
		result = ret['result']
		self.assertEqual(ret['status_code'], 200)
		self.assertIsNone(result['result'])
