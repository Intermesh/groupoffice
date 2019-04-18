Ext.ns('go.modules.comments');

go.Modules.register('community', 'comments', {
	title: t("Comments", "comments"),
	entities: [{
		name: "Comment",
		relations: {
			creator: {store: "User", fk: "createdBy"}
		}
	}, 
	"CommentLabel"],
	initModule: function () {	
		
	}
});

