go.modules.community.pages.SiteWizard = Ext.extend(go.Wizard, {
    title: t('Create site'),
    height: dp(500),
    initComponent: function () {
	this.propForm = new go.modules.community.pages.SitePropertiesForm({
	});
	this.shareEntityPanel = new go.form.EntityPanel({
	    entityStore: go.Stores.get("Acl"),
	    items: [
		new go.modules.core.core.SharePanel({
		    anchor: '100% -' + dp(32),
		    hideLabel: true,
		    name: "groups",
		    title: 'Site permissions',

		})]

	});
	this.items = [
	    this.propForm,
	    this.shareEntityPanel
	]
	//enable the finish button once the sharepanel has finished loading.
	this.shareEntityPanel.items.get(0).store.on('load', function () {
	    if (this.shareEntityPanel.items.get(0).store.loaded) {
		this.continueButton.setDisabled(false);
		if (!this.nextItem) {
		    this.continueButton.setText('Finish');
		}
	    }
	}, this);
	go.modules.community.pages.SiteWizard.superclass.initComponent.call(this);

    },
    continue: function () {
	var activeItem = this.getLayout().activeItem;

	if (activeItem.isValid && !activeItem.isValid()) {
	    return false;
	}

	if (activeItem.onContinue && activeItem.onContinue(this) === false) {
	    return false;
	}
	if (this.nextItem && this.propForm.form.isDirty()) {
	    this.propForm.submit(this.afterPropertiesSubmit, this);
	} else {
	    if (this.shareEntityPanel.currentId) {
		//only post if there are changes.
		if (this.shareEntityPanel.getForm().isDirty()) {
		    this.shareEntityPanel.submit(this.afterPermissionsSubmit, this);
		} else {
		    this.close();
		}
	    }
	}
    },
    //set The acl id if it hasnt been set yet and open the sharepanel.
    afterPropertiesSubmit: function (form, succes, aclId) {
	if (succes) {
	    if (aclId) {
		this.shareEntityPanel.currentId = aclId;
		this.setActiveItem(this.nextItem);
	    } else if (this.shareEntityPanel.currentId) {
		this.setActiveItem(this.nextItem);
	    }
	}
    },

    afterPermissionsSubmit: function (form, succes, serverId) {
	if (succes) {
	    this.close();
	}
    },

    setActiveItem: function (item) {
	this.getLayout().setActiveItem(item);
	item = this.getLayout().activeItem;
	var index = this.items.indexOf(item);
	this.nextItem = this.items.itemAt(index + 1);
	this.previousItem = this.items.itemAt(index - 1);
	this.backButton.setDisabled(!this.previousItem);
	this.continueButton.setText(this.nextItem ? t("Continue") : t("Loading..."));
	this.continueButton.setDisabled(!this.nextItem);
	//if the sharepanel has already been loaded in, change the button to finish instead of loading.
	if (this.shareEntityPanel.items.get(0).store.loaded && !this.nextItem) {
	    this.continueButton.setDisabled(false);
	    this.continueButton.setText('Finish');
	}
	this.focus();
    },

});

