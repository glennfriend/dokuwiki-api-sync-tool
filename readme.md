### dokuwiki-api-tool
- 該程式能利用 XMLRPC 連接 Doku wiki 
- 將指定目錄的內容 sync 到 wiki 中
- 只會讀取 目錄 & 目錄中的 *.txt 檔案
- 來源檔案內容 與 wiki內容 如果不同, 就會覆蓋

#### Environment Request
- [x] PHP 5.6 ~ PHP 7
- [x] composer (https://getcomposer.org/)

#### Installation
```sh
cp example.config.php config.php
vi config.php
```

#### Execute
```sh
php go.php
```
