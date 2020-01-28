# PowerBI Authentication Proxy

## Installation

Please refer to the link below that relates to your framework.

- [CodeIgniter](CodeIgniter%20Installation%20Instructions)



# Embedding in your site

You will place the embed code inside a script tag on your site. The PowerBI reports will be placed just above the script tag in the rendered site.

Embed Code:

```js
<script>
    (function(){
       var s = document.createElement('script');
       s.src = "/auth_proxy_routes/asset/secure_embed.js";
       var t = document.scripts[document.scripts.length - 1];
       t.parentNode.insertBefore(s, t);
    })();
</script>

```