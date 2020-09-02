# prestashop-wfqa-pr-stats

:warning: this project **needs** PHP 7.

This is a small tool which gather the number of PR waiting for QA approval for a few branches of the 
[PrestaShop project](https://github.com/PrestaShop/PrestaShop).

### How to install
Use `composer install` to install all the dependencies.

Create a database using the `schema.sql` file at the root of the project.

Copy the `config.php.dist` file:
* change the values inside for your correct MySQL values
* add your [Github token](https://github.com/settings/tokens/new)
* change the scanned branches

and rename it `config.php`.

### How to use
Use the `generate.php` file to insert data into your database at regular intervals (for example, every 6 hours) 
with a cronjob or something similar.

Use the `index.php` file to browse the data.
