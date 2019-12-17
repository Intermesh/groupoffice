/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: UnknownRecipientsDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 		
GO.email.UnknownRecipientsDialog = Ext.extend(Ext.Window, {
	
	initComponent : function(){
		this.store = new GO.data.JsonStore({
			root: 'recipients',
			fields:['email','name', 'first_name', 'middle_name', 'last_name']
		});


		var action = new Ext.ux.grid.RowActions({
			header:'',
			hideMode:'display',
			keepSelection:true,
			actions:[{
				iconCls:'btn-add',
				qtip:t("Add")
			},{
				iconCls:'btn-edit',
				qtip:t("Edit")
			}],
			width: 50
		});

		action.on({
			action:function(grid, record, action, row, col) {

				var email = record.data.email;
				var tldi = email.lastIndexOf('.');
				if(tldi)
				{
					var tld = email.substring(tldi+1, email.length).toUpperCase();
					if(t("countries")[tld])
					{
						record.data.country=tld;
					}
				}
	
				if(action == 'btn-add')
				{
                    this.addContactToAddresslistAtSaveContactEvent = true;
                    var dialog = new go.modules.community.addressbook.ContactDialog();
                    var firstName = record.data.first_name;
                    var middleName = record.data.middle_name;
                    var lastName = record.data.last_name;
                    var email = record.data.email;
                    dialog.show();
                    dialog.setDialogValues(firstName, middleName, lastName, email);
				}else
				{
                    this.email = record.data.email;

                    var me = this;
                    var select = new go.util.SelectDialog({
                        entities: ['Contact'],
                        query: me.personal,
                        mode: 'id',				
                        singleSelect: true,
                        selectMultiple: function (ids) {
                            var dlg = new go.modules.community.addressbook.ContactDialog();
                            dlg.on("load", function() {

                                var a = dlg.formPanel.entity.emailAddresses;
                                a.push({
                                    type: "work",
                                    email: me.email
                                });
                                dlg.setValues({
                                    emailAddresses: a
                                });
                            });
                            dlg.load(ids[0]).show();
                        }					
                    });
                    select.show();

					// if(!GO.email.findContactDialog)
					// {
					// 	GO.email.findContactDialog = new GO.email.FindContactDialog();
						
					// 	if (!GO.util.empty(this.addresslistId)) {
					// 			GO.email.findContactDialog.on('email_merged', function(contactId) {
					// 			if (this.addresslistId>0) {
					// 				this.addToAddressbook(contactId,this.addresslistId);
					// 			} else {
					// 				alert(t("This panel expects an address list ID that is positive, but did not receive one. Please contact the administrator.", "addressbook"));
					// 			}
					// 			}, this);
					// 	}
					// }
		     
					// GO.email.findContactDialog.show(record.data);
				}

				var store = grid.getStore();
				store.remove(record);

				if(store.getCount()==0)
				{
					this.hide();
				}
			},
			scope:this
		});
				
		this.grid = new GO.grid.GridPanel({
			store: this.store,
			plugins:action,
			border:false,
			region:'center',
			loadMask:true,
			columns : [{
				header : t("Name"),
				dataIndex : 'name'
			}, {
				header : t("E-mail"),
				dataIndex : 'email'
			},
			action],
			sm : new Ext.grid.RowSelectionModel({
				singleSelect : false
			}),
			view : new Ext.grid.GridView({
				forceFit : true,
				autoFill : true
			})
		});

		var items = [
			this.descriptionTextPanel = new Ext.Panel({
				border: false,
				region:'north',
				html: this.descriptionText  ? this.descriptionText : t("You just sent an e-mail to one or more recipients that are not in your addressbook. Click on a name if you want to add that person or close this window.", "email"),
				cls:'go-form-panel'
			}),
			this.grid
		];
		
		if (GO.util.empty(this.disableSkipUnknownCheckbox)) {
			items.push(new Ext.Panel({
				border: false,
				items: this.skipUnknownRecipients = new Ext.form.Checkbox({
					boxLabel:t("Don't show this window next time", "email"),
					hideLabel:true,
					checked:false,
					name:'skip_unknown_recipients',
					listeners : {
						check : function(field, checked)
						{
                            //GO.email.skipUnknownRecipients = checked;
                            go.Db.store("User").single(go.User.id).then(function(user) {                            
                                update = {};
                                update[go.User.id] = {
                                    emailSettings: {
                                        skip_unknown_recipients: checked
                                    }
                                }

                                go.Db.store("User").set({
                                    update: update
                                });
                            })
						},
						scope : this
					}
				}),
				region:'south',
				autoHeight:true,
				cls:'go-form-panel'
			}))
		}
		
		this.title= !GO.util.empty(this.title) ? this.title : t("Add unknown recipients", "email");
		this.layout='fit';
		this.modal=false;
		this.height=400;
		this.width=600;
		this.closable=true;
		this.closeAction='hide';
		this.items= new Ext.Panel({
			autoScroll:true,
			layout:'border',
			items: items
		});	
		
		GO.email.UnknownRecipientsDialog.superclass.initComponent.call(this);
		
	},
	
	addToAddressbook : function(contactId,addresslistId) {
		Ext.Ajax.request({
			url: GO.url('addressbook/addresslist/addContactsToAddresslist'),
			params: {
				contactIds: Ext.encode(new Array(contactId)),
				addresslistId : addresslistId
			}
//			,
//			callback: function(options, success, response)
//			{
//
//			}
		});
	}
	
});
