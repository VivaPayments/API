//
//  ViewController.m
//  PaymentTest
//
//  Created by MacRealize on 25/11/14.
//  Copyright (c) 2014 Viva Payments. All rights reserved.
//

#import "ViewController.h"

#if !defined(__has_feature) || ! __has_feature(objc_arc)
#error This Class requires ARC. Either turn on ARC for the project or use -fobjc-arc flag
#endif

// Replace with YOUR Credentials
// See more at https://github.com/VivaPayments/API/wiki/API%20Authentication
static NSString *merchantID = @"90DDA476-CF7C-4CFD-A9CB-23DFE11F131E";
static NSString *apiKey = @"XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX";
static NSString *publicKey = @"u3a1fcKsxynRZwY8zb++1utUYr1vjdGW6okiEX0pJBc=";

@interface ViewController ()

@end

@implementation ViewController


- (void)viewDidLoad {
	[super viewDidLoad];
	// Do any additional setup after loading the view, typically from a nib.
}


- (void)didReceiveMemoryWarning {
	[super didReceiveMemoryWarning];
	// Dispose of any resources that can be recreated.
}


- (IBAction)createOrderPressed:(id)sender
{
	NSNumber *amountInCents = @100;
	
	mobileApi = [MobileAPI newDemoInstance];
	
	[mobileApi setMerchantID:merchantID
					  apiKey:apiKey
				   publicKey:publicKey];
	
	[mobileApi createOrderWithAmount:amountInCents.unsignedLongLongValue
							  params:nil
                  isRecurrentPayment:false
						  completion:^(BOOL success, NSURLResponse *urlResponse, NSDictionary *response, NSError *error)
	 {
		 NSLog(@"Create order response = %@", response);
		 
		 if (success)
		 {
			 // Warning. Order code is a 64-bit unsigned (uint64_t) encapsulated in a NSNumber
			 orderCode = [response objectForKey:@"OrderCode"];
			 [self createCreditCard];
		 }
	 }];
}


- (void) createCreditCard
{
	[mobileApi createCardTokenForCardNumber:@"4111111111111111"
										cvc:@"111"
					   expirationDateString:@"2021-01-01"
							 cardHolderName:@"John Papadopoulos"
								 completion:^(BOOL success, NSURLResponse *urlResponse, NSDictionary *response, NSError *error)
	 {
		 NSLog(@"Create card token response = %@", response);
		 
		 if (success)
		 {
			 cardToken = [response objectForKey:@"Token"];
			 [self checkInstallments];
		 }
	 }];
}


- (void) checkInstallments
{
	[mobileApi checkInstallmentsForCard:@"4111111111111111"
							 completion:^(BOOL success, NSURLResponse *urlResponse, NSDictionary *response, NSError *error)
	 {
		 NSLog(@"Check Installments reponse = %@", response);
		 
		 
		 if (success)
		 {
			 NSInteger MaxInstallments = [[response objectForKey:@"MaxInstallments"] intValue];
			 NSLog(@"Card max installments = %ld", (long) MaxInstallments);

			 [self createTransaction];
		 }
	 }];
}


- (void) createTransaction
{
	[mobileApi createTransactionWithOrderCode:orderCode
								   sourceCode:@"Default"
								 installments:1
                           isRecurrentPayment:false
							  creditCardToken:cardToken
								   completion:^(BOOL success, NSURLResponse *urlResponse, NSDictionary *response, NSError *error)
	 {
		 NSLog(@"Create transaction reponse = %@", response);
		 
		 if (success)
		 {
			 transactionId = [response objectForKey:@"TransactionId"];
			 NSString *StatusId = [response objectForKey:@"StatusId"];
			 NSLog(@"Completed transaction with id %@, status %@", transactionId, StatusId);
		 }
	 }];
}


- (IBAction)createRecurringTransactionPressed:(id)sender
{
    if (transactionId == nil)
        NSLog(@"First create a successfull payment with isRecurrentPayment: true");
    
    NSNumber *amountInCents = @100;
    
    [mobileApi createRecurringTransaction:amountInCents.unsignedLongLongValue
                                   params:nil
                             installments:1
                            transactionID:transactionId //Use transactionId from a previously completed Order that isRecurrentPayment:true
                               completion:^(BOOL success, NSURLResponse *urlResponse, NSDictionary *response, NSError *error)
     {
         NSLog(@"Create Recurring transaction reponse = %@", response);
         
         if (success)
         {
             NSString *transactionId = [response objectForKey:@"TransactionId"];
             NSString *StatusId = [response objectForKey:@"StatusId"];
             NSLog(@"Completed transaction with id %@, status %@", transactionId, StatusId);
         }
     }];
}


@end
