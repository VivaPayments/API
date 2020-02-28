using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Net;
using System.IO;

using RestSharp;

/*
 *  This example uses RestSharp, a simple REST and HTTP API Client for .NET (http://restsharp.org/ - https://github.com/restsharp/restsharp)
 */

public partial class _CSharp : System.Web.UI.Page
{
    // Redirect URL
    string vivaPaymentFormURL = "https://demo.vivapayments.com/web/newtransaction.aspx?ref=";
    //string vivaPaymentFormURL = "https://www.vivapayments.com/web/newtransaction.aspx?ref="; // production URL
    
    // class that contains the order options that will be sent
    public class OrderOptions
    {    
        public long Amount { get; set; }
    }

    // class that contains the order results
    public class OrderResult
    {
        public long? OrderCode { get; set; }
        public int ErrorCode { get; set; }
        public string ErrorText { get; set; }
        public DateTime TimeStamp { get; set; }

    }

    protected void Page_Load(object sender, EventArgs e)
    {
        System.Net.ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
        var cl = new RestClient("https://demo.vivapayments.com/");
        //var cl = new RestClient("https://www.vivapayments.com/"); // production URL

        var req = new RestRequest("api/orders", Method.POST);

        req.AddObject(new OrderOptions()
        {
            Amount = 100    // Amount is in cents

        });

        string MerchantId = Guid.Parse("1c204c59-9890-499c-bf5d-31e80dcdbdfd").ToString();    // the merchant id is found in the self-care environment (developers menu)
        string Password = "a123456"; // the password is set in the self-care environment (developers menu)

        cl.Authenticator = new HttpBasicAuthenticator(MerchantId, Password);

        // Do the post 
        var res = cl.Execute<OrderResult>(req);

        // once the order code is successfully created, redirect to payment form to complete the payment
        if (res.Data.ErrorCode == 0)
        {
            Response.Redirect(this.vivaPaymentFormURL + res.Data.OrderCode.ToString());
        }
    }
}
