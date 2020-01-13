import PbiClient, { models, service, factories } from 'powerbi-client';
const $ = require('jquery');
import axios from 'axios';


class App{

	constructor(){
		this.embedCSS();
		this.powerbi = new service.Service(factories.hpmFactory, factories.wpmpFactory, factories.routerFactory);
		axios.get('/auth_proxy_routes/embed_data').then(response => {
			this.data = response.data;
		    this.data.reports.forEach((report, i) => {
		        Object.defineProperty(this.data.reports, report.id, {
		            get: function(){
		                return report;
		            }
		        });
		    });
		    this.data.selected_reports = this.data.selected_reports.map(v => {
			    const found = this.data.reports[v.id];
			    found.slug = found.id.replace(/-/g, '');

			    return { ...found, ...v };
		    });

			this.render();
			this.attachHandlers();
		});


	}

	embedCSS(){
		const l = document.createElement('link');
		l.href = '/auth_proxy_routes/asset/secure_embed.css';
		l.rel = 'stylesheet';
		document.head.appendChild(l);
	}

	loadReport(link){
		const { reports } = this.data;
        const reportData = reports[$(link).data('report')];
        const $reportContainer = $(link.hash);
        if($reportContainer.find('iframe').length) return;
        $reportContainer.html('<span class="loading-indicator">loading...</span>');
        axios.get('/auth_proxy_routes/report_embed/' + reportData.id).then(response => {
            const embedConfiguration = {
            	type: 'report',
            	id: reportData.id,
            	groupId: this.data.group_id,
            	embedUrl: 'https://app.powerbi.com/reportEmbed',
            	tokenType: models.TokenType.Embed,
            	accessToken: response.data.embed_token
            };

            const report = this.powerbi.embed($reportContainer.get(0), embedConfiguration);
        });
	}

	attachHandlers(){

	    $(document).on('click', '[data-toggle="tab"]', (event) => {
	        this.loadReport(event.target);
	    });
	    // load first tab
	    var $active = $('[data-toggle="tab"]').filter(function(){
	        return $(this).parents('li.active').length;
	    });

	    this.loadReport($active[0]);
	}

	render(){
		const links = this.data.selected_reports.map((v, i) => `<li class="${i === 0 ? 'active' : ''}"><a data-toggle="tab" href="#${v.slug}" data-report="${v.id}">${v.name}</a></li>`);
		const tabs = `
			<ul class="nav nav-tabs" id="secure_embed_tabs">
				${links.join('')}
	            <li>
					<a data-toggle="tab" href="#cluster_detection">
						Cluster Detection & Response (Maps)
					</a>
				</li>
			</ul>
		`;
		const tab_panel_items = this.data.selected_reports.map((v, i) => `<div id="${v.slug}" class="tab-pane fade ${i === 0 ? 'in active' : ''}"></div>`);
		const tab_panels = `
			<div class="tab-content">
			${tab_panel_items.join('')}
			<div id="cluster_detection" class="tab-pane fade"></div>
			</div>
		`;

		const page = `<div id="secure_embed_root">
			<div class="main-container" id="main-container">
				<div class="page-header">
					<h1>
						EHRIS Dashboards
					</h1>
				</div>
				<div class="tabbable">
					${tabs} ${tab_panels}

				</div>
			</div>
		</div>`;

		$('script[src="/auth_proxy_routes/asset/secure_embed.js"]').before(page);

	}
}


export default new App;
