# prestashop-pr-stats

This is a small tool which gather the number of PR waiting for QA approval for a few branches of the 
[PrestaShop project](https://github.com/PrestaShop/PrestaShop).

### How to install
Use `composer install` to install all the dependencies.

Create a database using the `schema.sql` file at the root of the project.

Create a file named `token.txt` and put your [Github token](https://github.com/settings/tokens/new) in it.
Create a file named `branches.txt` and put a list of all the branches label you want to extract 
(eg: `1.7.7.x`, `develop`, etc), one per line.

Copy the `config.php.dist` file, change the values inside for your correct MySQL values
and rename it `config.php`.

### How to use
You can add or remove branches in the `branches.txt` file.

Use the `generate.php` file to insert data into your database

Use the `index.php` file to browse the data.
