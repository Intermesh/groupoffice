GO.users.contactCustomFieldsCategoriesStore = new GO.data.JsonStore({
	url: GO.url('users/settings/loadContactCustomfieldCategories'),
	root: 'results', 
	totalProperty: 'total', 
	id: 'cf_category_id',
	fields: Array('id','extends_model','acl_id','name','sort_index'),
	remoteSort: true
});