<?php define('AUTH_PROXY_INSTALLER_EMBEDDED', '1'); ?>
<style>
	[href="?secure_directory=true"]{
		display: none;
	}
</style>
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<h3 class="py-3">Update Application</h3>
			<form method="post">
			@csrf
			<input type="hidden" name="application_update" value="true">
			{!! require auth_proxy()->framework()->installerPath() !!}			
			</form>
		</div>
	</div>
</div>

