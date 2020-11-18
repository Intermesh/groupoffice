go.modules.community.multi_instance.AllowedModulesPanel = Ext.extend(go.grid.GridPanel, {
	title: t('Modules'),
	initComponent: function() {

		this.columns = [
			new GO.grid.CheckColumn({
				header: t('Allowed'),
				dataIndex: 'allowed'
			}),
			{
				header: 'Package',
				dataIndex: 'localizedPackage',
				width: dp(300)
			}, {
				header: 'Module',
				dataIndex: 'title',
				id: 'title',
				width: dp(500),
				renderer: function(name, cell, record) {
					return '<div class="mo-title" ' +
						'style="background-image:url(' + go.Jmap.downloadUrl('core/moduleIcon/'+(record.data.package || "legacy")+'/'+record.data.module) + ')">'
						+ record.data.title +'</div>';
				}
			}
		];

		this.store = new go.data.GroupingStore({

			fields: ['id', 'package', 'module', 'title', 'icon', 'allowed', 'localizedPackage'],
			root: 'allowedModules',
			id: 'id',

			remoteGroup: false,
			remoteSort: false,
			groupField: 'localizedPackage'

		});

		this.view = new Ext.grid.GroupingView({
			hideGroupedColumn:true,
			emptyText: t("No items to display")
		});

		this.supr().initComponent.call(this);
	}
})