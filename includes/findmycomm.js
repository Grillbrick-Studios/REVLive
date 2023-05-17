// see http://jscompress.com/ to compress

var findmycom = {};
findmycom.parseReference = function (textReference) {

  var
    bookIndex = -1,
    chapter1 = -1,
    verse1 = -1,
    chapter2 = -1,
    verse2 = -1,
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

    // remove 'my note on' or 'my notes on'
    if(possibleMatch.indexOf('my comm on ')==0)
      possibleMatch = possibleMatch.slice(11);
    if(possibleMatch.indexOf('my note on ')==0)
      possibleMatch = possibleMatch.slice(11);
    else if(possibleMatch.indexOf('my notes on ')==0)
      possibleMatch = possibleMatch.slice(12);
    else if(possibleMatch.indexOf('my commentary on ')==0)
      possibleMatch = possibleMatch.slice(17);
    else if(possibleMatch.indexOf('my commentaries on ')==0)
      possibleMatch = possibleMatch.slice(19);
    // go through all books and test all names
    for (i = 0, il = bbooks.Books.length ; i < il && bookIndex == -1; i++) {
      // test each name starting with the full name, then short code, then abbreviation, then alternates
      for (j = 0, jl = bbooks.Books[i].names.length; j<jl; j++) {
        name = new String(bbooks.Books[i].names[j]).toLowerCase();
        if (possibleMatch == name) {
          bookIndex = i;
          input = input.substring(name.length);
          break;
        }
      }
    }
    if (bookIndex > -1) {
      params = findmycom.parseScriptureRef(input, bookIndex);
      chapter1 = params.c1;
      chapter2 = params.c2;
      verse1   = params.v1;
      verse2   = params.v2;
    }
  }
  // finalize
  return findmycom.Reference(bookIndex, chapter1, verse1, chapter2, verse2);

}

findmycom.parseScriptureRef = function(txt, bidx){
  var c1 = -1, c2 = -1, v1 = -1, v2 = -1,
      i, ch,
      afterRange = false,
      afterSeparator = false,
      startedNumber = false,
      currentNumber = '';


  for (i = 0; i < txt.length; i++) {
    ch = txt.charAt(i);

    if (ch == ' ' || isNaN(ch)) {
      if (!startedNumber)
        continue;

      if (ch == '-') {
        afterRange = true;
        afterSeparator = false;
      } else if (ch == ':' || ch == ',') {
        afterSeparator = true;
      } else {
        // ignore
      }

      // reset
      currentNumber = '';
      startedNumber = false;

    } else {
      startedNumber = true;
      currentNumber += ch;

      if (afterSeparator) {
        if (afterRange) {
          v2 = parseInt(currentNumber, 10);
        } else { // 1:1
          v1 = parseInt(currentNumber, 10);
        }
      } else {
        if (afterRange) {
          c2 = parseInt(currentNumber, 10);
        } else { // 1
          c1 = parseInt(currentNumber, 10);
        }
      }
    }
  }

  // for books with only one chapter, treat the chapter as a vers
  if (bbooks.Books[bidx].verses.length == 1) {
    // Jude 6 ==> Jude 1:6
    if (c1 > 0 && v1 == -1) {
      v1 = c1;
      c1 = 1;
    }
  }

  // reassign 1:1-2
  if (c1 > 0 && v1 > 0 && c2 > 0 && v2 <= 0) {
    v2 = c2;
    c2 = c1;
  }
  // fix 1-2:5
  if (c1 > 0 && v1 <= 0 && c2 > 0 && v2 > 0) {
    v1 = 1;
  }

  // just book // rsww
  if (bidx > -1 && c1 <= 0 && v1 <= 0 && c2 <= 0 && v2 <= 0) {
    c1 = 1;
  }

  // validate max chapter
  if ( typeof bbooks.Books[bidx].verses  != 'undefined') {
    if (c1 == -1) {
      c1 = 1;
    } else if (v1!=-1 && c1 > bbooks.Books[bidx].verses.length) {
      c1 = bbooks.Books[bidx].verses.length;
      if (v1 > 0)
        v1 = 1;
    }

    // validate max verse
    if (v1 > bbooks.Books[bidx].verses[c1 - 1]) {
      v1 = bbooks.Books[bidx].verses[c1 - 1];
    }
  }
  return {
    c1: c1,
    c2: c2,
    v1: v1,
    v2: v2
  }
}

