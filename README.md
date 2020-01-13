

To install:

```
curl -o composer.phar https://getcomposer.org/composer-stable.phar
php composer.phar init -n
php composer.phar config minimum-stability dev
php composer.phar config scripts.post-autoload-dump "@php vendor/blueraster/powerbi_auth_proxy/install.php"
php composer.phar config repositories.powerbi_auth_proxy vcs https://github.com/blueraster/powerbi_auth_proxy
php composer.phar require blueraster/powerbi_auth_proxy:dev-master

```
