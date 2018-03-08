/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: LinksTemplate.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.linksTemplate =
	'<tpl if="values.links && values.links.length">'+
		'{[this.collapsibleSectionHeader(t("Latest links"), "latestlinks-"+values.panelId, "links")]}'+
		
			'<table class="display-panel" cellpadding="0" cellspacing="0" border="0" id="latestlinks-{panelId}">'+
			'<tr>'+
				'<td colspan="2" class="display-panel-links-header">&nbsp;</td>'+
				'<td style="width: 100%" class="table_header_links">' + t("Name") + '</td>'+
				/*'<td class="table_header_links">' + t("Type") + '</td>'+*/
				'<td class="table_header_links" style="white-space:nowrap">' + t("Modified at") + '</td>'+
			'</tr>'+
			'<tpl if="!links.length">'+
				'<tr><td colspan="4">'+t("No items to display")+'</td></tr>'+
			'</tpl>'+
			'<tpl for="links">'+
				'<tr class="display-panel-link">'+
					//'<td><div class="display-panel-link-icon {iconCls}" ext:qtip="{type}">&nbsp;<sup>{link_count}</sup></div></td>'+
					'<td style="padding-right:0px !important;"><div class="display-panel-link-icon go-model-icon-{[values.model_name.replace(/\\\\/g,"_")]}" ext:qtip="{type}"></div></td>'+
					'<td style="padding-right:0px !important;padding-left:0px !important;"><div class="display-panel-has-links <tpl if="link_count&gt;1">has-links</tpl>"></div></td>'+
					'<td><a href="#link_{[xindex-1]}">{name}</a><tpl if="link_description.length"><br />{link_description}</tpl></td>'+
					'<td style="white-space:nowrap">{mtime}</td>'+
				'</tr>'+
				'<tpl if="description.length">'+
					'<tr class="display-panel-link-description">'+
						'<td colspan="2">&nbsp;</td>'+
						'<td colspan="3">{description}</td>'+
				'</tr>'+
				'</tpl>'+
			'</tpl>'+
			'<tr><td colspan="4"><a class="display-panel-browse" href="#browselinks">'+t("Browse")+'</a>&nbsp;'+
			
			'<tpl if="values.show_all_btn_enabled">'+
				'<a class="display-panel-browse" href="#showalllinks">'+t("Show all")+'</a>'+
			'</tpl>'+
			
			'</td></tr>'+

			'</table>'+
		'</tpl>';
	
GO.linksTemplateConfig = {};
