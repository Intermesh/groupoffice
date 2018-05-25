/*jsl:option explicit*/
/*jsl:declare document*/
/*
 * https://code.google.com/archive/p/cut-html-string
 * 
This class is used to cut the string which is having html tags. 
It does not count the html tags, it just count the string inside tags and keeps
the tags as it is.

ex: If the string is "welcome to <b>JS World</b> <br> JS is bla". and If we want to cut the string of 12 charaters then output will be "welcome to <b>JS</b>". 

Here while cutting the string it keeps the tags for the cutting string and skip the rest and without distorbing the div structure.

USAGE:
 var obj = new cutString("welcome to <b>JS World</b> <br> JS is",12);
 var newCutString = obj.cut();
*/
function CutString(string,limit){
    // temparary node to parse the html tags in the string
    this.tempDiv = document.createElement('div');
    this.tempDiv.id = "TempNodeForTest";
    this.tempDiv.innerHTML = string;
    // while parsing text no of characters parsed
    this.charCount = 0;
    this.limit = limit;

}
CutString.prototype.cut = function(){
    var newDiv = document.createElement('div');
    this.searchEnd(this.tempDiv, newDiv);
    return newDiv.innerHTML;
};

CutString.prototype.searchEnd = function(parseDiv, newParent){
    var ele;
    var newEle;
    for(var j=0; j< parseDiv.childNodes.length; j++){
	ele = parseDiv.childNodes[j];
	// not text node
	if(ele.nodeType != 3){
	    newEle = ele.cloneNode(true);
	    newParent.appendChild(newEle);
	    if(ele.childNodes.length === 0)
		continue;
	    newEle.innerHTML = '';
	    var res = this.searchEnd(ele,newEle);
	    if(res)
		return res;
	    else{
		continue;
	    }
	}

	// the limit of the char count reached
	if(ele.nodeValue.length + this.charCount >= this.limit){
	    newEle = ele.cloneNode(true);
	    newEle.nodeValue = ele.nodeValue.substr(0, this.limit - this.charCount);
	    newParent.appendChild(newEle);
	    return true;
	}
	newEle = ele.cloneNode(true);
	newParent.appendChild(newEle);
	this.charCount += ele.nodeValue.length;
    }
    return false;
};

function cutHtmlString($string, $limit){
    var output = new CutString($string,$limit);
    return output.cut();
}
