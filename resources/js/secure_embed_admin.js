import 'bootstrap';

(function(){
	var _this = {};

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
		if(confirm('Are you sure you would like to remove this item?')){
			$(this).parents('fieldset').remove();
		}
	}



	_this.add = function(e, data){
		var tp = $(this).parents('.tab-pane');
		var id = tp[0].id;
		var tpl = $(_this.templates[id]).clone();
		var mockid = Date.now() + '';
		tpl.find('[name]').each(function(){
			this.name = this.name.replace(/reports\[\]/, 'reports['+mockid+']');
		});
		$(tpl).prependTo(tp.find('.outer')).find('input').first().focus();

	}

	function handle(e){
		e.preventDefault();
		var data = $(this).data();
		_this[data.action].call(this, e, data);
	}

	$(document).on('click', '[data-action]', handle);

	$(document).on('change', '[name*="_version"]', function(){
		console.log(this.form);
		this.form.submit();
		// var href = _this.opener.location.href;
		// var newhref = href.replace(_this.opener.location.search, '?_version=' + this.value);
		// _this.opener.location.assign(newhref);
		// console.log(newhref);
	});



	(function(){
		_this.templates = $('.tab-pane').toArray().reduce(function(current, node){
			var _tpl = $(node).find('.outer fieldset[disabled]');
			if(!_tpl.length) return current;
			var tpl = _tpl.clone();
			_tpl.remove();

			tpl.find('[name], label').each(function (){
				this.removeAttribute('id');
				this.removeAttribute('for');
				this.removeAttribute('aria-describedby');
			});
			var template = tpl[0];
			template.removeAttribute('disabled');
			current[node.id] = template;
			return current;

		}, {});

		// user messages
		if(localStorage._auth_proxy_message){
			$('<div class="alert alert-success">'+localStorage._auth_proxy_message+'<a class="close auth-proxy-reload-main-page" href="#" data-toggle="tooltip" title="Close message and reload main window to view changes" data-dismiss="alert">&times;</a></div>').prependTo('#messages');
			delete localStorage._auth_proxy_message;

		}
		if(localStorage._auth_proxy_error){
			$('<div class="alert alert-danger">'+localStorage._auth_proxy_error+'<a class="close " href="#" data-toggle="tooltip" title="Close message" data-dismiss="alert">&times;</a></div>').prependTo('#messages');
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
				$('[href="'+window.location.hash+'"]').tab('show');
			}
		});
	})();

})()
