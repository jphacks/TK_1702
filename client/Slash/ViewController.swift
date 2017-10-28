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
    
    let request : Request = {
        return Request(deviceId: UIDevice.current.identifierForVendor!.uuidString)
    }()
    
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
        
        self.flashTimer = Timer.scheduledTimer(timeInterval: 0.2, target: self, selector: #selector(toggleFlash), userInfo: nil, repeats: true)
        self.flashTimer.fire()

        
        // NOTE: Work correctly?
        let notificationCenter = NotificationCenter.default
        notificationCenter.addObserver(self, selector: #selector(appMovedToBackground), name: Notification.Name.UIApplicationDidEnterBackground, object: nil)
        notificationCenter.addObserver(self, selector: #selector(appMovedToForeground), name: Notification.Name.UIApplicationWillEnterForeground, object: nil)
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

    override func didReceiveMemoryWarning() {
        print("View did receive memory warning")
        super.didReceiveMemoryWarning()
    }
    
    override func viewWillDisappear(_ animated: Bool) {
        print("View will disappear")
        super.viewWillDisappear(animated)
        
        if self.flashTimer.isValid {
            self.flashTimer.invalidate()
        }
    }
    
    @objc func appMovedToForeground() {
        print("App moved to foreground")
        
        if self.flashTimer.isValid {
            self.flashTimer.invalidate()
        }
        
        self.flashTimer = Timer.scheduledTimer(timeInterval: 0.2, target: self, selector: #selector(toggleFlash), userInfo: nil, repeats: true)
        self.flashTimer.fire()
    }
    
    @objc func appMovedToBackground() {
        if self.flashTimer.isValid {
            self.flashTimer.invalidate()
        }
    }
    
    func locationManager(_ manager: CLLocationManager, didUpdateLocations locations: [CLLocation]) {
        print("Update location")
        
        let location = locations.last!
        
        self.request.postLocation(
            latitude: location.coordinate.latitude,
            longtitude: location.coordinate.longitude
        )
    }
    
    func captureOutput(captureOutput: AVCaptureFileOutput!, didStartRecordingToOutputFileAtURL fileURL: NSURL!, fromConnections connections: [AnyObject]!) {
    }
    
    func captureOutput(captureOutput: AVCaptureFileOutput!, didFinishRecordingToOutputFileAtURL outputFileURL: NSURL!, fromConnections connections: [AnyObject]!, error: NSError!) {
        print("didFinishRecordingToOutputFileAtURL: \(outputFileURL)")
        //        let assetsLib = ALAssetsLibrary()
        //        assetsLib.writeVideoAtPath(toSavedPhotosAlbum: outputFileURL as URL!, completionBlock: nil)
    }
    
    func fileOutput(_ output: AVCaptureFileOutput, didFinishRecordingTo outputFileURL: URL, from connections: [AVCaptureConnection], error: Error?) {
        print("didFinishRecordingTo: \(outputFileURL)")

//        let data = try! Data(contentsOf: outputFileURL, options: [])
        self.request.postVideo(fileURL: outputFileURL, completion: {
            
        })
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
}
