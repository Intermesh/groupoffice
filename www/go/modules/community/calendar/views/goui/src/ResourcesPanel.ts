import {
	btn, checkbox,
	column, combobox,
	comp,
	Component, DataSourceStore,
	datasourcestore, DefaultEntity,
	h3,
	hr,
	menu, router,
	searchbtn, select, splitter,
	t,
	Table,
	table,
	tbar, textarea, textfield, Window
} from "@intermesh/goui";
import {FormWindow, jmapds} from "@intermesh/groupoffice-core";
import {calendarStore} from "./Index.js";

class ResourceGroupWindow extends FormWindow {
	constructor() {
		super('ResourceGroup');
		this.title = t('Resource group');
		this.width = 400
		this.height = 316;
		this.generalTab.cls = 'flow pad';
		this.generalTab.items.add(
			textfield({name:'name', label: t('Name')}),
			textarea({name:'description', label: t('Description')})
		);
	}
}
const resourceStore = datasourcestore({
	dataSource:jmapds('Calendar'),
	queryParams:{filter:{isResource:true}},
	//properties: ['id', 'name', 'description'],
	sort: [{property:'sortOrder'}]
})

const resourceGroupStore = datasourcestore({
	dataSource: jmapds("ResourceGroup")
});

class ResourceWindow extends FormWindow {
	constructor() {
		super('Calendar');
		this.title = t('Resource');
		this.generalTab.cls = 'flow pad';
		this.generalTab.items.add(
			select({name:'groupId', label:t('Group'), 	store: resourceGroupStore, valueField: 'id', textRenderer: (r: any) => r.name}),
			textfield({name:'name', label: t('Name')}),
			textfield({name:'color', hidden:true, value: '69554f'}),
			textarea({name:'description', label: t('Description')}),
			checkbox({disabled:true, name:'needsApproval', label: t('Needs approval')})
		)
	}
}
export class ResourcePanel extends Window {

	resourceTable: Table<DataSourceStore>
	constructor() {
		super();
		this.title = t('Manage');
		this.width = 800;
		this.height = 600;

		this.on('render', () => {
			resourceStore.load();
			resourceGroupStore.load();
		})

		const aside = comp({tagName:'aside', width: 300},
			tbar({},
				h3({html:t('Group')}),'->',
				btn({icon: 'add', cls: 'filled', handler: _ => (new ResourceGroupWindow()).show()})
			),
			table({cls: "no-row-lines", headers: false, fitParent: true,
				store: resourceGroupStore,
				rowSelectionConfig: {
					multiSelect: false,
					listeners: {
						selectionchange: (tableRowSelect) => {
							const groupIds = tableRowSelect.selected.map((index) => tableRowSelect.list.store.get(index)!.id);
							this.resourceTable!.store.setFilter("group", {groupId: groupIds[0]})
							void this.resourceTable!.store.load();

						}
					}
				},
				columns:[
					column({id:'name', header:t('Name') }),
					column({id: "btn", width: 48,renderer: (columnValue: any, record, td, table, rowIndex) =>
							btn({
								icon: "more_vert",
								menu: menu({},
									btn({
										icon: "edit",
										text: t("Edit"),
										handler: async (btn) => {
											const g = table.store.get(rowIndex)!;
											const d = new ResourceGroupWindow();
											await d.load(g.id);
											d.show();
										}
									}),
									hr(),
									btn({
										icon: "delete",
										text: t("Delete"),
										handler: async (btn) => {
											jmapds("ResourceGroup").confirmDestroy(table.store.get(rowIndex)!.id);
										}
									})

								)
							})

					})
				]
			})
		);

		this.items.add(
			comp({cls:'hbox fit'},
				aside,
				splitter({stateId:'resource-splitter',resizeComponentPredicate:aside}),
				comp({flex:1, cls:'vbox'},
					tbar({cls: "border-bottom"},
						h3(t("Resources")),
						'->',
						searchbtn({
							listeners: {
								input: (searchBtn, text) => {
									this.resourceTable!.store.setFilter("search", {text: text})
								}
							}
						}),
						btn({
							title: "Add",
							text: t("Add"),
							cls: "filled primary",
							icon: "add",
							handler: () => {
								const d = new ResourceWindow();
								d.form.value = {
									groupId: this.resourceTable!.store.getFilter("group").groupId
								};
								d.show();
							}
						}),
					),
					this.resourceTable = table({
						fitParent: true,
						store: resourceStore,
						columns: [column({header: t("ID"), id:"id", sortable: true, width: 60}),
							column({header: t("Name"), id:"name", resizable: true, sortable: true, width: 180}),
							column({header: t("Capacity"), id: "capacity", sortable: true}),
							column({header: t("Needs approval"), id: "needsApproval"}),
							column({header: t("Group"), id: "group", sortable: true})
						],
						listeners: {
							rowdblclick:(list, storeIndex, row, ev) => {
								const d = new ResourceWindow();
								d.show();
								void d.load(list.store.get(storeIndex)!.id);
							},

							delete: async (tbl) => {
								const ids = this.resourceTable!.rowSelection!.selected.map(index => this.resourceTable!.store.get(index)!.id);
								await jmapds("Project3")
									.confirmDestroy(ids);
							}
						}
					})
				)
			)
		)
	}
}