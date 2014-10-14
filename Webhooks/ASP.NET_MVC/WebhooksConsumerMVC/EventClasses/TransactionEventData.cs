using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace WebhooksConsumerMVC
{
    public class TransactionEventData
    {
        public decimal Amount { get; set; }
        public string CardNumber { get; set; }
        public byte CardTypeId { get; set; }
        public Guid? ClientId { get; set; }
        public string CompanyName { get; set; }
        public string CurrencyCode { get; set; }
        public byte CurrentInstallment { get; set; }
        public string CustomerTrns { get; set; }
        public string Email { get; set; }
        public string FullName { get; set; }
        public DateTime InsDate { get; set; }
        public Guid MerchantId { get; set; }
        public string MerchantTrns { get; set; }
        public long? OrderCode { get; set; }
        public Guid? ParentId { get; set; }
        public string ResellerCompanyName { get; set; }
        public Guid? ResellerId { get; set; }
        public string ResellerSourceAddress { get; set; }
        public string ResellerSourceCode { get; set; }
        public string ResellerSourceName { get; set; }
        public string SourceCode { get; set; }
        public string StatusId { get; set; }
        public Guid? TargetPersonId { get; set; }
        public long? TargetWalletId { get; set; }
        public decimal TotalCommission { get; set; }
        public decimal TotalFee { get; set; }
        public byte TotalInstallments { get; set; }
        public Guid TransactionId { get; set; }
        public int TransactionTypeId { get; set; }
    }
}