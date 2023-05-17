
// see http://jscompress.com/ to compress

/*





ALERT!!!!!

jscompress breaks this.

Use https://javascript-minifier.com/




*/

var findtop = {};
(function() {})();

findtop.Reference = function () {

  var
    _topic = 'none',
    args = arguments;
  if(!args[0]){
    return undefined;
  }else{
    _topic = trim(args[0]);
    while(_topic.indexOf('  ',0) > -1){
      _topic = _topic.replace('  ',' ');
    }
    _topic = _topic.replace(/ /g, '_');

  }

  return {
    bookIndex: _topic,

    isValid: function () {
      return (_topic != 'none');
    },

    toString: function () {
      return 'see topic on "'+this.bookIndex+'"';
    },

    toShortUrl: function () {
      return '/topic/'+this.bookIndex; //+((prfcommnewtab==1)?'/ct':'/bb');
    }
  }
};

// adapted from old scripturizer.js code

(function() {

  /* handler for when a topic is found */
  var
    createBiblyLinks = function(newNode, reference) {
        newNode.setAttribute('href', reference.toShortUrl());
        newNode.setAttribute('title', 'Click to ' + reference.toString());
        if(prfcommnewtab) newNode.setAttribute('target', '_self');
        newNode.setAttribute('rel', reference.toString());
        newNode.setAttribute('class', findtopics.className);
    };

  /* core findtopics functions */
  var findtopics = {
      maxNodes: 500,
      autoStart: true,
      startNodeId: '',
      className: 'findvers_reference',
      ignoreClassName: 'bibly_ignore',
      ignoreTags: ['h1','h2','h3','h4'],
      newTagName: 'A',
      handleLinks: createBiblyLinks
    },
    win = window,
    doc = document,
    body = null,

    // rsw regex
    //regexPattern =  '(?:word (?:study|studies) on )((?:(?:, | | and |, and )?\u201C([A-Za-z \\-]+)(?:(?:\\.|,|;)?\u201D))+)',

    // messing with diacritics
    //regexPattern =  '(?:word (?:study|studies) on )((?:(?:, | | and |, and )?\u201C([^\u201D]+)(?:(?:\\.|,|;)?\u201D))+)',

    // removing non-grouping indicators
    //regexPattern =  '(word (study|studies) on )(((, | | and |, and )?\u201C([^\u201D]+)((\\.|,|;)?\u201D))+)',


    regexPattern =  '((topic|topics) (on|of)( the)? )(((, | | and |, and )?\u201C([^\u201D]+)((\\.|,|;)?\u201D))+)',

    referenceRegex = new RegExp(regexPattern, 'mi'),
    alwaysSkipTags = ['a','script','style','textarea'],
    textHandler = function(node) {
      var match = referenceRegex.exec(node.data),
        val,
        referenceNode,
        afterReferenceNode,
        newLink;

      if (match) {
        val = match[0];
        //alert(val);
        // see https://developer.mozilla.org/en/DOM/text.splitText
        // split into three parts [node=before][referenceNode][afterReferenceNode]
        referenceNode = node.splitText(match.index);
        afterReferenceNode = referenceNode.splitText(val.length);

        // send the matched text down the line
        newLink = createLinksFromNode(node, referenceNode);

        return newLink;
      } else {
        return node;
      }
    },
    createLinksFromNode = function(node, referenceNode) {
      if (referenceNode.nodeValue == null) return referenceNode;

      var
        commaIndex = referenceNode.nodeValue.indexOf('\u201D,'),
        semiColonIndex = referenceNode.nodeValue.indexOf('\u201D;'),
        andIndex = referenceNode.nodeValue.indexOf('\u201D and'),
        commaandIndex = referenceNode.nodeValue.indexOf('\u201D, and'),
        spaceIndex = referenceNode.nodeValue.indexOf('\u201D '),
        separatorIndex,
        separator,
        remainder,
        reference,
        refText,
        tmpNode;

      separatorIndex = Math.min(((commaIndex<0)?999:commaIndex), ((semiColonIndex<0)?999:semiColonIndex), ((andIndex<0)?999:andIndex), ((commaandIndex<0)?999:commaandIndex), ((spaceIndex<0)?999:spaceIndex));
      if(separatorIndex==999) separatorIndex=-1;

      // if there is a separator (,|;|and) then split up into three parts [node][separator][after]
      if (separatorIndex > 0) {
        separatorIndex++;
        separator = referenceNode.splitText(separatorIndex);
        remainder = separator.splitText(separator.nodeValue.indexOf('\u201C'));
      }

      // strip off junk
      referenceNode = referenceNode.splitText(referenceNode.nodeValue.indexOf('\u201C')+1);
      tmpNode = referenceNode.splitText(referenceNode.nodeValue.indexOf('\u201D'));
      // ok. now we're left with whatever was between the smart quotes.
      // need to check for ending punctuation
      refText = referenceNode.nodeValue;
      tmp = Math.max(refText.indexOf('.'), refText.indexOf(','), refText.indexOf(';'));
      if(tmp>0){
        tmpNode = referenceNode.splitText(tmp);
        refText = referenceNode.nodeValue;
      }
      //alert('refText='+refText);
      reference = parseRefText(refText);
      if (typeof reference != 'undefined') {
        // replace the referenceNode TEXT with an anchor node
        newNode = node.ownerDocument.createElement(findtopics.newTagName);
        node.parentNode.replaceChild(newNode, referenceNode);
        findtopics.handleLinks(newNode, reference);
        newNode.appendChild(referenceNode);
        // if there was a separator, now parse the stuff after it
        //alert(remainder);
        if (remainder) {
          newNode = createLinksFromNode(node, remainder);
        }
        return newNode;
      } else {
        // for false matches, return it unchanged
        return referenceNode;
      }
    },
    parseRefText = function(refText) { // rsw
      var text = refText,
          reference = new findtop.Reference(text);
      if (reference != null && typeof reference.isValid != 'undefined' && reference.isValid()) {
      //if (reference != null) {
        return reference;

      } else {
        return undefined;
      }
    },

    scanForReferences = function(node) {
      // build doc
      traverseDOM(node.childNodes[0], 1, textHandler);
    },
    traverseDOM = function(node, depth, textHandler) {
      var count = 0,
        //skipRegex = /^(a|script|style|textarea)$/i,
        skipRegex = new RegExp('^(' + alwaysSkipTags.concat(findtopics.ignoreTags).join('|') + ')$', 'i');


      while (node && depth > 0) {
        count++;
        if (count >= findtopics.maxNodes) {
          setTimeout(function() { traverseDOM(node, depth, textHandler); }, 50);
          return;
        }

        switch (node.nodeType) {
          case 1: // ELEMENT_NODE
            if (!skipRegex.test(node.tagName.toLowerCase()) && node.childNodes.length > 0 && (findtopics.ignoreClassName == '' || node.className.toString().indexOf(findtopics.ignoreClassName) == -1)) {
              node = node.childNodes[0];
              depth ++;
              continue;
            }
            break;
          case 3: // TEXT_NODE
          case 4: // CDATA_SECTION_NODE
            node = textHandler(node);
            break;
        }

        if (node.nextSibling) {
          node = node.nextSibling;
        } else {
          while (depth > 0) {
            node = node.parentNode;
            depth --;
            if (node.nextSibling) {
              node = node.nextSibling;
              break;
            }
          }
        }
      }
    },
    addEvent = function(obj,name,fxn) {
      if (obj.attachEvent) {
        obj.attachEvent('on' + name, fxn);
      } else if (obj.addEventListener) {
        obj.addEventListener(name, fxn, false);
      } else {
        var __ = obj['on' + name];
        obj['on' + name] = function() {
           fxn();
          __();
        };
      }
    },
    isStarted = false,
    extend = function() {
      // borrowed from ender
      var options, name, src, copy,
        target = arguments[0] || {},
        i = 1,
        length = arguments.length;

      // Handle case when target is a string or something (possible in deep copy)
      if ( typeof target !== "object" && typeof target !== "function" ) {
        target = {};
      }

      for ( ; i < length; i++ ) {
        // Only deal with non-null/undefined values
        if ( (options = arguments[ i ]) != null ) {
          // Extend the base object
          for ( name in options ) {
            src = target[ name ];
            copy = options[ name ];

            // Prevent never-ending loop
            if ( target === copy ) {
              continue;
            }

            if ( copy !== undefined ) {
              target[ name ] = copy;
            }
          }
        }
      }

      // Return the modified object
      return target;
    },
    scan=function() {
      if (isStarted)return;
      isStarted = true;
      doc = document;
      body = doc.body;
      if (findtopics.startNodeId != '') {
        node = doc.getElementById(findtopics.startNodeId);
      }
      //alert(node);
      if (node != null) scanForReferences(node);
    };

  // super cheater version of DOMoade
  // do whatever happens first
  //addEvent(doc,'DOMContentLoaded',startBibly);
  //addEvent(win,'load',startBibly);

  if (typeof window.findtopics != 'undefined')
    findtopics = extend(findtopics, window.findtopics);

  // export
  findtopics.scan = scan;
  findtopics.scanForReferences = scanForReferences;
  win.findtopics = findtopics;
})();
