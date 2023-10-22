![](src/Resources/config/poc-shopware-6.jpg)

# Shopware 6.5 Warranty Manager Plugin 

This a initial plugin that  allows you to manage customer warranties in Shopware 6. 
You can also import and export warranty data using CSV files, which makes it easy to transfer and backup your data.
This plugin is useful for merchants who want to provide better customer service and keep track of their warranty obligations.
## Features
<ul>
<li> Bulk upload customer warranties using a CSV file with the following format:</li>
<pre>
<code>
﻿product_number;customer_number;warranty_text;warranty_duration
;;;
SWDEMO10007;SWDEMO10000;"test text for product SWDEMO10007";11
SWDEMO10006;SWDEMO10000;"test text for product SWDEMO10007";10
SWDEMO10006;SWDEMO10000;"test text for product SWDEMO10007";8
SWDEMO10001;SWDEMO10000;"test text for product SWDEMO10007";7
SWDEMO10006;SWDEMO10000;"test text for product SWDEMO100013";5
</code>
</pre>

<li>  Can import your the CSV from administration panel  or command line</li>
<p align="center"><img src="./src/Resources/config/pic-1.jpg" alt="API Platform"></p>
<pre>
php bin/console warrantyManager:importCsv
</pre>
</ul>

## How-To Guide

This is a simple plugin that allows you to manage warranties for your products. The plugin uses a [one-to-one](https://developer.shopware.com/docs/guides/plugins/plugins/framework/data-handling/add-data-associations.html#one-to-one-associations) relationship in the [ProductWarrantyDefinition](https://github.com/pedramham/shopware-warranty-manager/blob/master/src/Content/ProductWarranty/ProductWarrantyDefinition.php) class, but you can adjust it to use a many-to-many relationship if needed.

The [sas_warranty_manager_product](https://github.com/pedramham/shopware-warranty-manager/blob/master/src/Migration/Migration1697208982ProductWarranty.php) table has two fields: `warrantyDuration` and `warrantyText` for manage warranty. You can add more fields such as `image`, `icon`, `price`, etc. if needed.

According to the Shopware core codes, I used the [ImportExportFactoryDecorator](https://github.com/pedramham/shopware-warranty-manager/blob/master/src/Content/Decorator/ImportExportFactoryDecorator.php) decorator. The [WarrantyManager](https://github.com/pedramham/shopware-warranty-manager/blob/master/src/Content/Decorator/WarrantyManager.php) class gets a CSV file from the administration panel and uses the [makeWarrantiesArray](https://github.com/pedramham/shopware-warranty-manager/blob/master/src/Content/Decorator/WarrantyManager.php#L219) method to create an array.
The [ImportWarrantyService](https://github.com/pedramham/shopware-warranty-manager/blob/master/src/Service/ImportWarrantyService.php) class is then used to import the data into the database. If you add more fields, you can adjust the [ImportWarrantyService](https://github.com/pedramham/shopware-warranty-manager/blob/master/src/Service/ImportWarrantyService.php#L30) class accordingly.

##Tools
####Fixing Issues with ECS
To start using [ECS](https://github.com/easy-coding-standard/easy-coding-standard), just run it, If you're sure, go for a fix command:
<pre>
vendor/bin/ecs check src --fix
</pre>
####Running Unit Tests with PHPUnit
<pre>
./vendor/bin/phpunit --configuration="custom/plugins/WarrantyManager"
</pre>
####Analyzing PHP Code with PHPStan
<pre>
vendor/bin/phpstan analyse src
</pre>