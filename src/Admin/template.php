<!DOCTYPE html>
<html lang="en" data-pbi-secure-embed-uses-bootstrap="true">

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>Auth Proxy Admin</title>
		<link rel="stylesheet" href="/auth_proxy_routes/asset/secure_embed.css">
		<style>
			fieldset[disabled]{
				display: none;
			}

			#outer fieldset:last-child [data-direction="down"]{
				display: none;
			}

			#outer fieldset:first-child [data-direction="up"]{
				display: none;
			}
		</style>
	</head>
	<body>
		<div class="container pt-3">
			<nav class="container nav nav-tabs">
				<a class="nav-item nav-link active" href="#edit-reports">Edit Reports</a>
				<a class="nav-item nav-link" href="#edit-admin-users">Edit Users</a>
				<a class="nav-item nav-link" href="#available-updates">Available Updates</a>
			</nav>
		</div>
		<div id="messages" class="container"></div>
		<div class="tab-content">
			<div class="tab-pane fade in show active" id="edit-reports">
				@import('./edit-reports-template.php')
			</div>

			<div class="tab-pane fade" id="edit-admin-users">
				@import('./edit-users-template.php')
			</div>
			<div class="tab-pane fade" id="available-updates">
@if($standalone)
				@import('./app-updates-template.php')
@else
				<div class="p-4">
					<code>This application has been installed with Composer.</code>
					<code>Run: <pre>composer update</pre> from the command line</code>
				</div>
@endif
			</div>



		</div>


		<script src="/auth_proxy_routes/asset/secure_embed_admin.js"></script>

	</body>
</html>

