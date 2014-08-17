"""
Depends on the dateutil module
`pip install python-dateutil`
"""
import urllib2
import urllib
import base64
import json
from dateutil.parser import parse as duparse

class VivaPayments(object):
    """VivaPayments API Wrapper"""

    # Demo constants
    DEMO_URL = 'http://demo.vivapayments.com/api/'
    DEMO_REDIRECT_URL = 'http://demo.vivapayments.com/web/newtransaction.aspx?ref='

    # Production constants
    PRODUCTION_URL = 'https://www.vivapayments.com/api/'
    PRODUCT_REDIRECT_URL = 'http://www.vivapayments.com/web/newtransaction.aspx?ref='

    def __init__(self,merchant_id=None,api_key=None,production=False):
        self.url = VivaPayments.DEMO_URL \
            if production == False \
                else VivaPayments.PRODUCT_REDIRECT_URL
        self.merchant_id=merchant_id
        self.api_key=api_key

    def create_order(self,amount,**kwargs):
        """Create a Payment Order."""
        data = self.pack_data('amount',amount,kwargs)
        return self._request('POST','orders',data)

    def cancel_order(self,order_code,**kwargs):
        """Cancel an existing Payment Order."""
        data = self.pack_data('order_code',order_code,kwargs)
        return self._request('DELETE','orders/'+str(order_code),data)

    def get_transaction(self,transaction_id,**kwargs):
        """Get all details for a specific transaction, or for all transactions of a given date."""
        data = self.pack_data('transaction_id',transaction_id,kwargs)
        return self._request('GET','transactions/'+str(transaction_id),data)

    def create_recurring_transaction(self,transaction_id,**kwargs):
        """Make a recurring transaction."""
        data = self.pack_data('transaction_id',transaction_id,kwargs)
        return self._request('POST','transactions/'+str(transaction_id),data)

    def cancel_transaction(self,transaction_id,**kwargs):
        """Cancel or refund a payment."""
        data = self.pack_data('transaction_id',transaction_id,kwargs)
        return self._request('DELETE','transactions/'+str(transaction_id),data)
   
    def get_redirect_url(self,order_code):
        """Returns the order code appended on the REDIRECT_URL_PREFIX"""
        redirect_url = VivaPayments.DEMO_REDIRECT_URL \
            if self.url == VivaPayments.DEMO_URL \
                else VivaPayments.PRODUCT_REDIRET_URL
        return redirect_url+str(order_code)

    ### UTILITY FUNCTIONS ###
    def pack_data(self,arg_name,arg_val,kwargs):
        return dict({arg_name:arg_val}.items() + kwargs.items())

    def _request(self,request_method,url_suffix,data):
        # Construct request object
        data = urllib.urlencode(data)
        request_url = self.url + url_suffix
        request = urllib2.Request(request_url,data=data)

        # Request basic access authentication
        base64string = base64.encodestring('%s:%s' % \
                (self.merchant_id,self.api_key)).replace('\n', '')
        request.add_header("Authorization", "Basic %s" % base64string)   

        # Set http request method
        request.get_method = lambda: request_method
        response = urllib2.urlopen(request)
        return self._decode(response.read())

    def _decode(self,json_response):
        obj = json.loads(json_response)
        # TimeStamp is always present
        obj['TimeStamp'] = duparse(obj['TimeStamp'])
        # Transaction response
        if 'Transactions' in obj:
            for t in obj['Transactions']:
                for key in ('InsDate', 'ClearanceDate'):
                    if t[key]:
                        t[key] = duparse(t['InsDate'])
        return obj

# Examples
if __name__ == '__main__':
    # Create vivapayments API Wraper
    viva_payments = VivaPayments(merchant_id='1b2573e7-2f67-4443-8a2e-84cac16ec79f',api_key='09014933')
    
    # Example 1

    # Create order 
    result = viva_payments.create_order(100,RequestLang='en-US')
    
    # Get order code
    order_code = result['OrderCode']

    # Get redirect url
    redirect_url = viva_payments.get_redirect_url(order_code)

    # Get the redirect url and paste it at your browser
    print redirect_url


