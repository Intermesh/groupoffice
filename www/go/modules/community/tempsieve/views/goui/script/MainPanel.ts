import {Button, btn, comp, Component, ComponentEventMap, h3, Menu, menu, t, tbar, datasourcestore} from "@intermesh/goui";
import {
	authManager,
	client,
	img,
	jmapds,
	MainThreeColumnPanel,
	User
} from "@intermesh/groupoffice-core";


export class MainPanel extends MainThreeColumnPanel {
	private accountsGrid: Menu<ComponentEventMap>|undefined;
	private accountId: string|undefined;
	private successCmp: Component|undefined;
	private warningCmp: Component|undefined;

    protected createWest(): Component {
		return comp({
			width: 260,
			itemId: "west",
			stateId: "tempsieve-west",
			cls: "scroll"
		},
			tbar({
					cls: "border-bottom"
				},
				h3(t("Accounts"))
			),
			this.accountsGrid = menu({cls:"west-menu absence-west-menu"})
		);
    }
    protected createEast(): Component {
        return comp({
	        hidden: true
        });
    }
    protected createCenter(): Component {
		return comp({
			cls: "vbox pad",
		},
			comp({tagName: "h2", html: "TODO"}),
			this.successCmp = comp({cls: "success", hidden: true, html: t("This account supports Sieve!")}),
			this.warningCmp = comp({cls: "warning", hidden: true}),
			comp({html: "sieve rules"}),
			comp({html: "Out of office"}),
		);
    }
	constructor() {
		super("tempsieve");
		const store = datasourcestore({
			dataSource: jmapds("Account"),
			queryParams: {
				limit: 20,
				filter: {
				}
			},
			sort: [{property: "username", isAscending: true}]
		});

		this.on("render", async () => {
			this.accountsGrid!.items.clear();
			let accountBtns: Button[] = [];
			store.load().then((v) => {
				for(const curr of v) {
					accountBtns.push(btn({
						text: curr.username,
						cls: curr.id === this.accountId ? "pressed" : "",
						icon: "account_circle",
						handler: () => {
							this.accountId = curr.id;
							this.loadAccount(curr.id)
						}
					}));
				}
				this.accountsGrid!.items.add(...accountBtns);
			});

		});
	}

	public loadAccount(id: string) {
		console.log(id);
		this.successCmp!.hidden = true;
		this.warningCmp!.hidden = true;
		go.Jmap.request({
			method: "community/tempsieve/Sieve/isSupported",
			params: {accountId: id},
		}).then((response: any): any => {
			if(response.isSupported) {
				this.successCmp!.hidden = false;
			} else {
				this.warningCmp!.hidden = false;
				this.warningCmp!.html = response.message;
			}

		})
	}

}