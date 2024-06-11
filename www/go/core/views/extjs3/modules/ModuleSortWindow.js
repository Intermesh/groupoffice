go.modules.ModuleSortWindow = Ext.extend(go.Window,  {
	initComponent: function() {

		this.title = t("Modules sorting");
		this.modal = true;
		this.maximizable = true;

		this.width = 1000;
		this.height = 750;

		this.layout = "fit";
		this.items = [this.createGrid()];

		this.grid.store.load();

		go.modules.ModuleSortWindow.superclass.initComponent.call(this);


	},

	createGrid: function() {


		const store = new go.data.Store({
			entityStore: "Module",
			fields: ['name', 'package', 'id', 'sort_order', 'localizedPackage', 'title', 'icon', 'enabled'],
			sortInfo: {
				field: 'sort_order',
				direction: 'ASC'
			},
			remoteSort:true
		});


		this.grid = new go.grid.GridPanel({
			store: store,
			enableDragDrop: true,
			ddGroup: 'ModuleSortDD',
			cm: new Ext.grid.ColumnModel([
				{
					width: dp(300),
					header: t("Name"),
					dataIndex: 'title',
					id: 'title',
					renderer: this.iconRenderer
				},{
					width: dp(300),
					header: t("Package"),
					dataIndex: 'localizedPackage',
					id: 'localizedPackage'

				}])
		})


		this.grid.on('render', function(){
			//enable row sorting
			var DDtarget = new Ext.dd.DropTarget(this.grid.getView().mainBody,
				{
					ddGroup : 'ModuleSortDD',
					copy:false,
					notifyDrop : this.onNotifyDrop.createDelegate(this)
				});
		}, this);

		return this.grid;
	},

	iconRenderer: function(name, cell, r) {
		return '<div class="mo-title" style="background-image:url(' + r.data.icon + ')">'
			+ name + '</div>';
	},

	onNotifyDrop : function(dd, e, data)
	{
		const rows=this.grid.selModel.getSelections();
		const dragData = dd.getDragData(e);
		let cindex = dragData.rowIndex;
		if(cindex == 'undefined')
		{
			cindex = this.grid.store.data.length-1;
		}

		for(let i = 0; i < rows.length; i++)
		{
			const rowData = this.grid.store.getById(rows[i].id);
			this.grid.store.remove(rowData.id);
			this.grid.store.insert(cindex,rowData);
		}

		//save sort order
		const update = {};

		for (let i = 0; i < this.grid.store.data.items.length;  i++)
		{
			update[this.grid.store.data.items[i].get('id')] = {
				sort_order: i
			};

		}

		go.Db.store("Module").set({
			update: update
		});

	}

})