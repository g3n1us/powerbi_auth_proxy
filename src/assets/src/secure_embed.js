import PbiClient, { models, service, factories } from 'powerbi-client';
import axios from 'axios';
const { version } = require('./version.json');
console.log(version, 'version');
class App{

	constructor(){
		this.embedCSS();
		this.powerbi = new service.Service(factories.hpmFactory, factories.wpmpFactory, factories.routerFactory);
		axios.get('/auth_proxy_routes/embed_data').then(response => {
			this.data = response.data;

		    this.data.forEach((report, i) => {
		        Object.defineProperty(this.data, report.id, {
		            get: function(){
		                return report;
		            }
		        });
		    });

			console.log(this.data);
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
		l.href = `/auth_proxy_routes/asset/secure_embed.css?v=${version}`;
		l.rel = 'stylesheet';
		document.head.appendChild(l);
	}

	loadReport(link){
		const { $ } = this;
		$(link).tab('show');
		const itemdata = $(link).data('reportdata');
        const $reportContainer = $(link.hash);
        if($reportContainer.find('iframe').length) return;
        $reportContainer.html('<span class="loading-indicator">loading...</span>');

		if(itemdata.type == 'esri'){
			return this.loadEsriReport(link, $reportContainer);
		}
		
// 		const { reports } = this.data;
        const reportData = this.data[itemdata.id];

        axios.get(`/auth_proxy_routes/report_embed/${reportData.id}`).then(response => {
            const embedConfiguration = {
            	type: 'report',
            	id: reportData.id,
            	groupId: response.data.group_id,
            	embedUrl: 'https://app.powerbi.com/reportEmbed',
            	tokenType: models.TokenType.Embed,
            	accessToken: response.data.embed_token
            };
			console.log(embedConfiguration);
            const report = this.powerbi.embed($reportContainer.get(0), embedConfiguration);
        });
	}

	loadEsriReport(link, $reportContainer){
		const { $ } = this;
		const itemdata = $(link).data('reportdata');

		$reportContainer.html(`<iframe frameborder="0" src="${itemdata.url}"></iframe>`);
/*
        axios.get(`/auth_proxy_routes/esri_embed/${reportId}`).then(response => {
	        // The map layers are what are protected, not the dashboard itself.
	        // So the Dashboard itself must use the proxy functionality to function
	        // Use this installations endpoint for map embeds in the dashboard
            // We are passing the token so it can be retrieved from the referrer
            
			$reportContainer.html(`<iframe frameborder="0" src="${itemdata.url}"></iframe>`)
// 			$reportContainer.html(`<iframe frameborder="0" src="https://www.arcgis.com/apps/opsdashboard/index.html?token=${response.data.access_token}#/${reportId}"></iframe>`)
        });
*/

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
				return;
			}
			const link = tabs[hash];
			this.loadReport(link);

		});

		const initial_tab = tabs[window.location.hash] || tabs.initial_tab;
		this.loadReport(initial_tab);
	}

	render(){
		console.log('this.data', this.data);
		const { $ } = this;
// 		const links = this.data.selected_reports.map((v, i) => `<li class="${i === 0 ? 'active' : ''}"><a data-reportdata='${JSON.stringify(v)}' href="#${v.handle}" data-report="${v.id}" data-reporttype="${v.type}">${v.name || v.id}</a></li>`);
		const links = this.data.map((v, i) => `<li class="${i === 0 ? 'active' : ''}"><a data-reportdata='${JSON.stringify(v)}' href="#${v.handle}">${v.name || v.id}</a></li>`);
		const tabs = `
			<ul class="nav nav-tabs" id="secure_embed_tabs">
				${links.join('')}
			</ul>
		`;
		const tab_panel_items = this.data.map((v, i) => `<div id="${v.handle}" class="tab-pane fade ${i === 0 ? 'in active' : ''}">${v.handle}</div>`);
		const tab_panels = `
			<div class="tab-content">
			${tab_panel_items.join('')}
			</div>
		`;

		const page = `<div id="secure_embed_root">
			<div class="main-container" id="main-container">
				<div class="tabbable">
					${tabs} ${tab_panels}
				</div>
			</div>
		</div>`;

		$(`script[src="/auth_proxy_routes/asset/secure_embed.js"]`).before(page);

	}
}


export default new App;
