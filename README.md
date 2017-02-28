# simple-curl
Simple Curl Wrapper.

It is possible to send cookies. So you can use it for requests to pages that require login.

## install

```
composer require egapool/simple-curl
```

## usage

```
use Egapool\SimpleCurl\Curl
require('vendor/autoload.php');

$curl = new Curl();
echo $curl->setUrl('https://google.co.jp')->fire()->getBody();

```

## todo

- [ ] curl_resetがPHP5.5以上なので5.4以下用に代替処理いれる
- [ ] レスポンスヘッダーのパース
- [ ] `Curl::fire`やめて`post`,`get`に
- [ ] curl_optのパラメータをもうちょい柔軟に
