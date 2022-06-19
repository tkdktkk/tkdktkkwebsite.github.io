<?php

///////////////////////////////////////////////////
// いいねボタン改 Ver1.2
// 製作者    ：ガタガタ
// サイト    ：https://do.gt-gt.org/
// ライセンス：MITライセンス
// 全文      ：https://ja.osdn.net/projects/opensource/wiki/licenses%2FMIT_license
// 公開日    ：2021.12.30
// 最終更新日：2022.05.25
//
// このプログラムはどなたでも無償で利用・複製・変更・
// 再配布および複製物を販売することができます。
// ただし、上記著作権表示ならびに同意意志を、
// このファイルから削除しないでください。
///////////////////////////////////////////////////

header('Content-Type: text/html; charset=UTF-8');

$include = get_included_files();
if (array_shift($include) === __FILE__) {
    die('このファイルへの直接のアクセスは禁止されています。');
}

include_once(dirname(__FILE__).'/admin/inc/_config.php');

class newiine {

    // コンストラクタ宣言
    public function __construct() {

    date_default_timezone_set('Asia/Tokyo');
    $this->today = date("Y/m/d");
    $this->time = date("H:i:s");
    
    $this->visitorIP = $_SERVER["REMOTE_ADDR"];
    
    global $limitPost;
    global $saveperiod;
    
    $this->iineLimit = $limitPost;
    $this->saveperiod = $saveperiod;
    $this->sorteddate = date("Y/m/d", strtotime('-'.$this->saveperiod.' day'));
    }
    
    // タグなどの送信を拒否
    public function entity($txt) {
        $newTxt = htmlentities($txt);
        return $newTxt;
      }
  
    public function doublequotation($txt) {
      $newTxt = '"' .$txt. '"';
      return $newTxt;
    }
      
    // URL名がindex.htmlもしくはindex.phpで終わる場合はURLを丸める
    public function checkURL($url) {
        $filenames = array('index.html', 'index.php');
        foreach ($filenames as $filename) {
          if (strpos($url, $filename) !== false) {
            $url = rtrim($url, $filename);
          }
        }
        return $url;
      }

      // URLからタイトルを取得する
      public function getHTMLtitle($URL) {
        if( $source = @file_get_contents($URL)) {
          //文字コードをUTF-8に変換し、正規表現でタイトルを抽出
          if (preg_match('/<title>(.*?)<\/title>/i', mb_convert_encoding($source, 'UTF-8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS'), $result)) {
              $title = $result[1];
          } else {
              //TITLEタグが存在しない場合
              $title = 'タイトルを取得できませんでした。';
          }
          
                  } else {
                          
                    //エラー処理
                    if(count($http_response_header) > 0){
                      //「$http_response_header[0]」にはステータスコードがセットされている
                      $status_code = explode(' ', $http_response_header[0]);  //「$status_code[1]」にステータスコードの数字だけが入る
          
                      //エラーの判別
                      switch($status_code[1]){
                          //404エラーの場合
                          case 404:
                              $title = "指定したページが見つかりませんでした。data-iineurlの値を確認してください。";
                              break;
                          //500エラーの場合
                          case 500:
                            $title = "指定したページがあるサーバーにエラーがあります";
                              break;
                          //その他のエラーの場合
                          default:
                          $title = "何らかのエラーによって指定したページのデータを取得できませんでした";
                      }
                  }else{
                      //タイムアウトの場合 or 存在しないドメインだった場合
                      $title = "タイムエラー or URLが間違っています";
                  }
                }
          return $title;
      }

      private function checkTodaysCount($btnName, $iineNewCountLimit) {
        $count = $this->openCSV($btnName, 'count');
        $countLimit = '';

          if ($iineNewCountLimit !== "false") {
            // 個別にいいね回数上限が設定されていれば、それに従う
            $countLimit = $iineNewCountLimit;
          } else {
            $countLimit = $this->iineLimit;
          }

          if ($count < $countLimit) {
            // 上限に達していない場合はfalseを返す
            return false;
          } else {
            // 上限に達している場合はtrueを返す
            return true;
          }

        }

    // CSVを開いて当該いいねボタンに関するデータを引っ張り出す関数
    public function openCSV($planeBtnName, $mode = null, $URL = null) {
      $btnName = mb_convert_encoding($planeBtnName, "UTF-8");
      if($mode === true) {
        $filename = dirname(__FILE__, 1). '/datas/'.$btnName.'.csv';
      } else {
        $filename = 'datas/'.$btnName.'.csv';
      }
      if($mode === 'count' && !file_exists($filename)) {
        return 0;
      }
      if(file_exists($filename)) {
          $fp = fopen($filename, "r");
          $csvArray = array();
    
          // CSVからデータを取得し二次元配列に変換する
          $row = 0;
          while( $ret_csv = fgetcsv( $fp, 0 ) ){
            for($col = 0; $col < count( $ret_csv ); $col++ ){
              $csvArray[$row][$col] = $ret_csv[$col];
            }
            $row++;
          }
          fclose($fp);

          // いいね数を返すモード
          if($mode === 'count') {
            if(!$csvArray) {
              return 0;
            }
            $count = 0;
            foreach ($csvArray as $key => $value) {
              if($value[2] === $this->visitorIP && $value[3] === $this->today) {
                $count += $value[5];
              }
            }
              return $count;
          }
    
          // データがある場合は、取得した二次元配列から、
          // リクエストの飛んできたいいねボタンのデータを探す。なければfalseを返す
          $num = false;
          if($mode === null) {
            foreach ($csvArray as $key => $value) {
              if($value[2] === $this->visitorIP && $value[3] === $this->today && $value[0] === $URL) {
                $num = $key;
              }
            }
          }

        } else {
          $num = false;
          $csvArray = false;
        }

        return array($num, $csvArray);
      }
      
