			<div class="container">
				<div class="row">
					<div class="col-md-12 py-5" id="main_content_users">
						<h2>Auth Proxy Admin
							<br><small class="text-muted">Edit Administrative Users</small>
						</h2>
						
						<div class="text-right py-3">
							<a href="#user_versions_collapse" class="text-muted" data-toggle="collapse">versions</a>
						</div>
						<div class="collapse" id="user_versions_collapse">
							<form class="text-right text-muted py-3" id="version_form_users">
								<div class="input-group">
									<div class="input-group-prepend">
										<label class="input-group-text" for="version_select_users">Versions</label>
									</div>
									
									<select class="custom-select" name="_version_users" id="version_select_users">
										<option value="">LATEST</option>
									@foreach($data['users_versions'] as $users_version)
										<option value="{{ $users_version['version'] }}" @if(@$_GET['_version_users'] === $users_version['version']) selected @endif>{{ $users_version['timestamp'] }}</option>
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
								<button type="button" class="btn btn-lg btn-primary"  data-action="add">Add User</button>
								<button type="submit" class="btn btn-lg btn-success ml-auto">Save</button>
							</div>
							
							<div class="outer">
								
							@foreach($data['users'] as $i => $user)
							
							<fieldset class="card mb-4" @if(!$user) disabled @endif>
								<div class="card-header">
									<div class="clearfix"><a href="#" data-action="remove" data-toggle="tooltip" title="Remove User" class="close">&times;</a></div>
								</div>
								<div class="card-body">
									<div class="input-group mb-3">
										<div class="input-group-prepend">
											<label class="input-group-text" for="{{$i}}user" id="{{$i}}user_label">email</label>
										</div>
										<input type="text" name="users[]" required class="form-control" id="{{$i}}user" aria-describedby="{{$i}}user_label" value="{{$user}}">
									</div>
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
