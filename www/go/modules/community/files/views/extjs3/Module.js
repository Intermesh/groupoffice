Ext.ns('go.modules.community.files');

go.Modules.register("community", 'files', {
	mainPanel: "go.modules.community.files.MainPanel",
	title: t("Files", "files"),
	entities: ["Node"],
	initModule: function () {
		go.Links.registerLinkToWindow("Node", function() {
			return new go.modules.community.files.FileForm();
		});
	
		// Check if the user is accessing it's files for the first time (No storage is available for the user)
		if(!go.User.storage){
			//Make an empty storage object
			go.User.storage = {
				createdAt:null,
				id:null,
				modifiedAt:null,
				modifiedBy:null,
				ownedBy:null,
				quota:0,
				rootFolderId:null,
				usage:0,
				userId:null
			};
		}
	}
});

go.Router.add(/files\/([\w\-]+)\/([0-9\/]*)/, function(root, path) {
	var mainPanel = GO.mainLayout.openModule('files');
	mainPanel.browser.currentRootNode = root;
	mainPanel.browser.path = [];
	mainPanel.browser.nav(path);
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
		go.Stores.get("Node").set(params, function (options, success, response) {
			if(response.notUpdated){
				console.log(response.notUpdated);
			}
		});
	}	
};

/**
 * 
 * @param array nodes [{id:#int,bookmarked:#boolean},{id:#int,bookmarked:#boolean}]
 * @return {undefined}
 */
go.modules.community.files.removeBookmark = function(nodes){
		
	if(nodes && nodes.length >= 1){
		var params = {
			update:{}
		};
		for(var i=0; i< nodes.length; i++){
			params.update[nodes[i].id] = {bookmarked:false};
		}
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
	
	var blobs = new Array(); // array with relative paths to the files
	Ext.each(nodes, function(node) {

		var blob = {
			humanSize:go.util.humanFileSize(node.size,true),
			extension:go.util.contentTypeClass(node.contentType, node.name),
			blobId:node.blobId
		};
	
		blobs.push(blob);
	});

	GO.email.emailBlobs(blobs);
	
}

go.modules.community.files.move = function(nodes, copy){
	
}