GO.users.contactCustomFieldsCategoriesStore = new GO.data.JsonStore({
	url: GO.url('users/settings/loadContactCustomfieldCategories'),
	root: 'results', 
	totalProperty: 'total', 
	id: 'cf_category_id',
	fields: Array('id','extendsModel','aclId','name','sortOrder'),
	remoteSort: true
});