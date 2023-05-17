// see http://jscompress.com/ to compress

var buks = 'Genesis|Exodus|Leviticus|Numbers|Deuteronomy|Joshua|Judges|Ruth|1 Samuel|2 Samuel|1 Kings|2 Kings|1 Chronicles|2 Chronicles|Ezra|'+
           'Nehemiah|Esther|Job|Psalms|Proverbs|Ecclesiastes|Song of Songs|Isaiah|Jeremiah|Lamentations|Ezekiel|Daniel|Hosea|Joel|Amos|Obadiah|Jonah|Micah|'+
           'Nahum|Habakkuk|Zephaniah|Haggai|Zechariah|Malachi|'+
           'Matthew|Mark|Luke|John|Acts|Romans|1 Corinthians|2 Corinthians|Galatians|Ephesians|Philippians|Colossians|1 Thessalonians|'+
           '2 Thessalonians|1 Timothy|2 Timothy|Titus|Philemon|Hebrews|James|1 Peter|2 Peter|1 John|2 John|3 John|Jude|Revelation';
var arbuks = buks.split('|');

var findbcom = {};
findbcom.parseReference = function (textReference) {

  var
    bookIndex = -1,
    input = new String(textReference).replace('&ndash;','-').replace('–','-').replace(/(\d+[\.:])\s+(\d+)/gi, '$1$2'),
    i, j, il, jl,
    possibleMatch = null;

  // take the entire reference (John 1:1 or 1 Cor) and move backwards until we find a letter or space
  for (i=input.length; i>=0; i--) {
    if (/[A-Za-z]/.test(input.substring(i-1,i))) {
      possibleMatch = input.substring(0,i).toLowerCase();
      break;
    }
  }

  if (possibleMatch != null) {

    // remove 'commentary on' or 'note on'
    if(possibleMatch.indexOf('rev commentary on the book of ')==0)
      possibleMatch = possibleMatch.slice(30);
    if(possibleMatch.indexOf('commentary on the book of ')==0)
      possibleMatch = possibleMatch.slice(26);
    if(possibleMatch.indexOf('rev commentary on ')==0)
      possibleMatch = possibleMatch.slice(18);
    if(possibleMatch.indexOf('book commentary on ')==0)
      possibleMatch = possibleMatch.slice(19);
    //if(possibleMatch.indexOf('commentary on ')==0)
    //  possibleMatch = possibleMatch.slice(14);
    if(possibleMatch.indexOf('rev introduction to the book of ')==0)
      possibleMatch = possibleMatch.slice(32);
    if(possibleMatch.indexOf('introduction to the book of ')==0)
      possibleMatch = possibleMatch.slice(28);
    if(possibleMatch.indexOf('rev introduction to ')==0)
      possibleMatch = possibleMatch.slice(20);
    if(possibleMatch.indexOf('book introduction to ')==0)
      possibleMatch = possibleMatch.slice(21);
    if(possibleMatch.indexOf('introduction to ')==0)
      possibleMatch = possibleMatch.slice(16);
    // go through all books and test all names
    for (i = 0, il = arbuks.length ; i < il && bookIndex == -1; i++) {
      name = new String(arbuks[i]).toLowerCase();
      if (possibleMatch == name) {
        bookIndex = i;
        input = input.substring(name.length);
        break;
      }
    }
  }
  // finalize
  return findbcom.Reference(bookIndex);

}

findbcom.Reference = function () {

  var args = arguments;

  if (args.length == 0) {
    // error
  } else if (args.length == 1 && typeof args[0] == 'string') { // a string that needs to be parsed
    return findbcom.parseReference(args[0]);
  }else {
    _bookIndex = args[0];
  }
  return {
    bookIndex: _bookIndex,

    isValid: function () {
      return (_bookIndex > -1 && _bookIndex < arbuks.length);
    },
    toString: function () {
      if (this.bookIndex < 0 || this.bookIndex >= arbuks.length) return "invalid";

      return arbuks[this.bookIndex];
    },
    toShortUrl: function () {
      if (this.bookIndex < 0 || this.bookIndex >= arbuks.length) return "invalid";
      return '/book/'+arbuks[this.bookIndex].replace(' ','-');
    }
  }
};

// adapted from old scripturizer.js code

