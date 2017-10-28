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

class ViewController: UIViewController,AVCaptureFileOutputRecordingDelegate, CLLocationManagerDelegate  {
    func fileOutput(_ output: AVCaptureFileOutput, didFinishRecordingTo outputFileURL: URL, from connections: [AVCaptureConnection], error: Error?) {
        // TODO: send movie to server.
    }
    
    var index = 2
    
    let captureSession = AVCaptureSession()
    let videoDevice = AVCaptureDevice.default(for: AVMediaType.video)
    let audioDevice = AVCaptureDevice.default(for: AVMediaType.audio)
    let fileOutput = AVCaptureMovieFileOutput()
    
    var locationManager: CLLocationManager!
    
    var stopButton : UIButton!
    var isRecording = false

    override func viewDidLoad() {
        super.viewDidLoad()
        
        let videoInput = try! AVCaptureDeviceInput(device: self.videoDevice!) as AVCaptureDeviceInput
        self.captureSession.addInput(videoInput)
        let audioInput = try! AVCaptureDeviceInput(device: self.audioDevice!)  as AVCaptureInput
        self.captureSession.addInput(audioInput);
        
        self.captureSession.addOutput(self.fileOutput)
        
        let videoLayer : AVCaptureVideoPreviewLayer = AVCaptureVideoPreviewLayer(session: self.captureSession) as AVCaptureVideoPreviewLayer
        videoLayer.frame = self.view.bounds
        videoLayer.videoGravity = AVLayerVideoGravity.resizeAspectFill
        self.view.layer.addSublayer(videoLayer)
        
        self.setupButton()
        self.captureSession.startRunning()
        
        if locationManager == nil {
            locationManager = CLLocationManager()
            if #available(iOS 8.0, *) {
                // NSLocationWhenInUseUsageDescriptionに設定したメッセージでユーザに確認
                locationManager.requestWhenInUseAuthorization()
                // NSLocationAlwaysAndWhenInUseUsageDescriptionに設定したメッセージでユーザに確認
                //locationManager.requestAlwaysAuthorization()
            }
        }
        
        locationManager.delegate = self
        locationManager.startUpdatingLocation()
    }
    
    
    func setupButton(){
        self.stopButton = UIButton(frame: CGRect(x:0,y:0,width:120,height:50))
        self.stopButton = UIButton(frame: CGRect(x:0,y:0,width:120,height:50))
        self.stopButton.backgroundColor = UIColor.gray;
        self.stopButton.layer.masksToBounds = true
        self.stopButton.setTitle("stop", for: UIControlState.normal)
        self.stopButton.layer.cornerRadius = 20.0

        self.stopButton.layer.position = CGPoint(x: self.view.bounds.width/2 + 70, y:self.view.bounds.height-50)

        self.stopButton.addTarget(self, action: #selector(onClickStopButton), for: .touchUpInside)

        self.view.addSubview(self.stopButton);
        self.view.addSubview(self.stopButton);

        self.isRecording = true

    }
    
    @objc func onClickStopButton(sender: UIButton){
        if self.isRecording {
            fileOutput.stopRecording()
            
            self.isRecording = false
            self.changeButtonColor(target: self.stopButton, color: UIColor.gray)
        }
    }
    
    func changeButtonColor(target: UIButton, color: UIColor){
        target.backgroundColor = color
    }
    
    func captureOutput(captureOutput: AVCaptureFileOutput!, didStartRecordingToOutputFileAtURL fileURL: NSURL!, fromConnections connections: [AnyObject]!) {
    }
    
    func captureOutput(captureOutput: AVCaptureFileOutput!, didFinishRecordingToOutputFileAtURL outputFileURL: NSURL!, fromConnections connections: [AnyObject]!, error: NSError!) {
        let assetsLib = ALAssetsLibrary()
        assetsLib.writeVideoAtPath(toSavedPhotosAlbum: outputFileURL as URL!, completionBlock: nil)
    }
    
    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }
    
    func locationManager(_ manager: CLLocationManager, didUpdateLocations locations: [CLLocation]) {
        let location = locations.last!
        let latitude = String(format: "%+.06f", location.coordinate.latitude)
        let longitude = String(format: "%+.06f", location.coordinate.longitude)
        let df = DateFormatter()
        df.dateFormat = "yyyy/MM/dd HH:mm:ss"
        let timestamp = df.string(from: location.timestamp)
        print("\(timestamp) \(latitude) \(longitude)")
    }


}

