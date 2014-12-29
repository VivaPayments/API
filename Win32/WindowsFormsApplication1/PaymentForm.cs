using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

namespace Win32Payments
{
    /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// ///
    //THIS SAMPLE USES RestSharp Nuget Package, a Simple REST and HTTP API Client//
    /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// /// ///

    public partial class PaymentForm : Form
    {
        private Guid MerchantId = new Guid("F45F2396-2643-4A04-BDDA-BC5B833DB199");
        private string ApiKey = "l(kjh&a";
        private string PublicKey = "ptwJwOLh397ff3NylMVxuwMYs+sIxLytRgwWbI1M1hs=";
        private string SourceCode = "9091"; //A SourceCode that is set for Native Checkout
        private string BasePaymentsApiUrl = "http://demo.vivapayments.com";

        public PaymentForm()
        {
            InitializeComponent();
        }

        private void btnPay_Click(object sender, EventArgs e)
        {
            int Cvv = 0;
            int ExpiryMonth = 0;
            int ExpiryYear = 0;
            if (String.IsNullOrEmpty(txtCardHolder.Text) ||
                    string.IsNullOrEmpty(txtCardNumber.Text) ||
                    !int.TryParse(txtCVV.Text, out Cvv) ||
                    !int.TryParse(cmbMonth.SelectedItem.ToString(), out ExpiryMonth) ||
                    !int.TryParse(cmbYear.SelectedItem.ToString(), out ExpiryYear))
            {
                MessageBox.Show("Invalid input");
                return;
            }

            var cl = new ApiClient(BasePaymentsApiUrl, MerchantId, ApiKey, PublicKey);

            //CREATE ORDER
            long orderCode = 0;
            var orderResp = cl.CreateOrder(100, SourceCode);
            if (orderResp != null && orderResp.Data != null)
            {
                if (orderResp.Data.ErrorCode == 0)
                {
                    orderCode = orderResp.Data.OrderCode;
                }
                else
                {
                    MessageBox.Show(String.Format(
                        "Create Order Failed with Error ({0}){1}",
                        orderResp.Data.ErrorCode,
                        orderResp.Data.ErrorText));
                    return;
                }
            }
            else
            {
                MessageBox.Show("Create Order failed");
                return;
            }
            
            //CREATE TOKEN
            string token = String.Empty;
            var tokenResp = cl.Tokenize(txtCardHolder.Text, txtCardNumber.Text, Cvv, ExpiryMonth, ExpiryYear);
            if (tokenResp.StatusCode == System.Net.HttpStatusCode.OK &&
                tokenResp.Data != null)
            {
                token = tokenResp.Data.Token;
            }
            else
            {
                MessageBox.Show(String.Format(
                    "Tokenization Failed with Error ({0}){1}",
                    tokenResp.StatusCode,
                    tokenResp.StatusDescription));
                return;
            }

            //EXECUTE TRANSACTION
            int inst = 1;
            if (cmbIntallments.SelectedItem != null)
                int.TryParse(cmbIntallments.SelectedItem.ToString(), out inst);
            TransactionResult res = cl.ExecuteTransaction(orderCode, SourceCode, token, inst);
            if (res != null)
            {
                if (res.ErrorCode == 0 && res.StatusId == "F")
                {
                    MessageBox.Show(String.Format(
                        "Transaction was successful ({0})",
                        res.TransactionId));
                }
                else
                {
                    MessageBox.Show(String.Format(
                        "Transaction failed with Error ({0}){1}",
                        res.ErrorCode,
                        res.ErrorText));
                }
            }
            else
            {
                MessageBox.Show("Transaction failed");
                return;
            }


        }

        private void txtCardNumber_LostFocus(object sender, EventArgs e)
        {
            cmbIntallments.Items.Clear();
            var cl = new ApiClient(BasePaymentsApiUrl, MerchantId, ApiKey, PublicKey);
            var CheckInstResp = cl.CheckInstallments(txtCardNumber.Text);
            if (CheckInstResp != null && CheckInstResp.Data != null &&
                CheckInstResp.Data.MaxInstallments > 0)
            {
                cmbIntallments.Visible = true;
                lblInstallments.Visible = true;

                for(int i = 1;i<=CheckInstResp.Data.MaxInstallments;i++){
                    cmbIntallments.Items.Add(i);
                }
                cmbIntallments.SelectedItem = 1;
            }
            else
            {
                cmbIntallments.Visible = false;
                lblInstallments.Visible = false;
            }
        }
    }
}
