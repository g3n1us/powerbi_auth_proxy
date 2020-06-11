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
			<div class="container">
				<div class="row">
					<div class="col-md-12 py-5" id="main_content">
						<h1>Auth Proxy Admin
							<br><small class="text-muted">add/remove/edit and rearrange reports</small>
						</h1>
						<script type="text/template">{!! $data !!}</script>
						<form method="post">
							@csrf
							<div class="sticky-top py-3 d-flex bg-white border-bottom">
								<button type="button" class="btn btn-lg btn-success"  data-action="add">Add Report</button>
								<button type="submit" class="btn btn-lg btn-primary ml-auto">Submit</button>
							</div>
							
							<div id="outer">
							@foreach($data['reports'] as $report)
							
							<fieldset class="card mb-4" @if(!$report['id']) disabled @endif>
								<div class="card-header">
									<div class="clearfix"><a href="#" data-action="remove" data-toggle="tooltip" title="Remove Report" class="close">&times;</a></div>
								</div>
								<div class="card-body">
									<div class="input-group mb-3">
										<div class="input-group-prepend">
											<label class="input-group-text" for="{{$report['id']}}id" id="{{$report['id']}}id_label">Report ID</label>
										</div>
										<input type="text" name="reports[{{$report['id']}}][id][]" required class="form-control" id="{{$report['id']}}id" aria-describedby="{{$report['id']}}id_label" value="{{$report['id']}}">
									</div>
									
									<div class="input-group mb-3">
										<div class="input-group-prepend">
											<label class="input-group-text" for="{{$report['name']}}name" id="{{$report['name']}}name_label">Report Label</label>
										</div>
										<input type="text" name="reports[{{$report['id']}}][name][]" class="form-control" id="{{$report['name']}}name" aria-describedby="{{$report['name']}}name_label" value="{{$report['name']}}">
									</div>
									
									<div class="input-group mb-3">
										<div class="input-group-prepend">
											<label class="input-group-text" for="{{$report['id']}}type">Report Type</label>
										</div>
										<select id="{{$report['id']}}type" class="custom-select" required name="reports[{{$report['id']}}][type][]">
											<option value="">--</option>
											<option value="power_bi" @selected($report['type'] == 'power_bi')>Power BI</option>
											<option value="esri" @selected($report['type'] == 'esri')>Esri</option>
										</select>								
									</div>
								</div>
								<div class="card-footer d-flex">
									<a href="#" data-toggle="tooltip" title="Move Up" data-action="move" data-direction="up" class="btn btn-info">&uarr;</a>
									<a href="#" data-toggle="tooltip" title="Move Down" data-action="move" data-direction="down" class="btn btn-info ml-auto">&darr;</a>
								</div>
							</fieldset>
							@endforeach
							</div>
							<div class="sticky-top py-3 text-right">
								<button type="submit" class="btn btn-lg btn-primary">Submit</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		
		
		<script src="//code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha256-4+XzXVhsDmqanXGHaHvgh1gMQKX40OUvDEBTu8JcmNs=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>		
		<script>
			
			(function(){
				var _this = this;
				
				_this.opener = window.opener;
				console.log('_this.opener', _this.opener);
				
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
						console.log(this.name);
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
					if(localStorage._message){
						$('<div class="alert alert-success">'+localStorage._message+'<a class="close auth-proxy-reload-main-page" href="#" data-toggle="tooltip" title="Reload main window to view changes" data-dismiss="alert">&times;</a></div>').prependTo('#main_content');
						delete localStorage._message;
						
					}
					$(document).on('click', '.auth-proxy-reload-main-page', function(){
						if(window.opener){
							window.opener.location.reload();
						}
					});
					
					$('[data-toggle="tooltip"]').tooltip();
				})();
				
			})()
			
		</script>
	</body>
</html>
	
