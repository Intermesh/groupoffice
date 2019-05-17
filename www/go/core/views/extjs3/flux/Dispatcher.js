go.flux.Dispatcher = {
	
	receivers: [],
  
	/**
	 * Dispatch a flux action
	 * 
	 * @param {string} type
	 * @param {object} payload
	 */
  dispatch: function(type, payload) {
//	console.log("dispatching '" + type + "'", payload);	
   for(var i = 0, l = this.receivers.length; i < l; i++) {		 
      this.receivers[i].receive.call(this.receivers[i].scope || this.receivers[i], {
				type: type,
				payload: payload
			});
   }	 
  },
	
	/**
	 * Register an object that needs to receive actions.
	 * It can be any object that implements the receive(action) method.
	 * 
	 * @param {object} receiver
	 */
  register: function(receiver) {
		
		if(typeof receiver.receive != "function") {
			throw "The object registered at the dispatcher must implement the receive() method";
		}
		
    this.receivers.push(receiver);
		
		receiver.on('destroy', function(cmp) {
			this.unregister(cmp);
		}, this);
  },
	
	unregister : function(receiver) {
		for(var i = 0, l = this.receivers.length; i < l; i++) {
      if(this.receivers[i] == receiver) {
				this.receivers.splice(i, 1);				
				break;
			}
   };
	}
};


