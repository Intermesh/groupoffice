GO.zpushadmin.deviceStore = new GO.data.JsonStore({
		url: GO.url('zpushadmin/device/store'),		
		root: 'results',
		id: 'id',
		totalProperty:'total',
		fields: ['id','device_id','device_type','remote_addr','can_connect','ctime','mtime','new','username','comment','as_version'],
		remoteSort: true,
		model:"GO\\Zpushadmin\\Model\\Device"
	});
