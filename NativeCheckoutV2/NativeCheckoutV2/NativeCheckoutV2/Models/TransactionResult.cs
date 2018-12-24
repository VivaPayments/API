namespace NativeCheckoutV2.Models
{
    using System;

    public class TransactionResult
    {
        public int ErrorCode { get; set; }
        public string StatusId { get; set; }
        public string ErrorText { get; set; }
        public DateTime TimeStamp { get; set; }
        public Guid? TransactionId { get; set; }
        public string CorrelationId { get; set; }
    }
}
