<%@ Page Language="C#" AutoEventWireup="true" CodeFile="Default.aspx.cs" Inherits="_Default" EnableEventValidation="false"%>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head runat="server">
    <title></title>
    <script type="text/javascript" src="Scripts/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="http://demo.vivapayments.com/web/checkout/js"></script>
</head>
<body>
    <form id="paymentform" runat="server">
        CardHolder's name:
        <asp:TextBox ID="txtCardHolder" runat="server" data-vp="cardholder"></asp:TextBox><br />
        CardNumber:
        <asp:TextBox ID="txtCardNumber" runat="server" data-vp="cardnumber" MaxLength="16"></asp:TextBox><br />
        <div id="divInstallments" style="display:none">
            Installments: <asp:DropDownList ID="drpInstallments" runat="server"></asp:DropDownList>
        </div>
        CVV:
        <asp:TextBox ID="txtCVV" runat="server" data-vp="cvv"></asp:TextBox><br />
        Expiration Month:
        <asp:TextBox ID="txtExpMonth" runat="server" data-vp="month"></asp:TextBox><br />
        Expiration Year:
        <asp:TextBox ID="txtExpYear" runat="server" data-vp="year"></asp:TextBox><br />
        <asp:HiddenField ID="hidToken" runat="server" />
        <asp:Button ID="btnSubmit" runat="server" text="Submit" OnClientClick="VivaPayments.cards.requestToken();return false;" />
    </form>
    <script type="text/javascript">
        $(document).ready(function () {
            if ($('#paymentform').length)
                VivaPayments.cards.setup({
                    publicKey: 'u3a1fcKsxynRZwY8zb++1utUYr1vjdGW6okiEX0pJBc=',
                    baseURL: 'https://demo.vivapayments.com/',
                    cardTokenHandler: function (response) {
                        if (!response.Error) {
                            $('#hidToken').val(response.Token);
                            $('#paymentform').submit();
                        }
                        else
                            alert(response.Error);
                    },
                    installmentsHandler: function (response) {
                        if (!response.Error) {
                            if (response.MaxInstallments == 0) {
                                $('#drpInstallments').empty();
                                $('#divInstallments').hide();
                                return;
                            }
                        
                            $('#divInstallments').show();
                            for (i = 1; i <= response.MaxInstallments; i++) {
                                $('#drpInstallments').append($("<option>").val(i).text(i));
                            }
                        }
                        else
                            alert(response.Error);
                    }
                });
        });
    </script>

</body>
</html>
