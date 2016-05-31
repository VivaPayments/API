using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using RestSharp;

namespace Win32Payments
{
    public class ApiClient
    {
        private string _PaymentsCreateOrderUrl = "/api/orders";
        private string _PaymentsTokenizeUrl = "/api/cards?key={0}";
        private string _PaymentsExecutionUrl = "/api/transactions";
        private string _PaymentsInstallmentsCheckUrl = "/api/cards/installments?key={0}";
        private string _BaseApiUrl;
        private Guid _MerchantId;
        private string _ApiKey;
        private string _PublicKey;

        public ApiClient(string baseApiUrl, Guid merchantId, string apiKey, string publicKey)
        {
            _BaseApiUrl = baseApiUrl;
            _MerchantId = merchantId;
            _ApiKey = apiKey;
            _PublicKey = publicKey;
        }

        public IRestResponse<OrderResult> CreateOrder(long amount, string sourceCode)
        {
            var cl = new RestClient(_BaseApiUrl);
            cl.Authenticator = new HttpBasicAuthenticator(
                                    _MerchantId.ToString(),
                                    _ApiKey);

            var req = new RestRequest(_PaymentsCreateOrderUrl, Method.POST);

            req.AddObject(new
            {
                Amount = amount,    // Amount is in cents
                SourceCode = sourceCode
            });

            return cl.Execute<OrderResult>(req);
        }

        public IRestResponse<TokenizeResult> Tokenize(string CardHolderName, string CardNumber, int CVV, int ExpiryMonth, int ExpiryYear)
        {
            var cl = new RestClient(_BaseApiUrl);
            _PaymentsTokenizeUrl = String.Format(_PaymentsTokenizeUrl, RestSharp.Contrib.HttpUtility.UrlEncode(_PublicKey));
            var req = new RestRequest(_PaymentsTokenizeUrl, Method.POST);

            req.AddObject(new
            {
                CardHolderName = CardHolderName,
                Number = CardNumber,
                CVC = CVV,
                ExpirationDate = new DateTime(ExpiryYear, ExpiryMonth, 15, 0, 0 ,0, DateTimeKind.Utc).ToString("yyyy-MM-dd")
            });

            return cl.Execute<TokenizeResult>(req);
        }

        public TransactionResult ExecuteTransaction(long OrderCode, string SourceCode, string Token, int Installments = 1)
        {
            var cl = new RestClient(_BaseApiUrl);
            cl.Authenticator = new HttpBasicAuthenticator(
                                    _MerchantId.ToString(),
                                    _ApiKey);

            var req = new RestRequest(_PaymentsExecutionUrl, Method.POST);
            req.RequestFormat = DataFormat.Json;
            req.AddBody(new
            {
                OrderCode = OrderCode,
                SourceCode = SourceCode,             //MAKE SURE THIS IS A SOURCE OF TYPE NATIVE/SIMPLE 
                Installments = Installments,
                CreditCard = new
                {
                    Token = Token
                }
            });


            var res = cl.Execute<TransactionResult>(req);
            return res.Data;
        }

        public IRestResponse<CheckInstallmentsResult> CheckInstallments(string CardNumber)
        {
            var cl = new RestClient(_BaseApiUrl);
            _PaymentsInstallmentsCheckUrl = String.Format(_PaymentsInstallmentsCheckUrl, RestSharp.Contrib.HttpUtility.UrlEncode(_PublicKey));
            
            var req = new RestRequest(_PaymentsInstallmentsCheckUrl, Method.GET);
            req.AddHeader("CardNumber", CardNumber);
            req.RequestFormat = DataFormat.Json;

            return cl.Execute<CheckInstallmentsResult>(req);
        }

    }

    public class OrderResult
    {
        public long OrderCode { get; set; }
        public int ErrorCode { get; set; }
        public string ErrorText { get; set; }
        public DateTime TimeStamp { get; set; }

    }

    public class TokenizeResult
    {
        public string Token {get; set;}
    }

    public class CheckInstallmentsResult
    {
        public int MaxInstallments { get; set; }
    }

    public class TransactionResult
    {
        public string StatusId { get; set; }
        public Guid TransactionId { get; set; }
        public int ErrorCode { get; set; }
        public string ErrorText { get; set; }
        public DateTime TimeStamp { get; set; }
    }

}
