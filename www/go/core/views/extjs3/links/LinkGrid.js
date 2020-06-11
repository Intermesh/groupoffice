/* global Ext, go */

go.links.LinkGrid = Ext.extend(go.grid.GridPanel, {
	cls: 'go-links-link-grid',
	initComponent: function () {

		this.columns = [
			{
				id: 'name',
				header: t('Name'),
				width: dp(100),
				sortable: true,
				dataIndex: 'name',
				renderer: function (value, metaData, record, rowIndex, colIndex, store) {
					var linkIconCls = go.Entities.getLinkIcon(record.data.entity, record.data.filter);
					
					return '<i class="entity ' + linkIconCls + '"></i><small class="go-top-right">' + go.util.Format.shortDateTime(record.get('modifiedAt')) + '</small> ' + value;
				}
			},{
				id: 'id',
				header: t('ID'),
				width: dp(80),
				sortable: true,
				dataIndex: 'entityId',
				hidden: true
			}, {

				header: t('Type'),
				width: dp(80),
				sortable: false,
				dataIndex: 'entity',
				hidden: true
			},
			{
				id: 'modifiedAt',
				header: t('Modified at'),
				width: dp(160),
				sortable: true,
				dataIndex: 'modifiedAt',
				renderer: Ext.util.Format.dateRenderer(go.User.dateTimeFormat),
				hidden: true
			}
		];
		
		
		this.view = new go.grid.GridView({
			  enableRowBody:true,
			  showPreview:true,
			  getRowClass : function(record, rowIndex, p, ds) {
			  	if(!record.data.description) {
						record.data.description = "-";
					}
					p.body = '<small>' + record.data.description + '</small>';
					return 'x-grid3-row-expanded';				
			  },
				grid: this,
				emptyText: t("No items to display")
			  // emptyText: '<span class="go-hrml-formatted">' + t('Use "*" for wildcards and prefix words with "-" to omit results with that word when searching.') + '<br /><br /><a target="_blank" href="https://groupoffice.readthedocs.io/en/latest/using/search.html">' + t('Click here for more information') + '</a></span>',
				// deferEmptyText: false
		   }),
							 
		this.autoExpandColumn = 'name';
		this.store = new go.data.Store({			
			fields: ['id', 'entityId', 'entity', 'name', 'description', 'filter', {name: 'modifiedAt', type: 'date'}],
			entityStore: "Search",
			sortInfo: {
				field: 'modifiedAt',
				direction: 'DESC'
			}
		});
		
		go.links.LinkGrid.superclass.initComponent.call(this);
	}
});
