//
//  MobileAPI.m
//  MobileAPI
//
//  Created by MacRealize on 25/11/14.
//  Copyright (c) 2014 Viva Payments. All rights reserved.
//

#import "MobileAPI.h"

static NSString *productionURL = @"https://www.vivapayments.com";
static NSString *demoURL = @"http://demo.vivapayments.com";

#if !defined(__has_feature) || ! __has_feature(objc_arc)
#error This Class requires ARC. Either turn on ARC for the project or use -fobjc-arc flag
#endif

#if __IPHONE_OS_VERSION_MIN_REQUIRED < __IPHONE_7_0
#error This code requires iOS 7 or later. If you insist on compiling for older iOS versions, make sure you replace the -[NSData base64EncodedStringWithOptions:] function
#endif

@interface MobileAPI ()

@property (nonatomic, strong) NSURL *apiURL;
@property (nonatomic, strong) NSString *username;
@property (nonatomic, strong) NSString *password;
@property (nonatomic, strong) NSString *apiKey;

@end

@implementation MobileAPI

#pragma mark Public Functions


+ (instancetype) newProductionInstance
{
	return [[self alloc] initWithBaseURLString:productionURL];
}

+ (instancetype) newDemoInstance
{
	return [[self alloc] initWithBaseURLString:demoURL];
}

- (instancetype)initWithBaseURLString:(NSString *)urlString
{
	if (self = [self init])
	{
		_apiURL = [NSURL URLWithString:urlString];
	}
	return self;
}


- (void) setUsername:(NSString *)username password:(NSString *)password apiKey:(NSString *)apiKey
{
	_username = [username copy];
	_password = [password copy];
	_apiKey = [apiKey copy];
}


- (void) createOrderWithAmount:(unsigned long long)amountInEuroCents params:(NSDictionary *)params completion:(VPCompletionBlock)completionBlock
{
	NSMutableURLRequest *request = [self createOrderRequest];
	
	NSMutableDictionary *paramsDict = [[NSMutableDictionary alloc] initWithDictionary:params copyItems:YES];
	
	[paramsDict setObject:[NSNumber numberWithUnsignedLongLong:amountInEuroCents]
				   forKey:@"Amount"];
	
	NSData *paramsData = [NSJSONSerialization dataWithJSONObject:paramsDict options:0 error:0];
	
	if (paramsData)
		[request setHTTPBody:paramsData];
	
	[self executeRequestInBackground:request completion:completionBlock];
}


- (void) createCardTokenForCardNumber:(NSString *)cardNumber cvc:(NSString *)cvc expirationDateString:(NSString *)expirationDateString cardHolderName:(NSString *)cardHolderName completion:(VPCompletionBlock)completionBlock
{
	NSMutableURLRequest *request = [self createCardRequest];
	
	NSMutableDictionary *paramsDict = [NSMutableDictionary new];
	
	if (cardNumber.length)
		[paramsDict setObject:cardNumber
					   forKey:@"Number"];

	if (cvc.length)
		[paramsDict setObject:cvc forKey:@"CVC"];
	
	if (expirationDateString.length)
		[paramsDict setObject:expirationDateString
					   forKey:@"ExpirationDate"];
	
	if (cardHolderName.length)
		[paramsDict setObject:cardHolderName
					   forKey:@"CardHolderName"];
	
	NSData *paramsData = [NSJSONSerialization dataWithJSONObject:paramsDict options:0 error:0];

	if (paramsData)
		[request setHTTPBody:paramsData];
	
	[self executeRequestInBackground:request completion:completionBlock];

}

- (void) checkInstallmentsForCard:(NSString *)cardNumber completion:(VPCompletionBlock)completionBlock
{
	NSMutableURLRequest *request = [self createInstallmentsRequest];

	if (cardNumber)
		[request setValue:cardNumber forHTTPHeaderField:@"CardNumber"];
	
	[self executeRequestInBackground:request completion:completionBlock];
}


- (void) createTransactionWithOrderCode:(NSNumber *)orderCode sourceCode:(NSString *)sourceCode installments:(NSInteger)installments creditCardToken:(NSString *)creditCardToken completion:(VPCompletionBlock)completionBlock
{
	NSMutableURLRequest *request = [self createTransactionRequest];
	
	NSMutableDictionary *paramsDict = [NSMutableDictionary new];
	
	[paramsDict setObject:orderCode
				   forKey:@"OrderCode"];
	
	if (sourceCode.length)
		[paramsDict setObject:sourceCode
					   forKey:@"SourceCode"];

	[paramsDict setObject:[NSString stringWithFormat:@"%ld", (long)installments]
				   forKey:@"Installments"];

	if (creditCardToken.length)
		[paramsDict setObject:@{@"Token" : creditCardToken}
					   forKey:@"CreditCard"];
	
	NSData *paramsData = [NSJSONSerialization dataWithJSONObject:paramsDict options:0 error:0];
	
	if (paramsData)
		[request setHTTPBody:paramsData];

	[self executeRequestInBackground:request completion:completionBlock];
}


