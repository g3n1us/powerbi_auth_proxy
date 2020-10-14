<?php

require __DIR__.'/vendor/autoload.php';
BlueRaster\PowerBIAuthProxy\Routes::route();

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">

  </head>
  <body style="padding:25px" data-pbi-secure-embed-uses-bootstrap>
		<div>
			<h4>Other Links for Testing</h4>
			<p><a href="auth_proxy_routes/auth_proxy_admin.html">Admin Page</a></p>
			<p><a href="auth_proxy_routes/asset/secure_embed.js">JavaScript</a></p>
			<p><a href="auth_proxy_routes/asset/secure_embed.css">CSS</a></p>
		</div>
	  
		<script>
		    (function(){
		       var s = document.createElement('script');
		       s.src = "/auth_proxy_routes/asset/secure_embed.js";
		       var t = document.scripts[document.scripts.length - 1];
		       t.parentNode.insertBefore(s, t);
		    })();
		</script>

  </body>
</html>
