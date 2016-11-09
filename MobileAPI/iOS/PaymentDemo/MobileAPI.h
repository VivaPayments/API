//
//  MobileAPI.h
//  MobileAPI
//
//  Created by MacRealize on 25/11/14.
//  Copyright (c) 2014 Viva Payments. All rights reserved.
//

#import <Foundation/Foundation.h>

typedef void (^VPCompletionBlock)(BOOL success, NSURLResponse *urlResponse, NSDictionary *response, NSError *error);


@interface MobileAPI : NSObject


/**
  *  Create a MobileAPI instance for the Viva Payments production (real) enviroment.
  *
  *  Don't forget to call -[MobileAPI setMerchantID:apiKey:publicKey:] to configure the API with the necessary credentials
  */
+ (instancetype) newProductionInstance;



/**
  *  Create a MobileAPI instance for the Viva Payments DEMO enviroment.
  *
  *  Don't forget to call -[MobileAPI setMerchantID:apiKey:publicKey:] to configure the API with the necessary credentials
  */
+ (instancetype) newDemoInstance;


/**
  *  Configures the MobileAPI instance with your credentials.
  *
  *  Make sure you call this function with the correct credentials or all other calls will fail
  */
- (void) setMerchantID:(NSString *)username apiKey:(NSString *)password publicKey:(NSString *)apiKey;



/**
  *  Creates an order.
  *
  *  Pass the amount in EURO Cents (ex specify 100 for 1 euro) and any other parameters as a NSDictionary (like RequestLang or SourceCode).
  *
  *  @warning The amount is a 64-bit unsigned integer. Please use -[NSNumber unsignedLongLongValue] to convert your NSNumber.
  *  @warning The response dictionary contains the order code as a NSNumber (64-bit unsigned number). If you want to convert this to a number make sure you use 'uint64_t' or 'long long' types.
  */
- (void) createOrderWithAmount:(unsigned long long)amountInEuroCents params:(NSDictionary *)params completion:(VPCompletionBlock)completionBlock;



/**
  *  Creates a token for a credit card.
  *
  */
- (void) createCardTokenForCardNumber:(NSString *)cardNumber cvc:(NSString *)cvc expirationDateString:(NSString *)expirationDateString cardHolderName:(NSString *)cardHolderName completion:(VPCompletionBlock)completionBlock;


/**
  *  Returns the maximum number of installments for a credit card
  *
  */
- (void) checkInstallmentsForCard:(NSString *)cardNumber completion:(VPCompletionBlock)completionBlock;



/**
  *  Creates a transaction (Charges the credit card)
  *
  *  @warning orderCode should be the 64-bit encapsulated NSNumber which -[MobileAPI createOrderWithAmount:params:completion:] returned
  */
- (void) createTransactionWithOrderCode:(NSNumber *)orderCode sourceCode:(NSString *)sourceCode installments:(NSInteger)installments creditCardToken:(NSString *)creditCardToken completion:(VPCompletionBlock)completionBlock;


@end
