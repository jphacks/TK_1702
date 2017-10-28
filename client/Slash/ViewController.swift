//
//  ViewController.swift
//  Slash
//
//  Created by Hiroaki KARASAWA on 2017/10/28.
//  Copyright © 2017年 Hiroaki KARASAWA. All rights reserved.
//

import UIKit
import AVFoundation
import AssetsLibrary
import CoreLocation
import Alamofire
import UserNotifications

class ViewController: UIViewController, AVCaptureFileOutputRecordingDelegate, CLLocationManagerDelegate {
    let captureSession = AVCaptureSession()
    let videoDevice = AVCaptureDevice.default(for: AVMediaType.video)
    let audioDevice = AVCaptureDevice.default(for: AVMediaType.audio)
    let fileOutput = AVCaptureMovieFileOutput()
    
    var locationManager : CLLocationManager = {
        let manager = CLLocationManager()
        
        // TODO: Fix for production
        manager.distanceFilter = 1
        manager.allowsBackgroundLocationUpdates = true
        
        return manager
    }()
    
    let avDevice = AVCaptureDevice.default(for: .video)
    var flashTimer : Timer!
    
    @IBOutlet weak var cameraLayer: UIView!
    @IBOutlet weak var stopButton: UIButton!
    
    var isRecording = false

    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.setupStyles()
        
        print("Set up camera")
        if let videoLayer = self.setupVideo() {
            self.cameraLayer.layer.addSublayer(videoLayer)
        }
        
        self.captureSession.startRunning()
        
        print("Set up location manager")
        if CLLocationManager.authorizationStatus() != .authorizedAlways {
            locationManager.requestAlwaysAuthorization()
        }
        
        self.locationManager.delegate = self
        
        print("Start updating Location")
        self.locationManager.startUpdatingLocation()
        
        print("Set timer")
        self.flashTimer = Timer.scheduledTimer(timeInterval: 0.2, target: self, selector: #selector(toggleFlash), userInfo: nil, repeats: true)
        self.flashTimer.fire()
                
        let notificationCenter = NotificationCenter.default
        notificationCenter.addObserver(self, selector: #selector(appMovedToBackground), name: Notification.Name.UIApplicationWillResignActive, object: nil)
        
        // This is for debugging.
        let params = [
            "latitude" : 34.5,
            "longtitude" : 124.8
        ]
        
        let header = [
            "Content-Type":"application/json",
            "X-UDID": UIDevice.current.identifierForVendor!.uuidString
        ]
        
        Alamofire.request("https://private-anon-72073cf4f6-slashapp.apiary-mock.com/locations", parameters: params, headers:header)
    }
    
    func setupVideo() -> AVCaptureVideoPreviewLayer? {
        do {
            if let videoDevice = self.videoDevice {
                let videoInput = try AVCaptureDeviceInput(device: videoDevice) as AVCaptureDeviceInput
                self.captureSession.addInput(videoInput)
            }
            if let audioDevice = self.audioDevice {
                let audioInput = try AVCaptureDeviceInput(device: audioDevice)  as AVCaptureInput
                self.captureSession.addInput(audioInput);
            }
        } catch {
            return nil
        }
        
        self.captureSession.addOutput(self.fileOutput)
        let videoLayer = AVCaptureVideoPreviewLayer(session: self.captureSession)
        
        videoLayer.frame = self.view.bounds
        videoLayer.videoGravity = .resizeAspectFill
        
        return videoLayer
    }
    
    func setupStyles() {
        self.stopButton.layer.masksToBounds = true
        self.stopButton.layer.cornerRadius = 20.0
    }
    
    @IBAction func onClickStopButton(_ sender: Any) {
        print("Finish capturing")
        
        fileOutput.stopRecording()
    }

    func captureOutput(captureOutput: AVCaptureFileOutput!, didStartRecordingToOutputFileAtURL fileURL: NSURL!, fromConnections connections: [AnyObject]!) {
    }
    
    func captureOutput(captureOutput: AVCaptureFileOutput!, didFinishRecordingToOutputFileAtURL outputFileURL: NSURL!, fromConnections connections: [AnyObject]!, error: NSError!) {
//        let assetsLib = ALAssetsLibrary()
//        assetsLib.writeVideoAtPath(toSavedPhotosAlbum: outputFileURL as URL!, completionBlock: nil)
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    override func viewWillDisappear(_ animated: Bool) {
        super.viewWillDisappear(animated)

        self.flashTimer.invalidate()
    }
    
    @objc func appMovedToBackground() {
        self.flashTimer.invalidate()
    }
    
    func applicationDidEnterBackground(application: UIApplication) {
        
    }
    
    func locationManager(_ manager: CLLocationManager, didUpdateLocations locations: [CLLocation]) {
        let location = locations.last!
        let params = [
            "latitude" : location.coordinate.latitude,
            "longtitude" : location.coordinate.longitude
        ]
        print(params)
        Alamofire.request("http://www.slashapp.ml/locations", parameters: params)
    }
    
    func fileOutput(_ output: AVCaptureFileOutput, didFinishRecordingTo outputFileURL: URL, from connections: [AVCaptureConnection], error: Error?) {
        self.sendMovie()
    }
    
    @objc func toggleFlash(flg: Bool) {
        if self.avDevice!.hasTorch {
            do {
                try self.avDevice?.lockForConfiguration()
                
                self.avDevice?.torchMode = (self.avDevice?.torchMode == .on ? .off : .on)
                
                self.avDevice?.unlockForConfiguration()
            } catch {
                print("Torch could not be used")
            }
        } else {
            print("Torch is not available")
        }
    }
    
    private func sendMovie() {
        // This is for debugging.
        Alamofire.request("https://httpbin.org/get").response { response in
            print("Request: \(String(describing: response.request))")
            print("Response: \(String(describing: response.response) )")
            print("Error: \(String(describing: response.error))")
            
            if let data = response.data, let utf8Text = String(data: data, encoding: .utf8) {
                print("Data: \(utf8Text)")
            }
        }
    }
}

