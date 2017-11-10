//
//  Request.swift
//  Slash
//
//  Created by Hiroaki KARASAWA on 2017/10/29.
//  Copyright © 2017年 Hiroaki KARASAWA. All rights reserved.
//

import Foundation
import Alamofire
import SwiftyJSON

class Request {
    static let URL_BASE = "https://www.slashapp.ml" // https://private-anon-72073cf4f6-slashapp.apiary-mock.com"

    let deviceId : String
    var headers : [ String : String ] {
        get {
            return [
                "Content-Type": "application/json",
                "X-UDID": self.deviceId
            ]
        }
    }
    
    init(deviceId : String) {
        self.deviceId = deviceId
    }
    
    static let LOCATIONS_PATH = "\(URL_BASE)/location"
    
    func postLocation(latitude : Double, longitude : Double) {
        print(#function)
        // MEMO: https://qiita.com/Chan_moro/items/a1aa89acf2b1d21f9498
        
        let parameters = [ "latitude": latitude, "longitude": longitude ]
        
        Alamofire.request(Request.LOCATIONS_PATH, method: .post, parameters: parameters, encoding: JSONEncoding.default, headers: self.headers).responseJSON { response in
            print("postLocation Status: \(String(describing: response.response?.statusCode))")
            print("Error: \(String(describing: response.error))")
        }
    }
    
    static let VIDEO_PATH = "\(URL_BASE)/video"
    
    // MEMO: Output file is too large!
    func postVideo(data : Data, completion : @escaping () -> Void) {
        print(#function)
        // MEMO: https://github.com/Alamofire/Alamofire#uploading-data-to-a-server
        
        Alamofire.upload(
            multipartFormData: { multipartFormData in
                multipartFormData.append(
                    data,
                    withName: "video",
                    fileName: "temp.mp4",
                    mimeType: "multipart/form-data"
                )
            },
            to: Request.VIDEO_PATH,
            headers: [ "X-UDID" : self.deviceId ],
            encodingCompletion: { encodingResult in
                switch encodingResult {
                case .success(let upload, _, _):
                    upload
                        .uploadProgress(closure: { (progress) in
                            print("Upload Progress: \(progress.fractionCompleted)")
                        })
                        .responseString { response in
                            debugPrint(response)
                        }
                case .failure(let encodingError):
                    print(encodingError)
                }
        })
    }
    
    func getVideo(completion : @escaping ([ Video ]) -> Void) {
        print(#function)
        
        Alamofire.request(Request.VIDEO_PATH, method: .get, encoding: JSONEncoding.default, headers: [ "X-UDID" : self.deviceId ]).responseJSON { response in
            print("getVideo Status: \(String(describing: response.response?.statusCode))")
            
            guard let object = response.result.value else { return }
            let json = JSON(object)

            let videos = json.arrayValue.map {
                return Video(
                    id: $0["owner_id"].intValue,
                    created_at: $0["created_at"].intValue,
                    video_file: $0["file_name"].string!,
                    thumbnail: $0["thumb_name"].string!,
                    place: $0["location"].string!
                )
            }
            
            completion(videos)
        }
    }
    
    static let FCM_PATH = "\(URL_BASE)/fcm"
    
    func postFCM(fcmToken : String) {
        print(#function)

        let parameters = [ "token" : fcmToken ]
        
        Alamofire.request(Request.FCM_PATH, method: .post, parameters: parameters, encoding: JSONEncoding.default, headers: [ "X-UDID" : self.deviceId ]).responseJSON { response in
            print("postFCM Status: \(String(describing: response.response?.statusCode))")
        }
    }
    
//    guard let object = response.result.value else {    return }
//    let json = JSON(object)
//    guard let full_text = json["responses"][0]["textAnnotations"][0]["description"].string else { return }
//
//    self.MARequest(full_text)
//    func postVideo(data : Data, completion : () -> Void) {
//        Alamofire.upload(
//            multipartFormData: { multipartFormData in
//                multipartFormData.append(
//                    data,
//                    withName: self.deviceId,
//                    fileName: "movie",
//                    mimeType: "multipart/form-data"
//                )
//            },
//            to: Request.VIDEO_PATH,
//            headers: self.headers,
//            encodingCompletion: { encodingResult in
//                switch encodingResult {
//                case .success(let upload, _, _):
//                    upload.response { request, response, data, error in
//                        print("response: \(response)")
//                        print("error: \(error)")
//
//                        if error != nil {
//                            completion()
//                        }
//                    }
//                case .failure(let encodingError):
//                    print(encodingError)
//                }
//            }
//        )
//    }
}
