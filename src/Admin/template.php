<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	  
		<title>Auth Proxy Admin</title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
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
				<a class="nav-item nav-link" href="#available-updates">Available Updates</a>
			</nav>			
		</div>
		<div class="tab-content">
			<div class="tab-pane fade in show active" id="edit-reports">
				@import('./edit-reports-template.php')
			</div>
			
			<div class="tab-pane fade" id="available-updates">
				@import('./app-updates-template.php')
			</div>
			
		</div>
					
		
		
		<script src="//code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha256-4+XzXVhsDmqanXGHaHvgh1gMQKX40OUvDEBTu8JcmNs=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>		
		<script>
			
			
			(function(){
				var _this = this;
				
				_this.opener = window.opener;
				
				_this.page_data = JSON.parse($('[type="text/template"]').html());


				_this.move = function(e, data){
					var report = $(this).parents('fieldset');
					if(data.direction === 'up'){
						report.prev().before(report);
					}
					else{
						report.next().after(report);
					}
				}
				
				
				_this.remove = function(e, data){
					if(confirm('Are you sure you would like to remove this report?')){
						$(this).parents('fieldset').remove();
					}
				}
				
				
				
				_this.add = function(e, data){
					var tpl = $(_this.template).clone();
					var mockid = Date.now() + '';
					tpl.find('[name]').each(function(){
						this.name = this.name.replace(/reports\[\]/, 'reports['+mockid+']');
					});
					$(tpl).prependTo($('#outer')).find('input').first().focus();

				}
				
				function handle(e){
					e.preventDefault();
					var data = $(this).data();
					_this[data.action].call(this, e, data);
				}
				
				$(document).on('click', '[data-action]', handle);
				
				
				(function(){
					var tpl = $('fieldset[disabled]').clone();
					$('fieldset[disabled]').remove();
					
					tpl.find('[name], label').each(function (){
						this.removeAttribute('id');
						this.removeAttribute('for');
						this.removeAttribute('aria-describedby');
					});
					_this.template = tpl[0];
					_this.template.removeAttribute('disabled');
					
					// user messages
					if(localStorage._auth_proxy_message){
						$('<div class="alert alert-success">'+localStorage._auth_proxy_message+'<a class="close auth-proxy-reload-main-page" href="#" data-toggle="tooltip" title="Close message and reload main window to view changes" data-dismiss="alert">&times;</a></div>').prependTo('#main_content');
						delete localStorage._auth_proxy_message;
						
					}
					if(localStorage._auth_proxy_error){
						$('<div class="alert alert-danger">'+localStorage._auth_proxy_error+'<a class="close " href="#" data-toggle="tooltip" title="Close message" data-dismiss="alert">&times;</a></div>').prependTo('#main_content');
						delete localStorage._auth_proxy_message;
						
					}
					$(document).on('click', '.auth-proxy-reload-main-page', function(){
						if(window.opener){
							window.opener.location.reload();
						}
					});
					
					$('[data-toggle="tooltip"]').tooltip();
					
					$(window).on('hashchange load', function(){
						if(window.location.hash){
							console.log($(window.location.hash));
							$('[href="'+window.location.hash+'"]').tab('show');
						}
						console.log(window.location.hash);
					});
				})();
				
			})()
			
		</script>
	</body>
</html>
	
