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
    
//    func setCell(imageName: String, titleText: String, descriptionText: String) {
//        tableImageView.image = UIImage(named: imageName)
//        tablePlaceLabel.text = titleText
//        tableTimeLabel.text = descriptionText
//    }
    
//    func setCell(string: String, titleText: String, descriptionText: String) {
//        guard let url = URL(string: string) else { print("CustomTableViewCell#setCell Failed"); return }
//        guard let data = try? Data(contentsOf: url) else { print("CustomTableViewCell#setCell Failed"); return }
//
//        tableImageView = UIImageView(image: UIImage(data: data))
//    }
    
    func setCell(video: Video) {
        let url = URL(string: video.thumbnail)
        let data = try? Data(contentsOf: url!)
        
        tableImageView = UIImageView(image: UIImage(data: data!))
        
        tablePlaceLabel.text = video.place
        tableTimeLabel.text = self.convertUnixTimeToStringDate(unixtime: video.created_at)
    }
    
    static let FORMATTER : DateFormatter = {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy年MM月dd日 HH:mm:ss"
        return formatter
    }()
    
    private func convertUnixTimeToStringDate(unixtime : Int) -> String {
        let date = Date(timeIntervalSince1970: Double(unixtime))
        return CustomTableViewCell.FORMATTER.string(from: date)
    }
}
