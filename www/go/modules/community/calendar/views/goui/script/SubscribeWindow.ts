import {
	browser,
	btn,
	column,
	comp,
	Component,
	datasourcestore, hr,
	menu,
	searchbtn,
	Table,
	table,
	tbar,
	Window
} from "@intermesh/goui";
import {client, jmapds, modules} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";
import {ResourceWindow} from "./ResourcesWindow";
import {CalendarWindow} from "./CalendarWindow";

export class SubscribeWindow extends Window {

	grid: Table
	private scroller: Component;
	constructor() {
		super();
		this.title = t('Subscribe to calendar');
		this.height = 800;
		this.width = 500;
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

		const rights = modules.get("community", "calendar")!.userRights;

		this.items.add(

			tbar({},
				'->',
				searchbtn({
					listeners: {
						input: ({text}) => {
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
				column({id:'name', width: 270}),
				column({id:'id', width:160, renderer: v=> btn({
						text: t("Subscribe"),
						cls:'primary',
						handler: () => { store.dataSource.update(v, {isSubscribed: true, syncToDevice: false}); }
					})
				}),
				column({id: 'more', width:60, renderer: (_, data)=> btn({
						icon: "more_vert",
						menu: menu({},
							btn({icon:'share', text: t('Permissions')+'…', hidden: data.davaccountId || (data.groupId && !rights.mayChangeResources), disabled:!data.myRights.mayReadItems, handler: async _ => {
									const dlg = data.groupId ? new ResourceWindow() : new CalendarWindow();
									await dlg.load(data.id);
									dlg.show();
								}}),
							btn({icon:'delete', text: t('Delete','core','core')+'…', hidden: data.davaccountId || !rights.mayChangeCalendars, disabled:!data.myRights.mayAdmin, handler: async _ => {
									jmapds("Calendar").confirmDestroy([data.id]);
								}}),
							hr({hidden:data.groupId || !rights.mayChangeCalendars}),
							btn({icon:'file_save',hidden:data.groupId || !rights.mayChangeCalendars, text: t('Export','core','core'), handler: _ => {
								client.getBlobURL('community/calendar/calendar/'+data.id).then(window.open) }
							}),

						)
					})
				})
			]
		})));
	}
}