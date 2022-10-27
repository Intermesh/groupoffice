'use strict';

go.util.QuoteStripper = function (body) {
	/**
	 * @type string
	 */
	this.body = body;
	this.bodyWithoutQuote = null;
	this.quote = null;
}

go.util.QuoteStripper.prototype.getBodyWithoutQuote = function () {
	if (this.quote === null) {
		this._split();
	}

	return this.bodyWithoutQuote;
};

go.util.QuoteStripper.prototype.getQuote = function () {
	if (this.quote === null) {
		this._split();
	}

	return this.quote;
};

go.util.QuoteStripper.prototype._split = function () {
	var quoteIndex = this._findByBlockQuote();

	if (quoteIndex === -1) {
		quoteIndex = this._findByGreaterThan();
	}

	if (quoteIndex === -1) {
		quoteIndex = this._findQuoteByHeaderBlock();
	}

	if (quoteIndex > -1) {
		this.bodyWithoutQuote = this.body.substring(0, quoteIndex);
		this.quote = this.body.substring(quoteIndex);
	} else {
		this.bodyWithoutQuote = this.body;
		this.quote = "";
	}
};

go.util.QuoteStripper.prototype._findByGreaterThan = function () {
	var pattern = /\n&gt;/;

	var match = pattern.exec(this.body);

	if (match) {
		return pattern.lastIndex;
	}

	return -1;
};

go.util.QuoteStripper.prototype._findByBlockQuote = function () {
	this.quoteIndex = this.body.indexOf("<blockquote");

	return this.quoteIndex;
};

go.util.QuoteStripper.prototype._splitLines = function () {
	if (!this.lines) {
		var br = '|BR|';

		var html = this.body
			.replace(/<\/p>/ig, br + "$&")
			.replace(/<\/div>/ig, br + "$&")
			.replace(/<br[^>]*>/ig, br + "$&");

		this.lines = html.split(br);
	}
	return this.lines;
};


/**
 * eg
 *
 * Van: Merijn Schering [mailto:mschering@intermesh.nl]
	  Verzonden: donderdag 20 november 2014 16:40
	  Aan: Someone
	  Onderwerp: Subject
 *
 * @return int|boolean
 */
go.util.QuoteStripper.prototype._findQuoteByHeaderBlock = function() {

	var lines = this._splitLines(this.body);

	var pos = 0;

	for (var i = 0, c = lines.length; i < c; i++) {

		var plain = lines[i].replace(/(<([^>]+)>)/ig,""); //strip html tags
		var pattern = /[a-z]+:\s*[a-z0-9\._\-+\&]+@[a-z0-9\.\-_]+/i;
		//Match:
		//ABC: email@domain.com
		if (plain.match(pattern)) {
			return pos;
		}

		pos += lines[i].length;
	}
	return -1;
};