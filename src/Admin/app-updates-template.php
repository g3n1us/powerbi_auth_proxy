<?php define('AUTH_PROXY_INSTALLER_EMBEDDED', '1'); ?>
<style>
	[href="?secure_directory=true"]{
		display: none;
	}
</style>
<div class="container">
	<div class="row">
		<div class="col-md-12">
			{!! require auth_proxy()->framework()->installerPath() !!}			
		</div>
	</div>
</div>

