//
//  ViewController.swift
//  ExampleSwift
//
//  Created by Mike on 11/30/16.
//  Copyright © 2016 Mike. All rights reserved.
//

import UIKit

class ViewController: UIViewController {
    
    // Change below variables with your merchantID,apikey,publicKey
    var merchantID = "90DDA476-CF7C-4CFD-A9CB-23DFE11F131E"
    var apiKey = "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
    var publicKey = "u3a1fcKsxynRZwY8zb++1utUYr1vjdGW6okiEX0pJBc="
    // If you don't change the above variables you will get [:] as a response
    var cardNum = "4111111111111111"
    var cvv = "111"
    var expdate = "2021-01-01"
    var cardHolder = "John Papadopoulos"
    var cardToken = ""
    var orderCode: NSNumber = 0
    var transactionId = ""
    var mobileApi = MobileAPI.newDemoInstance()
    
    
    override func viewDidLoad() {
        super.viewDidLoad()
        // Do any additional setup after loading the view, typically from a nib.
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    @IBAction func payButton(_ sender: AnyObject) {
        viva()
    }
    
    @IBAction func payRecurringButton(_ sender: Any) {
        createRecurringTransaction()
    }
    
    func viva() {
        
        let amountInCents = UInt64(10037)
        
        mobileApi?.setMerchantID(merchantID, apiKey: apiKey, publicKey: publicKey)
        
        mobileApi?.createOrder(withAmount: amountInCents, params: nil, isRecurrentPayment: false) { (success, urlResponse, response, error) in
            print("Create order response = \(response ?? [:])")
            
            if success {
                self.orderCode = response?["OrderCode"] as? NSNumber ?? 0
                self.createCreditCard()
            }
        }
    }
    
    func createCreditCard() {
        
        mobileApi?.createCardToken(forCardNumber: cardNum, cvc: cvv, expirationDateString: expdate, cardHolderName: cardHolder) { (success, urlResponse, response, error) in
            print("Create card token response = \(response ?? [:])")
            
            if success {
                self.cardToken = response?["Token"] as? String ?? ""
                self.checkInstallments()
                
            }
        }
    }
    
    func checkInstallments() {
        
        mobileApi?.checkInstallments(forCard: cardNum) { (success, urlResponse, response, error) in
            print("Check Installments reponse = \(response ?? [:])")
            
            if success {
                let MaxInstallments = response?["MaxInstallments"] as? NSNumber ?? 0
                print("Card max installments = \(MaxInstallments)")
                
                self.createTransaction()
                
            }
        }
    }
    
    
    
    func createTransaction() {
        
        mobileApi?.createTransaction(withOrderCode: orderCode as NSNumber, sourceCode: "Default", installments: 1, isRecurrentPayment: false, creditCardToken: cardToken) { (success, urlResponse, response, error) in
            print("Create transaction reponse = \(response ?? [:])")
            
            if success {
                
                self.transactionId = response?["TransactionId"] as! String
                let StatusId      = response?["StatusId"]
                print("Completed transaction with id \(self.transactionId), status \(StatusId ?? [:])");
                
            }
        }
    }
    
    func createRecurringTransaction() {
        
        if transactionId == "" {
            print("First create a successfull payment with isRecurrentPayment: true")
            return
        }
        
        let amountInCents = UInt64(10037)
        
        mobileApi?.createRecurringTransaction(amountInCents, params: nil, installments: 1, transactionID: transactionId) { (success, urlResponse, response, error) in
            print("Create transaction reponse = \(response ?? [:])")
            
            if success {
                
                let transactionId = response?["TransactionId"]
                let StatusId      = response?["StatusId"]
                print("Completed transaction with id \(transactionId ?? [:]), status \(StatusId ?? [:])");
                
            }
        }
    }
    
    
}

