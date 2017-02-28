<?php

namespace Egapool\SimpleCurl;

class Curl
{

	/**
	 * curlのハンドル
	 */
	private $ch = null;

	/**
	 * Cookie保存用のリソース
	 * 同一プロセス中は同一リソースを使いまわす
	 */
	private $cookie = null;

	/**
	 * リクエストURL
	 */
	private $url = null;

	/**
	 * POSTデータ
	 */
	private $postData = null;

	/**
	 * Basic認証ユーザーとパスワード
	 */
	private $basicIdPass = null;

	/**
	 * HTTPリクエストヘッダー
	 */
	private $httpHeader = [];

	/**
	 * レスポンスの詳細情報
	 */
	private $resInfo = null;

	/**
	 * レスポンスボディ
	 */
	private $resBody = null;

	/**
	 * レスポンスエラーテキスト
	 * @see https://curl.haxx.se/libcurl/c/libcurl-errors.html
	 */
	private $error = null;

	/**
	 * error number
	 * @see https://curl.haxx.se/libcurl/c/libcurl-errors.html
	 */
	private $errorNo = null;

	/**
	 * インスタンス生成時にcURLハンドル、
	 * インスタンス生成時にCookie用リソース生成
	 */
	public function __construct()
	{
		if ( is_null($this->ch) ) {
			$this->ch = curl_init();
		}

		if ( is_null($this->cookie) ) {
			$this->cookie = tmpfile();
		}
	}

	/**
	 * リクエストURLをセット
	 * @return this Service_Request_Curl
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * Basic認証情報をセット
	 * @return this Service_Request_Curl
	 */
	public function setBasic(String $username, String $password)
	{
		$this->basicIdPass = $username . ":" . $password;
		return $this;
	}

	/**
	 * POSTデータをセット
	 * @return this Service_Request_Curl
	 */
	public function setStringPostData($postData)
	{
		$this->postData = http_build_query($postData);
		return $this;
	}

	/**
	 * POSTデータをセット
	 * @return this Service_Request_Curl
	 */
	public function setFilePostData(array $postData)
	{
		$this->postData = $postData;
		return $this;
	}

	public function setHttpHeader(array $httpHeader)
	{
		$this->httpHeader += $httpHeader;
		return $this;
	}

	public function getInfo()
	{
		return $this->resInfo;
	}

	public function getBody()
	{
		return $this->resBody;
	}

	public function getError()
	{
		return $this->error;
	}

	public function getErrorNo()
	{
		return $this->errorNo;
	}

	public function getCurlResource()
	{
		return $this->ch;
	}

	public function fire()
	{
		// 転送前に前回のレスポンス内容をリセット
		$this->resetResponses();

		$cookie = stream_get_meta_data($this->cookie)['uri'];

		curl_setopt_array($this->ch, [
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1, // HTTP/1.1 を使用する
			CURLOPT_URL            => $this->url,            // 取得するURL
			CURLOPT_RETURNTRANSFER => true,                  // TRUE を設定すると、curl_exec()の返り値を文字列で返します。
			CURLOPT_HEADER         => true,                  // レスポンスヘッダー情報を取得するか
			CURLOPT_FOLLOWLOCATION => true,                  // リダイレクト先まで追跡するか(通常true推奨か？)
			CURLOPT_MAXREDIRS      => 2,                     // 何回リダイレクトを許すか
			CURLOPT_COOKIEJAR      => $cookie,               // ハンドルを閉じる際、すべての内部クッキーを保存するファイルの名前
			CURLOPT_COOKIEFILE     => $cookie,               // クッキーのデータを保持するファイルの名前
			CURLOPT_TIMEOUT        => 10,                    //
			CURLOPT_CONNECTTIMEOUT => 10,                    //
			// CURLINFO_HEADER_OUT    => true,                  // 送信ヘッダの取得を取得
			// CURLOPT_VERBOSE        => true,                  // 詳細な情報を出力します。情報は STDERR か、または CURLOPT_STDERR で指定したファイルに出力されます
			// CURLOPT_STDERR         => fopen(APPPATH."tmp/curl_logs/".date('Ymd'),"a") // ログ吐き出されないなぁ〜
		]);

		if ( !is_null($this->postData) ) {
			curl_setopt($this->ch, CURLOPT_POSTFIELDS,$this->postData);
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST,'POST');
		}

		if ( !is_null($this->basicIdPass) ) {
			curl_setopt($this->ch, CURLOPT_USERPWD,$this->basicIdPass);
		}

		if ( $this->httpHeader !== [] ) {
			curl_setopt($this->ch, CURLOPT_HTTPHEADER,$this->httpHeader);
		}

		$this->resBody 	= curl_exec($this->ch);
		$this->resInfo 	= curl_getinfo($this->ch);
		$this->error 		= curl_error($this->ch);

		// 転送後は転送に使用したオプション等をリセット
		$this->resetSettings();

		return $this;
	}

	public function destroy()
	{
		if ( !is_null($this->ch)) {
			curl_close($this->ch);
		}

		if ( !is_null($this->cookie) ) {
			fclose($this->cookie);
		}
	}

	/**
	 * すべての設定をリセットする
	 * CURLハンドルとcookieリソースはリセットしない
	 */
	private function resetSettings()
	{
		$this->url 				 = null;
		$this->postData 	 = null;
		$this->basicIdPass = null;
		$this->httpHeader  = [];

		// すべてのオプションをリセットする
		curl_reset($this->ch);
	}

	/**
	 * すべてのレスポンスをリセットする
	 */
	private function resetResponses()
	{
		$this->resInfo 	= null;
		$this->resBody	= null;
		$this->error 		= null;
		$this->errorNo 	= null;
	}
}
