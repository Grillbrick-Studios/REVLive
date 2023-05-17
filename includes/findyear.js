
// see http://jscompress.com/ to compress

var findyr = {};
(function() {})();

findyr.Reference = function () {

  var _navyear = arguments[0];

  return {
    navyear: _navyear,

    isValid: function () {
        return true;
      },

    toString: function () {
      return 'go to '+this.navyear;
    },

    toShortUrl: function () {
      this.navyear = this.navyear.replace (/\W/g, '');
      this.navyear = this.navyear.replace(/year/gi, '');
      return '/chronology/s~'+this.navyear+'/0/0';
    },
    onClick: function () {
      this.navyear = this.navyear.replace (/\W/g, '');
      //this.navyear = this.navyear.replace(/year/gi, '');
      //return 'alert(\''+this.navyear+'\');';
      return 'chronnav(\'yar\', \''+this.navyear+'\');';
    }
  }
};

// adapted from old scripturizer.js code

(function() {

  /* handler for when a verse node is found */
  var
    createBiblyLinks = function(newNode, reference) {
        //newNode.setAttribute('href', reference.toShortUrl());
        newNode.setAttribute('onclick', reference.onClick());
        newNode.setAttribute('title', 'Click to ' + reference.toString());
        newNode.setAttribute('rel', reference.toString());
        newNode.setAttribute('class', this.linkClassName);
    };

  /* core findyear functions */
  var findyear = {
      maxNodes: 500,
      autoStart: true,
      startNodeId: '',
      ignoreClassName: 'bibly_ignore',
      linkClassName: '',
      ignoreTags: ['h1','h2','h3','h4'],
      newTagName: 'A',
      handleLinks: createBiblyLinks
    },
    win = window,
    doc = document,
    body = null,

    // rsw regex
    //regexPattern = 'year\\s{1}\\d{1,4}\\s?((b|a)\\.?\\s?(c|d)\\.?)',
    regexPattern = '\\d{1,4}\\s?((b|a)\\.?\\s?(c|d)\\.?)',

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
      if (referenceNode.nodeValue == null)
        return referenceNode;

      var reference;

      // test if the text matches a real reference
      refText = referenceNode.nodeValue;
      reference = parseRefText(refText);
      if (typeof reference != 'undefined' && reference.isValid()) {
        // replace the referenceNode TEXT with an anchor node to bib.ly
        newNode = node.ownerDocument.createElement(findyear.newTagName);
        node.parentNode.replaceChild(newNode, referenceNode);
        findyear.handleLinks(newNode, reference);
        newNode.appendChild(referenceNode);
        return newNode;
      } else {
        // for false matches, return it unchanged
        return referenceNode;
      }
    },
    parseRefText = function(refText) { // rsw
      var text = refText,
          reference = new findyr.Reference(text);

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
        skipRegex = new RegExp('^(' + alwaysSkipTags.concat(findyear.ignoreTags).join('|') + ')$', 'i');


      while (node && depth > 0) {
        count++;
        if (count >= findyear.maxNodes) {
          setTimeout(function() { traverseDOM(node, depth, textHandler); }, 50);
          return;
        }

        switch (node.nodeType) {
          case 1: // ELEMENT_NODE
            if (!skipRegex.test(node.tagName.toLowerCase()) && node.childNodes.length > 0 && (findyear.ignoreClassName == '' || node.className.toString().indexOf(findyear.ignoreClassName) == -1)) {
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
      if (findyear.startNodeId != '') {
        node = doc.getElementById(findyear.startNodeId);
      }
      if (node == null) {node = body;}
      scanForReferences(node);
    };

  // super cheater version of DOMoade
  // do whatever happens first
  //addEvent(doc,'DOMContentLoaded',startBibly);
  //addEvent(win,'load',startBibly);

  if (typeof window.findyear != 'undefined')
    findyear = extend(findyear, window.findyear);

  // export
  findyear.scan = scan;
  findyear.scanForReferences = scanForReferences;
  win.findyear = findyear;
})();
