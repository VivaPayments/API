import os
import unittest
from VivaPayments import client

TEST_MERCHANT = os.environ['TEST_MERCHANT']
TEST_KEY = os.environ['TEST_KEY']

class VivaTestClient(unittest.TestCase):
	"""unittest.TestCase subclass used for the modules tests.

	This class provides the test client already initialized and all
	the unittest.TestCase methods.

	"""
	test_client = client.Client( TEST_MERCHANT, TEST_KEY)
	