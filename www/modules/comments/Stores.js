GO.comments.categoriesStore = new GO.data.JsonStore({
	url: GO.url('comments/category/store'),
	fields: ['id','name'],
	remoteSort: true
});