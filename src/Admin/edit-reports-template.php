			<div class="container">
				<div class="row">
					<div class="col-md-12 py-5" id="main_content">
						<h1>Auth Proxy Admin
							<br><small class="text-muted">add/remove/edit and rearrange reports</small>
						</h1>
						
						<script type="text/template">{!! $data !!}</script>
						
						<div class="text-right py-3">
							<a href="#versions_collapse" class="text-muted" data-toggle="collapse">versions</a>
						</div>
						<div class="collapse" id="versions_collapse">
							<form class="text-right text-muted py-3" id="version_form">
								<div class="input-group">
									<div class="input-group-prepend">
										<label class="input-group-text" for="version_select">Versions</label>
									</div>
									
									<select class="custom-select" name="_version" id="version_select">
										<option value="">LATEST</option>
									@foreach($data['versions'] as $version)
										<option value="{{ $version['version'] }}" @if(@$_GET['_version'] === $version['version']) selected @endif>{{ $version['timestamp'] }}</option>
									@endforeach								
									</select>
									<div class="input-group-append">
										<button class="btn btn-outline-secondary" type="submit">ok</button>
									</div>
								</div>
																
							</form>
						</div>
						<form method="post">
							@csrf
							<div class="sticky-top py-3 d-flex bg-white border-bottom">
								<button type="button" class="btn btn-lg btn-primary"  data-action="add">Add Report</button>
								<button type="submit" class="btn btn-lg btn-success ml-auto">Save</button>
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
								<button type="submit" class="btn btn-lg btn-success">Save</button>
							</div>
							
						</form>
					</div>
				</div>
			</div>
