Imports System
Imports System.Collections.Generic
Imports System.Linq
Imports System.Web
Imports System.Web.UI
Imports System.Web.UI.WebControls
Imports System.Net
Imports System.IO

Imports RestSharp

' *  This example uses RestSharp, a simple REST and HTTP API Client for .NET (http://restsharp.org/ - https://github.com/restsharp/restsharp)

Partial Public Class VB
    Inherits System.Web.UI.Page
    ' Redirect URL
    Private vivaPaymentFormURL As String = "http://demo.vivapayments.com/web/newtransaction.aspx?ref="
    'Private vivaPaymentFormURL As String = "https://www.vivapayments.com/web/newtransaction.aspx?ref=" ' Production environment

    Protected Sub Page_Load(sender As Object, e As EventArgs) Handles Me.Load
        Dim cl = New RestClient("http://demo.vivapayments.com/")
        'Dim cl = New RestClient("https://www.vivapayments.com/")   ' Production URL

        Dim req = New RestRequest("api/orders", Method.POST)

        ' amount is in cents
        req.AddObject(New OrderOptions() With { _
         .Amount = 100 _
        })


        Dim MerchantId As String = Guid.Parse("1c204c59-9890-499c-bf5d-31e80dcdbdfd").ToString  ' the merchant id is found in the self-care environment (developers menu)
        Dim Password As String = "a123456" ' the password is set in the self-care environment (developers menu)

        cl.Authenticator = New HttpBasicAuthenticator(MerchantId, Password)

        ' Do the post 
        Dim res = cl.Execute(Of OrderResult)(req)

        ' once the order code is successfully created, redirect to payment form to complete the payment
        If res.Data.ErrorCode = 0 Then
            Response.Redirect(Me.vivaPaymentFormURL + res.Data.OrderCode.ToString())
        End If
    End Sub


    ' class that contains the order options that will be sent
    Public Class OrderOptions
        Public Property Amount() As Long
            Get
                Return m_Amount
            End Get
            Set(value As Long)
                m_Amount = value
            End Set
        End Property
        Private m_Amount As Long
    End Class

    ' class that contains the order results
    Public Class OrderResult
        Public Property OrderCode() As System.Nullable(Of Long)
            Get
                Return m_OrderCode
            End Get
            Set(value As System.Nullable(Of Long))
                m_OrderCode = value
            End Set
        End Property
        Private m_OrderCode As System.Nullable(Of Long)
        Public Property ErrorCode() As Integer
            Get
                Return m_ErrorCode
            End Get
            Set(value As Integer)
                m_ErrorCode = value
            End Set
        End Property
        Private m_ErrorCode As Integer
        Public Property ErrorText() As String
            Get
                Return m_ErrorText
            End Get
            Set(value As String)
                m_ErrorText = value
            End Set
        End Property
        Private m_ErrorText As String
        Public Property TimeStamp() As DateTime
            Get
                Return m_TimeStamp
            End Get
            Set(value As DateTime)
                m_TimeStamp = value
            End Set
        End Property
        Private m_TimeStamp As DateTime

    End Class
End Class


