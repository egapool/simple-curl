# simple-curl
simple curl wrapper

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
