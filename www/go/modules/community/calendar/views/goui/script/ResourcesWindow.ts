import {
	btn,
	checkbox,
	column,
	combobox,
	comp,
	DataSourceStore,
	datasourcestore,
	h3,
	hr,
	menu,
	menucolumn,
	searchbtn,
	splitter,
	Table,
	table,
	tbar,
	textarea,
	textfield,
	Window
} from "@intermesh/goui";
import {AclLevel, FormWindow, jmapds, principalDS} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";
import {ResourceWindow} from "./ResourceWindow.js";

class ResourceGroupWindow extends FormWindow {

	constructor() {
		super('ResourceGroup');
		this.title = t('Resource group');
		this.width = 500;
		this.generalTab.cls = 'flow pad';
		this.generalTab.items.add(
			textfield({name:'name', label: t('Name')}),
			textarea({name:'description', label: t('Description')}),
			checkbox({name:'autoAccept', label: t('Auto-accept available resources')}),
			combobox({
				dataSource: principalDS, displayProperty: 'name', filter: {entity: 'User'},
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

export const resourceGroupStore = datasourcestore({
	dataSource: jmapds("ResourceGroup"),
	sort: [{property:'name'}],
	filters: {
		def: {permissionLevel: AclLevel.MANAGE}
	}
});

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

			await resourceGroupStore.load();
			const first = resourceGroupStore.first();
			if(first) {
				this.resourceGroupTable.rowSelection!.add(first);
			}
		})

		const aside = comp({tagName:'aside', cls:'vbox', width: 300},
			tbar({},
				h3({html:t('Group')}),'->',
				btn({icon: 'add', cls: 'filled', handler: _ => (new ResourceGroupWindow()).show()})
			),
			comp({cls: "scroll",flex:1},
				this.resourceGroupTable = table({
					cls: "no-row-lines",
					headers: false,
					store: resourceGroupStore,
					rowSelectionConfig: {
						multiSelect: false,
						listeners: {
							selectionchange: ({selected}) => {
								const groupIds = selected.map((row) => row.record.id);
								this.resourceTable!.store.setFilter("group", {groupId: groupIds[0]})
								void this.resourceTable!.store.load();
							}
						}
					},
					columns:[
						column({id:'name', header:t('Name') }),

						menucolumn({
							menu: menu({},
								btn({
									icon: "edit",
									text: t("Edit"),
									handler: async (b) => {
										const tbl = b.parent!.dataSet.table;
										const group = tbl.store.get(b.parent!.dataSet.rowIndex)!;
										const d = new ResourceGroupWindow();
										d.show();
										void d.load(group.id);
									}
								}),
								hr(),
								btn({
									icon: "delete",
									text: t("Delete"),
									handler: async (b) => {
										const tbl = b.parent!.dataSet.table;
										const group = tbl.store.get(b.parent!.dataSet.rowIndex)!;

										await jmapds("ResourceGroup").confirmDestroy([group]).catch((e:any) => {
											console.log(e);
											if(e.type=='dbException') {
												Window.error(t('Could not delete non-empty resource group'));
											} else
												Window.error(e);
										});
									}
								})

							)
						})
					]
				})
			)
		);

		this.items.add(
			comp({cls:'hbox', flex:1},
				aside,
				splitter({stateId:'resource-splitter',resizeComponent:aside}),
				comp({flex:1, cls:'vbox', style:{backgroundColor: 'var(--bg-low)'}},
					tbar({cls: "border-bottom"},
						h3(t("Resources")),
						'->',
						searchbtn({
							listeners: {
								input: ( {text}) => {
									this.resourceTable!.store.setFilter("search", {text: text}).load()
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
									groupId: this.resourceTable!.store.getFilter("group")?.groupId
								};
								d.show();
							}
						}),
					),
					comp({cls: "scroll bg-lowest", flex:1},
					this.resourceTable = table({
						fit: true,
						store: resourceStore,
						columns: [
							column({header: t("ID"), id:"id", sortable: true, hidden:true, width: 60}),
							column({header: t("Color"), id:"color", width: 60, renderer: v => comp({html: '&nbsp;',style:{backgroundColor:'#'+v}}) }),
							column({header: t("Name"), id:"name", resizable: true, sortable: true}),
							menucolumn({
								menu: menu({},
									btn({
										icon: "edit",
										text: t("Edit"),
										handler: async (b) => {
											const tbl = b.parent!.dataSet.table;
											const cal = tbl.store.get(b.parent!.dataSet.rowIndex)!;
											const d = new ResourceWindow();
											d.show();
											void d.load(cal.id);
										}
									}),
									hr(),
									btn({
										icon: "delete",
										text: t("Delete"),
										handler: async (b) => {
											const tbl = b.parent!.dataSet.table;
											const cal = tbl.store.get(b.parent!.dataSet.rowIndex)!;
											jmapds("Calendar").confirmDestroy([cal.id]);
										}
									})

								)
							})

						],
						listeners: {
							rowdblclick:( {target, storeIndex}) => {
								const d = new ResourceWindow();
								d.show();
								void d.load(target.store.get(storeIndex)!.id!);
							},

							delete: async (_tbl) => {
								const ids = this.resourceTable!.rowSelection!.getSelected().map(row => row.record.id);
								await jmapds("Calendar")
									.confirmDestroy(ids);
							}
						}
					})
				))
			)
		)
	}
}