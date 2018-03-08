/*
 * Needed for tinyMCE to load in compressed mode. ($config['debug']=false).
 * Otherwise it can't determine where the tiny_mce resources are.
 *
 * TinyMCE must be loaded with three scripts in this order:
 *
 * javascript/form/TinyMCEpreInit.js
 * javascript/tiny_mce/tiny_mce_src.js
 * javascript/form/TinyMCE.js
 * 
 */

tinyMCEPreInit={
	base:document.location.protocol+'//'+document.location.host+BaseHref+'modules/cms/plugins/tiny_mce',
	suffix:'_src',
	query:''
};