# Minecraft Server Panel
統合版マインクラフトサーバー（Minecraft Bedrock Server)用の管理パネルです。
ApacheやNginxなどのWebサーバー上で動作し、ブラウザからアクセスできます。

※注：個人用に作ったものなので、うまく動かない場合はご自分で改造していただければうれしいです。
※注：既存のサーバーを管理下に置きたい場合は、ここで作ったサーバーのディレクトリにワールドデータをコピーしてください。

## 主な機能

- サーバーの追加と削除（追加にはテンプレートとなるサーバーを利用）
- サーバーの開始および停止
- ポート番号やサーバー名、初期シード値の指定

## スクリーンショット

1. トップ画面
![capture1](https://user-images.githubusercontent.com/3386721/69837317-71fa2c00-1291-11ea-8f16-df83024f561e.jpg)

2. サーバー追加画面
![capture2](https://user-images.githubusercontent.com/3386721/69837319-74f51c80-1291-11ea-9936-be9fc9eecbd0.jpg)


## 使い方

1. **統合版マインクラフトサーバーのインストール**
     https://www.minecraft.net/ja-jp/download/server/bedrock/ からサーバーをダウンロードし、インストールします。
       例として `/home/smilkobuta/server-bedrock/` にインストールし、起動を確認できたものとします。

2. **テンプレート用にサーバーディレクトリをコピー**
     `/home/smilkobuta/server-bedrock/` はそのまま残しておいて（このディレクトリはアップグレード時にも使う）、`/home/smilkobuta/server-bedrock-template/` にコピーします。

3. **起動、停止、プロパティファイル更新などのスクリプトをコピー**

     > `/home/smilkobuta/` 以下へコピーするもの

     ```
     minecraftserverpanel/scripts/*.sh
     ```

     > `/home/smilkobuta/server-bedrock-template/` 以下へコピーするもの

     ```
     minecraftserverpanel/scripts/server-bedrock-template/*.sh
     ```

4. **Webパネルをインストール**
   `minecraftserverpanel/serverpanel` をWebサーバーで読み込む。↓ はNginxでの設定例。
   ここではルートディレクトリでアクセスする設定にしていますが（http://xxx.xxx/）、サブディレクトリでのアクセス設定も可能です（http://xxx.xxx/serverpanel/など）。

     ```
     server {
         listen 80 default_server;
         listen [::]:80 default_server;
         root /var/www/html/minecraftserverpanel/serverpanel;
         server_name _;
     
         location / {
             try_files $uri $uri/ =404;
             index index.php;
         }
      
         location ~ \.php$ {
             include snippets/fastcgi-php.conf;
             fastcgi_pass unix:/var/run/php/php7.0-fpm.sock;
         }
     
     }
     ```

5. **Webパネルの管理ユーザーを追加**

     .htusers.json ファイルを編集し、ログインできるユーザーのメールアドレス＆パスワードを登録してください。
     このファイルはWebアクセスできる場所に配置されていますので、場所を変えたりアクセスできない設定にするなど適宜管理をお願いします。

6. **sudoでscreenコマンドを実行できるよう、sudoユーザーに加える**
     sudo visudo を実行し、下記の行を追加する。

     ```
     www-data ALL=(smilkobuta) NOPASSWD: ALL
     ```

     www-dataはWebサーバー実行ユーザー。nginxとかapacheとか実行環境にあわせる。
     smilkobutaはscreenコマンド実行ユーザーです。

## TODO

- サーバーのアップグレード機能（現在はコマンドラインからのみ可能）

