go.modules.community.pages.SiteWizard = Ext.extend(go.Wizard, {
	title: t('Create site'),
	height: dp(500),
	initComponent : function() {
		
		//todo: finish button disablen tot store.onLoad (load event) gebeurt. Deze wordt meerdere keren aangeroepen, gebruik niet de toggle methode.
		this.propForm = new go.modules.community.pages.SitePropertiesForm({
			});
		this.shareEntityPanel = new go.form.EntityPanel({
			entityStore: go.Stores.get("Acl"),
			items: [
			    new go.modules.core.core.SharePanel({
				anchor: '100% -' + dp(32),
				hideLabel: true,
				name: "groups",
				title: 'Site permissions'
			})]
			
		});
		this.items = [
			this.propForm,
			this.shareEntityPanel
		]
		go.modules.community.pages.SiteWizard.superclass.initComponent.call(this);
		
	},
	continue : function(){
	    var activeItem = this.getLayout().activeItem;
		
		if(activeItem.isValid && !activeItem.isValid()) {
			return false;
		}
		
		if(activeItem.onContinue && activeItem.onContinue(this) === false) {
			return false;
		}
		if(this.nextItem){
		    this.propForm.submit(this.afterPropertiesSubmit,this);
		}else{
		    if(this.shareEntityPanel.currentId){
			this.shareEntityPanel.submit(this.afterPermissionsSubmit, this);
		}
		}
	},
	
	afterPropertiesSubmit : function(form, succes, aclId){
	    if(succes){
		if(aclId){
		this.shareEntityPanel.currentId = aclId;
		}
		this.setActiveItem(this.nextItem);
	    }
	},
	
	afterPermissionsSubmit : function(form, succes, serverId){
	    if(succes){
		this.close();
	    }
	}
		
		
});

