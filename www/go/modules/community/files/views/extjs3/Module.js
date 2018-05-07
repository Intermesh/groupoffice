Ext.ns('go.modules.community.files');

go.Modules.register("community", 'files', {
	mainPanel: "go.modules.community.files.MainPanel",
	title: t("Files", "files"),
	entities: ["Node"],
	initModule: function () {
		go.Links.registerLinkToWindow("Node", function() {
			return new go.modules.community.files.FileForm();
		});
	}
});

go.Preview = function(file) {
	if(!this.preview) {
		this.preview = new go.modules.community.files.PreviewLayer();
	}
	this.preview.show(file);
}

// ### GLOBAL FUNCTIONS ###

/**
 * 
 * @param array nodes [{id:#int,bookmarked:#boolean},{id:#int,bookmarked:#boolean}]
 * @return {undefined}
 */
go.modules.community.files.bookmark = function(nodes){
		
	if(nodes && nodes.length >= 1){
		
		var params = {
			update:{}
		};
		
		for(var i=0; i< nodes.length; i++){
			params.update[nodes[i].id] = {bookmarked:true};
		}
		
		console.log(params);
		
		go.Stores.get("Node").set(params, function (options, success, response) {
			
			if(response.notUpdated){
				console.log(response.notUpdated);
			}
		});	
	}	
};

go.modules.community.files.lock = function(nodes){
	
}

go.modules.community.files.email = function(nodes){
	
}

go.modules.community.files.move = function(nodes, copy){
	
}