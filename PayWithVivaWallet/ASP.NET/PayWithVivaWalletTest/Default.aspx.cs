using System;
using System.Web.UI;

using RestSharp;

namespace WebApplication2
{
    public partial class _Default : Page
    {
        private const string merchantId = "6466348D-85B2-4CBC-978B-422C688D2D45";
        private const string apiKey = "Y^!xL#";

        protected async void Page_Load(object sender, EventArgs e)
        {
            if (Request.HttpMethod == "POST") {
                var cl = new RestClient("https://demo.vivapayments.com/api/") {
                    Authenticator = new HttpBasicAuthenticator(merchantId, apiKey)
                };
                var request = new RestRequest("transactions", Method.POST) {
                    RequestFormat = DataFormat.Json
                };

                request.AddParameter("PaymentToken", Request.Form["vivaWalletToken"]);

                var response = await cl.ExecuteTaskAsync<TransactionResult>(request);

                if (response.Data != null) {
                    Response.Write(response.Data.ErrorCode + "--" + response.Data.ErrorText);

                    if (response.StatusCode == System.Net.HttpStatusCode.OK &&
                      response.Data.ErrorCode == 0) {
                        Response.Write("<br />Successful payment");
                    }
                } else {
                    Response.Write(response.ResponseStatus);
                }
            }
        }
    }

    public class TransactionResult
    {
        public int ErrorCode { get; set; }
        public string ErrorText { get; set; }
        public decimal Amount { get; set; }
        public Guid TransactionId { get; set; }
    }
}