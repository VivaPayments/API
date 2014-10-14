using Microsoft.Owin;
using Owin;

[assembly: OwinStartupAttribute(typeof(WebhooksConsumerMVC.Startup))]
namespace WebhooksConsumerMVC
{
    public partial class Startup
    {
        public void Configuration(IAppBuilder app)
        {
            ConfigureAuth(app);
        }
    }
}
