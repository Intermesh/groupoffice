/* global go, Ext */

go.login.LoginDialog = Ext.extend(go.Window, {

	closable: false,
	resizable: false,
	draggable: false,
	cls: "go-login-dialog",
	
	minWidth : 40,
	minHeight: 40,
	
	initComponent: function() {

		Ext.apply(this,{
			width: dp(480),
			height: dp(260),
			layout:'card',
			title: t("Login required"),
			maximized: false,
			activeItem: 0,
			listeners: {
				scope:this,
				add: function(panel, cmp, index) {
					cmp.on('success', function() {
						this.next();
					}, this);
					
					cmp.on('cancel', function() {
						this.reset();
					}, this);
				}
			}		
		});
		
		
		
		go.login.LoginDialog.superclass.initComponent.call(this);

		this.on('render', function() {
			if(GO.util.isMobileOrTablet())
				this.maximize();
		}, this);

		this.add(this.userNamePanel = new go.login.UsernamePanel());
		for(var i=0; i< go.AuthenticationManager.panels.length; i++){
			this.add(go.AuthenticationManager.panels[i].panel);
		}
	},
	
	next : function(index){

		var current = this.items.findIndex('id', this.getLayout().activeItem.id),
		next = (typeof index !== 'undefined') ? index : current+1;
		
		//skip panel if not required for user		
		//console.log(this.wizard.items.itemAt(next).id);
		if(next > 0) {			
			
			var nextItem = this.items.itemAt(next);

			if(!nextItem) {
				//next is called when authentication is complete and there are no panels left. Should we call next() in this case?
				return;
			}

			while(next < this.items.length && go.AuthenticationManager.userMethods.indexOf(nextItem.id) == -1) {
				next++;
			}
		}
 
		this.getLayout().setActiveItem(next);
		this.focus();
	},
	
	reset : function(){

//		this.welcomeComp.clearWelcomeMessage();
//		this.avatarComp.clearAvatar();
		this.items.each(function(item, index, length){
			item.reset();
		},this);
		this.next(0); 
	},
		
	focus: function() {
		// If it's the username panel then set the username field active
		var i = this.getLayout().activeItem;
		if(i.rendered) {
			i.focus();
		} else
		{
			i.on('render', function() {
				i.focus();
			}, this, {single: true});
		}
		
	}
	
	
});
