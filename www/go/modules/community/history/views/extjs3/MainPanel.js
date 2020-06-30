/* global go, Ext, GO */

Ext.define('go.modules.community.history.MainPanel', {
	extend: go.modules.ModulePanel,

	layout: "responsive",
	layoutConfig: {triggerWidth: 1000},

	initComponent: function () {

		this.createGrid();

		this.sidePanel = new Ext.Panel({
			width: dp(300),
			cls: 'go-sidenav',
			region: "west",
			split: true,
			// autoScroll: true,
			layout: "border",
			items: [
				this.createFilterPanel()
			]
		});

		this.items = [
			this.grid, //first is default in narrow mode
			this.sidePanel
		];

		go.modules.community.addressbook.MainPanel.superclass.initComponent.call(this);
	},

	createGrid : function() {
		this.grid = new go.modules.community.history.LogEntryGrid({
			forDetailView: false,
			region: 'center',
			tbar: [{
				cls: 'go-narrow',
				iconCls: "ic-menu",
				handler: function () {
					this.sidePanel.show();
				},
				scope: this
			}, '->', {
				xtype: 'tbsearch'
			}]
		});

		return this.grid;
	},

	search: function (v) {

		var filter = {};

		filter.entities = this.entityGrid.getSelectionModel().getSelections().map(function(r){ return r.data.entity; });

		this.grid.store.setFilter('search', filter);

		this.grid.store.load();
	},

	createFilterPanel: function () {
		var fakeLinkConfigs = []
		go.Entities.getAll().forEach(function (m) {
			if(m.module !== 'history')
				fakeLinkConfigs.push({id: m.name, entity: m.name, title: m.title });
		});
		this.entityGrid = new go.links.EntityGrid({
			savedSelection: "history",
			autoHeight: true,
			entities:fakeLinkConfigs
		});

		this.entityGrid.getSelectionModel().on('selectionchange', function (sm) {
			this.search();
		}, this, {buffer: 1});

		return new Ext.Container({
			region: "center",
			//padding: dp(16),
			minHeight: dp(200),
			autoScroll: true,
			items: [
				{xtype:'panel', layout:'anchor',items: [
					{
						xtype:'datefield',
						emptyText: t('from'),
						anchor: '100%'
					},{
						xtype:'datefield',
						emptyText: t('till'),
							anchor: '100%'
					},new go.users.UserCombo({
						emptyText: t('All users'),
							allowBlank:true,
						listeners: {
							select:function(me, v){
								this.grid.store.setFilter('creator', {user:v.id}).load();
							},
							change: function(me,v) {
								if(v === null) {
									this.grid.store.setFilter('creator',null).load();
								}
							},
							scope:this
						}
					}),],
					padding: dp(16)
				},{
					title:t('Actions'),
					defaults: {xtype:'checkbox', listeners: {
							check:function(cb, checked) {
								var combos = cb.ownerCt.findByType('checkbox');
								var arr = [];
								for(var i=0;i<combos.length;i++) {
									if(combos[i].getValue()) {
										arr.push(combos[i].id);
									}
								}
								//console.log(combos[i].getValue(), combos[i].id);
								var actionFilter = !arr ? null : {actions: arr};
								this.grid.store.setFilter('actions',actionFilter).load();
							},
							scope:this
						}},
					padding: '0px ' + dp(16),
					items: [
						{id:'create',boxLabel: t('Create')},
						{id:'update',boxLabel: t('Update')},
						{id:'delete',boxLabel: t('Delete')},
						{id:'login',boxLabel: t('Login')},
					]
				},{
					xtype:'panel',
					title: t('Types'),
					items: [this.entityGrid]
				}
			]
		});


	}

});
