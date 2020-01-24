
# PowerBI Authentication Proxy

## WIP Ignore 

```
php composer.phar config repositories.powerbi_auth_proxy vcs https://github.com/blueraster/powerbi_auth_proxy

curl -o composer.phar https://getcomposer.org/composer-stable.phar
composer init -n -s dev --repository="{\"type\":\"vcs\",\"url\":\"https://github.com/blueraster/powerbi_auth_proxy\"}" --require=blueraster/powerbi_auth_proxy:dev-master
php composer.phar config scripts.post-autoload-dump "@php vendor/blueraster/powerbi_auth_proxy/install.php"
php composer.phar install



composer create-project --repository="{\"type\":\"vcs\",\"url\":\"https://github.com/blueraster/powerbi_auth_proxy\"}" blueraster/powerbi_auth_proxy . dev-master

```

For internal development purposes:

```
rm -rf vendor && \
rm composer.* && \
curl -o composer.phar https://getcomposer.org/composer-stable.phar && \
php composer.phar init -n -s dev && \
php composer.phar config repositories.auth_proxy "{\"type\":\"path\",\"url\":\"../../auth_proxy\"}" && \
php composer.phar config scripts.post-autoload-dump "BlueRaster\\PowerBIAuthProxy\\Installer::postAutoloadDump" && \
php composer.phar require blueraster/powerbi_auth_proxy:dev-master

```

.
