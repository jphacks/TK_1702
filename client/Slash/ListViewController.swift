//
//  ListViewController.swift
//  Slash
//
//  Created by Hiroaki KARASAWA on 2017/10/28.
//  Copyright © 2017年 Hiroaki KARASAWA. All rights reserved.
//

import UIKit
import Foundation

class ListViewController: UIViewController {
    @IBOutlet weak var startButton: UIButton!
    
    override func viewDidLoad() {
        super.viewDidLoad()
        
        self.setupStyles()
    }
    
    func setupStyles() {
        self.startButton.layer.masksToBounds = true
        self.startButton.layer.cornerRadius = 20.0
    }
}
