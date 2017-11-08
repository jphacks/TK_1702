//
//  Video.swift
//  Slash
//
//  Created by Hiroaki KARASAWA on 2017/10/29.
//  Copyright © 2017年 Hiroaki KARASAWA. All rights reserved.
//

import Foundation

class Video {
    let id: Int
    let created_at: Int
    let video_file: String
    let thumbnail: String
    let place: String
    
    init(id: Int, created_at: Int, video_file: String, thumbnail: String, place: String) {
        self.id = id
        self.created_at = created_at
        self.video_file = video_file
        self.thumbnail = thumbnail
        self.place = place
    }
}
