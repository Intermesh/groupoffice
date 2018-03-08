GO.DialogListeners = function() {
};
GO.DialogListeners.prototype = {

	listeners : {},

	add : function(goDialogId, listener){

		if(!this.listeners[goDialogId])
			this.listeners[goDialogId]=[];

		this.listeners[goDialogId].push(listener);
	},

	apply : function(dialog){
		if(dialog.goDialogId){
			if(this.listeners[dialog.goDialogId]){
				for(var i=0,max=this.listeners[dialog.goDialogId].length;i<max;i++){
					dialog.on(this.listeners[dialog.goDialogId][i]);
				}

				this.listeners[dialog.goDialogId]=[];
			}
		}
	}

}

GO.dialogListeners=new GO.DialogListeners();