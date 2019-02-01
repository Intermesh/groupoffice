go.util.parseSearchQuery = function (string, defaultFilter) {
	var data = {};
	
	//Simple text check
	if(string.indexOf(':') === -1) {
		data[defaultFilter] = [];
		data[defaultFilter].push(string);
		return data;
	}
	
	var stripBackSlash = function(val) {
	// Strip backslashes respecting escapes
  return  (val + '').replace(/\\(.?)/g, function (s, n1) {
          switch (n1) {
          case '\\':
            return '\\';
          case '0':
            return '\u0000';
          case '':
            return '';
          default:
            return n1;
          }
        });
	};

	var regex = /(\S+:'(?:[^'\\]|\\.)*')|(\S+:"(?:[^"\\]|\\.)*")|(-?"(?:[^"\\]|\\.)*")|(-?'(?:[^'\\]|\\.)*')|\S+|\S+:\S+/g;
	var match, token, currentKey = defaultFilter, val;
	while ((match = regex.exec(string)) !== null) {		
//		console.log(match);
		token = match[0];
		var semiColPos = token.indexOf(':');
		
		if(semiColPos != -1) {
			currentKey = token.substring(0, semiColPos);
			
			val = token.substring(semiColPos + 1, token.length);
//			console.log(currentKey, val);
		} else
		{
			val = token;
//			console.log(val);
		}
		
		//strip surrounding quotes
		if(val) {
			val = val.replace(/^\"|\"$|^\'|\'$/g, '');
			val = stripBackSlash(val);
			
			if(!data[currentKey]) {
				data[currentKey] = [];
			}
			
			data[currentKey].push(val);
			
			currentKey = defaultFilter;
		}		
	}
	
//	console.log(data);
	
	return data;
};



