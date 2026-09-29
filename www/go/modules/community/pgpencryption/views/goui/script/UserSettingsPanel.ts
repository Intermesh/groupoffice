import {btn, column, comp, datasourcestore, datecolumn, hr, menu, t, Table, table, tbar} from "@intermesh/goui";
import {AppSettingsPanel, client, Export, Import} from "@intermesh/groupoffice-core";
import {publicPGPKeyDS} from "./Index.js";
import {KeyDialog} from "./KeyDialog.js";
import {ImportKeyDialog} from "./ImportKeyDialog.js";

export class UserSettingsPanel extends AppSettingsPanel {
	private table: Table;

	constructor() {
		super();

		this.title = t("PGP Encryption");

		this.items.add(
			tbar({
					cls: "border-bottom"
				},
				btn({
					icon: "add",
					text: t("Add key"),
					handler: () => {
						const dlg = new KeyDialog();

						dlg.show();
					}
				}),
				"->",
				btn({
					icon: "cloud_upload",
					text: t("Import"),
					handler: async () => {
						const dlg = new ImportKeyDialog();

						dlg.form.handler = (form) => {
							Import.fromFile(
								"PublicKey",
								".asc, .key, .gpg",
								{
									email: form.value.email,
									expiresAt: form.value.expiresAt,
								},
								{}
							);
						}

						dlg.show();
					}
				})
			),
			comp({
					style: {maxHeight: "200px"},
					cls: "scroll"
				},
				this.table = table({
					fit: true,
					store: datasourcestore({
						dataSource: publicPGPKeyDS,
						filters: {
							contact: {contactId: client.user.id}
						}
					}),
					columns: [
						column({
							id: "email",
							header: t("E-mail address"),
							width: 200
						}),
						column({
							id: "key",
							header: t("Public key"),
							width: 300
						}),
						datecolumn({
							id: "createdAt",
							header: t("Created at"),
							width: 100
						}),
						column({
							id: "btn",
							sticky: true,
							width: 32,
							renderer: (columnValue, record, td, table, storeIndex, column) => {
								return btn({
									icon: "more_vert",
									menu: menu({},
										btn({
											icon: "copy_all",
											text: t("Copy key"),
											handler: async () => {
												await navigator.clipboard.writeText(record.key);
											}
										}),
										btn({
											icon: "cloud_download",
											text: t("Export"),
											handler: () => {
												Export.toFile(
													"PublicKey",
													{
														filter: {
															id: record.id
														}
													},
													"asc"
												)
											}
										}),
										hr(),
										btn({
											icon: "delete",
											text: t("Delete"),
											handler: () => {
												void publicPGPKeyDS.confirmDestroy([record.id]);
											}
										})
									)
								})
							}
						})
					]
				})
			)
		);

		this.on("render", () => {
			void this.table.store.load();
		});
	}
}