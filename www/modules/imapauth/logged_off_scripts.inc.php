<?php
$conf = str_replace('config.php', 'imapauth.config.php', \GO::config()->get_config_file());
if(file_exists($conf)){
	require_once($conf);

	if(!empty($config[0]['imapauth_combo_domains'])){
		$arr = explode(',',$config[0]['imapauth_combo_domains']);
		$domains = json_encode($arr);
		$default_domain = empty($config[0]['imapauth_default_domain']) ? $arr[0] : $config[0]['imapauth_default_domain'];
	}
}

if(!empty($domains) && !empty($default_domain)){ ?>

<script type='text/javascript'>
Ext.override(GO.dialog.LoginDialog, {
	initComponent : GO.dialog.LoginDialog.prototype.initComponent.createSequence(function(){
		var domains = <?php echo $domains ?>;
		domains.push('');
		var domainData = new Array();
		domainData[0] = ['-', domains[i]]
		for (var i=0; i<domains.length; i++) {
			if (domains[i]!='')
				domainData[i+1] = ['@'+domains[i], '@'+domains[i]];
		}

		var usernameField = this.formPanel.items.get('username');
		var fieldLabel = usernameField.fieldLabel;
		delete usernameField.fieldLabel;
		usernameField.flex=1;

		this.usernameCompositeField = new Ext.form.CompositeField({
			anchor:'100%',
			fieldLabel: fieldLabel,
			items:[
				usernameField,
				{
					flex:1,
					xtype:'combo',
					hideLabel: true,
					triggerAction : 'all',
					editable : false,
					selectOnFocus : true,
					width : 144,
					forceSelection : true,
					mode : 'local',
					value : '@<?php echo $default_domain; ?>',
					hiddenName : 'domain',
					valueField : 'value',
					displayField : 'name',
					store : new Ext.data.SimpleStore({
							fields: ['name', 'value'],
							data: domainData
						})
				}
			]
		})

		this.formPanel.insert(2,this.usernameCompositeField);
	})
});
</script>

<?php }
	?>