go.groups.GroupDialog = Ext.extend(go.form.Dialog, {
	title: t('Group'),
	entityStore: "Group",
	height: dp(800),
	width: dp(1000),
	formPanelLayout: "border",

	initComponent: function() {
		this.supr().initComponent.call(this);


		this.on('show', function () {

			if(go.User.isAdmin) {
				this.groupModuleGrid.groupId = this.currentId;
				//sorts selected groups on top
				this.groupModuleGrid.store.setFilter("groupIsAllowed", {groupIsAllowed: this.currentId});
			}
			//this sorts the selected members on top
			this.groupUserGrid.store.setFilter('sort', {'groupMember' : this.currentId});

			if (!this.currentId) {
				//needed to load the grid.
				this.groupUserGrid.setValue([]);
			} else if (this.currentId == 2) { //group everyone
				this.groupUserGrid.setDisabled(true);
				this.groupUserGrid.hide();
			}
		}, this);

	},

	onSubmit: function(success, groupId) {
		//for(var id in changedModules) {
		if(success && go.User.isAdmin) {
			this.groupModuleGrid.groupId = groupId;
			let changedModules = this.groupModuleGrid.getValue();
			go.Db.store('Module').get(Object.keys(changedModules), (modules) => {
				for(let key in changedModules) {
					for(let module of modules) {
						if(module.id == key) {
							changedModules[key].permissions = {...module.permissions, ...changedModules[key].permissions};
							break;
						}
					}
				}
				//console.warn(changedModules);
				go.Db.store('Module').set({update: changedModules}).then((response) => {
					if(response.notUpdated) {
						GO.errorDialog.show(t("Failed to save"));
					}
				})
			});


		}
		//}
	},

	initFormItems: function () {

		this.addPanel(new go.permissions.SharePanel());

		if(go.User.isAdmin) {
			this.addPanel(this.groupModuleGrid = new go.groups.GroupModuleGrid());
		}
		
		return [{
				region: "north",
				autoHeight: true,
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',						
						allowBlank: false
					}]
			},
			this.groupUserGrid = new go.groups.GroupUserGrid({
				region: "center",
				hideLabel: true,
				value: []
			})
		];
	}
});

