import {btn, checkbox, comp, Component, EntityID, Notifier, router, searchbtn, t, tbar, Window} from "@intermesh/goui";
import {authManager, client, emptystate, MainThreeColumnPanel, User} from "@intermesh/groupoffice-core";
import {DomainTable} from "./DomainTable.js";
import {DomainDialog} from "./DomainDialog.js";
import {DomainDetail} from "./DomainDetail.js";

export class MainPanel extends MainThreeColumnPanel<Component, DomainDetail> {

    protected createEast(): Component {
	    return comp({
		    width: 260,
		    itemId: "west",
		    stateId: "maildomains-west",
		    hidden: true
	    })
    }
    protected createCenter() {
	    const detail = new DomainDetail();

	    detail.itemId = "detail";
	    detail.stateId = "maildomains-detail";
			detail.flex = 1;

	    detail.toolbar.items.insert(0,
				this.showWestButton(),
				this.openWestButton(),);
	    return detail;
    }
    protected createWest() {

	    this.tbl = new DomainTable();
			this.tbl.stateId = "maildomains-table";
			this.tbl!.store.setFilter("active", {active: true});
			this.tbl.emptyState = emptystate({
				store: this.tbl.store,
				icon: "domain",
				heading: t("No mail domains yet"),
				description: t("Host e-mail for your own domains on this mail server. Add a domain, then create its mailboxes and aliases and check its DNS records for MX, SPF, DKIM and DMARC."),
				addText: t("Add mail domain"),
				onAdd: () => this.addDomain()
			});

			this.tbl.rowSelection!.on("rowselect", ({target}) => {
				if(target.getSelected().length) {
					router.goto("maildomains/" + target.getSelected()[0].record.id);
				}
	    })

	    return comp({
			    width: 560,
			    cls: 'vbox active' //for mobile view this is active
		    },

		    tbar({cls: "table-aligned"},



					checkbox({
						type: "switch",
						label: t("Show inactive"),
						listeners: {
							change: ({newValue}) => {
								this.tbl!.store.setFilter("active", newValue ? undefined : {active: true});
								this.tbl!.store.load();
							}
						}
					}),
			    '->',
			    this.ptrStatus = comp({html: ""}),
			    searchbtn({
				    listeners: {
					    input: ({text}) => {
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
				    handler: () => this.addDomain()
			    }),


					btn({
						cls: "small for-medium-device",
						title: t("Close sidebar"),
						icon: "close",
						handler: (button, ev) => {
							this.activatePanel(this.center);
						}
					}),
					this.closeWestButton(),
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

		this.setup(this.createCenter(), this.createWest(), this.createEast());

		this.on("render", async () => {
			try {
				this.user = await authManager.requireLogin();
			} catch (e) {
				console.warn(e);
				Notifier.error(t("Login is required on this page"));
			}
			await this.tbl.store.load();
			client.jmap( "MailDomain/checkPtr", {}).then((result) => {
				let ptrOk = true, ip = "";
				for(const rr of result) {
					ptrOk = ptrOk && rr.status === "SUCCESS";
					ip += rr.ip + " ";
				}
				if (ptrOk) {
					this.ptrStatus.html = '<i class="icon success">check</i>&nbsp;' +t("PTR OK")
				} else {
					this.ptrStatus.html = '<i class="icon warning">warning</i>&nbsp;' + t("PTR error") + " (" + ip + ")";
				}
			}).catch((e) => {
				this.ptrStatus.html = '<i class="icon warning">warning</i>&nbsp;' + t("PTR error");

				Window.error(e);
			})
		});



	}

	private addDomain() {
		const dlg = new DomainDialog();
		dlg.show();
		dlg.form.value = {userId: this.user!.id, active: 1};
	}

	setDomainId(domainId:EntityID) {
		void this.center.load(domainId);
		this.activatePanel(this.center);
	}
}
