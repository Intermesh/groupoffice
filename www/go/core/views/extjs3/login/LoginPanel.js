go.login.LoginPanel = Ext.extend(Ext.Container, {
	id: "login",
	initComponent: function () {

		this.languageContainer = new Ext.Container({
			id: 'go-select-language',
			//renderTo: 'go-select-language',
			layout: 'form',
			items: [
				this.langCombo = new go.login.LanguageCombobox({
					listeners: {
						select: function (cmb) {
							if (cmb.getValue() != '') {
								document.location = BaseHref + 'index.php?SET_LANGUAGE=' + cmb.getValue();
							}
						},
						scope: this
					}
				})
			]
		});

		var htmlText = 'Powered by Group-Office - <a target="_blank" href="https://www.group-office.com">https://www.group-office.com</a>';

		this.items = [{
				xtype: 'box',
				id: "go-login-header"
			},
			this.logoComp = new Ext.BoxComponent({cls: "go-app-logo"}),
			this.languageContainer,
			{
				xtype: 'box',
				id: 'go-powered-by',
				html: htmlText
			}
		];


		go.login.LoginPanel.superclass.initComponent.call(this);

		

		this.on('render', function () {

			// go.Router.on("change", this.onRouterChange,this);
			this.on('destroy', function() {
				this.loginDialog.close();
				go.Router.un("change", this.onRouterChange, this);
			}, this);

			//todo, this dialog should be part of this component
			this.loginDialog = new go.login.LoginDialog();
			this.loginDialog.panel = this;
			this.loginDialog.show();

			if (GO.settings.config.login_message) {
				var motd = new Ext.BoxComponent({
					id: 'motd',
					cls: 'go-html-formatted',
					html: GO.settings.config.login_message,
					renderTo: Ext.getBody()
				})
				this.on("destroy", function () {
					go.Notifier.remove(motd);
				});
			}

			var me = this;
			setTimeout(function () {
				if (GO.settings.config.debug) {
					go.Notifier.flyout({
						title: t("Warning! Debug mode enabled"), icon: 'warning', description: t("Use $config['debug']=true; only with development and problem solving. It slows " + t('product_name') + " down."), time: 4000});
				}
			}, 1000); // 1 second delay for groupoffice loading

		}, this);

	},
	onRouterChange : function(path, oldPath, route) {
		//console.warn(arguments);
		this.destroy();
	}
});