findmycom.Reference = function () {

  var
    _bookIndex = -1,
    _chapter1 = -1,
    _verse1 = -1,
    _chapter2 = -1,
    _verse2 = -1,
    args = arguments;

  if (args.length == 0) {
    // error
  } else if (args.length == 1 && typeof args[0] == 'string') { // a string that needs to be parsed
    return findmycom.parseReference(args[0]);
  } else if (args.length == 1) { // unknown
    return null;
  } else {
    _bookIndex = args[0];
    _chapter1 = args[1];
    if (args.length >= 3) _verse1 = args[2];
    if (args.length >= 4) _chapter2 = args[3];
    if (args.length >= 5) _verse2 = args[4];
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
      // 20160427 MUST have a verse.
      return (_bookIndex > -1 && _bookIndex < bbooks.Books.length && _chapter1 > 0 && _verse1 > -1 && _chapter1 <= bbooks.Chapters[_bookIndex]);
      //return (_bookIndex > -1 && _bookIndex < bbooks.Books.length && _chapter1 > 0);
    },

    chapterAndVerse: function (cvSeparator, vvSeparator, ccSeparator) {
      cvSeparator = cvSeparator || ':';
      vvSeparator = vvSeparator || '-';
      ccSeparator = ccSeparator || '-';

      var chapter1 = this.chapter1,
        chapter2 = this.chapter2,
        verse1 = this.verse1,
        verse2 = this.verse2;
      var chapstr = ((bbooks.Books[this.bookIndex].verses.length == 1)?'':chapter1);
      var cvsep = ((chapstr=='')?'':cvSeparator);

      // some of these are not necessary for findmycomm
      if (chapter1 > 0 && verse1 <= 0 && chapter2 <= 0 && verse2 <= 0) // John 1
        return chapstr;
      else if (chapter1 > 0 && verse1 > 0 && chapter2 <= 0 && verse2 <= 0) // John 1:1
        return chapstr + cvsep + verse1;
      else if (chapter1 > 0 && verse1 > 0 && chapter2 <= 0 && verse2 > 0) // John 1:1-5
        return chapstr + cvsep + verse1 + vvSeparator + verse2;
      else if (chapter1 > 0 && verse1 <= 0 && chapter2 > 0 && verse2 <= 0) // John 1-2
        return chapter1 + ccSeparator + chapter2;
      else if (chapter1 > 0 && verse1 > 0 && chapter2 > 0 && verse2 > 0) // John 1:1-2:2
        return chapter1 + cvSeparator + verse1 + ccSeparator + ((chapter1 != chapter2) ? chapter2 + cvSeparator : '') + verse2;
      else
        return '?';
    },

    toString: function () {
      if (this.bookIndex < 0 || this.bookIndex >= bbooks.Books.length) return "invalid";

      return bbooks.Books[this.bookIndex].names[0] + ' ' + this.chapterAndVerse();
    },

    toShortUrl: function () {
      if (this.bookIndex < 0 || this.bookIndex >= bbooks.Books.length) return "invalid";
      //return '/'+bbooks.Books[this.bookIndex].names[0].replace(' ','-')+'/'+this.chapter1+'/'+this.verse1;
      var mytest = ((this.bookIndex<39)?0:1);
      var mrloc = myrevid+'|'+mytest+'|'+(this.bookIndex+1)+'|'+this.chapter1+'|'+this.verse1;
      //alert(findmycomm.mrlightbox);
      if(findmycomm.mrlightbox==0)
        return '/myrevnotes.php?loc='+mrloc;
      else
        return 'javascript: rlightbox(\'note\',\''+mrloc+'\')';
    },
    contextUrl: function () {
      if (this.bookIndex < 0 || this.bookIndex >= bbooks.Books.length) return "invalid";
      //return '/'+bbooks.Books[this.bookIndex].names[0].replace(' ','-')+'/'+this.chapter1+'/ct';
      return '/'+bbooks.Books[this.bookIndex].names[0].replace(' ','-')+'/'+this.chapter1+((this.verse1>-1)?'/nav'+this.verse1:'')+'/ct';
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
    },
    getBibleText = function(refString, callback) { // rsw
      var reference = new findmycom.Reference(refString);
      href = (reference.bookIndex+1) + ','
            + reference.chapter1 + ','
            + reference.verse1   + ','
            + reference.chapter2 + ','
            + reference.verse2;
      //alert(href);
      //jsonp('http://' + findmycomm.remoteURL + '/jsonverse.php?ref=' + encodeURIComponent(href), callback);
      jsonp(findmycomm.remoteURL + '/jsonverse.php?ref=' + encodeURIComponent(href), callback);
    },
    handleBibleText = function(d) {
      popp.content.innerHTML = '<div>' + d.content + '</div>';
    };

  /* handler for when a verse node is found */
  var
    createfindmycommLinks = function(newNode, reference) {
        newNode.setAttribute('href', reference.toShortUrl());
        newNode.setAttribute('target', '_self');
        newNode.setAttribute('title', 'Click to read my note on ' + reference.toString());
        newNode.setAttribute('rel', reference.toString());
        newNode.setAttribute('ver', ' <span style="font-weight:normal;font-size:80%;">(REV)</span>');
        newNode.setAttribute('class', findmycomm.className);
        newNode.setAttribute('context', '<span style="float:right;margin-right:-2px;"><a href="' + reference.contextUrl() + '" target="_blank" title="view context (new window)"><img src="/i/context.png" style="width:1.1em;border:0" /></a></span>');
        //newNode.setAttribute('onclick', 'pophidePopup();');
        addEvent(newNode,'mouseover', handleLinkMouseOver);
        addEvent(newNode,'mouseout', pophandleLinkMouseOut);
    },

    handleLinkMouseOver = function(e) {
      if (!e) {e = win.event;}
      if (!findmycomm.enablePopups) {return;}

      popclearPositionTimer();

      var target = (e.target) ? e.target : e.srcElement,
        p = popp,
        referenceText = target.getAttribute('rel'),
        version = target.getAttribute('ver');

      p.outer.style.display = 'block';
      p.outer.style.visibility = 'hidden';
      popdivShowTimer = setTimeout('popp.outer.style.visibility = \'visible\';', 300);
      p.header.innerHTML = referenceText+version+target.getAttribute('context');
      p.content.innerHTML = 'Loading...<br/><br/><br/>';

      poppositionPopup(target);

      getBibleText(referenceText, function(d) {
        handleBibleText(d);
        poppositionPopup(target);
      });
    };


  /* core findmycomm functions */
  var findmycomm = {
      maxNodes: 500,
      className: 'findvers_reference',
      enablePopups: true,
      autoStart: true,
      startNodeId: '',
      mrlightbox: 1,
      ignoreClassName: 'findvers_ignore',
      ignoreTags: ['h1','h2','h3','h4'],
      newTagName: 'A',
      remoteURL: '', // MUST be passed
      handleLinks: createfindmycommLinks
    },
    win = window,
    doc = document,
    body = null,
    bok = bbooks.bbooks,
    ver = '(?:\\d{1,3}(?::\\d+)?)(?:-\\d+(?::\\d+)?)?',
   ver2 = '(?:\\d{1,3}(?::\\d+))(?:-\\d+(?::\\d+)?)?',
   ver3 = '(?: ?, ?'+ver+')|(?: ?(?:(?:(?:;|,)? and|;)) ?'+ver2+')',
   regexPattern =  '\\b(?:my (?:note|notes|commentary|commentaries|comm) on )(?:'+bok+')\\.? '
          + '(?:' + ver
          + '(?:' + ver3 + ')*'
          + '(?:(?:(?:;|,)? and|;) (?:'+bok+')\\.? ' // ; chapter and verse -required- after semi-colon, or 'and' or ', and'
          + '(?:' + ver
          + '(?:' + ver3 + ')*'
          + '))*'
          + ')\\b',

    referenceRegex = new RegExp(regexPattern, 'mi'),
    verseRegex = new RegExp(ver, 'mi'),
    alwaysSkipTags = ['a','script','style','textarea'],
    lastReference = null,
    textHandler = function(node) {
       //alert(node.data);
       var match = referenceRegex.exec(node.data),
        val,
        referenceNode,
        afterReferenceNode,
        newLink;

      // reset this
      lastReference = null;
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

      // split up match by ; and , characters and make a unique link for each
      var
        commaIndex = referenceNode.nodeValue.indexOf(','),
        semiColonIndex = referenceNode.nodeValue.indexOf(';'),
        andIndex = referenceNode.nodeValue.indexOf(' and'),
        commaandIndex = referenceNode.nodeValue.indexOf(', and'),
        semiandIndex = referenceNode.nodeValue.indexOf('; and'),
        separatorIndex,
        separator,
        remainder,
        reference,
        startRemainder;

      separatorIndex = Math.min(((commaIndex<0)?999:commaIndex), ((semiColonIndex<0)?999:semiColonIndex), ((andIndex<0)?999:andIndex), ((commaandIndex<0)?999:commaandIndex), ((semiandIndex<0)?999:semiandIndex));
      if(separatorIndex==999) separatorIndex=-1;

      // if there is a separator (,|;|and) then split up into three parts [node][separator][after]
      if (separatorIndex > 0) {
        separator = referenceNode.splitText(separatorIndex);
        switch (separator.nodeValue.substring(0,3)) {
        case 'and': startRemainder = 4; break;
        case ' an': startRemainder = 5; break;
        case ', a': startRemainder = 6; break;
        case '; a': startRemainder = 6; break;
        default: startRemainder = 1;
        }
        while(startRemainder < separator.nodeValue.length && (separator.nodeValue.substring(startRemainder,startRemainder+1)==' '))
          startRemainder++;
        remainder = separator.splitText(startRemainder);
      }

      // test if the text matches a real reference
      refText = referenceNode.nodeValue;
      reference = parseRefText(refText);
      if (typeof reference != 'undefined' && reference.isValid()) {

        // replace the referenceNode TEXT with an anchor
        newNode = node.ownerDocument.createElement(findmycomm.newTagName);
        node.parentNode.replaceChild(newNode, referenceNode);

        // this can be overridden for other systems
        findmycomm.handleLinks(newNode, reference);

        newNode.appendChild(referenceNode);

        // if there was a separator, now parse the stuff after it
        if (remainder) {
          newNode = createLinksFromNode(node, remainder);
        }

        return newNode;
      } else {
        // for false matches, return it unchanged
        return referenceNode;
      }
    },
    parseRefText = function(refText) {

      var
        text = refText,
        reference = new findmycom.Reference(text),
        match = null,
        p1, p2, p3, p4;

      if (reference != null && typeof reference.isValid != 'undefined' && reference.isValid()) {

        lastReference = reference;
        return reference;

      } else if (lastReference  != null) {

        // single verse match (3)
        match = verseRegex.exec(refText);
        if (match) {
          input = match[0];

          params = findmycom.parseScriptureRef(input, lastReference.bookIndex);
          p1 = params.c1;
          p2 = params.v1;
          p3 = params.c2;
          p4 = params.v2;

          // single verse (1)
          if (p1 > 0 && p2<0 && p3<0 && p4<0) {
            lastReference.verse1 = p1;
            lastReference.chapter2 = -1;
            lastReference.verse2 = -1;

          // 1:2
          } else if (p1>0 && p2>0 && p3<0 && p4<0) {
            lastReference.chapter1 = p1;
            lastReference.verse1 = p2;
            lastReference.chapter2 = -1;
            lastReference.verse2 = -1;

          // 1-2
          } else if (p1>0 && p2<0 && p3>0 && p4<0) {
            lastReference.verse1 = p1;
            lastReference.chapter2 = -1;
            lastReference.verse2 = p3;

          // 1:2-3
          } else if (p1>0 && p2>0 && p3<0 && p4>0) {
            lastReference.chapter1 = p1;
            lastReference.verse1 = p2;
            lastReference.chapter2 = -1;
            lastReference.verse2 = p4;

          // 1:2-3:4
          } else if (p1>0 && p2>0 && p3>0 && p4>0) {
            lastReference.chapter1 = p1;
            lastReference.verse1 = p2;
            lastReference.chapter2 = p3;
            lastReference.verse2 = p4;
          }

          return lastReference;
        }

        // failure
        return 'javascript:null';
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
        skipRegex = new RegExp('^(' + alwaysSkipTags.concat(findmycomm.ignoreTags).join('|') + ')$', 'i');


      while (node && depth > 0) {
        count++;
        if (count >= findmycomm.maxNodes) {
          setTimeout(function() { traverseDOM(node, depth, textHandler); }, 50);
          return;
        }

        switch (node.nodeType) {
          case 1: // ELEMENT_NODE
            if (!skipRegex.test(node.tagName.toLowerCase()) && node.childNodes.length > 0 && (findmycomm.ignoreClassName == '' || node.className.toString().indexOf(findmycomm.ignoreClassName) == -1)) {
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
      if (findmycomm.startNodeId != '') {
        node = doc.getElementById(findmycomm.startNodeId);
      }
      if (node == null) {node = body;}
      scanForReferences(node);
      isStarted = false;
    };

  // super cheater version of DOMoade
  // do whatever happens first
  //addEvent(doc,'DOMContentLoaded',startfindmycomm);
  //addEvent(win,'load',startfindmycomm);

  if (typeof window.findmycomm != 'undefined')
    findmycomm = extend(findmycomm, window.findmycomm);

  // export
  findmycomm.scan = scan;
  findmycomm.scanForReferences = scanForReferences;
  win.findmycomm = findmycomm;
})();
