<%
dim baseUrl
baseUrl = "http://demo.vivapayments.com/api/transactions/"

dim MerchantId
MerchantId = "XXXXXXXXXXXXXXXXXXXXXXX"
dim ApiKey
ApiKey = "yyyyy"

dim http
Set http = Server.CreateObject("Msxml2.ServerXMLHTTP.3.0")
http.open "GET", baseUrl & "?OrderCode=8243103019172607", False
http.setRequestHeader "Content-Type", "application/x-www-form-urlencoded"
http.setRequestHeader "Authorization", "Basic "&Base64Encode(MerchantId & ":" & ApiKey)

http.send()

response.write http.responsetext
%>
