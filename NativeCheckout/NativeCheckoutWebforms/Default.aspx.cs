using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RestSharp;

public partial class _Default : System.Web.UI.Page 
{
    private Guid _MerchantId = new Guid("90DDA476-CF7C-4CFD-A9CB-23DFE11F131E");
    private string _ApiKey = "XXXXXX";
    private string _BaseApiUrl = "http://demo.vivapayments.com";
    private string _PaymentsUrl = "/api/transactions";
    private string _PaymentsCreateOrderUrl = "/api/orders";

    protected void Page_Load(object sender, EventArgs e)
    {
        if (Request.HttpMethod == "POST") {

            var _orderCode = CreateOrder(10000);    //CALL TO CREATE AN ORDER. IF AN ORDER CODE ALREADY EXISTS FROM A PREVIOUS STEP, USE THAT ONE INSTEAD

            var cl = new RestClient(_BaseApiUrl);
            cl.Authenticator = new HttpBasicAuthenticator(
                                    _MerchantId.ToString(),
                                    _ApiKey);

            var req = new RestRequest(_PaymentsUrl, Method.POST);
            req.RequestFormat = DataFormat.Json;
            req.AddBody(new {
                OrderCode = _orderCode,
                SourceCode = "Default",             //MAKE SURE THIS IS A SOURCE OF TYPE SIMPLE/NATIVE  
                CreditCard = new {
                    Token = hidToken.Value.ToString()
                }
            });
            
            paymentform.Visible = false;

            var res = cl.Execute<TransactionResult>(req);
            if (res.Data != null && res.Data.ErrorCode == 0 && res.Data.StatusId == "F") {
                Response.Write(String.Format(
                    "Transaction was successful. TransactionId is {0}",
                    res.Data.TransactionId));
            }
            else{
                Response.Write(String.Format(
                    "Transaction failed. Error code was {0}",
                    res.Data.ErrorCode));
            }
            
        }
    }

    private long CreateOrder(long amount)
    {
        var cl = new RestClient(_BaseApiUrl);
        cl.Authenticator = new HttpBasicAuthenticator(
                                _MerchantId.ToString(), 
                                _ApiKey);

        var req = new RestRequest(_PaymentsCreateOrderUrl, Method.POST);

        req.AddObject(new {
            Amount = 100,    // Amount is in cents
            SourceCode = "Default"
        });
                
        var res = cl.Execute<OrderResult>(req);

        if (res.Data != null && res.Data.ErrorCode == 0) {
            return res.Data.OrderCode;
        }
        else
            return 0;
    }

    public class OrderResult
    {
        public long OrderCode { get; set; }
        public int ErrorCode { get; set; }
        public string ErrorText { get; set; }
        public DateTime TimeStamp { get; set; }

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