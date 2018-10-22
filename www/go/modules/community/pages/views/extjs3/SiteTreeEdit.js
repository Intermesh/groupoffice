go.modules.community.pages.SiteTreeEdit = Ext.extend(Ext.Panel,{
    layout:"fit",
    initComponent : function() {
	this.items = [
	this.myImage1 = new Ext.BoxComponent({
	autoEl: {
        tag: 'img',
        //src: 'http://www.barebooks.com/wp-content/uploads/2013/10/GM05.gif'
	//src: 'https://media.giphy.com/media/LkjlH3rVETgsg/giphy.gif'
	src: 'https://i.gifer.com/4noV.gif'
	}
	})
	]

	

	go.modules.community.pages.SiteTreeEdit.superclass.initComponent.call(this);
    }
    
})