GO.LinksAccordion = Ext.extend(Ext.Panel, {
	link_id : 0,
	link_type : 0,
	title:t("Links"),
	disabled : true,
	//renderHidden : true,
	
	initComponent : function(){

		this.layout='accordion';

		this.items=[];



		if(go.Modules.isAvailable("community", "calendar"))
		{
			var eventLinksPanel = new GO.grid.LinksGrid({
				title:t("Appointments", "calendar"),
				noSearchField:true
			});
			eventLinksPanel.store.baseParams.types=Ext.encode([1]);
			this.items.push(eventLinksPanel);
		}

		if(go.Modules.isAvailable("community", "tasks"))
		{
			var tasksLinksPanel = new GO.grid.LinksGrid({
				title:t("Tasks", "tasks"),
				noSearchField:true
			});
			tasksLinksPanel.store.baseParams.types=Ext.encode([12]);
			this.items.push(tasksLinksPanel);
		}

		if(go.Modules.isAvailable("community", "notes"))
		{
			var noteLinksPanel = new GO.grid.LinksGrid({
				title:t("Notes", "notes"),
				noSearchField:true
			});
			noteLinksPanel.store.baseParams.types=Ext.encode([4]);
			this.items.push(noteLinksPanel);
		}

		var p;
		for(var i=0;i<this.items.length;i++)
		{
			p = this.items[i];
			p.store.baseParams.no_filter_save=true;
			p.on('expand', function(){
				if(!p.store.loaded)
					p.store.load();
			}, this);
		}

		this.on('show', this.loadActivePanel, this);

		GO.LinksAccordion.superclass.initComponent.call(this);
	},

	setLinkID : function(link_id, link_type){
		this.setDisabled(GO.util.empty(link_id));
		
		this.link_id=link_id;
		this.items.each(function(p){
				p.store.loaded=false;
				p.store.baseParams.link_id=link_id;
				p.store.baseParams.link_type=link_type;
				p.store.baseParams.type_filter=true;
		});

		if(this.isVisible())
		{
			this.loadActivePanel();
		}
	},

	loadActivePanel : function(){
		var p = this.layout.activeItem;

			if(!p.store.loaded)
				p.store.load();
	}

});
