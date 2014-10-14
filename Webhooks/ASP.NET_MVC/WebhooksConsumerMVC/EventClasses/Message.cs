using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace WebhooksConsumerMVC
{
    public class Message<T> where T : TransactionEventData
    {
        public DateTime Created { get; set; }
        public T EventData { get; set; }
        public int EventTypeId { get; set; }
    }
}