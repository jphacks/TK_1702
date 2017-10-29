//
//  ListViewController.swift
//  Slash
//
//  Created by Hiroaki KARASAWA on 2017/10/28.
//  Copyright © 2017年 Hiroaki KARASAWA. All rights reserved.
//

import UIKit
import Foundation

class ListViewController: UIViewController, UITableViewDataSource, UITableViewDelegate  {
    @IBOutlet weak var tableView: UITableView!
    @IBOutlet weak var startButton: UIButton!
    
    var placeList = ["本郷1丁目","本郷2丁目", "本郷3丁目","本郷4丁目","本郷5丁目","本郷6丁目","本郷7丁目", "本郷8丁目"]
    var timeList = ["2017年10月22日","2017年10月23日", "2017年10月24日","2017年10月27日","2017年10月27日","2016年9月17日","2016年11月1日", "2017年12月31日"]
    
    var imageNameList = ["list_sample_image", "list_sample_image_2", "list_sample_image_3",
         "list_sample_image_4", "list_sample_image_5",
         "list_sample_image_5", "list_sample_image_5",
         "list_sample_image_5"]
    
    //データを返すメソッド（スクロールなどでページを更新する必要が出るたびに呼び出される）
    internal func tableView(_ tableView:UITableView, cellForRowAt indexPath:IndexPath) -> UITableViewCell {
        let cell = tableView.dequeueReusableCell(withIdentifier: "testCell", for: indexPath) as! CustomTableViewCell

        cell.setCell(imageName: imageNameList[indexPath.row], titleText: placeList[indexPath.row], descriptionText: timeList[indexPath.row])
        
        return cell
    }
    
    //データの個数を返すメソッド
    func tableView(_ tableView:UITableView, numberOfRowsInSection section:Int) -> Int {
        return timeList.count
    }
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.setupStyles()
        tableView.delegate = self
        tableView.dataSource = self
    }
    
    func setupStyles() {
        self.startButton.layer.masksToBounds = true
        self.startButton.layer.cornerRadius = 20.0
    }
    
}
