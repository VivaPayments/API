namespace NativeCheckoutV2.Models
{
    using System;

    public class OrderResult
    {
        public long OrderCode { get; set; }
        public int ErrorCode { get; set; }
        public string ErrorText { get; set; }
        public DateTime TimeStamp { get; set; }
    }
}
