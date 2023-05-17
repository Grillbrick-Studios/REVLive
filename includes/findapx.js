
// see http://jscompress.com/ to compress

var findapx = {};
(function() {})();

findapx.parseReference = function (textReference) {

  var bookIndex = -1,
      input = new String(textReference);

  bookIndex = input.replace (/[^\d.]/g, '');
  return findapx.Reference(bookIndex, 1, 1, -1, -1);
}

findapx.Reference = function () {

  var
    _bookIndex = -1,
    _chapter1 = -1,
    _verse1 = -1,
    _chapter2 = -1,
    _verse2 = -1,
    args = arguments;
  // rsw alert(args);

  if (args.length == 0) {
    // error
  } else if (args.length == 1 && typeof args[0] == 'string') {
    // a string that needs to be parsed
    return findapx.parseReference(args[0]);
  } else if (args.length == 1) { // unknown
    return null;
  } else {
    _bookIndex = args[0];
    _chapter1 = args[1];
    _verse1 = args[2];
  }

  return {
    bookIndex: _bookIndex,
    chapter: _chapter1,
    verse: _verse1,
    chapter1: _chapter1,
    verse1: _verse1,
    chapter2: _chapter2,
    verse2: _verse2,

    isValid: function () {
      //return (_bookIndex > -1 && 1==1); // rsw make sure bookIndex is a valid appendix
      for (var i=0;i<findappx.apxidx.length;i++) {
        if(_bookIndex==findappx.apxidx[i]) {
          return true;
          break;
        }
      }
      return false;
    },

    toString: function () {
      if (this.bookIndex < 0) return "invalid";
      return 'see Appendix '+this.bookIndex;
    },

    toShortUrl: function () {
      if (this.bookIndex < 0) return "invalid";
      return '/Appendix/'+this.bookIndex+((prfcommnewtab==1)?'/ct':'/bb');
    }
  }
};

// adapted from old scripturizer.js code

(function() {

  /* handler for when a verse node is found */
  var
    createBiblyLinks = function(newNode, reference) {
        newNode.setAttribute('href', reference.toShortUrl());
        newNode.setAttribute('title', 'Click to ' + reference.toString());
        if(prfcommnewtab) newNode.setAttribute('target', '_blank');
        newNode.setAttribute('rel', reference.toString());
        newNode.setAttribute('class', findappx.className);
    };

  /* core findappx functions */
  var findappx = {
      maxNodes: 500,
      linkVersion: '',
      autoStart: true,
      startNodeId: '',
      ignoreClassName: 'bibly_ignore',
      ignoreTags: ['h1','h2','h3','h4'],
      newTagName: 'A',
      apxidx: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],
      handleLinks: createBiblyLinks
    },
    win = window,
    doc = document,
    body = null,

    // rsw regex
    regexPattern =  '\\b(?:see\\s(?:appendix|appx)\\s#?)\\d{1,3}\\b',

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
        // add 4 for "see "
        referenceNode = node.splitText(match.index+4);
        // subtract 4 for "see ", afterReferenceNode gets trashed
        afterReferenceNode = referenceNode.splitText(val.length-4);

        // send the matched text down the
        newLink = createLinksFromNode(node, referenceNode);

        return newLink;
      } else {
        return node;
      }
    },
    createLinksFromNode = function(node, referenceNode) {
      if (referenceNode.nodeValue == null)
        return referenceNode;

      var reference;

      // test if the text matches a real reference
      refText = referenceNode.nodeValue;
      reference = parseRefText(refText);
      if (typeof reference != 'undefined' && reference.isValid()) {
        // replace the referenceNode TEXT with an anchor node to bib.ly
        newNode = node.ownerDocument.createElement(findappx.newTagName);
        node.parentNode.replaceChild(newNode, referenceNode);
        findappx.handleLinks(newNode, reference);
        newNode.appendChild(referenceNode);
        return newNode;
      } else {
        // for false matches, return it unchanged
        return referenceNode;
      }
    },
    parseRefText = function(refText) { // rsw
      var text = refText,
          reference = new findapx.Reference(text);

      if (reference != null && typeof reference.isValid != 'undefined' && reference.isValid()) {
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
        skipRegex = new RegExp('^(' + alwaysSkipTags.concat(findappx.ignoreTags).join('|') + ')$', 'i');


      while (node && depth > 0) {
        count++;
        if (count >= findappx.maxNodes) {
          setTimeout(function() { traverseDOM(node, depth, textHandler); }, 50);
          return;
        }

        switch (node.nodeType) {
          case 1: // ELEMENT_NODE
            if (!skipRegex.test(node.tagName.toLowerCase()) && node.childNodes.length > 0 && (findappx.ignoreClassName == '' || node.className.toString().indexOf(findappx.ignoreClassName) == -1)) {
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
      if (findappx.startNodeId != '') {
        node = doc.getElementById(findappx.startNodeId);
      }
      if (node == null) {node = body;}
      scanForReferences(node);
      isStarted = false;
    };

  // super cheater version of DOMoade
  // do whatever happens first
  //addEvent(doc,'DOMContentLoaded',startBibly);
  //addEvent(win,'load',startBibly);

  if (typeof window.findappx != 'undefined')
    findappx = extend(findappx, window.findappx);

  // export
  findappx.scan = scan;
  findappx.scanForReferences = scanForReferences;
  win.findappx = findappx;
})();
