import unittest

def viva_test_suite():
	test_loader = unittest.TestLoader()
	return test_loader.discover('.', pattern = 'test_*.py')

