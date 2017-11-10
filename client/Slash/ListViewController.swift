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
    @IBOutlet weak var lineButton: UIButton!
    
    var placeList = ["本郷1丁目","本郷2丁目", "本郷3丁目","本郷4丁目","本郷5丁目","本郷6丁目","本郷7丁目", "本郷8丁目"]
    var timeList = ["2017年10月22日","2017年10月23日", "2017年10月24日","2017年10月27日","2017年10月27日","2016年9月17日","2016年11月1日", "2017年12月31日"]
    
    var imageNameList = ["list_sample_image", "list_sample_image_2", "list_sample_image_3",
         "list_sample_image_4", "list_sample_image_5",
         "list_sample_image_5", "list_sample_image_5",
         "list_sample_image_5"]
    
    var refreshControl: UIRefreshControl!

    let request : Request = {
        return Request(deviceId: UIDevice.current.identifierForVendor!.uuidString)
    }()
    
    var videos : [ Video ] = []
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.fetchVideo()
        self.setupStyles()
        
        tableView.delegate = self
        tableView.dataSource = self
        
        self.refreshControl = UIRefreshControl()
        self.refreshControl.attributedTitle = NSAttributedString(string: "")
        self.refreshControl.addTarget(self, action: #selector(refresh), for: UIControlEvents.valueChanged)
        self.tableView.addSubview(refreshControl)
    }
    
    @objc func refresh() {
        self.refreshControl.endRefreshing()
        self.fetchVideo()
        self.tableView.reloadData()
    }
    
    func fetchVideo() {
        request.getVideo { self.videos = $0.reversed() }

        self.tableView.reloadData()
    }
    
    func setupStyles() {
        self.startButton.layer.masksToBounds = true
        self.startButton.layer.cornerRadius = 20.0
    }
    
    //データを返すメソッド（スクロールなどでページを更新する必要が出るたびに呼び出される）
    internal func tableView(_ tableView:UITableView, cellForRowAt indexPath:IndexPath) -> UITableViewCell {
        let cell = tableView.dequeueReusableCell(withIdentifier: "testCell", for: indexPath) as! CustomTableViewCell
        
        cell.setCell(video: videos[indexPath.row])
        
        return cell
    }
    
    //データの個数を返すメソッド
    func tableView(_ tableView:UITableView, numberOfRowsInSection section:Int) -> Int {
        return videos.count
    }
    @IBAction func pushLineButton(_ sender: Any) {
        let alertController = UIAlertController(title: "Line連携します", message: "totem", preferredStyle: .alert)
        
        let otherAction = UIAlertAction(title: "はい", style: .default) {
            action in NSLog("はいボタンが押されました")
        }
        let cancelAction = UIAlertAction(title: "いいえ", style: .cancel) {
            action in NSLog("いいえボタンが押されました")
        }
        
        // addActionした順に左から右にボタンが配置されます
        alertController.addAction(otherAction)
        alertController.addAction(cancelAction)
        
        present(alertController, animated: true, completion: nil)
    }
}
