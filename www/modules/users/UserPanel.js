GO.users.UserPanel = Ext.extend(GO.DisplayPanel,{

	model_name : "GO\\Base\\Model\\User",

	loadParams : {task: 'user_with_items'},

	idParam : 'id',

	loadUrl : "users/user/display",

	editGoDialogId : 'user',

	editHandler : function(){
		GO.users.showUserDialog(this.link_id);		
	},

	initComponent : function(){


		this.template =
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
					'<tr>'+
						'<td colspan="2" class="display-panel-heading"><b>'+GO.lang.strUsername+': {username}</b></td>'+
					'</tr>'+

					'<tr>'+

						// PERSONAL DETAILS+ 1e KOLOM
						'<td valign="top">'+
							'<table cellpadding="0" cellspacing="0" border="0">'+
								//NAME

								'<tr>'+
									'<td>' + GO.lang['strName'] + ':</td><td> {name}</td>'+
								'</tr>'+
								'<tr>'+
									'<td>' + GO.lang['strUsername'] + ':</td><td> {username}</td>'+
								'</tr>'+
								'<tr>'+
									'<td>' + GO.lang['strEmail'] + ':</td><td> {email}</td>'+
								'</tr>'+
								
								'<tpl if="contact_id"><tr>'+
									'<td colspan="2"><a href="#" onclick="GO.linkHandlers[\'GO\\\\\\\\Addressbook\\\\\\\\Model\\\\\\\\Contact\'].call(this, {contact_id});">'+GO.users.lang.openContact+'</a></td></tr></tpl>'+
								
							'</table>'+
							
							
							
						'</td>'+
					'</tr>'+
				'</table>'+
				GO.linksTemplate;

				if(GO.customfields)
				{
					this.template +=GO.customfields.displayPanelTemplate;
				}


		Ext.apply(this.templateConfig, {
			addSlashes : function(str)
			{
				str = GO.util.html_entity_decode(str, 'ENT_QUOTES');
				str = GO.util.add_slashes(str);
				return str;
			},
			mailTo : function(email, name) {

				if(GO.email && GO.settings.modules.email.read_permission)
				{
					return '<a href="#" onclick="GO.email.showAddressMenu(event, \''+this.addSlashes(email)+'\',\''+this.addSlashes(name)+'\');">'+email+'</a>';
				}else
				{
					return '<a href="mailto:'+email+'">'+email+'</a>';
				}
			},
			
			isuserFieldset: function(values){
				if(!GO.util.empty(values['email']) ||
					!GO.util.empty(values['home_phone']) ||
					!GO.util.empty(values['fax']) ||
					!GO.util.empty(values['cellular']) ||
					!GO.util.empty(values['work_phone']) ||
					!GO.util.empty(values['work_fax'])	)
				{
					return true;
				} else {
					return false;
				}
			},
		isPhoneFieldset : function(values)
			{
				if(!GO.util.empty(values['home_phone']) ||
					!GO.util.empty(values['fax']) ||
					!GO.util.empty(values['cellular']) )
				{
					return true;
				} else {
					return false;
				}
			},
			isWorkPhoneFieldset : function(values)
			{
				if(!GO.util.empty(values['work_phone']) ||
					!GO.util.empty(values['work_fax']) )
				{
					return true;
				} else {
					return false;
				}
			},
			isWorkFieldset : function(values)
			{
				if(!GO.util.empty(values['company']) ||
					!GO.util.empty(values['function']) ||
					!GO.util.empty(values['department']))
				{
					return true;
				} else {
					return false;
				}
			},
			GoogleMapsCityStreet : function(values)
			{
				var google_url = 'http://maps.google.com/maps?q=';

				if(!GO.util.empty(values['address']) && !GO.util.empty(values['city']))
				{
					if(!GO.util.empty(values['address_no']))
					{
						return '<a href="' + google_url + values['address'] + '+' + values['address_no'] + '+' + values['city'] + '" target="_blank" >' + values['address'] + ' ' + values['address_no'] + '</a>';
					} else {
						return '<a href="' + google_url + values['address'] + '+' + values['city'] + '" target="_blank" >' + values['address'] + '</a>';
					}
				} else {
					return values['address'] + ' ' + values['address_no'];
				}
			}
		});

		Ext.apply(this.templateConfig, GO.linksTemplateConfig);


		if(GO.files)
		{
			Ext.apply(this.templateConfig, GO.files.filesTemplateConfig);
			this.template += GO.files.filesTemplate;
		}

		if(GO.comments)
		{
			this.template += GO.comments.displayPanelTemplate;
		}


		GO.users.UserPanel.superclass.initComponent.call(this);


		if(GO.documenttemplates)
		{
			this.newOODoc = new GO.documenttemplates.NewOODocumentMenuItem();
			this.newOODoc.on('create', function(){this.reload();}, this);

			this.newMenuButton.menu.add(this.newOODoc);

			GO.documenttemplates.ooTemplatesStore.on('load', function(){
				this.newOODoc.setDisabled(GO.documenttemplates.ooTemplatesStore.getCount() == 0);
			}, this);
		}
	},
	getLinkName : function(){
		return this.data.full_name;
	},
	setData : function(data)
	{
		GO.users.UserPanel.superclass.setData.call(this, data);

		if(GO.documenttemplates && !GO.documenttemplates.ooTemplatesStore.loaded)
			GO.documenttemplates.ooTemplatesStore.load();
	}
});