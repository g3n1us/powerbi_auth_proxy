import PbiClient, { models, service, factories } from 'powerbi-client';
// const $ = require('jquery');
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
			    const found = this.data.reports[v.id] || { id: '', slug: '' };
			    found.slug = found.id.replace(/-/g, '');
				found.handle = v.name.toLowerCase().replace(/[^a-z]/g, '-');

			    return { ...found, ...v };
		    });
			this.$ = window.$ || window.jQuery;
			this.render();
			this.attachHandlers();
		})
		.catch($e => {
			console.log($e);
		});


	}

	embedCSS(){
		const l = document.createElement('link');
		l.href = '/auth_proxy_routes/asset/secure_embed.css';
		l.rel = 'stylesheet';
		document.head.appendChild(l);
	}

	loadReport(link){
		const { $ } = this;
		$(link).tab('show');
		const { reports } = this.data;
        const reportData = reports[$(link).data('report')];
        const $reportContainer = $(link.hash);
        if($reportContainer.find('iframe').length) return;
        $reportContainer.html('<span class="loading-indicator">loading...</span>');

		if($(link).data('reporttype') == 'esri'){
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
		const { $ } = this;
		const reportId = $(link).data('report');
        axios.get(`/auth_proxy_routes/esri_embed/${reportId}`).then(response => {
	        console.log(response);
	        //! X-man!!!
	        // below does not work!
	        // The map layers are what are protected, not the dashboard itself.
// 	        https://icap.maps.arcgis.com/apps/opsdashboard/index.html#/be8cf70442fc4ff491247d47708302df

			$reportContainer.html(`<iframe frameborder="0" src="https://icap.maps.arcgis.com/apps/opsdashboard/index.html?token=${response.access_token}#/${reportId}"></iframe>`)
        });

	}

	attachHandlers(){
		const { $ } = this;

		const $tabpanes = $('#secure_embed_root .tab-pane');

		const tabs = {initial_tab: false};
		$tabpanes.toArray().map(v => {
			const $a = $(`[href="#${v.id}"]`);
			if(!$a.length){
				throw new Error(`The tabpane, ${v.id}, has no corresponding a to go with it.`);
				return;
			}
			$a.tab();
			if(!tabs.initial_tab) tabs.initial_tab = $a[0];
			tabs['#'+v.id] = $a[0];
		});

		$(window).on('hashchange', (event) => {
			const { hash } = window.location;
			if(!(hash in tabs)) {
				alert('ddd');
				return;
			}
			const link = tabs[hash];
			this.loadReport(link);

		});

		const initial_tab = tabs[window.location.hash] || tabs.initial_tab;
		this.loadReport(initial_tab);
	}

	render(){
		const { $ } = this;
		console.log('this.data.selected_reports', this.data.selected_reports);
		const links = this.data.selected_reports.map((v, i) => `<li class="${i === 0 ? 'active' : ''}"><a data-XXtoggle="tab" href="#${v.handle}" data-report="${v.id}" data-reporttype="${v.type}">${v.name}</a></li>`);
		const tabs = `
			<ul class="nav nav-tabs" id="secure_embed_tabs">
				${links.join('')}
			</ul>
		`;
		const tab_panel_items = this.data.selected_reports.map((v, i) => `<div id="${v.handle}" class="tab-pane fade ${i === 0 ? 'in active' : ''}">${v.handle}</div>`);
		const tab_panels = `
			<div class="tab-content">
			${tab_panel_items.join('')}
			</div>
		`;
// 				<iframe src="https://icap.maps.arcgis.com/apps/opsdashboard/index.html#/be8cf70442fc4ff491247d47708302df" frameborder="0" allowFullScreen="true"></iframe>

		const page = `<div id="secure_embed_root">
			<div class="main-container" id="main-container">
				<div class="tabbable">
					${tabs} ${tab_panels}
				</div>
			</div>
		</div>`;

		$('script[src="/auth_proxy_routes/asset/secure_embed.js"]').before(page);

	}
}


export default new App;
