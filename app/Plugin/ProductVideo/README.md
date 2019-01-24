機能概要

動画は販売できるようにする。
ダウンロード販売とは違い閲覧できるは、本サイトのマイページでのみ可能
通常商品とは商品種別(6)で区別します。
従って、予め商品種別コード(6)を設定します。






[root@ik1-304-12240 ec_maeda]# php app/console plugin:develop generate
------------------------------------------------------
---Plugin Generator
---[*]You can exit from Console Application, by typing quit instead of typing another word.
------------------------------------------------------

[+]Please enter Plugin Name
Input[1] : ビデオ商品
[+]Please enter Plugin Code (First letter is uppercase alphabet only. alphabet and numbers are allowed.)
Input[2] : ProductVideo
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
Input[6] : eccube.event.app.request
--- your entry list
 - eccube.event.app.controller
 - eccube.event.app.request

--- Press Enter to move to the next step ---
[+]Please enter site events(you can find documentation here http://www.ec-cube.net/plugin/)
Input[6] : eccube.event.app.respons
[!] No results have been found
--- there are more then one search result
 - eccube.event.app.response

[+]Please enter site events(you can find documentation here http://www.ec-cube.net/plugin/)
Input[6] : eccube.event.app.response
--- your entry list
 - eccube.event.app.controller
 - eccube.event.app.request
 - eccube.event.app.response

--- Press Enter to move to the next step ---
[+]Please enter site events(you can find documentation here http://www.ec-cube.net/plugin/)
Input[6] : eccube.event.app.exception
--- your entry list
 - eccube.event.app.controller
 - eccube.event.app.request
 - eccube.event.app.response
 - eccube.event.app.exception

--- Press Enter to move to the next step ---
[+]Please enter site events(you can find documentation here http://www.ec-cube.net/plugin/)
Input[6] :
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] : admin.customer.edit.index.initialize
--- your entry list
 - admin.customer.edit.index.initialize

--- Press Enter to move to the next step ---
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] : admin.customer.edit.index.complete
--- your entry list
 - admin.customer.edit.index.initialize
 - admin.customer.edit.index.complete

--- Press Enter to move to the next step ---
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] : admin.product.product.class.edit.complete
--- your entry list
 - admin.customer.edit.index.initialize
 - admin.customer.edit.index.complete
 - admin.product.product.class.edit.complete

--- Press Enter to move to the next step ---
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] : admin.product.product.class.edit.update
--- your entry list
 - admin.customer.edit.index.initialize
 - admin.customer.edit.index.complete
 - admin.product.product.class.edit.complete
 - admin.product.product.class.edit.update

--- Press Enter to move to the next step ---
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] : admin.product.product.class.edit.initialize
--- your entry list
 - admin.customer.edit.index.initialize
 - admin.customer.edit.index.complete
 - admin.product.product.class.edit.complete
 - admin.product.product.class.edit.update
 - admin.product.product.class.edit.initialize

--- Press Enter to move to the next step ---
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] : admin.order.edit.index.complete
--- your entry list
 - admin.customer.edit.index.initialize
 - admin.customer.edit.index.complete
 - admin.product.product.class.edit.complete
 - admin.product.product.class.edit.update
 - admin.product.product.class.edit.initialize
 - admin.order.edit.index.complete

--- Press Enter to move to the next step ---
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] : front.mypage.mypage.history.initialize
--- your entry list
 - admin.customer.edit.index.initialize
 - admin.customer.edit.index.complete
 - admin.product.product.class.edit.complete
 - admin.product.product.class.edit.update
 - admin.product.product.class.edit.initialize
 - admin.order.edit.index.complete
 - front.mypage.mypage.history.initialize

--- Press Enter to move to the next step ---
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] : front.cart.buystep.complete
--- your entry list
 - admin.customer.edit.index.initialize
 - admin.customer.edit.index.complete
 - admin.product.product.class.edit.complete
 - admin.product.product.class.edit.update
 - admin.product.product.class.edit.initialize
 - admin.order.edit.index.complete
 - front.mypage.mypage.history.initialize
 - front.cart.buystep.complete

--- Press Enter to move to the next step ---
[+]Please enter hookpoint, sample：front.cart.up.initialize
Input[7] :
[+]Would you like to use orm.path? [y/n]
Input[8] : y

---Entry confirmation
[+]Plugin Name:  ビデオ商品
[+]Plugin Code:  ProductVideo
[+]Version:  0.1.0
[+]Author:  ふまねっと
[+]Old version support:  No
[+]SiteEvents:
  eccube.event.app.controller
  eccube.event.app.request
  eccube.event.app.response
  eccube.event.app.exception
[+]hookpoint:
  admin.customer.edit.index.initialize
  admin.customer.edit.index.complete
  admin.product.product.class.edit.complete
  admin.product.product.class.edit.update
  admin.product.product.class.edit.initialize
  admin.order.edit.index.complete
  front.mypage.mypage.history.initialize
  front.cart.buystep.complete
[+]Use orm.path:  Yes

[confirm] Do you want to proceed? [y/n] : y

[+]File system

 this files and folders were created.
 - /var/www/ec_maeda/app/Plugin/ProductVideo
 - /var/www/ec_maeda/app/Plugin/ProductVideo/ServiceProvider
 - /var/www/ec_maeda/app/Plugin/ProductVideo/Controller
 - /var/www/ec_maeda/app/Plugin/ProductVideo/Form/Type
 - /var/www/ec_maeda/app/Plugin/ProductVideo/Resource/template/admin
 - /var/www/ec_maeda/app/Plugin/ProductVideo/config.yml
 - /var/www/ec_maeda/app/Plugin/ProductVideo/PluginManager.php
 - /var/www/ec_maeda/app/Plugin/ProductVideo/ServiceProvider/ProductVideoServiceProvider.php
 - /var/www/ec_maeda/app/Plugin/ProductVideo/Controller/ConfigController.php
 - /var/www/ec_maeda/app/Plugin/ProductVideo/Controller/ProductVideoController.php
 - /var/www/ec_maeda/app/Plugin/ProductVideo/Form/Type/ProductVideoConfigType.php
 - /var/www/ec_maeda/app/Plugin/ProductVideo/Resource/template/admin/config.twig
 - /var/www/ec_maeda/app/Plugin/ProductVideo/Resource/template/index.twig
 - /var/www/ec_maeda/app/Plugin/ProductVideo/event.yml
 - /var/www/ec_maeda/app/Plugin/ProductVideo/ProductVideoEvent.php
 - /var/www/ec_maeda/app/Plugin/ProductVideo/LICENSE

[+]Database
 Plugin information was added to table [DB.Plugin] (id=1)

 Plugin information was added to table [DB.PluginEventHandler] (inserts number=8)
Plugin was created successfully