#pragma mark Helpers / Private Functions


- (NSMutableURLRequest *)createOrderRequest
{
	NSURL *ordersURL = [self.apiURL URLByAppendingPathComponent:@"/api/orders"];
	
	NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL:ordersURL];
	[request setHTTPMethod:@"POST"];
	[request setValue:@"application/json" forHTTPHeaderField:@"Content-type"];
	[request setValue:[self authenticationHeaderForUsername:self.username password:self.password]
   forHTTPHeaderField:@"Authorization"];
	
	return request;
}


- (NSMutableURLRequest *)createCardRequest
{
	NSAssert(self.apiKey, @"Viva Payments API Key is not specified. Did you forget to call -[MobileAPI setUsername:password:apiKey:] ?");

	NSString *escapedAPIKey = [self urlEncodeString:self.apiKey usingEncoding:NSUTF8StringEncoding];
	
	NSString *urlString = [NSString stringWithFormat:@"%@/api/cards?key=%@", self.apiURL.absoluteString, escapedAPIKey];
	NSURL *ordersURL = [NSURL URLWithString:urlString];
	
	NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL:ordersURL];
	[request setHTTPMethod:@"POST"];
	[request setValue:@"application/json" forHTTPHeaderField:@"Content-type"];
	
	return request;
}


- (NSMutableURLRequest *)createInstallmentsRequest
{
	NSAssert(self.apiKey, @"Viva Payments API Key is not specified. Did you forget to call -[MobileAPI setUsername:password:apiKey:] ?");

	NSString *urlString = [NSString stringWithFormat:@"%@/api/cards/installments?key=%@", self.apiURL.absoluteString, self.apiKey];
	NSURL *ordersURL = [NSURL URLWithString:urlString];
	
	NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL:ordersURL];
	
	return request;
}


- (NSMutableURLRequest *)createTransactionRequest
{
	NSURL *ordersURL = [self.apiURL URLByAppendingPathComponent:@"/api/Transactions"];
	
	NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL:ordersURL];
	[request setHTTPMethod:@"POST"];
	[request setValue:@"application/json" forHTTPHeaderField:@"Content-type"];
	[request setValue:[self authenticationHeaderForUsername:self.username password:self.password]
   forHTTPHeaderField:@"Authorization"];

	return request;
}


- (void) executeRequestInBackground:(NSURLRequest *)request completion:(VPCompletionBlock)completionBlock
{
	[NSURLConnection sendAsynchronousRequest:request
									   queue:[NSOperationQueue mainQueue]
						   completionHandler:^(NSURLResponse *response, NSData *data, NSError *connectionError)
	 {
		 NSHTTPURLResponse *httpResponse = (NSHTTPURLResponse *)response;
		 NSDictionary *responseDictionary = nil;
		 
		 if (data)
			 responseDictionary = [NSJSONSerialization JSONObjectWithData:data options:0 error:0];
		 
		 if (completionBlock)
		 {
			 BOOL success = (responseDictionary != nil &&
							httpResponse.statusCode == 200 &&
			                [[responseDictionary objectForKey:@"ErrorCode"] intValue] == 0);
			 
			 completionBlock(success,
							 response,
							 responseDictionary,
							 connectionError);
		 }
	 }];
}


- (NSString *)authenticationHeaderForUsername:(NSString *)username password:(NSString *)password
{
	NSAssert(username, @"Viva Payments API username is not specified. Did you forget to call -[MobileAPI setUsername:password:apiKey:] ?");
	NSAssert(password, @"Viva Payments API username is not specified. Did you forget to call -[MobileAPI setUsername:password:apiKey:] ?");
	
	NSString *userAndPass = [NSString stringWithFormat:@"%@:%@", username, password];
	
	NSString *base64UserNamePassword = [[userAndPass dataUsingEncoding:NSUTF8StringEncoding] base64EncodedStringWithOptions:0];
	
	return [NSString stringWithFormat:@"Basic %@", base64UserNamePassword];
}


-(NSString *)urlEncodeString:(NSString *)string usingEncoding:(NSStringEncoding)encoding {
	return (NSString *)CFBridgingRelease(CFURLCreateStringByAddingPercentEscapes(NULL,
															   (CFStringRef)string,
															   NULL,
															   (CFStringRef)@"!*'\"();:@&=+$,/?%#[]% ",
															   CFStringConvertNSStringEncodingToEncoding(encoding)));
}

@end
