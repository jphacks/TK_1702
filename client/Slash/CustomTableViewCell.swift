//
//  CustomTableViewCell.swift
//
//  Created by AkariAsai on 2017/10/28.
//

import UIKit

class CustomTableViewCell: UITableViewCell {

    @IBOutlet weak var tableImageView: UIImageView!
    @IBOutlet weak var tablePlaceLabel: UILabel!
    @IBOutlet weak var tableTimeLabel: UILabel!
    
    override func awakeFromNib() {
        super.awakeFromNib()
        // Initialization code
    }

    override func setSelected(_ selected: Bool, animated: Bool) {
        super.setSelected(selected, animated: animated)

        // Configure the view for the selected state
    }
    
    func setCell(imageName: String, titleText: String, descriptionText: String) {
        tableImageView.image = UIImage(named: imageName)
        tablePlaceLabel.text = titleText
        tableTimeLabel.text = descriptionText
    }
    

}
