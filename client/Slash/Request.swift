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
    static let URL_BASE = "https://private-anon-72073cf4f6-slashapp.apiary-mock.com"

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
    
    func postLocation(latitude : Double, longtitude : Double) {
        // MEMO: https://qiita.com/Chan_moro/items/a1aa89acf2b1d21f9498
        
        let parameters = [ "latitude": latitude, "longtitude": longtitude ]
        
        Alamofire.request(Request.LOCATIONS_PATH, method: .post, parameters: parameters, encoding: JSONEncoding.default, headers: self.headers).responseJSON { response in
            print("postLocation Status: \(String(describing: response.response?.statusCode))")
            print("Error: \(String(describing: response.error))")
        }
    }
    
    static let VIDEO_PATH = "\(URL_BASE)/video"
    
    func postVideo(fileURL : URL, completion : @escaping () -> Void) {
        // MEMO: https://github.com/Alamofire/Alamofire#uploading-data-to-a-server
        
        Alamofire.upload(fileURL, to: Request.VIDEO_PATH, headers: [ "X-UDID" : self.deviceId ]).response { response in
            print("postVideo Status: \(String(describing: response.response?.statusCode))")
            
            if response.error != nil {
                completion()
            }
        }
    }
    
    func getVideo(completion : @escaping ([ Video ]) -> Void) {
        Alamofire.request(Request.VIDEO_PATH, method: .get, encoding: JSONEncoding.default, headers: [ "X-UDID" : self.deviceId ]).responseJSON { response in
            print("getVideo Status: \(String(describing: response.response?.statusCode))")
            
            guard let object = response.result.value else { return }
            let json = JSON(object)

            let videos = json.arrayValue.map {
                return Video(
                    id: $0.intValue,
                    created_at: $0.intValue,
                    video_file: $0.string!,
                    thumbnail: $0.string!,
                    longtitude: $0.intValue,
                    latitude: $0.intValue
                )
            }
            
            completion(videos)
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
