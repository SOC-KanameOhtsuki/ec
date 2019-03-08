機能概要
管理画面　会員管理の検索条件を保存する。

検索条件を設定時に保存名を入力して、保存ボタンを押下するとDBにそのときの検索条件が保存される。
保存された検索条件はリストボックスで選択して、復元することができる。
保存された検索条件はリストボックスで選択して、削除することができる。

非同期でサーバと通信を行い。画面遷移は行わない

実装
フロント側
１．フォームの値を取得して、サーバーに送信
１．サーバーに送信から値を取得してリストボックスに表示する。
１．リストボックスに選択すると、値をFormに復元する。
１．リストボックスに選択すると、サーバーに送信して削除する

サーバ



[root@ik1-304-12240 ec_maeda]# php app/console plugin:develop generate
------------------------------------------------------
---Plugin Generator
---[*]You can exit from Console Application, by typing quit instead of typing another word.
------------------------------------------------------

[+]Please enter Plugin Name
Input[1] : フォームDB
[+]Please enter Plugin Code (First letter is uppercase alphabet only. alphabet and numbers are allowed.)
Input[2] : FormDb
[+]Please enter version (correct format is x.y.z)
Input[3] : 0.1.0
[+]Please enter author name or company
Input[4] : ふまねっと
[+]Do you want to support old versions too? [y/n]
Input[5] : n
[+]Please enter site events(you can find documentation here http://www.ec-cube.net/plugin/)
Input[6] : eccube.event.app.controller
--- your entry list
 - eccube.event.app.controller

--- Press Enter to move to the next step ---
[+]Please enter site events(you can find documentation here http://www.ec-cube.net/plugin/)
Input[6] :
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] : admin.customer.edit.index.initialize
--- your entry list
 - admin.customer.edit.index.initialize

--- Press Enter to move to the next step ---
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] :
[+]Would you like to use orm.path? [y/n]
Input[8] : y

---Entry confirmation
[+]Plugin Name:  フォームDB
[+]Plugin Code:  FormDb
[+]Version:  0.1.0
[+]Author:  ふまねっと
[+]Old version support:  No
[+]SiteEvents:
  eccube.event.app.controller
[+]hookpoint:
  admin.customer.edit.index.initialize
[+]Use orm.path:  Yes

[confirm] Do you want to proceed? [y/n] : y

[+]File system

 this files and folders were created.
 - /var/www/ec_maeda/app/Plugin/FormDb
 - /var/www/ec_maeda/app/Plugin/FormDb/ServiceProvider
 - /var/www/ec_maeda/app/Plugin/FormDb/Controller
 - /var/www/ec_maeda/app/Plugin/FormDb/Form/Type
 - /var/www/ec_maeda/app/Plugin/FormDb/Resource/template/admin
 - /var/www/ec_maeda/app/Plugin/FormDb/config.yml
 - /var/www/ec_maeda/app/Plugin/FormDb/PluginManager.php
 - /var/www/ec_maeda/app/Plugin/FormDb/ServiceProvider/FormDbServiceProvider.php
 - /var/www/ec_maeda/app/Plugin/FormDb/Controller/ConfigController.php
 - /var/www/ec_maeda/app/Plugin/FormDb/Controller/FormDbController.php
 - /var/www/ec_maeda/app/Plugin/FormDb/Form/Type/FormDbConfigType.php
 - /var/www/ec_maeda/app/Plugin/FormDb/Resource/template/admin/config.twig
 - /var/www/ec_maeda/app/Plugin/FormDb/Resource/template/index.twig
 - /var/www/ec_maeda/app/Plugin/FormDb/event.yml
 - /var/www/ec_maeda/app/Plugin/FormDb/FormDbEvent.php
 - /var/www/ec_maeda/app/Plugin/FormDb/LICENSE

[+]Database
 Plugin information was added to table [DB.Plugin] (id=5)

 Plugin information was added to table [DB.PluginEventHandler] (inserts number=1)


エンティティとレポジトリの生成
[root@ik1-304-12240 ec_maeda]#  php app/console plugin:develop entity

[entity]How to generate entities from db schema or yml? [d => db, y => yml] : y
------------------------------------------------------
---Plugin Generator for Entity
---[*]You need to create yml file first.
---[*]You can exit from Console Application, by typing quit instead of typing another word.
------------------------------------------------------

[+]Please enter Plugin Code (First letter is uppercase alphabet only. alphabet and numbers are allowed.)
Input[1] : FormDb
[+]Plese enter yml file name
Input[2] : Plugin.FormDb.Entity.FormContent.dcm.yml
--- your entry list
 - Plugin.FormDb.Entity.FormContent.dcm.yml

--- Press Enter to move to the next step ---
[+]Plese enter yml file name
Input[2] :
[+]Do you want to support old versions too? [y/n]
Input[3] : n

---Entry confirmation
[+]Plugin Code:  FormDb
[+]Yml file name:
  Plugin.FormDb.Entity.FormContent.dcm.yml
[+]Old version support:  No

[confirm] Do you want to proceed? [y/n] : y

[+]File system

 this files and folders were created.
 - /var/www/ec_maeda/app/Plugin/FormDb/Entity
 - /var/www/ec_maeda/app/Plugin/FormDb/Repository
 - /var/www/ec_maeda/app/Plugin/FormDb/Resource/doctrine/migration
 - /var/www/ec_maeda/app/Plugin/FormDb/Entity/FormContent.php
 - /var/www/ec_maeda/app/Plugin/FormDb/Repository/FormContentRepository.php
 - /var/www/ec_maeda/app/Plugin/FormDb/Resource/doctrine/migration/Version201901


テーブルの作成
php app/console plugin:develop uninstall --code FormDb
php app/console plugin:develop install --code FormDb
php app/console plugin:develop enable --code FormDb

04014203.php
[root@ik1-304-12240 ec_maeda]# php app/console plugin:develop uninstall --code FormDb
success
[root@ik1-304-12240 ec_maeda]# php app/console plugin:develop install --code FormDb
success
[root@ik1-304-12240 ec_maeda]# php app/console plugin:develop enable --code FormDb
success

mysql> desc plg_FormDb_formcontent;
+------------------+------------------+------+-----+---------+----------------+
| Field            | Type             | Null | Key | Default | Extra          |
+------------------+------------------+------+-----+---------+----------------+
| product_video_id | int(10) unsigned | NO   | PRI | NULL    | auto_increment |
| urlpath          | longtext         | NO   |     | NULL    |                |
| form_name        | longtext         | NO   |     | NULL    |                |
| form_value       | longtext         | YES  |     | NULL    |                |
+------------------+------------------+------+-----+---------+----------------+
4 rows in set (0.00 sec)


