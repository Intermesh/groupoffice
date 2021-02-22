go.cron.cronStore = new GO.data.JsonStore({
	url: GO.url('core/cron/store'),		
	root: 'results',
	id: 'id',
	totalProperty:'total',
	fields: ['id','name','active','expression', 'hours','error', 'monthdays', 'months', 'weekdays','years','job','nextrun','lastrun','completedat'],
	remoteSort: false,
	model:"GO\\Base\\Cron\\CronJob"
});
	
go.cron.periodStore = new GO.data.JsonStore({
	url: GO.url('core/cron/runBetween'),		
	root: 'results',
	id: 'id',
	totalProperty:'total',
	fields: ['id','name','active','minutes', 'hours', 'monthdays', 'months', 'weekdays','years','job','nextrun','lastrun','completedat'],
	remoteSort: true,
	model:"GO\\Base\\Cron\\CronJob"
});
	
go.cron.jobStore = new GO.data.JsonStore({
	url: GO.url('core/cron/availableCronCollection'),		
	root: 'results',
	id: 'class',
	totalProperty:'total',
	fields: ['name','class','selection'],
	remoteSort: true
});
