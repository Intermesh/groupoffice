Ext.ns('go.modules.comments');

go.Modules.register('community', 'comments', {
	title: t("Comments", "comments"),
	entities: [{
		name: "Comment",
		relations: {
			creator: {store: "User", fk: "createdBy"},
			modifier: {store: "User", fk: "modifiedBy"},
			labels: {store: "CommentLabel", fk: "labels"}
		}
	}, 
	"CommentLabel"],
	initModule: function () {	
		
	}
});

