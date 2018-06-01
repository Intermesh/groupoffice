

/**
 * Open the given model inside Group-Office
 * 
 * @param string modelTypeAndKey Example: "GO\Projects2\Model\Project:2"
 * @returns string url The url to the model
 */
GO.links.openModelLink = function(modelTypeAndKey){
	
	// modelTypeAndKey = "GO\Projects2\Model\Project:2"
	var parts = modelTypeAndKey.split(":");
	
	GO.linkHandlers[parts[0]].call(this, parts[1]);
};
