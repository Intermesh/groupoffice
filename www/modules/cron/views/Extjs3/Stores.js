GO.cron.cronStore = new GO.data.JsonStore({
	url: GO.url('core/cron/store'),		
	root: 'results',
	id: 'id',
	totalProperty:'total',
	fields: ['id','name','active','minutes', 'hours','error', 'monthdays', 'months', 'weekdays','years','job','nextrun','lastrun','completedat'],
	remoteSort: true,
	model:"GO\\Base\\Cron\\CronJob"
});
	
GO.cron.periodStore = new GO.data.JsonStore({
	url: GO.url('core/cron/runBetween'),		
	root: 'results',
	id: 'id',
	totalProperty:'total',
	fields: ['id','name','active','minutes', 'hours', 'monthdays', 'months', 'weekdays','years','job','nextrun','lastrun','completedat'],
	remoteSort: true,
	model:"GO\\Base\\Cron\\CronJob"
});
	
GO.cron.jobStore = new GO.data.JsonStore({
	url: GO.url('core/cron/availableCronCollection'),		
	root: 'results',
	id: 'class',
	totalProperty:'total',
	fields: ['name','class','selection'],
	remoteSort: true
});
