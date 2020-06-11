GO.data.GroupingStore = function(config) {

	if((config.url || config.api) && !config.proxy){
		config.proxy = new GO.data.PrefetchProxy({url: config.url, api: config.api, fields: config.fields ? config.field : config.reader.meta.fields});
	}

	GO.data.GroupingStore.superclass.constructor.call (this, config);
	
	this.on('load', function(){
		this.loaded=true;
		
		if(!this.suppressError && this.reader.jsonData.feedback){
			GO.errorDialog.show(this.reader.jsonData.feedback);
		}
		
	}, this);

	this.on('exception',
		function( store, type, action, options, response){

			if(response.isAbort || this.suppressError) {
				//ignore aborts.
			} else if(response.isTimeout){
				console.error(response);

				GO.errorDialog.show(t("The request timed out. The server took too long to respond. Please try again."));
			}else	if(!this.reader.jsonData || GO.jsonAuthHandler(this.reader.jsonData, this.load, this))
			{
				var msg;

				if(!GO.errorDialog.isVisible()){
					if(this.reader.jsonData && this.reader.jsonData.feedback)
					{
						msg = this.reader.jsonData.feedback;
						GO.errorDialog.show(msg);
					}else
					{
						msg = t("An error occurred on the webserver. Contact your system administrator and supply the detailed error.");
						msg += '<br /><br />JsonStore load exception occurred';
						GO.errorDialog.show(msg);
					}
				}
			}else
			{
				console.error(response);

				GO.errorDialog.show(t("Failed to send the request to the server. Please check your internet connection."));
			}
		}
		,this);
};

Ext.extend(GO.data.GroupingStore, Ext.data.GroupingStore, {
	loaded : false
	
});
	