      // CSVファイルに二次元配列を上書きする関数
    private function rewriteCSV($planeBtnName, $csvArray, $num) {
        $btnName = mb_convert_encoding($planeBtnName, "UTF-8");
        $filename = 'datas/'.$btnName.'.csv';
        $fp = fopen($filename, 'w');
  
        // 二次元配列を１行ずつCSV形式に直して書き込む
        foreach ($csvArray as $key => $v) {
            if ($key !== $num) {
                  $v[0] = $this->doublequotation($v[0]);
                  $v[1] = $this->doublequotation($v[1]);
            }
          $line = implode(',' , $v);
          fwrite($fp, $line . "\n");
        }
        // ファイルを閉じる
        fclose($fp);
    }
    
    // 過去3ヶ月分の詳細ないいねログを統合する関数
    public function sortOutData($btnname, $url) {
      list($num, $csvArray) = $this->openCSV($btnname);

      if($csvArray === false) {
        return;
      }
      
      $time1 = strtotime($this->today);
      $time2 = strtotime($csvArray[0][3]);
      $days = ($time1 - $time2) / (60 * 60 * 24);

      if($days > $this->saveperiod) {
        $sum = 0;
        $newarray = array();
        foreach ($csvArray as $value) {
          if(($time1 - strtotime($value[3])) / (60 * 60 * 24) > $this->saveperiod) {
            $sum += $value[5];
          } else {
            $value[0] = $this->doublequotation($value[0]);
            $value[1] = $this->doublequotation($value[1]);
            $newarray[] = $value;
          }
        }

        $sourOutedData = array($this->doublequotation($url), $this->doublequotation($this->getHTMLtitle($url)), 'admin', $this->sorteddate, $this->time, $sum);
        array_unshift($newarray, $sourOutedData);

        // $btnname = mb_convert_encoding($btnname, "UTF-8");
        $filename = 'datas/'.$btnname.'.csv';
        $fp = fopen($filename, 'w');
  
        // 二次元配列を１行ずつCSV形式に直して書き込む
        foreach ($newarray as $v) {
          $line = implode(',' , $v);
          fwrite($fp, $line . "\n");
        }
        // ファイルを閉じる
        fclose($fp);
      }
    }

    // いいね数を増やす関数！
    public function newiineCount($postPath, $btnName, $iinecountlimit) {

      // IPアドレスが拒否されていれば、いいねを拒否する
      $IPs = file('datas/setting/deny.dat');
      $checkIP = false;
      foreach($IPs as $IP) {
        if($this->visitorIP === trim($IP)) {
          $checkIP = true;
        }
      }

      if($checkIP === true) {
        echo 'denyIP';
      } else {
        $rowtitle = $this->getHTMLtitle($postPath);
        $newtitle = $this->doublequotation($rowtitle);
        $newURL = $this->doublequotation($postPath);
        $filename = 'datas/'.$btnName.'.csv';
        
        list($num, $csvArray) = $this->openCSV($btnName, null, $postPath);
        if($this->checkTodaysCount($btnName, $iinecountlimit)) {
          echo 'upper';
        } elseif($num === false) {
            // まずは古いログを整理
            $this->sortOutData($btnName, $postPath);
            // 今日はまだいいねしていない場合は新しい行で受け付ける
            $data = array($newURL, $newtitle, $this->visitorIP, $this->today, $this->time, 1);
            $fp = fopen($filename, 'a');
            if(flock($fp, LOCK_EX)) {
            $line = implode(',' , $data);
            fwrite($fp, $line . "\n");
            flock($fp, LOCK_UN);
            }
            fclose($fp);
            $sum = $this->newiineSum($btnName);
            echo $sum;
        } elseif($num !== false) {
            // 今日はいいねしているけど１日上限数未満の場合は上書きして受け付ける
            $count = $csvArray[$num][5];
            $newCount = $count + 1;
            $newdata = array($newURL, $newtitle, $this->visitorIP, $this->today, $this->time, $newCount);
            
            $addArray = array($newdata);
            array_splice($csvArray, $num, 1, $addArray);
  
            $this->rewriteCSV($btnName, $csvArray, $num);
            $sum = $this->newiineSum($btnName);
            echo $sum;
        } else {
            // それ以外の場合は受け付けない
            echo 'else';
        }
      }

    }

    // いいねボタンの総いいね数を返す関数
    public function newiineSum($btnName) {
        list($num, $csvArray) = $this->openCSV($btnName);
        $sum = 0;
        $today = false;
        if($csvArray !== false) {
            foreach ($csvArray as $value) {
                $sum = $sum + $value[5];
                if($value[2] === $this->visitorIP) {
                  $today = true;
                }
            }
        }

        $ret_array = array($sum, $today);
        $datas = json_encode($ret_array);
        
        echo $datas;
    }

}

?>