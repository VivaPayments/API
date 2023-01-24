import json
import urllib2
import urllib
from datetime import datetime, timedelta


def _timestamp_toUTC(timestamp):
    """Function to turn a timestamp to UTC datetime object.

    Arguments:
        timestamp (str): Timestamp returned from the API response.
    
    Attributes:
        offset (str): The last six characters representing the offset.
        sign (str): The offset sign (+ or -).
        hours (int): The hours that need to be converted.
        extra (int): The second part of the offset to be converted.
        transaction_date (datetime): The datetime object from the timestamp.

    Returns:
        Unaware Datetime object, in UTC time.
    """

    offset = timestamp[-6:]
    sign = offset[0]
    hours = int(offset.split(':')[0][1:])
    extra = int(offset.split(':')[1]) % 60.0 + hours
    transaction_date = datetime.strptime(timestamp[:-7], '%Y-%m-%dT%H:%M:%S.%f')
    return transaction_date - timedelta(hours=extra) if sign == '+' else transaction_date + timedelta(hours=extra)


class Urllib2Client(object):

    def make_request(self, method, url, headers, post_data=None):
        """Function that makes the API requests using the urllib2 module.

        Arguments:
            method (str): Request method to be used.
            url (str): The url the request will be sent to.
            headers (str): The headers to be included in the request.
            post_data (dict): The data to be passed in the URL's body.
        
        Returns:
            {
                'result': result
                'status_code': status_code
            }
            result (dict): Dictionary with the API response data.
            status_code (int): Integer with the status code returned from the API.

        Raises:
            urllib2.HTTPError: Generic urllib2 error handler.
        """

        request = urllib2.Request(url, post_data, headers)

        if method not in ('GET', 'POST'):
            request.get_method = lambda: method.upper()

        try:
            response = urllib2.urlopen(request)
            rbody = response.read()
            if hasattr(rbody, 'decode'):
                rbody = rbody.decode('utf-8')
            rcode = response.code
        except urllib2.HTTPError, e:
            raise urllib2.HTTPError(e.url, e.code, e.msg, e.hdrs, e.fp)

        try:
            result = json.loads(rbody)
        except ValueError:
            result = rbody

        if 'TimeStamp' in (result or {}):
            result['TimeStamp'] = _timestamp_toUTC(result['TimeStamp'])

        return {
            'result': result,
            'status_code': rcode
        }
