import {jmapds} from "@intermesh/groupoffice-core";
import {
	Window,
	btn,
	colorfield,
	column,
	DataSourceStore,
	datasourcestore,
	t,
	table,
	tbar,
	textfield,
	comp
} from "@intermesh/goui";

export class LabelDialog extends Window {
	public store!: DataSourceStore
	private table: any

	constructor() {
		super();

		this.title = t("Labels");

		this.stateId = "label-dialog";
		this.maximizable = false;
		this.resizable = true;

		this.height = 600;
		this.width = 300;

		this.store = datasourcestore({dataSource: jmapds("CommentLabel")});

		this.table = table({
			cls: "bg-lowest",
			fitParent: true,
			rowSelectionConfig: {
				multiSelect: false
			},
			columns: [
				column({
					id: "name",
					width: 175,
					header: t("Name"),
					resizable: true,
					sortable: true,
					renderer: (value, record) => {
						return textfield({
							name: "name",
							value: value,
							listeners: {
								change: ({newValue}) => {
									jmapds("CommentLabel").update(record.id, {name: newValue});
								}
							}
						})
					}
				}),
				column({
					id: "color",
					width: 80,
					header: t("Color"),
					resizable: true,
					renderer: (value, record) => {
						return colorfield({
							name: "color",
							value: value,
							listeners: {
								change: ({newValue}) =>{
									jmapds("CommentLabel").update(record.id, {color: newValue});
								}

							}
						})
					}
				})
			],
			store: this.store
		});

		this.items.add(
			tbar({
					cls: "border-bottom"
				},
				btn({
					icon: "add",
					text: t("Add"),
					handler: () => {
						jmapds("CommentLabel").create(
							Object.assign({
								name: t("Label"),
								color: "fff"
							})
						)
					}
				}),
				btn({
					icon: "delete",
					text: t("Delete"),
					handler: () => {
						if (this.table.rowSelection.getSelected()[0]) {
							jmapds("CommentLabel").confirmDestroy([this.table.rowSelection.getSelected()[0].record.id]);
						}
					}
				})
			),
			comp({cls: "scroll", flex:1}, this.table)
		)
	}
}