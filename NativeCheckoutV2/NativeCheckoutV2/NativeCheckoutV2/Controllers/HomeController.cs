namespace NativeCheckoutV2.Controllers
{
    using System;
    using System.Net;
    using System.Net.Http;
    using System.Threading.Tasks;
    using Microsoft.AspNetCore.Mvc;

    using IdentityModel.Client;

    using Newtonsoft.Json;

    using Models;

    public class HomeController : Controller
    {
        /// <summary>
        /// 
        /// </summary>
        private const string ApiKey = "1549bffb-f82d-4f43-9c07-0818cdcdb2c4";
        
        /// <summary>
        /// 
        /// </summary>
        private static Guid MerchantId => Guid.Parse("1549bffb-f82d-4f43-9c07-0818cdcdb2c4");

        /// <summary>
        /// 
        /// </summary>
        public const string VivaPaymentsIdentityProviderUrl =
            "https://demo-accounts.vivapayments.com";

        /// <summary>
        /// 
        /// </summary>
        private static Lazy<HttpClient> client_ =
            new Lazy<HttpClient>(() =>
            {
                var client = new HttpClient() {
                    BaseAddress = new Uri("https://demo.vivapayments.com/api/")
                };

                client.SetBasicAuthentication(MerchantId.ToString(), ApiKey);

                return client;
            });

        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        [HttpGet]
        public async Task<IActionResult> Index()
        {
            var token = await RequestTokenAsync();

            if (!token.IsError) {
                ViewData.Add("access_token", token.AccessToken);
            }

            return View();
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="chargeToken"></param>
        /// <returns></returns>
        [HttpPost]
        public async Task<IActionResult> ChargeCard(string chargeToken)
        {
            if (string.IsNullOrWhiteSpace(chargeToken)) {
                return BadRequest($"Null {chargeToken}");
            }

            var orderResult = await CreateOrderAsync(1034);

            if (orderResult.ErrorCode != 0) {
                return StatusCode(orderResult.ErrorCode, orderResult.ErrorText);
            }

            var transactionResult = await ChargeAsync(orderResult.OrderCode,
                chargeToken);

            return Json(new {
                statusId = transactionResult.StatusId,
                transactionId = transactionResult.TransactionId
            });
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="amountInCents"></param>
        /// <returns></returns>
        private async Task<OrderResult> CreateOrderAsync(long amountInCents)
        {
            var payload = JsonConvert.SerializeObject(
                new {
                    Amount = amountInCents
                });

            var response = await client_.Value.PostAsync("orders",
                new StringContent(payload, System.Text.Encoding.UTF8, "application/json"));

            if (!response.IsSuccessStatusCode) {
                return new OrderResult() {
                    ErrorCode = (int)response.StatusCode
                };
            }

            try {
                return JsonConvert.DeserializeObject<OrderResult>(
                    await response.Content.ReadAsStringAsync());
            } catch (Exception e) {
                return new OrderResult() {
                    ErrorCode = (int)HttpStatusCode.BadGateway,
                    ErrorText = $"{e.ToString()}"
                };
            }
        }

        /// <summary>
        /// 
        /// </summary>
        /// <param name="orderCode"></param>
        /// <param name="chargeToken"></param>
        /// <returns></returns>
        private async Task<TransactionResult> ChargeAsync(long orderCode,
            string chargeToken)
        {
            if (string.IsNullOrWhiteSpace(chargeToken)) {
                return new TransactionResult() {
                    ErrorCode = (int)HttpStatusCode.BadRequest,
                    ErrorText = $"Null {nameof(chargeToken)}"
                };
            }

            var payload = JsonConvert.SerializeObject(
                new {
                    OrderCode = orderCode,
                    CreditCard = new {
                        Token = chargeToken
                    }
                });

            var response = await client_.Value.PostAsync("transactions",
                new StringContent(payload, System.Text.Encoding.UTF8, "application/json"));

            if (!response.IsSuccessStatusCode) {
                return new TransactionResult() {
                    ErrorCode = (int)response.StatusCode
                };
            }

            try {
                return JsonConvert.DeserializeObject<TransactionResult>(
                    await response.Content.ReadAsStringAsync());
            } catch (Exception e) {
                return new TransactionResult() {
                    ErrorCode = (int)HttpStatusCode.BadGateway,
                    ErrorText = $"{e.ToString()}"
                };
            }
        }

        /// <summary>
        /// 
        /// </summary>
        /// <returns></returns>
        public static async Task<TokenResponse> RequestTokenAsync()
        {
            var discovery = await DiscoveryClient.GetAsync(
                VivaPaymentsIdentityProviderUrl);

            if (discovery.IsError) {
                return new TokenResponse(discovery.StatusCode, discovery.Error,
                    null);
            }

            var client = new TokenClient(
                discovery.TokenEndpoint,
                "dpvnjl16xnbmosjtuadp0d4xi6y6s2iw0odsrz8ly97dp.apps.vivapayments.com",
                "dpdZAZaquc}O)Kbu%c@Lft{e3D@GGi", AuthenticationStyle.PostValues);

            return await client.RequestClientCredentialsAsync();
        }
    }
}
