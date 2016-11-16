<%@ Page Title="Home Page" Language="C#" AutoEventWireup="true" CodeBehind="Default.aspx.cs" Inherits="WebApplication2._Default" Async="true" %>

<html>
    <body>
        <script src="http://code.jquery.com/jquery-1.11.2.min.js"></script>


<script src="https://demo.vivapayments.com/web/checkout/js"></script>
<form id="myform" method="post">
<button type="button"
data-vp-sourcecode="Default"
data-vp-publickey="Ob/Mdq9m9azAOHhsX1m8FHOYYaa95hY+mPAqGuJAyAQ="
data-vp-baseurl="https://demo.vivapayments.com"
data-vp-lang="el"
data-vp-amount="1000"
data-vp-customeremail="customer@vivawallet.com"
data-vp-customerfirstname = "John"
data-vp-customersurname = "Smith"
data-vp-merchantref="test merchant ref"
data-vp-expandcard="true"
data-vp-description="My product">
</button>
</form>

    </body>
</html>