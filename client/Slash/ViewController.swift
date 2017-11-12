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
import CoreBluetooth

class ViewController: UIViewController, AVCaptureFileOutputRecordingDelegate, CLLocationManagerDelegate, CBCentralManagerDelegate, CBPeripheralDelegate {
    let captureSession = AVCaptureSession()
    let videoDevice = AVCaptureDevice.default(for: AVMediaType.video)
    let audioDevice = AVCaptureDevice.default(for: AVMediaType.audio)
    let fileOutput = AVCaptureMovieFileOutput()
    let fileOutputPath : URL = {
        let paths = NSSearchPathForDirectoriesInDomains(.documentDirectory, .userDomainMask, true)
        let documentsDirectory = paths[0] as String
        let filePath : String? = "\(documentsDirectory)/temp.mp4"
        return URL(fileURLWithPath: filePath!)
    }()
    
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
    
    var flashEnable : Bool = false
    
    @IBOutlet weak var cameraLayer: UIView!
    @IBOutlet weak var stopButton: UIButton!
    
    var isRecording = false
    
    var notificationCenter : UNUserNotificationCenter?

    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.centralManager = CBCentralManager(delegate: self, queue: nil)
        
        self.notificationCenter = UNUserNotificationCenter.current()
        self.notificationCenter?.requestAuthorization(options: [.alert, .sound, .badge]) { (granted, error) in
            print(error ?? "");
        }
        
        self.setupStyles()
        
        print("Set up camera")
        if let videoLayer = self.setupVideo() {
            self.cameraLayer.layer.addSublayer(videoLayer)
        }
        
        self.captureSession.startRunning()
        self.fileOutput.startRecording(to: fileOutputPath, recordingDelegate: self)
        
        print("Set up location manager")
        if CLLocationManager.authorizationStatus() != .authorizedAlways {
            locationManager.requestAlwaysAuthorization()
        }
        
        self.locationManager.delegate = self
        
        print("Start updating Location")
        self.locationManager.startUpdatingLocation()
        
