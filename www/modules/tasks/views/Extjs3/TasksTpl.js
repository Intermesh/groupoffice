GO.tasks.TasksTpl = '<tpl for="[completed_tasks, tasks]">\
	<h5>'+t("Incomplete tasks", "tasks")+'</h5>\
<tpl for=".">\
		<p>\
			<label>{status}</label>\
			<span>{name}</span><div>{taskList}\
			<small>{due_time}</small>\
		</p>\
	</tpl></tpl>\
';
