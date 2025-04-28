import {
	arrayfield,
	btn, checkbox, colorfield,
	column, combobox,
	comp, containerfield,
	DataSourceStore,
	datasourcestore, displayfield, durationfield,
	h3,
	hr, mapfield,
	menu, numberfield,
	searchbtn, select, splitter,
	Table,
	table,
	tbar, textarea, textfield, Window
} from "@intermesh/goui";
import {FormWindow, jmapds, principalcombo} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";

class ResourceGroupWindow extends FormWindow {

	constructor() {
		super('ResourceGroup');
		this.title = t('Resource group');
		this.width = 500;
		this.generalTab.cls = 'flow pad';
		this.generalTab.items.add(
			textfield({name:'name', label: t('Name')}),
			textarea({name:'description', label: t('Description')}),
			combobox({
				dataSource: jmapds("Principal"), displayProperty: 'name', filter: {entity: 'User'},
				label: t("Default admin"), name: "defaultOwnerId", filterName: "text", flex:'1 0', required:true
			})
		);
	}
}
const resourceStore = datasourcestore({
	dataSource:jmapds('Calendar'),
	filters:{isResource:{isResource:true}},
	//properties: ['id', 'name', 'description'],
	sort: [{property:'sortOrder'}]
})

const resourceGroupStore = datasourcestore({
	dataSource: jmapds("ResourceGroup")
});

export class ResourceWindow extends FormWindow {
	constructor() {
		super('Calendar');
		this.title = t('Resource');

		this.generalTab.cls = 'flow pad';
		this.generalTab.items.add(
			select({name:'groupId', label:t('Group'), 	store: resourceGroupStore, valueField: 'id', textRenderer: (r: any) => r.name}),
			textfield({name:'name', flex:1,label: t('Name')}),
			colorfield({name:'color',width:100, value: '69554f'}),
			textarea({name:'description', label: t('Description')})
			//checkbox({disabled:true, name:'needsApproval', label: t('Needs approval')})
		);

		this.addCustomFields();

		this.addSharePanel([
			{value: "",name: ""},
			{value: 5, name: t("Read free/busy")},
			{value: 10,name: t("Read items")},
			//{value: 20,name: t("Update private")},
			{value: 25,name: t("Approve / Disapprove")}, // RSVP
			//{value: 30,name: t("Write own")},
			//{value: 35,name: t("Write all")},
			{value: 50,name: t("Manage")}
		]);
	}
}
export class ResourcesWindow extends Window {

	resourceTable: Table<DataSourceStore>
	private resourceGroupTable: Table<DataSourceStore>

	constructor() {
		super();
		this.title = t('Manage resources');
		this.width = 900;
		this.height = 600;
		this.resizable = true;

		this.on('render', async () => {
			resourceStore.load();
			await resourceGroupStore.load();
			const first = resourceStore.first();
			if(first) {
				this.resourceGroupTable.rowSelection!.add(first);
			}
		})

		const aside = comp({tagName:'aside', width: 300},
			tbar({},
				h3({html:t('Group')}),'->',
				btn({icon: 'add', cls: 'filled', handler: _ => (new ResourceGroupWindow()).show()})
			),
			this.resourceGroupTable =table({cls: "no-row-lines", headers: false, fitParent: true,
				store: resourceGroupStore,
				rowSelectionConfig: {
					multiSelect: false,
					listeners: {
						selectionchange: (tableRowSelect) => {
							const groupIds = tableRowSelect.getSelected().map((row) => row.record.id);
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
										handler: async (_btn) => {
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
										handler: async (_btn) => {
											jmapds("ResourceGroup").confirmDestroy([table.store.get(rowIndex)!.id]);
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
				comp({flex:1, cls:'vbox', style:{backgroundColor: 'var(--bg-low)'}},
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
							title: t("Add"),
							//text: t("Add"),
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
							column({header: t("Needs approval"), id: "needsApproval"}),
						],
						listeners: {
							rowdblclick:(list, storeIndex) => {
								const d = new ResourceWindow();
								d.show();
								void d.load(list.store.get(storeIndex)!.id!);
							},

							delete: async (_tbl) => {
								const ids = this.resourceTable!.rowSelection!.getSelected().map(row => row.record.id);
								await jmapds("Resources")
									.confirmDestroy(ids);
							}
						}
					})
				)
			)
		)
	}
}