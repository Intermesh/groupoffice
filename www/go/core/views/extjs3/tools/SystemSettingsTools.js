Ext.ux.IFrameComponent = Ext.extend(Ext.BoxComponent, {
	onRender : function(ct, position){
		this.el = ct.createChild({
			tag: 'iframe', 
			id: 'iframe-'+ this.id, 
			frameBorder: 0, 
			src: this.url
			});
	}
});
 
go.tools.SystemSettingsTools = Ext.extend(Ext.Panel,{
	itemId: "tools", //makes it routable
	iconCls: 'ic-build',
	
	initComponent: function() {

		this.title = t("Tools")

		this.runPanel = new Ext.ux.IFrameComponent();
		this.runWindow = new Ext.Window({
			title:t("Script output", "tools"),
			width:500,
			height:500,
			maximizable:true,
			closeAction:'hide',
			items:this.runPanel,
			layout:'fit'
		});

		this.tbar = new Ext.Toolbar({
			items: [{
				xtype:'tbtitle',
				html:t("Admin tools", "tools"),
			}]
		});

		var tools = [
			[t('System check'),'', 'install/gotest.php'],
			[t('Database check'),'', GO.url('maintenance/checkDatabase') ],
			[t('Update search index'),'', GO.url('maintenance/buildSearchCache') ],
			[t('Update search index (Complete rebuild)'),'', GO.url('maintenance/buildSearchCache', {"reset" : 1}) ],
			[t('Remove duplicate contacts and events'),'', GO.url('maintenance/removeDuplicates') ]
		];
		if(go.Modules.isAvailable(null,'files')) {
			tools.push([t('Sync file system'),'', GO.url('files/folder/syncFileSystem') ]);
		}
		if(go.Modules.isAvailable(null,'filesearch')) {
			tools.push([t('Update filesearch index'),'', GO.url('filesearch/filesearch/sync') ]);
		}
		if(go.Modules.isAvailable(null,'calendar')) {
			tools.push([t('Clear calendar holiday cache', 'calendar'),'', GO.url('calendar/calendar/truncateHolidays') ]);
		}

		var scriptList = new GO.grid.SimpleSelectList({
			cls: 'simple-list',
			tpl:'<tpl for=".">'+
				'<div id="{dom_id}" class="go-item-wrap">{name}<span>{description}</span></div>'+
				'</tpl>',
			store: new Ext.data.ArrayStore({
				idIndex:0,
				fields : ['name','description','script'],
				data : tools
			})
		});

		scriptList.on('click', function(dataview, index){
			window.open(dataview.store.data.items[index].data.script);				
		}, this);

		this.items = [scriptList];

		go.tools.SystemSettingsTools.superclass.initComponent.call(this);
	}	
});
