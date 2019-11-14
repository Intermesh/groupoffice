/* global go, Ext, GO */

go.users.SelectDialogPanel = Ext.extend(Ext.Panel, {
	
	layout: "responsive",
	mode: "email", // or "id" in the future "phone" or "address"	
	entityName:  "User",
	title: t("Users"),
	singleSelect: false,
	query: "",

	initComponent : function() {
		
		this.createGrid();		
		
		this.labels = t("emailTypes");


		this.searchField = new go.SearchField({
			anchor: "100%",
			handler: function(field, v){
				this.search(v);
			},
			emptyText: null,
			scope: this,
			value: this.query
		});

		var search = new Ext.Panel({
			layout: "form",
			region: "north",
			autoHeight: true,
			items: [{
					xtype: "fieldset",
					items: [this.searchField]
				}]
		});	

		
		this.items = [search, this.grid, this.createGroupFilter()];

		this.grid.getSelectionModel().singleSelect = this.singleSelect;		
		
		go.users.SelectDialogPanel.superclass.initComponent.call(this);
    
    this.grid.on("afterrender", function() {
			this.groupGrid.store.load();		
			this.search();			
		}, this);

		this.on("show", function() {
			this.searchField.focus.defer(100, this.searchField);			
		}, this);
		
  },
	
	search : function(v) {
		this.grid.store.setFilter("search", {text: v});
		this.grid.store.load();
		this.searchField.focus();
	},

  createGroupFilter: function() {

    var selModel = new Ext.grid.CheckboxSelectionModel();

    this.groupGrid = new go.grid.GridPanel({
      width: dp(300),
      cls: 'go-sidenav',
      tbar: [{
				xtype: "tbtitle",
				text: t("Groups")
      }, '->', 
      {
        xtype: 'tbsearch'
      },
			//add back button for smaller screens
			{
				//this class will hide it on larger screens
				cls: 'go-narrow',
				iconCls: "ic-arrow-forward",
				tooltip: t("Users"),
				handler: function () {
					this.grid.show();
				},
				scope: this

			}],
			region: "west",
			split: true,
      autoScroll: true,	
      selModel: selModel,
      store: new go.data.Store({
        fields: [
          'id', 
          'name'
        ],
        filters: {
          "default": {
            hideUsers:  true
          }
        },
        entityStore: "Group"
      }),
      columns: [
        selModel,
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'name'
				}				
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true
			},
    });


    this.groupGrid.getSelectionModel().on('selectionchange', this.onGroupGridSelectionChange, this, {buffer: 1}); //add buffer because it clears selection first

		return this.groupGrid;
  },

  onGroupGridSelectionChange : function (sm) {
		var ids = sm.getSelections().map(function(r){
      return r.id;
    });
		this.grid.store.setFilter("groups", ids.length ? {groupId: ids} : {});
		this.grid.store.load();
	},
	
	
	createGrid : function() {

		this.grid = new go.grid.GridPanel({
      region: "center",   
      store: new go.data.Store({
        fields: [
          'id', 
          'username', 
          'displayName',
          'avatarId',
          'email'			
        ],
        baseParams: {filter: {}},
        entityStore: "User"
      }),
      columns: [
				{
					id: 'name',
					header: t('Name'),
					width: dp(200),
					sortable: true,
					dataIndex: 'displayName',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						var style = record.get('avatarId') ?  'background-image: url(' + go.Jmap.thumbUrl(record.get("avatarId"), {w: 40, h: 40, zc: 1}) + ')"' : "";
						
						return '<div class="user"><div class="avatar" style="'+style+'"></div>' +
							'<div class="wrap">'+
								'<div class="displayName">' + value + '</div>' +
								'<small class="username">' + Ext.util.Format.htmlDecode(record.get('username')) + '</small>' +
							'</div>'+
							'</div>';
					}
				}				
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true
			},
    });

    this.grid.on('rowdblclick', function(grid, rowIndex, e){
      var r = grid.store.getAt(rowIndex);
      this.fireEvent('selectsingle', this, r.data.displayName, r.data.email, r.data.id);
    }, this);

		// this.grid.getSelectionModel().on("selectionchange", function(sm) {
		// 	this.addSelectionButton.setDisabled(sm.getSelections().length == 0);
		// }, this);
		
		return this.grid;
	},
	

	addAll : function() {
		var me = this;
		var promise = new Promise(function(resolve, reject) {
		
			var s = go.Db.store("User");
			me.getEl().mask(t("Loading..."));
			s.query({
				filter: me.grid.store.baseParams.filter
			}, function(response) {			
				me.getEl().unmask();
				Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to select all {count} results?").replace('{count}', response.ids.length), function(btn) {
					if(btn != 'yes') {
						reject();
					}
					resolve(response.ids);
				}, me);
				
			}, me);
		});

		return promise;
	},

	addSelection : function() {
		var records = this.grid.getSelectionModel().getSelections();				
		return Promise.resolve(records.column('id'));
	}
	
});
