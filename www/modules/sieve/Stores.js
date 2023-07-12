/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: Stores.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.sieve.cmbFieldStore = new Ext.data.ArrayStore({
	idIndex: 1,
	fields: ['field','value'],
	data: [
	[t("subject", "sieve"), 'Subject'],
	[t("sender", "sieve"), 'From'],
	[t("recipient", "sieve"), 'To'],
	[t("size", "sieve"), 'size'],
	[t("Body", "sieve"), 'body'],
	[t("Spam flag", "sieve"), 'X-Spam-Flag'],
	[t("Current Date", "sieve"), 'currentdate'],
	[t("Custom", "sieve"), 'custom']
	]
});

GO.sieve.cmbOperatorStore = new Ext.data.ArrayStore({
	idIndex: 1,
	fields: ['field', 'value'],
	data:[
	[t("contains", "sieve"), 'contains'],
	[t("doesn't contain", "sieve"), 'notcontains'],
	[t("is", "sieve"), 'is'],
	[t("doesn't equal", "sieve"), 'notis'],
	[t("matches", "sieve"), 'matches'],
	[t("doesn't match", "sieve"), 'notmatches'],
	[t("exists", "sieve"), 'exists'],
	[t("doesn't exist", "sieve"), 'notexists']
	]
});

GO.sieve.cmbBodyOperatorStore = new Ext.data.ArrayStore({
	idIndex: 1,
	fields: ['field', 'value'],
	data:[
	[t("contains", "sieve"), 'contains'],
	[t("doesn't contain", "sieve"), 'notcontains'],
	[t("matches", "sieve"), 'matches'],
	[t("doesn't match", "sieve"), 'notmatches'],
	]
});

GO.sieve.cmbActionStore = new Ext.data.ArrayStore({
	idIndex: 1,
	fields: ['field', 'value'],
	data:[
	[t("Mark message as read", "sieve"), 'set_read'],
  [t("Move email to selected folder", "sieve"), 'fileinto'],
  [t("Copy email to selected folder", "sieve"), 'fileinto_copy'],
	[t("Copy to e-mail", "sieve"), 'redirect_copy'],
	[t("Redirect to", "sieve"), 'redirect'],
	[t("Reply to message", "sieve"), 'vacation'],
	[t("Reject with message", "sieve"), 'reject'],
	[t("Discard", "sieve"), 'discard'],
	[t("Stop", "sieve"), 'stop']
	]
});

GO.sieve.cmbDateOperatorStore = new Ext.data.ArrayStore({
	idIndex: 1,
	fields: ['field', 'value'],
	data:[
	[t("before", "sieve"), 'value-le'], // before
  [t("is", "sieve"), 'is'],					// is
  [t("after", "sieve"), 'value-ge']		// after
	]
});

GO.sieve.cmbUnderOverStore = new Ext.data.ArrayStore({
	idIndex: 1,
	fields: ['field', 'value'],
	data:[
  [t("Under", "sieve"), 'under'],
  [t("Over", "sieve"), 'over']
	]
});
