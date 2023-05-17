// see http://jscompress.com/ to compress

var bible = {};

bible.parseReference = function (textReference) {

  var
    bookIndex = -1,
    chapter1 = -1,
    verse1 = -1,
    chapter2 = -1,
    verse2 = -1,
    input = new String(textReference).replace(/(\d+[\.:])\s+(\d+)/gi, '$1$2'),
    i, j, il, jl, ff=0,
    possibleMatch = null;
  //alert(input);

  if(input.slice(-2)=='ff'){
    input = input.slice(0, -2);
    ff = 1;
  }
  //if(input.slice(-1)=='a' || input.slice(-1)=='b'){input = input.slice(0, -1);}
  input = input.replace(/(\d)[a|b]/gi, '$1');

  //alert(input);

  // take the entire reference (John 1:1 or 1 Cor) and move backwards until we find a letter
  for (i=input.length; i>=0; i--) {
    if (/[A-Za-z]/.test(input.substring(i-1,i))) {
      possibleMatch = input.substring(0,i).toLowerCase();
      break;
    }
  }

  if (possibleMatch != null) {

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
      params = bible.parseScriptureRef(input, bookIndex, ff);
      chapter1 = params.c1;
      chapter2 = params.c2;
      verse1   = params.v1;
      verse2   = params.v2;
    }
  }

  // finalize
  return bible.Reference(bookIndex, chapter1, verse1, chapter2, verse2);

}
bible.parseScriptureRef = function(txt, bidx, ff){
  var c1 = -1, c2 = -1, v1 = -1, v2 = -1,
      i, ch,
      afterRange = false,
      afterSeparator = false,
      startedNumber = false,
      currentNumber = '';


  //alert('input: '+txt);
  for (i = 0; i < txt.length; i++) {
    ch = txt.charAt(i);

    //alert(ch);
    //if (ch == ' ' || (isNaN(ch) || ch=='a' || ch=='b')) {
    //if (ch==' ' || isNaN(ch) && (ch!='a' && ch!='b')) {
    //alert(ch);
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
      //alert('intheelse');
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
  //alert('currentNumber: '+currentNumber);

  // for books with only one chapter, treat the chapter as a vers
  if (bbooks.Books[bidx].verses.length == 1) {

    // Jude 6 ==> Jude 1:6
    if (c1 > 0 && v1 == -1) {
      v1 = c1;
      c1 = 1;
    }
  }

  // reassign 1:1-2
  if (c1 > 0 && v1 > 0 && c2 > 0 && (v2 <= 0 || ff==1)) {
    v2 = c2;
    c2 = c1;
  }
  // fix 1-2:5
  if (c1 > 0 && v1 <= 0 && c2 > 0 && v2 > 0) {
    v1 = 1;
  }

  // just book
  if (bidx > -1 && c1 <= 0 && v1 <= 0 && c2 <= 0 && v2 <= 0) {
    c1 = 1;
  }

  if(ff==1) v2 = 999;

  //alert('before\nc1='+c1+'\nv1='+v1+'\nc2='+c2+'\nv2='+v2);

  // validate max chapter
  //alert(bidx);
  //alert(bible.Books[bidx].verses);
  if ( typeof bbooks.Books[bidx].verses  != 'undefined') {
    if (c1 == -1) {
      c1 = 1;
    } else if (v1!=-1 && c1 > bbooks.Books[bidx].verses.length) {
      c1 = bbooks.Books[bidx].verses.length;
      if (v1 > 0) v1 = 1;
    }
    //alert('bidx='+bidx+'   c1='+c1);

    //alert('before2\nc1='+c1+'\nv1='+v1+'\nc2='+c2+'\nv2='+v2);
    // validate max verse
    if (v1 > bbooks.Books[bidx].verses[c1 - 1]) {
      v1 = bbooks.Books[bidx].verses[c1 - 1];
    }
  }
  //alert('after\nc1='+c1+'\nv1='+v1+'\nc2='+c2+'\nv2='+v2);
  return {
    c1: c1,
    c2: c2,
    v1: v1,
    v2: v2
  }
}

bible.Reference = function () {

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
    return bible.parseReference(args[0]);
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
      return (_bookIndex > -1 && _bookIndex < bbooks.Books.length && _chapter1 > 0 && _chapter1 <= bbooks.Chapters[_bookIndex]);
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

      if (chapter1 > 0 && verse1 <= 0 && chapter2 <= 0 && verse2 <= 0) // John 1
        return chapstr;
      else if (chapter1 > 0 && verse1 > 0 && chapter2 <= 0 && verse2 <= 0) // John 1:1
        return chapstr + cvsep + verse1;
      else if (chapter1 > 0 && verse1 > 0 && chapter2 <= 0 && verse2 > 0) // John 1:1-5
        return chapstr + cvsep + verse1 + ((verse2==999)?'ff':vvSeparator + verse2);
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

    toShortUrl: function () { //rsw
      if (this.bookIndex < 0 || this.bookIndex >= bbooks.Books.length) return "invalid";
      if (findvers.navigat || 1==1) {
        //alert(this.verse1);
        return '/'+bbooks.Books[this.bookIndex].names[0].replace(' ','-')+'/'+this.chapter1+((this.verse1>-1)?'/nav'+this.verse1:'')+'/ct';
      }else{
        return 'SIT';
      }
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
    getBibleText = function(refString, callback) {
      var reference = new bible.Reference(refString);

      href = (reference.bookIndex+1) + ','
            + reference.chapter1 + ','
            + reference.verse1   + ','
            + reference.chapter2 + ','
            + reference.verse2;
      //alert(href);
      //jsonp('http://' + findvers.remoteURL + '/jsonverse.php?ref=' + encodeURIComponent(href), callback);
      jsonp(findvers.remoteURL + '/jsonverse.php?ref=' + encodeURIComponent(href), callback);
    },
    handleBibleText = function(d) {
      popp.content.innerHTML = '<div>' + d.content + '</div>';
    };

  /* handler for when a verse node is found */
  var
    createFindVersLinks = function(newNode, reference) {
        newNode.setAttribute('href', 'SIT');
        newNode.setAttribute('context', '<span style="float:right;margin-right:-2px;"><a href="' + reference.toShortUrl() + '" target="_blank" title="view context (new window)"><img src="/i/context.png" style="width:1.1em;border:0" /></a></span>');
        newNode.setAttribute('rel', reference.toString());
        //newNode.setAttribute('onclick', 'pophidePopup(); return false;');
        newNode.setAttribute('onclick', 'return false;');
        newNode.setAttribute('ver', ' <span style="font-weight:normal;font-size:80%;">(REV)</span>');
        newNode.setAttribute('class', findvers.className);
        addEvent(newNode,'mouseover', handleLinkMouseOver);
        addEvent(newNode,'mouseout', pophandleLinkMouseOut);
    },

    handleLinkMouseOver = function(e) {
      if (!e) {e = win.event;}
      if (!findvers.enablePopups) {return;}
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


  /* core findvers functions */
  var findvers = {
      maxNodes: 500,
      className: 'findvers_reference',
      enablePopups: true,
      navigat: true,
      autoStart: true,
      startNodeId: '',
      ignoreClassName: 'findvers_ignore',
      ignoreTags: ['h1','h2','h3','h4', 'noparse'],
      newTagName: 'A',
      remoteURL: '', // MUST be passed
      handleLinks: createFindVersLinks
    },
    win = window,
    doc = document,
    body = null,
    bok = bbooks.bbooks,

   // all groups are non-capturing, supposedly improves performance
   // good
   //ver = '(?:\\d{1,3}(?::\\d+)?)(?:ff|a|b)?(?:-\\d+(?::\\d+)?)?',  // verse pattern (c, v, c:v, c:v-v)
   //ver2 = '(?:\\d{1,3}(?::\\d+))(?:ff|a|b)?(?:-\\d+(?::\\d+)?)?',

   ver = '(?:\\d{1,3}(?::\\d+)?)(?:ff|a|b)?(?:-\\d+((?::\\d+)?)(?:a)?)?',  // verse pattern (c, v, c:v, c:v-v)
   ver2 = '(?:\\d{1,3}(?::\\d+))(?:ff|a|b)?(?:-\\d+((?::\\d+)?)(?:a)?)?',

  regexPattern =  '\\b(?:'+bok+')\\.? '// book name with period(optional) and space afterward
          + '(?:' + ver                  // verse pattern (1 OR 1:1 OR 1:1-2)
            + '(?:'
            + '(?:, ?'+ver+')|' // , verse alone allowed
            + '(?:(?:(?:;|,)? (?:and|or)|;) +'+ver2+')' // ; c:v -required- after ';', 'and', ', and' or '; and'
            + ')*'                     // infinite reoccurance
          + ')\\b',
    referenceRegex = new RegExp(regexPattern, 'mi'),
    verseRegex = new RegExp(ver, 'mi'),
    alwaysSkipTags = ['a','script','style','textarea'],
    lastReference = null,
    textHandler = function(node) {
      //document.write(regexPattern+'<br /><br />');
      var match = referenceRegex.exec(node.data),
          val,
          referenceNode,
          afterReferenceNode,
          newLink;
      //alert(regexPattern);

      // reset this
      lastReference = null;

      if (match) {
        //alert(match);
        //alert(match.index);
        val = match[0];
        //alert(val);
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
        orIndex = referenceNode.nodeValue.indexOf(' or'),
        commaorIndex = referenceNode.nodeValue.indexOf(', or'),
        semiorIndex = referenceNode.nodeValue.indexOf('; or'),
        separatorIndex,
        separator,
        remainder,
        reference,
        startRemainder;

      //separatorIndex = Math.min(((commaIndex<0)?999:commaIndex), ((semiColonIndex<0)?999:semiColonIndex), ((andIndex<0)?999:andIndex), ((commaandIndex<0)?999:commaandIndex), ((semiandIndex<0)?999:semiandIndex));
      separatorIndex = Math.min(((commaIndex<0)?999:commaIndex), ((semiColonIndex<0)?999:semiColonIndex), ((andIndex<0)?999:andIndex), ((commaandIndex<0)?999:commaandIndex), ((semiandIndex<0)?999:semiandIndex), ((orIndex<0)?999:orIndex), ((commaorIndex<0)?999:commaorIndex), ((semiorIndex<0)?999:semiorIndex));
      if(separatorIndex==999) separatorIndex=-1;

      // if there is a separator (,|;|and) then split up into three parts [node][separator][after]
      if (separatorIndex > 0) {
        separator = referenceNode.splitText(separatorIndex);
        switch (separator.nodeValue.substring(0,3)) {
        case 'and': startRemainder = 4; break;
        case ' an': startRemainder = 5; break;
        case ', a': startRemainder = 6; break;
        case '; a': startRemainder = 6; break;
        case ' or': startRemainder = 3; break;
        case ', o': startRemainder = 5; break;
        case '; o': startRemainder = 5; break;
        default: startRemainder = 1;
        };
        while(startRemainder < separator.nodeValue.length && (separator.nodeValue.substring(startRemainder,startRemainder+1)==' '))
          startRemainder++;
        remainder = separator.splitText(startRemainder);
      }

      // test if the text matches a real reference
      refText = referenceNode.nodeValue;
      //alert('>'+refText+'<');
      reference = parseRefText(refText);
      //alert('ref>'+reference+'<');
      if (typeof reference != 'undefined' && reference.isValid()) {

        // replace the referenceNode TEXT with an anchor
        newNode = node.ownerDocument.createElement(findvers.newTagName);
        node.parentNode.replaceChild(newNode, referenceNode);

        // this can be overridden for other systems
        findvers.handleLinks(newNode, reference);

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
        reference = new bible.Reference(text),
        match = null, ff = 0, idxx, fftest,
        c1, v1, c2, v2, lref, lc1, lv1, lc2, lv2;
      //alert('ref>'+reference);

      if (reference != null && typeof reference.isValid != 'undefined' && reference.isValid()) {

        lastReference = reference;
        return reference;

      } else if (lastReference  != null) {

        // rsw
        match = verseRegex.exec(refText);
        if (match) {
          //alert('m='+match);
          input = match[0];
          if(input.slice(-2)=='ff'){
            input = input.slice(0, -2);
            ff = 1;
          }
          //if(input.slice(-1)=='a' || input.slice(-1)=='b'){input = input.slice(0, -1);}
          input = input.replace(/(\d)[a|b]/gi, '$1');
          lref= lastReference;
          lc1 = lref.chapter1;
          lv1 = lref.verse1;   // this is really the only one I'm testing for below
          lc2 = lref.chapter2;
          lv2 = lref.verse2;

          params = bible.parseScriptureRef(input, lastReference.bookIndex, ff);
          c1 = params.c1;
          v1 = params.v1;
          c2 = params.c2;
          v2 = params.v2;

          // !!debug, do not delete!!
          //alert('BEFORE\nlastReference: '+lref+'\nlref_c1: '+lc1+'\nlref_v1: '+lv1+'\nlref_c2: '+lc2+'\nlref_v2: '+lv2+'\nnewc1: '+c1+'\nnewv1: '+v1+'\nnewc2: '+c2+'\nnewv2: '+v2);

          // initialize
          lastReference.chapter1 = lastReference.chapter1;  // silly, I know
          lastReference.verse1 = -1;
          lastReference.chapter2 = -1;
          lastReference.verse2 = (ff==1)?999:-1;
          fftest = (v2<0 || ff==1);

          // single v after c:v
          if (lv1>0 && c1>0 && v1<0 && c2<0 && fftest) {
            //idxx = 1;
            lastReference.verse1 = c1;

          // single c after c
          } else if (c1>0 && v1<0 && c2<0 && fftest) {
            //idxx = 2;
            lastReference.chapter1 = c1;

          // 1:2
          } else if (c1>0 && v1>0 && c2<0 && fftest) {
            //idxx = 3;
            lastReference.chapter1 = c1;
            lastReference.verse1 = v1;

          // 1-2 after prev v
          } else if (lv1>0 && c1>0 && v1<0 && c2>0 && fftest) {
            //idxx = 4;
            lastReference.verse1 = c1;
            lastReference.verse2 = (ff==1)?999:c2;

          // 1-2 after no prev c, send back chapter range
          } else if (c1>0 && v1<0 && c2>0 && fftest) {
            //idxx = 5;
            lastReference.chapter1 = c1;
            lastReference.chapter2 = c2;

          // 1:2-3
          } else if (c1>0 && v1>0 && c2<0 && v2>0) {
            //idxx = 6;
            lastReference.chapter1 = c1;
            lastReference.verse1 = v1;
            lastReference.verse2 = v2;

          // 1:2-3:4
          } else if (c1>0 && v1>0 && c2>0 && v2>0) {
            //idxx = 7;
            lastReference.chapter1 = c1;
            lastReference.verse1 = v1;
            lastReference.chapter2 = c2;
            lastReference.verse2 = v2;
          } else idxx = -99999;

          //alert('c1='+c1+'\nv1='+v1+'\nc2='+c2+'\nv2='+v2);
          //alert('AFTER: '+idxx+'\nlastReference: '+lastReference+'\nlref_c1: '+lastReference.chapter1+'\nlref_v1: '+lastReference.verse1+'\nlref_c2: '+lastReference.chapter2+'\nlref_v2: '+lastReference.verse2);
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
        skipRegex = new RegExp('^(' + alwaysSkipTags.concat(findvers.ignoreTags).join('|') + ')$', 'i');


      while (node && depth > 0) {
        count++;
        if (count >= findvers.maxNodes) {
          setTimeout(function() { traverseDOM(node, depth, textHandler); }, 50);
          return;
        }

        switch (node.nodeType) {
          case 1: // ELEMENT_NODE
            if (!skipRegex.test(node.tagName.toLowerCase()) && node.childNodes.length > 0 && (findvers.ignoreClassName == '' || node.className.toString().indexOf(findvers.ignoreClassName) == -1)) {
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
    startFindvers = function() {

      if (isStarted)
        return;
      isStarted = true;

      doc = document;
      body = doc.body;

      if (findvers.autoStart) {
        if (findvers.startNodeId != '') {
          node = doc.getElementById(findvers.startNodeId);
        }

        if (node == null) {
          node = body;
        }

        scanForReferences(node);
      }
    };
    scan = function() {
      //alert(isStarted);
      if (isStarted)return;
      isStarted = true;
      doc = document;
      body = doc.body;
      if (findvers.startNodeId != '') {
          node = doc.getElementById(findvers.startNodeId);
      }
      if (node == null) {node = body;}
      scanForReferences(node);
      isStarted=false; // added 20200717, playing with resource lightbox
    };

  // super cheater version of DOMoade
  // do whatever happens first
  //addEvent(doc,'DOMContentLoaded',startFindvers);
  //addEvent(win,'load',startFindvers);

  if (typeof window.findvers != 'undefined')
    findvers = extend(findvers, window.findvers);

  // export
  findvers.scan = scan;
  //findvers.startFindvers = startFindvers;
  findvers.scanForReferences = scanForReferences;
  win.findvers = findvers;
})();

