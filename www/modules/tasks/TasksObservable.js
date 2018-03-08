GO.tasks.TasksObservable = function(){
	GO.tasks.TasksObservable.superclass.constructor.call(this);

	this.addEvents({
		'save':true
	})
}
Ext.extend(GO.tasks.TasksObservable, Ext.util.Observable);

GO.tasks.tasksObservable = new GO.tasks.TasksObservable();