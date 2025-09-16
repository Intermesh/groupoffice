import {client, FormWindow, jmapds} from "@intermesh/groupoffice-core";
import {
	btn,
	checkbox, checkboxcolumn, CheckboxField, checkboxselectcolumn, column,
	comp,
	containerfield,
	List, list, MapField,
	mapfield, menu,
	select, table,
	textfield
} from "@intermesh/goui";
import {allCalendarStore, CalendarView, t} from "./Index.js";

export class ViewWindow extends FormWindow {
	//groupIdsFld: MapField
	calendarIdList: List
	calandarIds: {[key:number]: boolean} = {}
	constructor() {
		super('CalendarView');
		this.title = 'view';
		this.width = 580;
		this.height = 800;

		this.on('beforerender', () => {
			this.title = t(this.form.currentId ? 'Edit view' : 'Create view');
		});

		this.form.on('beforesave', ( {data}) => {
			data.ownerId = client.user.id;
			data.calendarIds = this.calendarIdList.rowSelection?.getSelected().map(r => r.id);
			return data;
		}).on('load', ({data}) => {
			setTimeout(() => {
				for(const id of data.calendarIds) {
					const rec = this.calendarIdList.store.find(v => v.id == id);
					if(rec)
					this.calendarIdList.rowSelection!.add(rec);
				}
			}, 100)


		});


		this.generalTab.items.add(
			comp({cls: 'flow pad'},
				textfield({name: 'name', label: t('Name'), required: true, flex: 1}),
				select({name: 'defaultView', label: t('Display as'), options:[
					{value:null, name: t('Current ')},
					{value:'day', name: t('Day')},
					{value:'days-5', name: t('Workweek')},
					{value:'week',name:  t('Week')},
					{value:'weeks-2',name: '2 ' + t('Weeks')},
					{value:'weeks-3',name:  '3 ' + t('Weeks')},
					{value:'month',name:  t('Month')},
					{value:'split-5',name:  t('Split')},
				]}),
				this.calendarIdList = table({
					tagName: 'div',
					headers: false,
					store: allCalendarStore,
					cls: 'check-list',
					rowSelectionConfig: {
						multiSelect: true
					},
					listeners: {'render': ({target}) => {
							target.store.load()
						}
					},
					columns: [
						checkboxselectcolumn({id:'checkbox', listeners: {
							'change': ({checked, record}) => {this.calandarIds[record.id] = checked},
							'render': ({td,result,record,storeIndex}) => {
								(result as CheckboxField).color = '#'+record.color;
									td.addEventListener("mousedown", (ev) => {
										ev.stopPropagation(); // stop lists row selector event
									});
									td.addEventListener('contextmenu', (ev) => {
										ev.preventDefault();
										const m = menu({isDropdown: true},
											btn({text: t('Select all'), handler: () => {
													this.calendarIdList.rowSelection!.selectAll()
												}}),
											btn({text: t('Select none'), handler: () => {
													this.calendarIdList.rowSelection!.clear()
												}}),
											btn({text: t('Deselect others'), handler: () => {
													this.calendarIdList.rowSelection!.selectIndex(storeIndex)
												}})
										);
										m.showAt(ev);
									})
								}
						}}),
						column({id: 'name'})
					]
				})
			)
		);

		this.addSharePanel();

	}
}