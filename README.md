# Miga

![Miga](https://user-images.githubusercontent.com/16631193/32140703-7d0e8834-bcad-11e7-80d5-46efaf309f71.png)

## 製品概要
### Security X Tech 

Demo Movie

[![Demo Movie](https://img.youtube.com/vi/kPmTwPJhIHc/0.jpg)](https://www.youtube.com/watch?v=kPmTwPJhIHc)

### 背景（製品開発のきっかけ、課題等）
比較的安全な国と言われている日本。でも本当にそうでしょうか？

- 性犯罪（強姦・強制わいせつ・公然わいせつ）は毎年1万件近く発生し、迷惑防止条例違反（痴漢、盗撮）も増加傾向にあり、こういった犯罪は夜の時間帯20~24時に多く発生しています。
![時間帯別強姦・強制わいせつ・痴漢の発生件数](http://natural-friends.jp/thumb/blog/2015/security/security01/0128_007-1.gif)

- いけないとはわかっていていてもつい歩きスマホをして周囲への注意がおなざりになってしまいます。
[夜道での犯罪の加害者への調査](http://natural-friends.jp/dont-walk-alone/)では、「・無防備（音楽を聴いている、メールや電話をして歩いている）」女性は特に狙いやすいとの調査結果があります。
- 危険に気づいても咄嗟に「スマホのロックを解除して、Lineを起動して、友達に連絡して…」では取り返しのつかない事態になりかねません。
- 普段あまり歩かないエリアだと、一見安全にみえて犯罪が起こりやすい場所なども土地勘がないため知ることができません。


### 製品説明（具体的な製品の説明）
私たちの考えた「Miga」は、あなたが危ない場所に近づかないように見守り、
もしあなたが危険な目に遭遇したらあなたに代わって家族、恋人、友人にあなたの危険を伝えます。

### 特長
Migaは夜道での犯罪はどのように発生し、どうしたら防止ができるのか、また仮に発生してしまった場合どうしたら被害者の身体的心理的被害を減らすことができるかを考え、必要な機能を最も自然に、そして迅速に使用できるようにしました。

#### 1. 特長1
アプリケーションを起動していない時でも位置情報を取得し、もし最近犯罪の発生したエリアに接近した際には「どういった犯罪がいつどこで」発生したのかを通知します。
これによって例えば夜道にTwitterやFacebookなどを見ていた、音楽を聞いていた等の時に注意を歓喜し、ながら歩きをやめる、カバンの持ち方を変えるなど行動を変えることができます。

#### 2. 特長2
アプリを立ち上げると同時にカメラが起動し、撮影をすると同時にサーバーに位置情報、撮影した動画が送信されます。また同時にカメラが点滅し不審者に対してこちらが警戒をしていること、顔を撮影したことを示します。
Lineなどのメッセンジャーアプリで知り合いに緊急を伝える、防犯系アプリを起動するなどは、咄嗟に行わなくてはいけない動作が多くなります。
また携帯に注意力を向けることはより危険な状況を招き兼ねません。

#### 3. 特長3
サーバー側に動画が送られると、登録されたLineユーザー(アプリ使用者の家族、友達、恋人など)もしくはグループに対して位置情報、撮影された動画が送られ、仮に近くにいた場合は駆けつけることが、また遠くにいる際は彼らから警察に通報することが可能になります。

### 解決出来ること

女性が一人でも安心して夜道を歩くことができるようになる。

### 今後の展望

1. アプリにマップのビューを追加し、危険地域に近づく前に危険地域を可視化して注意できるようにする。
2. ユーザーから投稿された動画の地理情報を集積し、危険地域の情報をよりユーザーの実感に近いものにアップデートする。

## 開発内容・開発技術
### 活用した技術
#### API・データ
今回スポンサーから提供されたAPI、製品などの外部技術があれば記述をして下さい。

* 東京都・東京都青少年・治安対策本部, http://opendata-catalogue.metro.tokyo.jp/dataset/t000002d0000000017, 東京オープンデータカタログサイト「町丁字別犯罪情報　平成29年分（月ごとの数値）」, 2017年10月28日.
* 独立行政法人統計センター, http://e-stat.go.jp/SG2/eStatGIS/page/download.html, 東京都文京区の丁目境界データ, 2017年10月28日.

#### フレームワーク・ライブラリ・モジュール

**Client (iOS Application)**

* Firebase Cloud Messaging
* Firebase Instance Id
* Alamofire
* SwiftyJSON
* LINE Messaging API

**Server (Web Application)**

* Slim Framework v3
* Propel v2
* monolog
* LINE Messaging API

#### デバイス
* iPhone

### 研究内容・事前開発プロダクト（任意）
なし

### 独自開発技術（Hack Dayで開発したもの）
#### 2日間に開発した独自の機能・技術

* テーマが決まったのがハッカソン1日目の15時だったので、ちょうど1日ですべての機能を実現した！
    * iOSアプリケーションおよびAPI用のWebアプリケーション
* 特に難しかったのはユーザーの位置(経度・緯度)から町丁字情報(「本郷7丁目」というような文字情報)をmysqlから検索して犯罪情報をリストアップするところ
    * 東京都から公開されている町丁字ごとの犯罪情報に統計センターの丁目境界データを結合し、境界データから重心を計算し、重心をユーザーの位置情報で検索した
    * 検索にはMySQLの地理空間データ用の検索システム(GIS)を用いた
* 投稿された動画をLINEに投稿するためのユーザーインビテーションシステムの作り込みにも苦労した
    * APIとしては整備されているが、LINEを介さないユーザーの動作に応じて投稿を行う既存サービスが少ないため、今回の要件にぴったり当てはまるユーザー同定の仕組みを試行錯誤で作り上げる必要があった
