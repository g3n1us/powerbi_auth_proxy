## CodeIgniter Installation Instructions

**To Install:**

### Option 1 - Composer (preferred):

Require the package:
```
composer require blueraster/powerbi_auth_proxy
```



### Option 2 - Web Installer/Updater

You will upload an installation script manually to your web server. Then, the installation will take by accessing this script from the web.

Step 1:
Download the installation/update script from the link below: 
<a href="https://raw.githubusercontent.com/blueraster/powerbi_auth_proxy/master/installers/CodeIgniter/installer.php" target="_blank" download="installer.php">https://raw.githubusercontent.com/blueraster/powerbi_auth_proxy/master/installers/CodeIgniter/installer.php</a>


Step 2:
Create a folder named: `powerbi_auth_proxy_updater` in the web root on your webserver.

Step 3:
Upload the file you downloaded to the folder you just created.

Step 4:
Visit the installer on the web. It will be found at the url: `<website-url>/powerbi_auth_proxy_updater/installer.php`

Follow the instructions to complete installation of the library.

Next you will need to add the configuration values to the CodeIgniter config file: `/application/config/config.php`.

Add the following anywhere in the file.


```php
$config['powerbi_auth_proxy'] = [
    'auth_proxy_gate' => function($router){
        // dd($router);
        return true;
    },
];

```

This configuration that is provided can be used to restrict users based on permissions when accessing certain sections of the site. In the `auth_proxy_gate` function, you will return either true or false depending on the currently accessed state of the application. The `$router` variable passed into the function can be used to view the currently accessed report and other useful information.

You can now use the embed code provided [HERE](./#embedding-in-your-site) to insert your PowerBI and Esri applications into the site.

For CodeIgniter applications, this is usually placed inside a view template file located at `/application/views`.
