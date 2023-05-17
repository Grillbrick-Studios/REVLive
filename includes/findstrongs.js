
// see http://jscompress.com/ to compress

var findstrngs = {};
(function() {})();

findstrngs.Reference = function () {

  var
    _strongsNumber = -1,
    args = arguments;

  if (args.length == 0) {
    // error
  } else {
    _strongsNumber = args[0];
  }
  if(_strongsNumber.substring(0,1)=='#')
    _strongsNumber = _strongsNumber.substring(1,8);

  return {
    strongsNumber: _strongsNumber,

    isValid: function () {return true;},

    toString: function () {
      var lexicon = findstrongs.lexicon;
      var neww='';
      var ret = 'see xlexiconx\'s notes for #'+this.strongsNumber,
      lextext;
      switch(lexicon){
      case 1: lextext = 'BlueLetterBible.org';neww=' (new window)';break;
      case 2: lextext = 'BibleHub.com';neww=' (new window)';break;
      case 3: lextext = 'StudyLight.org';neww=' (new window)';break;
      case 4: lextext = 'Tyndale';break;
      case 5: lextext = 'Strong';break;
      default: lextext = 'Choose Lexicon';break;
      }
      return ret.replace('xlexiconx', lextext)+neww;
      },

    toShortUrl: function () {
      lexicon = findstrongs.lexicon;
      // the URL must have the 4 x's where the strongs number goes.
      var strongsURL, greekheb, prefix, wwidth, wheight;//, woffset;
      //woffset = ((ismobile)?50:150);
      wwidth = ((ismobile)?parent.window.innerWidth-50:600);
      wheight = ((ismobile)?parent.window.innerHeight-50:500);
      switch(lexicon){
      case 1: // blueletterbible
        //strongsURL = 'https://www.blueletterbible.org/lang/lexicon/lexicon.cfm?Strongs=xxxx';
        if(this.strongsNumber.substring(0,1)=='0'){
          greekheb = 'H';
          this.strongsNumber = this.strongsNumber.substring(1,8);
        }else{
          greekheb = 'G';
        }
        strongsURL = 'https://www.blueletterbible.org/lexicon/'+greekheb+'xxxx/kjv';
        break;
      case 2: // biblehub
        if(this.strongsNumber.substring(0,1)=='0'){
          greekheb = 'hebrew';
          this.strongsNumber = this.strongsNumber.substring(1,8);
        }else{
          greekheb = 'greek';
        }
        strongsURL = 'http://biblehub.com/'+greekheb+'/xxxx.htm';
        break;
      case 3: // studylight
        if(this.strongsNumber.substring(0,1)=='0'){
          greekheb = 'hebrew/hwview';
          this.strongsNumber = this.strongsNumber.substring(1,8);
        }else{
          greekheb = 'greek/gwview';
        }
        strongsURL = 'http://www.studylight.org/lexicons/'+greekheb+'.cgi?n=xxxx';
        break;
      case 4: // local tyndale table
      case 5: // local strongs table
      case 6: // Choose
        if(this.strongsNumber.substring(0,1)=='0'){
          prefix = 'H';
          this.strongsNumber = this.strongsNumber.substring(1,8);
        }else{
          prefix = 'G';
        }
        //strongsURL = 'javascript:olOpen(\'/'+((lexicon==4)?'strongstyndale':((lexicon==5)?'strongsstrong':'strongschoose'))+'.php?strongs='+prefix+'xxxx\','+wwidth+', '+wheight+');';
        strongsURL = 'javascript:olOpen(\'/viewstrongs.php?strongs='+prefix+'xxxx\','+wwidth+', '+wheight+');';
        break;
      }
      return strongsURL.replace('xxxx',this.strongsNumber);
    }
  }
};

// adapted from old scripturizer.js code

(function() {

  /* handler for when a verse node is found */
  var
    createBiblyLinks = function(newNode, reference) {
        newNode.setAttribute('title', 'Click to ' + reference.toString());
        newNode.setAttribute('href', reference.toShortUrl());
        //newNode.setAttribute('rel', reference.toString());
        newNode.setAttribute('rel', 'noopener');
        newNode.setAttribute('target', '_blank');
        newNode.setAttribute('class', findstrongs.className);
    };

  /* core findstrongs functions */
  var findstrongs = {
      maxNodes: 500,
      linkVersion: '',
      autoStart: true,
      startNodeId: '',
      ignoreClassName: 'bibly_ignore',
      ignoreTags: ['h1','h2','h3','h4'],
      newTagName: 'A',
      handleLinks: createBiblyLinks,
      lexicon: 1
    },
    win = window,
    doc = document,
    body = null,

    // rsw regex
    regexPattern =  '#\\d{1,7}\\b',

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
        newNode = node.ownerDocument.createElement(findstrongs.newTagName);
        node.parentNode.replaceChild(newNode, referenceNode);
        findstrongs.handleLinks(newNode, reference);
        newNode.appendChild(referenceNode);
        return newNode;
      } else {
        // for false matches, return it unchanged
        return referenceNode;
      }
    },
    parseRefText = function(refText) { // rsw
      var text = refText,
          reference = new findstrngs.Reference(text);

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
        skipRegex = new RegExp('^(' + alwaysSkipTags.concat(findstrongs.ignoreTags).join('|') + ')$', 'i');


      while (node && depth > 0) {
        count++;
        if (count >= findstrongs.maxNodes) {
          setTimeout(function() { traverseDOM(node, depth, textHandler); }, 50);
          return;
        }

        switch (node.nodeType) {
          case 1: // ELEMENT_NODE
            if (!skipRegex.test(node.tagName.toLowerCase()) && node.childNodes.length > 0 && (findstrongs.ignoreClassName == '' || node.className.toString().indexOf(findstrongs.ignoreClassName) == -1)) {
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

      if (findstrongs.startNodeId != '') {
        node = doc.getElementById(findstrongs.startNodeId);
      }
      if (node == null) {node = body;}
      scanForReferences(node);
      isStarted = false;
    };

  // super cheater version of DOMoade
  // do whatever happens first
  //addEvent(doc,'DOMContentLoaded',startBibly);
  //addEvent(win,'load',startBibly);

  if (typeof window.findstrongs != 'undefined')
    findstrongs = extend(findstrongs, window.findstrongs);

  // export
  findstrongs.scan = scan;
  findstrongs.scanForReferences = scanForReferences;
  win.findstrongs = findstrongs;
})();
