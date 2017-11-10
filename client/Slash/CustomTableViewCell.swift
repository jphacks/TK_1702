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
    }
    
    func setCell(video: Video) {
        let url = URL(string: video.thumbnail)
        let data = try? Data(contentsOf: url!)
        
        tableImageView.image = UIImage(data: data!)
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