        self.flashTimer = Timer.scheduledTimer(timeInterval: 0.2, target: self, selector: #selector(toggleFlash), userInfo: nil, repeats: true)
        self.flashTimer.fire()
        
        self.flashEnable = true
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
    
    override func viewWillAppear(_ animated: Bool) {
        self.flashEnable = true
    }
    
    override func viewWillDisappear(_ animated: Bool) {
        print("View will disappear")
        super.viewWillDisappear(animated)
        
        self.flashEnable = false
    }
    
    var lastLocation : CLLocation?
    
    func locationManager(_ manager: CLLocationManager, didUpdateLocations locations: [CLLocation]) {
        print("Update location")
        
        if let location = locations.last {
            self.connectToKnownPeripheral();
            
            self.lastLocation = location
            
            self.request.postLocation(
                latitude: location.coordinate.latitude,
                longitude: location.coordinate.longitude
            )
        }
    }
    
    func captureOutput(captureOutput: AVCaptureFileOutput!, didStartRecordingToOutputFileAtURL fileURL: NSURL!, fromConnections connections: [AnyObject]!) {
    }
    
    func captureOutput(captureOutput: AVCaptureFileOutput!, didFinishRecordingToOutputFileAtURL outputFileURL: NSURL!, fromConnections connections: [AnyObject]!, error: NSError!) {
        print("didFinishRecordingToOutputFileAtURL: \(outputFileURL)")
//                let assetsLib = ALAssetsLibrary()
//                assetsLib.writeVideoAtPath(toSavedPhotosAlbum: outputFileURL as URL!, completionBlock: nil)
    }
    
    func fileOutput(_ output: AVCaptureFileOutput, didFinishRecordingTo outputFileURL: URL, from connections: [AVCaptureConnection], error: Error?) {
        print("didFinishRecordingTo: \(outputFileURL)")

        let data = try! Data(contentsOf: outputFileURL, options: [])
        self.request.postVideo(data: data, completion: {
        })
    }

    @objc func toggleFlash(flg: Bool) {
        if self.avDevice!.hasTorch {
            do {
                try self.avDevice?.lockForConfiguration()
                
                self.avDevice?.torchMode = (self.avDevice?.torchMode == .on || self.flashEnable == false ? .off : .on)
                
                self.avDevice?.unlockForConfiguration()
            } catch {
                print("Torch could not be used")
            }
        } else {
            print("Torch is not available")
        }
    }
    
    var peripherals: [CBPeripheral] = []
    var centralManager: CBCentralManager? = nil
    
    let serviceUUID = CBUUID.init(string: "4FAFC201-1FB5-459E-8FCC-C5C9C331914B")
    let deviceIdentifier = UUID(uuidString: "B7183C66-4223-D143-8BC4-9BDEA5C0FC14")!

    func scanBLEDevice() {
        self.centralManager?.scanForPeripherals(withServices: [ serviceUUID ], options: [ CBConnectPeripheralOptionNotifyOnConnectionKey: true ])
//        self.centralManager?.scanForPeripherals(withServices: nil, options: nil)
    }
    
    func stopScanForBLEDevice() {
        centralManager?.stopScan()
        print("scan stopped")
    }
    
    func connectToKnownPeripheral() {
        if !self.centralManagerReady { return }
        
        guard let peripherals = self.centralManager?.retrievePeripherals(withIdentifiers: [ deviceIdentifier ]) else { return }
        
        for peripheral in peripherals {
            self.peripheral = peripheral
            self.peripheral!.delegate = self
            self.centralManager!.connect(self.peripheral!, options: nil)
        }
    }
    
    let BLE_DEVICE_NAME = "MyESP32"
    var peripheral : CBPeripheral?
    var centralManagerReady = false
    
    func centralManagerDidUpdateState(_ central: CBCentralManager) {
        print("CBCentraolManager state: \(central.state)")
        
        switch central.state {
        case .poweredOn:
            self.centralManagerReady = true
            self.scanBLEDevice()
            // なんかdidUpdateLocationsでやらないと動かない
//            self.connectToKnownPeripheral()
            break
        default:
            break
        }
    }
    
    func centralManager(_ central: CBCentralManager, didDiscover peripheral: CBPeripheral, advertisementData: [String : Any], rssi RSSI: NSNumber) {
        if (peripheral.name == self.BLE_DEVICE_NAME) {
            print("Try to connect \(peripheral.name ?? "")")
            
            self.peripheral = peripheral
            self.peripheral!.delegate = self
            self.centralManager!.connect(self.peripheral!, options: nil)
        }
    }
    
    func centralManager(_ central: CBCentralManager, didConnect peripheral: CBPeripheral) {
        print("Connected to " + peripheral.name!)

        peripheral.discoverServices(nil)
    }
    
    func centralManager(_ central: CBCentralManager, didFailToConnect peripheral: CBPeripheral, error: Error?) {
        print("Bluetooth connect failed: \(error!)")
    }
    
    func peripheral(_ peripheral: CBPeripheral, didDiscoverServices error: Error?) {
        print("Find services")

        guard let services = peripheral.services else { print("Invalid services"); return }

        for service in services {
            if service.uuid == serviceUUID {
                peripheral.discoverCharacteristics(nil, for: service)
            }
        }
    }
    
    let characteristicUUID = "BEB5483E-36E1-4688-B7F5-EA07361B26A8"

    func peripheral(_ peripheral: CBPeripheral, didDiscoverCharacteristicsFor service: CBService, error: Error?) {
        print("peripheral:\(peripheral) and service:\(service)")

        for characteristic in service.characteristics! {
            print("characteristic: \(characteristic)")
            
            if characteristic.uuid.uuidString == characteristicUUID {
                peripheral.readValue(for: characteristic)
            }
        }
    }
    
    func peripheral(_ peripheral: CBPeripheral, didUpdateValueFor characteristic: CBCharacteristic, error: Error?) {
        let readValue = String(data: characteristic.value!, encoding: .utf8)!
        
        print("Characteristic value: \(readValue)")
        
        if readValue == "Welp!" {
            self.sendSos()
        }
    }
    
    // beb5483e-36e1-4688-b7f5-ea07361b26a8
    
    func notifyAlertPushed() {
        print(#function)
        
        let content = UNMutableNotificationContent()
        
        content.title = "LINEグループにSOSを発信しました"
        content.body = "少しでも怪しい人がいたら今すぐ110番通報"

        let trigger = UNTimeIntervalNotificationTrigger(timeInterval: 3, repeats: false)
        let request = UNNotificationRequest(identifier: "sos-notification", content: content, trigger: trigger)
        
        let center = UNUserNotificationCenter.current()
        center.add(request) { (error : Error?) in
            if error != nil {
                print("残念！SOSに失敗しました！")
            }
        }
    }
    
    var sosSend = false

    func sendSos() {
//        if sosSend {
//            print("SOS was already sent")
//            return
//        }
        
        print("Sending SOS")
        
        sosSend = true
        
        if let location = lastLocation {
            self.request.postSos(
                latitude: location.coordinate.latitude,
                longitude: location.coordinate.longitude
            )
            
            self.notifyAlertPushed()
        }
    }
}
