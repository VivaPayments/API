using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using RestSharp;
using Newtonsoft.Json;

/*
 *  This example uses RestSharp, a simple REST and HTTP API Client for .NET (http://restsharp.org/ - https://github.com/restsharp/restsharp) 
 *  and Newtonsoft Json.NET, a Popular JSON framework for .NET (http://james.newtonking.com/json)
 */

public partial class _Default : System.Web.UI.Page
{
    private Guid _MerchantId = Guid.Parse("22C8A516-BE4A-4B21-AB5C-01F25C02557C");
    private string _ApiKey = "myapitestkey";
    private string _BaseApiUrl = "http://demo.vivapayments.com";
    private string _WebhooksAuthUrl = "/api/messages/config/token";

    protected void Page_Load(object sender, EventArgs e)
    {
        if (Request.HttpMethod == "POST") {
            //POST METHOD DETECTED. NEW WEBHOOK NOTIFICATION, PARSE INPUT

            //READ INPUT STREAM
            string data;
            using (var reader2 = new System.IO.StreamReader(HttpContext.Current.Request.InputStream)) {
                data = reader2.ReadToEnd();
            }

            //DESERIALIZE data TO A Message<TransactionEventData>
            if (!String.IsNullOrEmpty(data)) {
                Message<TransactionEventData> notif = null;
                try {
                    notif = JsonConvert.DeserializeObject<Message<TransactionEventData>>(data);
                }
                catch { }

                if (notif != null &&
                        notif.EventData != null) {
                    var ev = notif.EventData;
                    Response.Write(String.Format(
                        "{0}<br />{1}",
                        ev.TransactionId,
                        ev.Amount));
                    return;
                }
            }
            Response.Write("Input is null or empty");
        }
        else {
            //GET METHOD DETECTED. AUTHENTICATE WEBHOOK AND PRINT RESPONSE
            var cl = new RestClient(_BaseApiUrl);
            cl.Authenticator = new HttpBasicAuthenticator(
                                    _MerchantId.ToString(),
                                    _ApiKey);

            var req = new RestRequest(_WebhooksAuthUrl, Method.GET);
            var res = cl.Execute(req);

            if (res.StatusCode == System.Net.HttpStatusCode.OK) {
                Response.Write(res.Content);
            }
            else
                throw new ApplicationException("Need to handle non OK response from Viva Payments");
        }
    }
}