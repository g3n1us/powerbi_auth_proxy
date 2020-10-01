import PbiClient, { models, service, factories } from 'powerbi-client';
import axios from 'axios';
import { popupwindow, ltrim } from 'g3n1us_helpers';
import qs from 'qs';
const { version } = require('./version.json');

//require('popper.js');
import 'bootstrap';

class App{

	constructor(){
		this.embedCSS();
		this.powerbi = new service.Service(factories.hpmFactory, factories.wpmpFactory, factories.routerFactory);

		this.load().then(() => {
			this.render();
			this.attachHandlers();
		});
	}



	load(){

		return new Promise((resolve, reject) => {
			const { _version = null } = qs.parse(ltrim(location.search, '?'));
			Promise.all([axios.get(`/auth_proxy_routes/embed_data?_version=${_version}`), axios.get('/auth_proxy_routes/current_user')]).then(values => {

				const [embed_response, user_response] = values;
				this.current_user = user_response.data;
				this.data = embed_response.data;

			    this.data.forEach((report, i) => {
			        Object.defineProperty(this.data, report.id, {
			            get: function(){
			                return report;
			            }
			        });
			    });

				resolve();
			});

		});
	}



	embedCSS(){
		const l = document.createElement('link');
		l.href = `/auth_proxy_routes/asset/secure_embed.css?v=${version}`;
		l.rel = 'stylesheet';
		document.head.appendChild(l);
	}

	loadReport(link){
		$(link).tab('show');
		const itemdata = $(link).data('reportdata');
        const $reportContainer = $(link.hash);
        if($reportContainer.find('iframe').length) return;
        $reportContainer.html('<span class="loading-indicator">loading...</span>');

		if(itemdata.type == 'esri'){
			return this.loadEsriReport(link, $reportContainer);
		}

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

            const report = this.powerbi.embed($reportContainer.get(0), embedConfiguration);
        });
	}

	loadEsriReport(link, $reportContainer){
		const itemdata = $(link).data('reportdata');

		$reportContainer.html(`<iframe frameborder="0" src="${itemdata.url}"></iframe>`);
	}

	attachHandlers(){

		const $tabpanes = $('[data-pbi-secure-embed-root="true"] .tab-pane');

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

		$(document).on('click', '.auth_proxy_admin_link', function(e){
			e.preventDefault();
			const w = popupwindow(this.href, "Auth Proxy Admin", 800, 1000);
			w.persist = true;

		});
	}

	render(){
		const links = this.data.map((v, i) => `<a class="${i === 0 ? 'active' : ''} nav-link" data-reportdata='${JSON.stringify(v)}' href="#${v.handle}">${v.name || v.id}</a>`);
		if(this.current_user.is_auth_proxy_admin === true){
			const { admin_route } = this.current_user;
			links.push(`<a class="nav-link text-danger auth_proxy_admin_link ml-auto" href="${admin_route}">Admin Page</a>`);
		}
		const tabs = `
			<nav class="nav nav-tabs" id="secure_embed_tabs">
				${links.join('')}
			</nav>
		`;
		const tab_panel_items = this.data.map((v, i) => `<div id="${v.handle}" class="tab-pane fade ${i === 0 ? 'in show active' : ''}">${v.handle}</div>`);
		const tab_panels = `
			<div class="tab-content">
			${tab_panel_items.join('')}
			</div>
		`;

		const page = `
		<div data-pbi-secure-embed-root="true">
			<section>
				<div class="tabbable">
					${tabs} ${tab_panels}
				</div>
			</section>
		</div>`;

		$(`script[src="/auth_proxy_routes/asset/secure_embed.js"]`).before(page);

	}
}


export default new App;
