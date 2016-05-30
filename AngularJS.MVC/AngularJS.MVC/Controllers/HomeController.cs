using RestSharp;
using RestSharp.Authenticators;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Threading.Tasks;
using System.Web;
using System.Web.Mvc;

namespace AngularJS.MVC.Controllers
{
    public class HomeController : Controller
    {
        private Guid _merchantId = new Guid("xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx");
        private String _apiKey = "xxxxxx";

        public ActionResult Index()
        {
            return View();
        }

        public ActionResult About()
        {
            ViewBag.Message = "Your application description page.";

            return View();
        }

        public ActionResult Contact()
        {
            ViewBag.Message = "Your contact page.";

            return View();
        }

        public class TransactionResult
        {
            public int ErrorCode { get; set; }
            public string ErrorText { get; set; }
            public decimal Amount { get; set; }
            public Guid TransactionId { get; set; }
        }

        [HttpPost]
        public async Task<TransactionResult> Checkout(string vivaWalletToken)
        {
            var cl = new RestClient("http://demo.vivapayments.com/api/")
            {
                Authenticator = new HttpBasicAuthenticator(_merchantId.ToString(), _apiKey)
            };
            var request = new RestRequest("transactions", Method.POST)
            {
                RequestFormat = DataFormat.Json
            };

            request.AddParameter("PaymentToken", vivaWalletToken);

            var response = await cl.ExecuteTaskAsync<TransactionResult>(request);

            return response.ResponseStatus == ResponseStatus.Completed &&
                response.StatusCode == HttpStatusCode.OK ? response.Data : null;
        }
    }
}