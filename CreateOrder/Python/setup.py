import os
from setuptools import setup

def read(fname):
    return open(os.path.join(os.path.dirname(__file__), fname)).read()

setup(
	name = 'VivaPython',
	version = '0.0.1',
	description = 'Python wrapper for VivaPayments API',
	author = 'igneel64',
	author_email = 'p.perlepes@gmail.com',
	long_description = read('README.rst'),
	classifiers = [
		 'Development Status :: 4 - Beta',
		 'Intended Audience :: Developers',
		 'Programming Language :: Python :: 2.7'
	],
	license = 'LICENSE.txt',
	keywords = 'Viva VivaPayments payment',
	packages = ['VivaPayments', 'VivaPayments.util', 'VivaPayments.models', 'VivaPayments.tests'],
	test_suite = 'VivaPayments.tests.viva_test_suite'
)