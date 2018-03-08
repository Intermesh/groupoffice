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
						'<td colspan="2" class="display-panel-heading"><b>'+t("Username")+': {username}</b></td>'+
					'</tr>'+

					'<tr>'+

						// PERSONAL DETAILS+ 1e KOLOM
						'<td valign="top">'+
							'<table cellpadding="0" cellspacing="0" border="0">'+
								//NAME

								'<tr>'+
									'<td>' + t("Name") + ':</td><td> {name}</td>'+
								'</tr>'+
								'<tr>'+
									'<td>' + t("Username") + ':</td><td> {username}</td>'+
								'</tr>'+
								'<tr>'+
									'<td>' + t("E-mail") + ':</td><td> {email}</td>'+
								'</tr>'+
								
								'<tpl if="contact_id"><tr>'+
									'<td colspan="2"><a href="#addressbook/contact/{contact_id}">'+t("Open contact", "users")+'</a></td></tr></tpl>'+
								
							'</table>'+
							
							
							
						'</td>'+
					'</tr>'+
				'</table>';

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
					return '<a  onclick="GO.email.showAddressMenu(event, \''+this.addSlashes(email)+'\',\''+this.addSlashes(name)+'\');">'+email+'</a>';
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




		GO.users.UserPanel.superclass.initComponent.call(this);

	},
	getLinkName : function(){
		return this.data.full_name;
	}
});