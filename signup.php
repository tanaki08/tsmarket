<?php

//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//エラーメッセージを定数に設定
define('MSG01','入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03','パスワード（再入力）が合っていません');
define('MSG04','半角英数字のみご利用いただけます');
define('MSG05','6文字以上で入力してください');
define('MSG06','256文字以内で入力してください');

//配列$err_msgを用意
$err_msg = array();
//dbアクセス結果用
$dbRst = false;

//バリデーション関数（未入力チェック）
function validRequired($str, $err){
  if(empty($str)){
    $err = MSG01;
  }
}
//バリデーション関数（未入力チェック）
function validEmail($str, $err){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    $err = MSG02;
  }
}
//バリデーション関数（同値チェック）
function validMatch($str1, $str2, $err){
  if($str1 !== $str2){
    $err = MSG03;
  }
}
//バリデーション関数（最小文字数チェック）
function validMinLen($str, $min = 6, $err){
  if(mb_strlen($str) < $min){
    $err = MSG05;
  }
}
//バリデーション関数（最大文字数チェック）
function validMaxLen($str, $max = 256, $err){
  if(mb_strlen($str) < $max){
    $err = MSG06;
  }
}
//バリデーション関数（半角チェック）
function validHalf($str, $err){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    $err = MSG04;

  }
}

//post送信されていた場合
if(!empty($_POST)){
  
  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_retype'];

  //未入力チェック
  validRequired($email, $err_msg['email']);
  validRequired($pass, $err_msg['pass']);
  validRequired($pass_re, $err_msg['pass_retype']);
  
  //バリデーションエラーがない場合
  if(empty($err_msg)){

    //emailの形式チェック
    validEmail($email, $err_msg['email']);

    //パスワードとパスワード再入力が合っているかチェック
    validMatch($pass, $pass_re, $err_msg['pass']);
    
    //バリデーションエラーがない場合
    if(empty($err_msg)){

      //パスワードの半角英数字チェック
      validHalf($pass, $err_msg['pass']);
      //パスワードの最小文字数チェック
      validMinLen($pass, $err_msg['pass']);
      //パスワードの最大文字数チェック
      validMaxLen($pass, $err_msg['pass']);
      
      //バリデーションエラーがない場合
      if(empty($err_msg)){

        //DBへの接続準備
        $dsn = 'mysql:dbname=php_sample01;host=localhost;charset=utf8';
        $user = 'root';
        $password = 'root';
        $options = array(
          // SQL実行失敗時に例外をスロー
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          // デフォルトフェッチモードを連想配列形式に設定
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
          // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
          PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        );

        // PDOオブジェクト生成（DBへ接続）
        $dbh = new PDO($dsn, $user, $password, $options);

        //SQL文（クエリー作成）
        $stmt = $dbh->prepare('INSERT INTO users (email,pass,login_time) VALUES (:email,:pass,:login_time)');

        //プレースホルダに値をセットし、SQL文を実行
        $dbRst = $stmt->execute(array(':email' => $email, ':pass' => $pass, ':login_time' => date('Y-m-d H:i:s')));
        
        //SQL実行結果が成功の場合
        if($dbRst){
          header("Location:mypage.html"); //マイページへ
        }
      }

    }
  }
}

?>
<!DOCTYPE html>
<html lang="ja">

  <head>
    <meta charset="utf-8">
    <title>ユーザー登録 | WEBUKATU MARKET</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  </head>

  <body class="page-signup page-1colum">

    <!-- メニュー -->
    <header>
      <div class="site-width">
        <h1><a href="index.html">WEBUKATU MARKET</a></h1>
        <nav id="top-nav">
          <ul>
            <li><a href="signup.html" class="btn btn-primary">ユーザー登録</a></li>
            <li><a href="login.html">ログイン</a></li>
          </ul>
        </nav>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main" >

        <div class="form-container">

          <form action="mypage.html" class="form">
            <h2 class="title">ユーザー登録</h2>
            <div class="area-msg">
             <?php 
              if(!empty($err_msg)){
                foreach($err_msg as $msg){
                  echo $msg.'<br>';
                }
              }
              ?>
            </div>
            <label>
              Email
              <input type="text" name="email">
            </label>
            <label>
              パスワード <span style="font-size:12px">※英数字６文字以上</span>
              <input type="text" name="pass">
            </label>
            <label>
              パスワード（再入力）
              <input type="text" name="pass">
            </label>
            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="登録する">
            </div>
          </form>
        </div>

      </section>

    </div>

    <!-- footer -->
    <footer id="footer">
      Copyright <a href="http://webukatu.com/">ウェブカツ!!WEBサービス部</a>. All Rights Reserved.
    </footer>

    <script src="js/vendor/jquery-2.2.2.min.js"></script>
    <script>
      $(function(){
        var $ftr = $('#footer');
        if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
          $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
        }
      });
    </script>
  </body>
</html>