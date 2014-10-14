using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;
using System.Net.Http;
using System.Net.Http.Headers;
using WebhooksConsumerMVC;

namespace WebhooksConsumerMVC.Controllers
{
    public class HomeController : Controller
    {
        private Guid _MerchantId = Guid.Parse("22C8A516-BE4A-4B21-AB5C-01F25C02557C");
        private string _ApiKey = "myapitestkey";
        private string _BaseApiUrl = "http://demo.vivapayments.com";
        private string _WebhooksAuthUrl = "/api/messages/config/token";

        public ActionResult Index()
        {
            //USING HttpClient TO GET WEBHOOK AUTH TOKEN
            using (var client = new HttpClient()) {
                client.BaseAddress = new Uri(_BaseApiUrl);

                //AUTH HEADER
                client.DefaultRequestHeaders.Authorization =
                    new AuthenticationHeaderValue("Basic",
                        Convert.ToBase64String(
                            System.Text.ASCIIEncoding.ASCII.GetBytes(
                                string.Format("{0}:{1}", _MerchantId, _ApiKey))));
                
                var result = client.GetAsync(_WebhooksAuthUrl).Result;

                //CHECK FOR SUCCESS STATUS CODE
                if (result.IsSuccessStatusCode) {
                    string resultContent = result.Content.ReadAsStringAsync().Result;
                    ViewBag.WebhookAuthorizationToken = resultContent;
                }
                else
                    ViewBag.WebhookAuthorizationToken = "Please handle invalid response. Probably an authentication issue?";
            }
            
            return View();
        }

        public ActionResult Process(Message<TransactionEventData> data)
        {
            if (data == null ||
                data.EventData == null)
                return View("Error");
            
            return View(data.EventData);
        }
    }

}