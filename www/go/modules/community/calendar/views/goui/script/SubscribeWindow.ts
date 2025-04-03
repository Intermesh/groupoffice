import {btn, column, comp, Component, datasourcestore, searchbtn, Table, table, tbar, Window} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";

export class SubscribeWindow extends Window {

	grid: Table
	private scroller: Component;
	constructor() {
		super();
		this.title = t('Subscribe to calendar');
		this.height = 800;
		const store = datasourcestore({
			queryParams:{
				limit: 20,
			},
			filters: {
				subscribed: {isSubscribed: false}
			},
			sort:[{property:'name',isAscending:true}],
			dataSource:jmapds('Calendar')
		});



		this.on('render', () => {
			store.addScrollLoader(this.scroller.el)
			void store.load();
		} )

		this.items.add(

			tbar({},
				'->',

				searchbtn({
					listeners: {
						input: (searchBtn, text) => {
							store.setFilter("search", {text: text})
							void store.load();
						}
					}
				})

				),

			this.scroller = comp({cls:'scroll', flex:1},

			this.grid = table({
			//fitParent:true,
			style:{width:'100%'},
			headers: false,
			store,
			columns: [
				column({id:'name'}),
				column({id:'id', width:120, renderer: v=> btn({
						text: t("Subscribe"),
						cls:'primary',
						handler: () => { store.dataSource.update(v, {isSubscribed: true}); }
					})
				})
			]
		})));
	}
}