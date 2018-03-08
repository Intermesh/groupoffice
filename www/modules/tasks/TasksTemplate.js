GO.tasks.TaskTemplate =
		'<tpl if="values.tasks && values.tasks.length">'+
		'{[this.collapsibleSectionHeader(GO.tasks.lang.incompleteTasks, "tasks-"+values.panelId, "tasks")]}'+
		
			'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="tasks-{panelId}">'+
			'<tr>'+
				'<td class="table_header_links" width="16px;"></td>'+
				'<td class="table_header_links" width="10px;"></td>'+
				'<td class="table_header_links">' + GO.lang['strName'] + '</a></td>'+
				'<td class="table_header_links" width="110px">' + GO.tasks.lang.status + '</td>'+
				'<td class="table_header_links" width="110px">' + GO.tasks.lang.dueDate + '</td>'+
				'<td class="table_header_links" width="120px">' + GO.tasks.lang.tasklist + '</td>'+
			'</tr>'+
			'<tpl if="!tasks.length">'+
				'<tr><td colspan="4">'+GO.lang.strNoItems+'</td></tr>'+
			'</tpl>'+
			'<tpl for="tasks">'+
				'<tr class="display-panel-link">'+
					'<td style="padding-right:0px !important;" {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}><div class="display-panel-link-icon go-model-icon-GO_Tasks_Model_Task"></div></td>'+					
					'<td style="padding-right:0px !important;padding-left:0px !important;" {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}><div class="display-panel-has-links <tpl if="link_count&gt;1">has-links</tpl>"></div></td>'+
					'<td {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}><a href="#" onclick="GO.linkHandlers[\'GO\\\\\\\\Tasks\\\\\\\\Model\\\\\\\\Task\'].call(this, {id});" <tpl if="completion_time!=\'\'">class="tasks-completed"</tpl><tpl if="typeof is_active !== \'undefined\' && is_active!=\'\'">class="tasks-active"</tpl><tpl if="late!=\'\'">class="tasks-late"</tpl>>{name}</a></td>'+
					'<td {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}>{status}</td>'+
					'<td {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}>{due_time}</td>'+
					'<td {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}>{tasklist_name}</td>'+
				'</tr>'+
				'<tpl if="description!=\'\'">'+
					'<tr class="display-panel-link">'+
						'<td style="padding-right:0px !important;" colspan="1" {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}><div></div></td>'+
						'<td style="padding-right:0px !important;padding-left:0px !important;" colspan="5" {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}><div>{description}</div></td>'+
					'</tr>'+
				'</tpl>'+
			'</tpl>'+
			'</table>'+
		'</tpl>'+
		'<tpl if="values.completed_tasks && values.completed_tasks.length">'+
		'{[this.collapsibleSectionHeader(GO.tasks.lang.completedTasks, "completedTasks-"+values.panelId, "completed_tasks")]}'+
		
			'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="completedTasks-{panelId}">'+
			'<tr>'+
				'<td class="table_header_links" width="16px;"></td>'+
				'<td class="table_header_links" width="10px;"></td>'+
				'<td class="table_header_links">' + GO.lang['strName'] + '</a></td>'+
				'<td class="table_header_links" width="110px">' + GO.tasks.lang.status + '</td>'+
				'<td class="table_header_links" width="110px">' + GO.tasks.lang.dueDate + '</td>'+
				'<td class="table_header_links" width="120px">' + GO.tasks.lang.tasklist + '</td>'+
			'</tr>'+
			'<tpl if="!completed_tasks.length">'+
				'<tr><td colspan="4">'+GO.lang.strNoItems+'</td></tr>'+
			'</tpl>'+
			'<tpl for="completed_tasks">'+
				'<tr class="display-panel-link">'+
					'<td style="padding-right:0px !important;" {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}><div class="display-panel-link-icon go-model-icon-GO_Tasks_Model_Task"></div></td>'+					
					'<td style="padding-right:0px !important;padding-left:0px !important;" {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}><div class="display-panel-has-links <tpl if="link_count&gt;1">has-links</tpl>"></div></td>'+
					'<td {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}><a href="#" onclick="GO.linkHandlers[\'GO\\\\\\\\Tasks\\\\\\\\Model\\\\\\\\Task\'].call(this, {id});" >{name}</a></td>'+
					'<td {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}>{status}</td>'+
					'<td {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}>{due_time}</td>'+
					'<td {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}>{tasklist_name}</td>'+
				'</tr>'+	
				'<tpl if="description!=\'\'">'+
					'<tr class="display-panel-link">'+
						'<td style="padding-right:0px !important;" colspan="1" {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}><div></div></td>'+
						'<td style="padding-right:0px !important;padding-left:0px !important;" colspan="5" {[xindex % 2 === 0 ? "class=\\\"display-panel-link-even\\\"" : ""]}><div>{description}</div></td>'+
					'</tr>'+
				'</tpl>'+
			'</tpl>'+
			'</table>'+
		'</tpl>';