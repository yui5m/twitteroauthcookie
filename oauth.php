<?php
	$api_key = "3l4fKpPWSX3qrrb8faCma76EM";
	$api_secret = "MC0jlwZmYaJXcGQoB6JFaBgtU5rymXFHveWoucPB7IB64kyKEN";
	$callback_url = 'http://'.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$request_method = "POST";
	/*** [手順4] ユーザーが戻ってくる ***/
	if(isset($_GET['oauth_token']) || isset($_GET["oauth_verifier"])) {
		/*** [手順5] [手順5] アクセストークンを取得する ***/
		// リクエストURL
		$request_url = "https://api.twitter.com/oauth/access_token";
		// キーを作成する
		$signature_key = $api_secret."&".rawurlencode($_SESSION["oauth_token_secret"]);
		// パラメータ([oauth_signature]を除く)を連想配列で指定
		$params = array(
			"oauth_consumer_key" => $api_key,
			"oauth_token" => rawurlencode($_GET["oauth_token"]),
			"oauth_signature_method" => "HMAC-SHA1",
			"oauth_timestamp" => time(),
			"oauth_verifier" => rawurlencode($_GET["oauth_verifier"]),
			"oauth_nonce" => rawurlencode(microtime()),
			"oauth_version" => "1.0"
		);
		// 連想配列をアルファベット順に並び替え
		ksort($params);
		// パラメータの連想配列を[キー=値&キー=値...]の文字列に変換
		$request_params = http_build_query($params, "", "&");
		// 変換した文字列をURLエンコードする
		$request_params = rawurlencode($request_params);
		// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
		$signature_data = $request_method."&".rawurlencode($request_url)."&".$request_params;
		// キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
		$hash = hash_hmac("sha1", $signature_data, $signature_key, TRUE);
		// base64エンコードして、署名[$signature]が完成する
		$signature = base64_encode($hash);
		// パラメータの連想配列、[$params]に、作成した署名を加える
		$params["oauth_signature"] = $signature;
		// パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
		$header_params = http_build_query($params, "", ",");
		// リクエスト用のコンテキストを作成する
		$context = array(
			"http" => array(
				"method" => $request_method,//リクエストメソッド
				"header" => array(//カスタムヘッダー
					"Authorization: OAuth ".$header_params
				)
			)
		);
	  // cURLを使ってリクエスト
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $request_url);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $context["http"]["method"]);//メソッド
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//証明書の検証を行わない
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_execの結果を文字列で返す
		curl_setopt($curl, CURLOPT_HTTPHEADER, $context["http"]["header"]);//ヘッダー
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);//タイムアウトの秒数
		$res1 = curl_exec($curl);
		$res2 = curl_getinfo($curl);
		curl_close($curl);
	  //取得したデータ
		$response = substr($res1, $res2["header_size"]);// 取得したデータ(JSONなど)
		// $responseの内容(文字列)を$query(配列)に直す
		// aaa=AAA&bbb=BBB → [ "aaa"=>"AAA", "bbb"=>"BBB" ]
		$query = [];
		parse_str($response, $query);
		// アクセストークン
		// $query["oauth_token"]
		// アクセストークン・シークレット
		// $query["oauth_token_secret"]
		// ユーザーID
		// $query["user_id"]
		// スクリーンネーム
		// $query["screen_name"]
		foreach($query as $key => $value) {
			setcookie($key, $value, time()+60*60*24*365);
		}
		header('Location: http://'.$_SERVER["HTTP_HOST"]);
	} else {
		/*** [手順1] リクエストトークンの取得 ***/
		// エンドポイントURL
		$request_url = "https://api.twitter.com/oauth/request_token";
		// パラメータ([oauth_signature]を除く)を連想配列で指定
		$params = array(
			"oauth_callback" => $callback_url,
			"oauth_consumer_key" => $api_key,
			"oauth_signature_method" => "HMAC-SHA1",
			"oauth_timestamp" => time(),
			"oauth_nonce" => rawurlencode(microtime()),
			"oauth_version" => "1.0"
		);
		ksort($params);
		// パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
		$request_params = http_build_query($params, "", "&");
		// 変換した文字列をURLエンコードする
		$request_params = rawurlencode($request_params);
		// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
		$signature_data = $request_method."&".rawurlencode($request_url)."&".$request_params;
		// キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
		$hash = hash_hmac("sha1", $signature_data, $api_secret."&", TRUE);
		// base64エンコードして、署名[$signature]が完成する
		$signature = base64_encode($hash);
		// パラメータの連想配列、[$params]に、作成した署名を加える
		$params["oauth_signature"] = $signature;
		// パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
		$header_params = http_build_query($params, "", ",");
		// リクエスト用のコンテキストを作成する
		$context = array(
			"http" => array(
				"method" => $request_method,
				"header" => array(// カスタムヘッダー
					"Authorization: OAuth ".$header_params
				)
			)
		);
	  // cURLを使ってリクエスト
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $request_url);// リクエストURL
		curl_setopt($curl, CURLOPT_HEADER, true);// ヘッダーを取得する
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $context["http"]["method"]);// メソッド
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);// 証明書の検証を行わない
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);// curl_execの結果を文字列で返す
		curl_setopt($curl, CURLOPT_HTTPHEADER, $context["http"]["header"]);// リクエストヘッダーの内容
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);// タイムアウトの秒数
		$res1 = curl_exec($curl);
		$res2 = curl_getinfo($curl);
		curl_close($curl);
		// 取得したデータ
		$response = substr($res1, $res2["header_size"]);//取得したデータ(JSONなど)
		// $responseの内容(文字列)を$query(配列)に直す
		// aaa=AAA&bbb=BBB → [ "aaa"=>"AAA", "bbb"=>"BBB" ]
		parse_str($response, $query);
		// セッション[$_SESSION["oauth_token_secret"]]に[oauth_token_secret]を保存する
		session_start();
		session_regenerate_id(true);
		$_SESSION["oauth_token_secret"] = $query["oauth_token_secret"];
		header("Location: https://api.twitter.com/oauth/authorize?oauth_token=".$query["oauth_token"]);
	}
