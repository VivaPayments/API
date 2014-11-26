//
//  ViewController.h
//  PaymentTest
//
//  Created by MacRealize on 25/11/14.
//  Copyright (c) 2014 Viva Payments. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "MobileApi.h"

@interface ViewController : UIViewController
{
	MobileAPI *mobileApi;
	NSNumber  *orderCode;
	NSString  *cardToken;
}

@end

