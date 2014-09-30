## errCode.php
### 关于API接口错误码有两个版本：
**一个是普通公众号平台的errCode.php；
一个是企业号平台的 qyerrCode.php
用法都是一样的。**

当调用API接口失败时，可以用此类来换取失败原因的中文说明。

使用方法：
```php
include "errCode.php";  //或 qyerrCode.php

$ret=ErrCode::getErrText(48001); //错误码可以通过公众号类库的公开变量errCode得到

if ($ret) 
	echo $ret;
else 
    echo "未找到对应的内容";

```