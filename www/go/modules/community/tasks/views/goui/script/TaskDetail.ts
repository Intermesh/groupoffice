import {addbutton, client, DetailPanel, img, jmapds, modules} from "@intermesh/groupoffice-core";
import {
	avatar,
	btn, Button,
	comp,
	datasourceform,
	DataSourceForm,
	displayfield,
	hr,
	menu,
	t,
	tbar
} from "@intermesh/goui";
import {ProgressType} from "./Main.js";
import {ContinueTaskDialog} from "./ContinueTaskDialog.js";
import {TaskDialog} from "./TaskDialog.js";
import {CommentsPanel} from "@intermesh/community/comments";

export class TaskDetail extends DetailPanel {
	private form: DataSourceForm;

	private assignMeBtn: Button;
	private deleteBtn: Button;
	private editBtn: Button;

	constructor() {
		super("Task");

		this.scroller.items.add(
			this.form = datasourceform({
					dataSource: jmapds("Task")
				},
				comp({cls: "card", flex: 1},
					tbar({},
						this.titleCmp = comp({tagName: "h3"}),
						"->",
						displayfield({
							name: "progress",
							renderer: (v) => {
								return comp({
									flex: 1,
									cls: "status tasks-status-" + v,
									html: ProgressType[v as keyof typeof ProgressType],
									listeners: {
										render: (cmp) => {
											cmp.el.addEventListener("click", ev => {
												ev.preventDefault();

												const changeMenu = menu({isDropdown: true});

												for (const [key, value] of Object.entries(ProgressType)) {
													changeMenu.items.add(
														btn({
															text: t(value),
															handler: async () => {
																if (this.form.currentId)
																	await jmapds("Task").update(this.form.currentId, {progress: key});
															}
														})
													)
												}

												changeMenu.showAt(ev);
											});
										}
									}
								});
							}
						})
					),
					comp({cls: "hbox", flex: 1},
						comp({cls: "vbox", flex: 1},
							displayfield({
								name: "start",
								label: t("Start at")
							}),
							displayfield({
								name: "tasklistId",
								label: t("Tasklist"),
								renderer: async (v) => {
									if (!v) {
										return "";
									}

									const t = await jmapds("TaskList").single(v);

									return t ? t.name : "";
								}
							})
						),
						comp({cls: "vbox", flex: 1},
							displayfield({
								name: "due",
								label: t("Due at")
							}),
							displayfield({
								name: "responsibleUserId",
								label: t("Responsible"),
								renderer: async (v) => {
									if (!v) {
										return comp();
									}

									const r = await jmapds("Principal").single(v);

									return r ? comp({cls: "hbox"},
											r.avatarId ?
												img({
													cls: "goui-avatar",
													blobId: r.avatarId,
													title: r.name
												}) :
												avatar({
													displayName: r.name
												}),
											comp({cls: "tasks-principal-name", text: r.name}))
										: comp();
								}
							})
						)
					),
					displayfield({
						cls: "task-progressbar-displayfield",
						name: "percentComplete",
						label: t("Percent Complete"),
						renderer: (v) => {
							return comp({cls: "go-progressbar"},
								comp({style: {width: `${Math.ceil(v)}%`}})
							)
						}
					}),
					displayfield({
						name: "description",
						label: t("Description")
					}),
					displayfield({
						name: "location",
						label: t("Location")
					}),
					displayfield({
						name: "categories",
						label: t("Categories"),
						renderer: async (categoryIds: string[]) => {
							const response = await jmapds("TaskCategory").get(categoryIds);

							if (response.list) {
								return response.list.map(record => record.name).join(", ");
							}

							return "";
						}
					})
				)
			)
		);

		if(modules.isAvailable("community", "comments")) {
			CommentsPanel.addToDetail(this);
		}

		this.addCustomFields();

		this.addLinks();
		this.addHistory();

		this.items.add(
			tbar({
					cls: "border-top"
				},
				"->",
				btn({
					text: t("Continue task"),
					icon: "arrow_right_alt",
					handler: () => {
						const dlg = new ContinueTaskDialog();
						void dlg.load(this.entity!.id)
						dlg.show();
					}
				})
			)
		);

		this.on("load", (detailPanel, entity) => {
			this.title = entity.title;

			this.editBtn.disabled = (entity.permissionLevel < go.permissionLevels.write);
			this.deleteBtn.disabled = (entity.permissionLevel < go.permissionLevels.writeAndDelete);
			this.assignMeBtn.hidden = (entity.responsibleUserId);

			void this.form.load(entity.id);
		});

		this.toolbar.items.add(
			this.editBtn = btn({
				icon: "edit",
				title: t("Edit"),
				handler: () => {
					const dlg = new TaskDialog();
					void dlg.load(this.form.currentId!);
					dlg.show();
				}
			}),
			addbutton(),
			btn({
				icon: "more_vert",
				menu: menu({
						isDropdown: true
					},
					// todo: fix
					// linkbrowserbutton({
					// 	text: t("Links")
					// }),
					// filesbutton({
					// 	text: t("Files")
					// }),
					hr(),
					btn({
						icon: "print",
						text: t("Print"),
						handler: () => {
							this.print();
						}
					}),
					hr(),
					this.deleteBtn = btn({
						icon: "delete",
						text: t("Delete"),
						handler: () => {
							void jmapds("Task").confirmDestroy([this.form.currentId!]);
						}
					})
				)
			})
		);

		this.toolbar.items.insert(-this.toolbar.items.count(),
			this.assignMeBtn = btn({
				text: t("Assign me"),
				handler: async () => {
					this.mask();
					await jmapds("Task").update(this.form.currentId!, {
						responsibleUserId: client.user.id
					});
					this.unmask();
				},
				hidden: true
			})
		);
	}
}
