//
//  AppDelegate.swift
//  Slash
//
//  Created by Hiroaki KARASAWA on 2017/10/28.
//  Copyright © 2017年 Hiroaki KARASAWA. All rights reserved.
//

import UIKit
import Firebase
import UserNotifications

@UIApplicationMain
class AppDelegate: UIResponder, UIApplicationDelegate, UNUserNotificationCenterDelegate {

    var window: UIWindow?

    func application(_ application: UIApplication, didFinishLaunchingWithOptions launchOptions: [UIApplicationLaunchOptionsKey: Any]?) -> Bool {
        FirebaseApp.configure()
        
        print("Set up notification center")
        
        if #available(iOS 10.0, *) {
            // For iOS 10 display notification (sent via APNS)
            UNUserNotificationCenter.current().delegate = self
            
            let authOptions: UNAuthorizationOptions = [.alert, .badge, .sound]
            UNUserNotificationCenter.current().requestAuthorization(options: authOptions, completionHandler: {_, _ in })
        } else {
            let settings = UIUserNotificationSettings(types: [.alert, .badge, .sound], categories: nil)
            UIApplication.shared.registerUserNotificationSettings(settings)
        }
        
        UIApplication.shared.registerForRemoteNotifications()

        return true
    }

    func applicationWillResignActive(_ application: UIApplication) {
        // Sent when the application is about to move from active to inactive state. This can occur for certain types of temporary interruptions (such as an incoming phone call or SMS message) or when the user quits the application and it begins the transition to the background state.
        // Use this method to pause ongoing tasks, disable timers, and invalidate graphics rendering callbacks. Games should use this method to pause the game.
    }

    func applicationDidEnterBackground(_ application: UIApplication) {
        // Use this method to release shared resources, save user data, invalidate timers, and store enough application state information to restore your application to its current state in case it is terminated later.
        // If your application supports background execution, this method is called instead of applicationWillTerminate: when the user quits.
    }

    func applicationWillEnterForeground(_ application: UIApplication) {
        // Called as part of the transition from the background to the active state; here you can undo many of the changes made on entering the background.
    }

    func applicationDidBecomeActive(_ application: UIApplication) {
        // Restart any tasks that were paused (or not yet started) while the application was inactive. If the application was previously in the background, optionally refresh the user interface.
    }

    func applicationWillTerminate(_ application: UIApplication) {
        // Called when the application is about to terminate. Save data if appropriate. See also applicationDidEnterBackground:.
    }
    
    func application(_ application: UIApplication, didRegister notificationSettings: UNNotificationSettings) {
        application.registerForRemoteNotifications()
    }
    
    func application(_ application: UIApplication,
                       didRegisterForRemoteNotificationsWithDeviceToken deviceToken: NSData) {
        // デバイストークン取得後の処理
    }
    
    func application(_ application: UIApplication, didRegisterForRemoteNotificationsWithDeviceToken deviceToken: Data) {
        let token = deviceToken.map { String(format: "%.2hhx", $0) }.joined()

        print("Update APNs Token: \(token)")
        print("UUID: \(UIDevice.current.identifierForVendor!.uuidString)")

        Messaging.messaging().apnsToken = deviceToken
        
        let request = Request(deviceId: UIDevice.current.identifierForVendor!.uuidString)

        if let fcmToken = Messaging.messaging().fcmToken {
            request.postFCM(fcmToken: fcmToken)
        }
    }
    
    func userNotificationCenter(_ center: UNUserNotificationCenter, willPresent notification: UNNotification, withCompletionHandler completionHandler: @escaping (UNNotificationPresentationOptions) -> Void) {
        print("Notification called")
        
        if notification.request.identifier == "FAC12B16-AE9F-48FC-9118-8E1B0BD212CE" {
            let alertController = UIAlertController(title: "SOSが送信されました", message: "", preferredStyle: .alert)
            
            let otherAction = UIAlertAction(title: "はい", style: .default) {
                action in NSLog("はいボタンが押されました")
            }

            alertController.addAction(otherAction)
            
            window?.rootViewController?.present(alertController, animated: true, completion: nil)

            completionHandler(UNNotificationPresentationOptions.sound)
        }
    }
}
