go.Modules.register("community", "bookmarks", {
	mainPanel: "go.modules.community.bookmarks.MainPanel",
	title: t("Bookmarks"),
	entities: [{
		name:"Bookmark",
		relations: {
			category: { store: "BookmarksCategory", fk: "categoryId"}
		}
	}, {
		name: "BookmarksCategory",		
		relations: {
			creator: { store: "UserDisplay", fk: "createdBy"}
		}
	}],
	
	initModule: function () {
		var me = this;
		return go.Db.store("Bookmark").query({
			filter: {behaveAsModule: true}
		}).then(function(response){

			return go.Db.store("Bookmark").get(response.ids).then(function(result){

				result.entities.forEach(function(bookmark) {
					var style = document.createElement('style');
					style.type = 'text/css';
					style.innerHTML = '.go-menu-icon-bookmarks-' + bookmark.id + ' { ' +
						'background-image: url(' + go.Jmap.downloadUrl(bookmark.logo) + ');' +
					'}';

					document.getElementsByTagName('head')[0].appendChild(style);

					var cls = Ext.extend(GO.panel.IFrameComponent, {
						id: 'bookmarks-' + bookmark.id,
						title: bookmark.name,
						url: bookmark.content					
					});
					me.addPanel(cls);
				});
			});

		});	
	}
});