(function() {

  /* Scripture API methods */
  var
    callbackIndex = 100000,
    jsonpCache = {},
    jsonp = function(url, callback){

      var jsonpName = 'callback' + (callbackIndex++),
        script = doc.createElement("script");

      win[jsonpName] = function(d) {
        callback(d);
      }
      jsonpCache[url] = jsonpName;

      url += (url.indexOf("?") > -1 ? '&' : '?') + 'callback=' + jsonpName;
      script.setAttribute("src",url);
      script.setAttribute("type","text/javascript");
      body.appendChild(script);
    };

  /* handler for when a verse node is found */
  var
    createFindBComLinks = function(newNode, reference) {
        newNode.setAttribute('href', reference.toShortUrl());
        if(prfcommnewtab==1) newNode.setAttribute('target', '_blank');
        newNode.setAttribute('title', 'Click to read commentary on ' + reference.toString());
    };


  /* core findbcom functions */
  var findbcom = {
      maxNodes: 500,
      startNodeId: '',
      ignoreTags: ['h1','h2','h3','h4'],
      newTagName: 'A',
      handleLinks: createFindBComLinks
    },
    win = window,
    doc = document,
    body = null,
    // original
    // regexPattern =  '\\b(?:(?:REV|book) commentary on )(?:'+buks+')\\b',
    //regexPattern =  '\\b(?:REV |book )?(commentary on |introduction to )(?:the book of )?(?:'+buks+')\\b',
    regexPattern =  '\\b(?:REV |book )(commentary on |introduction to )(?:the book of )?(?:'+buks+')\\b',
    referenceRegex = new RegExp(regexPattern, 'mi'),
    alwaysSkipTags = ['a','script','style','textarea'],
    textHandler = function(node) {
       //alert('>>'+node.data+'<<');
       //alert('fire');
       // why does this fire ~30 times?
       var match = referenceRegex.exec(node.data),
        val,
        referenceNode,
        afterReferenceNode,
        newLink;
       //alert(regexPattern);

      if (match) {
        //alert('match='+match);

        val = match[0];
        // see https://developer.mozilla.org/en/DOM/text.splitText
        // split into three parts [node=before][referenceNode][afterReferenceNode]
        referenceNode = node.splitText(match.index);
        afterReferenceNode = referenceNode.splitText(val.length);

        // send the matched text down the
        newLink = createLinksFromNode(node, referenceNode);

        return newLink;
      } else {
        return node;
      }
    },
    createLinksFromNode = function(node, referenceNode) {
      if (referenceNode.nodeValue == null) return referenceNode;

      var refText, reference;

      // test if the text matches a real reference
      refText = referenceNode.nodeValue;
      reference = parseRefText(refText);
      if (typeof reference != 'undefined' && reference.isValid()) {

        // replace the referenceNode TEXT with an anchor
        newNode = node.ownerDocument.createElement(findbcom.newTagName);
        node.parentNode.replaceChild(newNode, referenceNode);

        // this can be overridden for other systems
        findbcom.handleLinks(newNode, reference);

        newNode.appendChild(referenceNode);

        return newNode;
      } else {
        // for false matches, return it unchanged
        return referenceNode;
      }
    },
    parseRefText = function(refText) {

      var
        text = refText,
        reference = new findbcom.Reference(text);

      if (reference != null && typeof reference.isValid != 'undefined' && reference.isValid()) {
        return reference;
      } else return undefined;
    },

    scanForReferences = function(node) {
      // build doc
      traverseDOM(node.childNodes[0], 1, textHandler);
    },
    traverseDOM = function(node, depth, textHandler) {
      var count = 0,
        //skipRegex = /^(a|script|style|textarea)$/i,
        skipRegex = new RegExp('^(' + alwaysSkipTags.concat(findbcom.ignoreTags).join('|') + ')$', 'i');


      while (node && depth>0) {
        count++;
        if (count >= findbcom.maxNodes) {
          setTimeout(function() { traverseDOM(node, depth, textHandler); }, 50);
          return;
        }

        switch (node.nodeType) {
          case 1: // ELEMENT_NODE
            if (!skipRegex.test(node.tagName.toLowerCase()) && node.childNodes.length > 0) {
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
      if (findbcom.startNodeId != '') {
        node = doc.getElementById(findbcom.startNodeId);
      }
      if (node == null) {node = body;}
      scanForReferences(node);
      isStarted = false;
    };

  if (typeof window.findbcom != 'undefined')
    findbcom = extend(findbcom, window.findbcom);

  // export
  findbcom.scan = scan;
  findbcom.scanForReferences = scanForReferences;
  win.findbcom = findbcom;
})();
