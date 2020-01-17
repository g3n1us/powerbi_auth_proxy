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
				found.handle = v.name.toLowerCase().replace(/ /g, '-');

			    return { ...found, ...v };
		    });
console.log(this.data.selected_reports);
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
        
		if($(link).data('esrireport')){
			return this.loadEsriReport(link, $reportContainer);
		}
        
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
	
	loadEsriReport(link, $reportContainer){
		const reportId = $(link).data('esrireport');
        axios.get(`/auth_proxy_routes/esri_embed/${reportId}`).then(response => {
	        console.log(response);
			$reportContainer.html(`<iframe frameborder="0" src="https://arcgis.com/apps/View/index.html?appid=${reportId}&token=${response.access_token}"></iframe>`)
        });
		
		// <iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://arcgis.com/apps/View/index.html?appid=6b6a075eca8d4899958fb273710a6806"></iframe>
		
		

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
		const links = this.data.selected_reports.map((v, i) => `<li class="${i === 0 ? 'active' : ''}"><a data-XXtoggle="tab" href="#${v.slug}" data-report="${v.id}">${v.name}</a></li>`);
		const tabs = `
			<ul class="nav nav-tabs" id="secure_embed_tabs">
				${links.join('')}
	            <li>
					<a data-toggle="tab" data-esrireport="be8cf70442fc4ff491247d47708302df" href="#cluster_detection">
						Cluster Detection & Response (Maps)
					</a>
				</li>
			</ul>
		`;
		const tab_panel_items = this.data.selected_reports.map((v, i) => `<div id="${v.slug}" class="tab-pane fade ${i === 0 ? 'in active' : ''}"></div>`);
		const tab_panels = `
			<div class="tab-content">
			${tab_panel_items.join('')}
			<div id="cluster_detection" class="tab-pane fade">

			</div>
			</div>
		`;
// 				<iframe src="https://icap.maps.arcgis.com/apps/opsdashboard/index.html#/be8cf70442fc4ff491247d47708302df" frameborder="0" allowFullScreen="true"></iframe>

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
