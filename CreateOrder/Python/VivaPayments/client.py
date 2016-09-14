import json
import logging
import urllib
from base64 import b64encode
from util.http import Urllib2Client
from util.optional import allowed_parameters
from models import *

class Client(object):
    """Client responsible for constructing the API calls.
    
    Example:
        new_client = Client(merchant_id = 'TEST_ID', api_key = 'TEST_KEY')
        new_client.Order.Create(100)

    """
    http_client = Urllib2Client()

    def __init__(self, merchant_id=None, api_key=None, enviroment=None):
        """__init__ function for Client

        Args:
            merchant_id (str): Defaults to None. The client's Merchant ID.
            api_key (str): Defaults to None. The client's API Key.
            enviroment (str): Defaults to None. The enviroment that 
                the client calls will be directed to. The production enviroment
                will only be called if 'production' is passed.

        Attributes:
            url (str): URL that will be used for the API calls. Depends on the
                enviroment argument.
            headers (str): Authentication header that is used in every request.
            Transaction (object): Transaction class instance.
            Order (object): Order class instance.
            Source (object): Source class instance.
            Card (object): Card class instance.
            Wallet (object): Wallet class instance.

        Raises:
            Exception: If the Client instance was initialized without Merchant ID or API Key.
        """
        if not (api_key and merchant_id):
            raise Exception('Client must be instantiated with Merchant ID and API Key.')

        self.api_key = api_key
        self.merchant_id = merchant_id
        self.url = 'https://www.vivapayments.com/api/' if enviroment in [
            'production'] else 'http://demo.vivapayments.com/api/'
        self.enviroment = 'demo' if 'demo' in self.url else 'production'
        self.headers = 'Basic {}'.format(
            b64encode(self.merchant_id + ':' + self.api_key))

        self.Transaction = Transaction(self)
        self.Order = Order(self)
        self.Source = Source(self)
        self.Card = Card(self)
        self.Wallet = Wallet(self)

    def __repr__(self):
        return 'Vivapayments {} Client object with api_key: {} , merchant_id: {} '.format(self.enviroment, self.api_key, self.merchant_id)

    def call_api(self, method, path, params=None, data=None, headers=None, optional_parameters = None):    
        """Function that formats the API request.

        The call_api function is used for every request done to API, it formats the request parameters,
        request body and optional parameters if expected and optional headers passed. Finally uses the
        http_client to fullfill the request.

        Args:
            method (str): The HTTP method that will be used.
            path (str): The path that the request is directed to.
            params (dict): Defaults to None. URL parameters to be used in the API call.
            data (dict): Defaults to None. Data to be used in the request's body if the
                appropriate method is used.
            headers (dict): Defaults to None. Optional headers that will be used along with
                the Basic Auth for the API.
            optional_parameters (dict): Defaults to None. Any optional parameter that could be
                passed in the request's body.

        Raises:
            Exception: HTTP method not supported by the API.

        """
        headers = headers if headers else {}
        headers['Authorization'] = self.headers

        request_url = self.url + path

        if params:
            request_url = request_url + '?' + urllib.urlencode(self._pack(params))

        if data and method not in ['POST', 'PUT', 'PATCH', 'DELETE']:
            raise Exception('Incorrect HTTP method for arguments: ' + data)
        elif data:
            headers['Content-type'] = 'application/json'
            if optional_parameters:
                self._check_allowed(optional_parameters)
            data = json.dumps(self._pack(data, optional_parameters = optional_parameters))

        return self.http_client.make_request(method, request_url, headers, post_data = data)


    def _pack(self, data, optional_parameters =  None):
        """Function to merge the standard with optional params in the request.

        The _pack function is used to add the optional parameters along with the standard, in the
        body of the request. It is also used to remove None values from body and URL params. 

        Args:
            data (dict): The standard data to be passed in the URL body.
            optional_parameters (dict): Any optional parameters that the user added.

        Returns:
            merged_data (dict): Dictionary stripped of the None valued keys, along with
                any optional parameters merged.

        Example:
            data = {'OrderCode':'1234', 'date': None}
            optional_parameters = {'TransactionId': '5678'}
            merged_data = _pack(data, optional_parameters)
            '{'OrderCode':'1234', 'TransactionId': '5678'}'

        """
        data = dict((key,value) for key, value in data.iteritems() if value != None)
        merged_data = data.copy()
        if optional_parameters: merged_data.update(optional_parameters)
        return merged_data

    def _check_allowed(self, optional_parameters):
        """Function that checks the validity of the optional parameters passed.

        The _check_allowed function, checks if the optional parameteres that were
        passed in the request, are valid based on the online documentation and warns
        the user using the logging module.

        Args:
            optional_parameters (dict): The optional parameters to be passed in the request.

        """
        for key in optional_parameters.keys():
            if key not in allowed_parameters.keys():
                logging.warn('Parameter {} is most likely not supported by the API.'.format(key))


