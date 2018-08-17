go.modules.community.music.MainPanel = Ext.extend(Ext.Panel, {
	
	// Use a responsive layout
	layout : "responsive",
	
	initComponent : function() {
		
		//create the genre filter component
		this.genreFilter = new go.modules.community.music.GenreFilter({
			region: "west",
			width: dp(300),
			
			//render a split bar for resizing
			split: true,
			
			tbar : [{
				xtype: "tbtitle",
				text: t("Genres")
			}]
		});
		
		//Create the artist grid
		this.artistGrid = new go.modules.community.music.ArtistGrid({
			region: "center",
			
			//toolbar with just a search component for now
			tbar: [
				'->',
				{					
					xtype: 'tbsearch'
				}
			]
		});
		
		//add the components to the main panel's items.
		this.items = [this.genreFilter, this.artistGrid];
		
		// Call the parent class' initComponent
		go.modules.community.music.MainPanel.superclass.initComponent.call(this);
		
		//Attach lister to changes of the filter selection.
		//add buffer because it clears selection first and this would cause it to fire twice
		this.genreFilter.getSelectionModel().on('selectionchange', this.onGenreFilterChange, this, {buffer: 1});
		
		// Attach listener for running the module
		this.on("afterrender", this.runModule, this);
	},
	
	// Fired when the Genre filter selection changes
	onGenreFilterChange : function (sm) {
		
		var selectedRecords = sm.getSelections(),
					ids = selectedRecords.column('id'); //column is a special GO method that get's all the id's from the records in an array.
		
		this.artistGrid.store.baseParams.filter.genres = ids;
		this.artistGrid.store.load();
	},
	
	// Fired when the module panel is rendered.
	runModule : function() {			
		// when this panel renders, load the genres and artists.
		this.genreFilter.store.load();
		this.artistGrid.store.load();		
	}
});
