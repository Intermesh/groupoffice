import {
	btn,
	checkbox,
	comp,
	Component,
	EntityID,
	Notifier,
	paginator,
	router,
	searchbtn,
	t,
	tbar,
	Window
} from "@intermesh/goui";
import {authManager, client, jmapds, MainThreeColumnPanel, User} from "@intermesh/groupoffice-core";
import {DomainTable} from "./DomainTable.js";
import {DomainDialog} from "./DomainDialog.js";
import {DomainDetail} from "./DomainDetail.js";

export class MainPanel extends MainThreeColumnPanel {

		protected center!: DomainDetail;
    protected createEast(): Component {
	    return comp({
		    width: 260,
		    itemId: "west",
		    stateId: "maildomains-west",
		    hidden: true
	    })
    }
    protected createCenter(): Component {
	    const detail = new DomainDetail();

	    detail.itemId = "detail";
	    detail.stateId = "maildomains-detail";
			detail.flex = 1;

	    detail.toolbar.items.insert(0,this.showCenterButton());
	    return detail;
    }
    protected createWest(): Component {

	    this.tbl = new DomainTable();
			this.tbl.stateId = "maildomains-table";
			this.tbl!.store.setFilter("active", {active: true});

			this.tbl.rowSelection!.on("rowselect", rowSelect => {
			    if(rowSelect.getSelected().length) {
				    router.goto("maildomains/" + rowSelect.getSelected()[0].record.id);
			    }
	    })

	    return comp({
			    width: 560,
			    cls: 'vbox active' //for mobile view this is active
		    },

		    tbar({},
					checkbox({
						type: "switch",
						label: t("Show inactive"),
						listeners: {
							change: (cb, checked) => {
								this.tbl!.store.setFilter("active", checked ? undefined : {active: true});
								this.tbl!.store.load();
							}
						}
					}),
			    '->',
			    this.ptrStatus = comp({html: ""}),
			    searchbtn({
				    listeners: {
					    input: (sender, text) => {
						    this.tbl!.store.setFilter("search", {text: text});
						    this.tbl!.store.load(false);
					    }
				    }
			    }),
			    btn({
				    itemId: "add",
				    icon: "add",
				    text: t("Add"),
				    cls: "filled primary",
				    handler: async () => {
					    const dlg = new DomainDialog();
					    dlg.show();
					    dlg.form.value = {userId: this.user!.id, active: 1};
				    }
			    })
		    ),

		    comp({
				    flex: 1,
				    stateId: "maildomains",
				    cls: "scroll border-top main fit"
			    },
			    this.tbl
		    )
	    )
    }
	private tbl!: DomainTable;
	private ptrStatus!: Component
	private user: User | undefined;

	constructor() {
		super("section");

		this.on("render", async () => {
			try {
				this.user = await authManager.requireLogin();
			} catch (e) {
				console.warn(e);
				Notifier.error(t("Login is required on this page"));
			}
			await this.tbl.store.load();
			client.jmap( "MailDomain/checkPtr", {}).then((result) => {
				let ptrOk = true;
				for(const rr of result) {
					ptrOk = ptrOk && rr.status === "SUCCESS";
				}
				if (ptrOk) {
					this.ptrStatus.html = '<i class="icon success">check</i>&nbsp;' +t("PTR OK")
				} else {
					this.ptrStatus.html = '<i class="icon warning">warning</i>&nbsp;' + t("PTR error");
				}
			}).catch((e) => {
				this.ptrStatus.html = '<i class="icon warning">warning</i>&nbsp;' + t("PTR error");

				Window.error(e);
			})
		});



	}

	setDomainId(domainId:EntityID) {
		void this.center.load(domainId);
		this.activatePanel(this.center);
	}
}
