//
// Misc javascript functions
// use https://codebeautify.org/minify-js
//
var tcolor;
function $(el) {
  return document.getElementById(el);
}

function olOpen (url, w, h, r) {
  var ol = $("overlay");
  var el = $("iframeholder");
  var frm = $("ifrm");
  if(r==undefined) r=((ismobile==1)?1:0);
  //r = r||((ismobile==1)?1:0);
  frm.style.width=w+'px';
  frm.style.height=(h-3)+'px';
  frm.src=url;
  el.style.marginTop = '-'+ h/2 + 'px';
  el.style.marginLeft = '-'+w/2 + 'px';
  ol.style.display = 'block';
  el.style.height=h+'px';
  el.style.overflow='auto';
  if(r==1){resizeOL()};
}

function trim(s)  { return ltrim(rtrim(s)) }
function ltrim(s) { return s.replace(/^\s+/g, "") }
function rtrim(s) { return s.replace(/\s+$/g, "") }

function dload(url, chap){
  if(chap<1 && !ismobile){
    olOpen('/includes/pleasewait.php', 240, 180, 0);
    setTimeout('location.href=\''+url+'\'', 300);
    setTimeout('stopwait()', 5400);
  }else location.href=url;
  return false;
}
function stopwait(){
  $("overlay").style.display = 'none';
  setTimeout('$("ifrm").src="/includes/empty.htm"', 200);
}

function resizeOL(){
  var ol = $("overlay");
  if(ol.style.display=='none') return;
  var el = $("iframeholder");
  var frm = $("ifrm");
  xdim = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
  ydim = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
  frm.style.width=(xdim*.9)+'px';
  //frm.style.height=((ismobile==1)?460:(ydim*.9))+'px';
  //el.style.height=((ismobile==1)?460:(ydim*.9))+'px';
  frm.style.height=(ydim*.88)+'px';
  el.style.height= (ydim*.9)+'px';
  el.style.marginLeft= '-'+xdim*.45+'px';
  el.style.marginTop = '-'+ydim*.45+'px';
}

function nvl(v,d){
  v = trim(v+'');
  if(v=='') return d;
  else return v;
}

function excol(expanded){
  if($('biblenav').style.height!='0px') sizenavto(0);
  var div = $('nav');
  if(!prfscrollynav) div.style.transition = 'left 0s';
  if(expanded==1){
    div.style.left = -210+'px';
  }else{
    div.style.left = 0;
    div.scrollTop=1; // glitch. Required when user is logged in on some screens
    div.scrollTop=0; // I don't know why
  }
  isexpanded = 1-isexpanded;
  return false;
}

doonload=function(){
  //document.body.style.webkitOverflowScrolling = 'auto';
  var screenwidth = parseInt(getViewDivWidth());
  var bwidth = parseInt(screenwidth/50);
  if(bwidth<8) bwidth=8;
  if(bwidth>24) bwidth=24;
  $('view').style.paddingLeft=bwidth+'px';
  $('view').style.paddingRight=bwidth+'px';
  //setTimeout('setparagraphs();', 100);
  setparagraphs();
  setCookie('rev_lastloc', mitm+';'+page+';'+test+';'+book+';'+chap+';'+vers, cookieexpiredays);
  if(page==0 && book>0) setCookie('rev_lastbibleloc', mitm+';'+page+';'+test+';'+book+';'+chap+';'+vers, cookieexpiredays);
  setCookie('rev_screenwidth', screenwidth, cookieexpiredays);
  //alert(isexpanded);
  if(isexpanded==1) setTimeout("excol(0);", 100);
}

function addLoadEvent(func) {
  //https://gist.github.com/dciccale/4087856
  var b=document,c='addEventListener';
  b[c]?b[c]('DOMContentLoaded',func):window.attachEvent('onload',func);
}

function getViewDivWidth() {
  //return Math.max(document.documentElement.clientWidth, window.innerWidth || 0)-((isexpanded==1)?200:0)+'px';
  return Math.max(document.documentElement.clientWidth, window.innerWidth || 0)+'px';
}

function setparagraphs(){
  var pchoice=prfparachoice;
  if(page==0 && prfversebreak==1){
    pchoice = ((prfparachoice>2)?prfparachoice-2:prfparachoice);
  }
  var view = document.getElementById('view');
  var tags=view.getElementsByTagName("p");
  for (var i=0, l=tags.length; i<l; i++){
    cname = tags[i].className;
    if(cname!='hp' && cname!='hp1' && cname!='lst' && cname!='spc') {
      tags[i].className = "style"+pchoice;
    }
    // this will justify HP
    //else
    //if(cname=='hp' || cname=='hp1') {
    //  tags[i].style.textAlign='justify';
    //}
  }
  if(prfparachoice==2 || prfparachoice==4){
    tags=view.getElementsByTagName("li");
    for (var i=0, l=tags.length; i<l; i++){tags[i].style.textAlign='justify';}
  }
  setTimeout('try{popp.outer.style.fontSize='+(.9*prffontsize)+'+\'em\';}catch(e){}', 1200);
}

function scrolltopos(fromid, toid, offset){
  if(offset === undefined) offset=0;
  var spaceneeded = getspaceneeded();
  var ypos = Math.floor(findTopPos(toid))+offset-spaceneeded;
  if(prfscrollynav){
    var yfrompos = Math.floor(findTopPos(fromid))-spaceneeded;
    if(Math.abs((yfrompos-ypos))>2300) {
      var gotopos = ((yfrompos>ypos)?ypos+2300:ypos-2300);
      window.scrollTo(0, gotopos);
    }
    callJSanim(ypos, toid, spaceneeded);
  }else{
    window.scrollTo(0,ypos);
  }
  return false;
}

function scrolltotop(id){
  // passing id only to pass to callJSAnim() for failures
  // here it is not pertinent because going up works in IE
  // it's necessary in scrolltopos() because scrolling down fails in IE
  if(id === undefined) id='botbot';
  if(prfscrollynav){
    cursorY = getCursorY();
    if(cursorY>2300) window.scrollTo(0,2300);
    callJSanim(0, id, 0);
  }else
    window.scrollTo(0,0);
  return false;
}

function callJSanim(ypos, toid, nospcneeded){
  var scrolltime = 700;
  jsAnimScroll.cubic_bezier(0.00, 1.00, 1.00, 1.00, window, ypos, scrolltime, 20);
  if(ypos>0){
    setTimeout('scrollfail(\''+toid+'\', '+ypos+', '+nospcneeded+');', (scrolltime+100));
  }
}

function getCursorY(e) {
  var ret;
  e = e || window.event;
  if (e.pageX || e.pageY) {
    ret = e.pageY;
  }else{
    ret = e.clientY +
          (document.documentElement.scrollTop ||
          document.body.scrollTop) -
          document.documentElement.clientTop;
  }
  return ret;
}

function scrollfail(id, origy, spc){
  var ypos = parseInt(Math.max(window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0));
  if(Math.abs(ypos-origy)>2){
    //alert('ypos: '+ypos);
    //alert('origy: '+origy);
    //alert('fire fail');
    ypos = Math.floor(findTopPos(id));
    window.scrollTo(0,ypos-spc);
  }
}

function getspaceneeded() {
  var ua = window.navigator.userAgent;
  //alert(ua);
  var test;
  var result = 0;

  // firefox
  test = ua.toLowerCase().indexOf('firefox');
  if(test > -1) result = 15;

  // MS browsers
  test = ua.indexOf('MSIE ');
  if(test > -1) result = 20;

  test = ua.indexOf('Trident/');
  if(test > -1) result = 20;

  test = ua.indexOf('Edge/');
  if(test > -1) result = 12;

  //alert(result);
  return result+hdrheight+3; // hdrheight is set in pagebot.php
}

//
// bookmark functions
//
function addbmk(p,t,b,c,v){
  var bmks = decodeURIComponent(getCookie('rev_bookmarks'));
  var bmk = p+','+t+','+b+','+c+','+((p==0 && v>0)?'nav':'')+v;
  bmks = bmk+((bmks=='')?'':';')+bmks;
  bmks = checknumbmks(bmks);
  setCookie('rev_bookmarks', bmks, cookieexpiredays);
  bmk = myrevid+'|'+t+'|'+b+'|'+c+'|'+v;
  try{$('mrimg_'+bmk).setAttribute('data-isbmked', '1');}catch(e){}
  reloadBookmarks(page,0);
}
function checknumbmks(bks){
  var arbmks = bks.split(';');
  if(arbmks.length>bookmarksallowed){
    arbmks.pop();
    bks = arbmks.join(';');
  }
  return bks;
}
function delbmks(){
  if(confirm('Are you sure you want to delete all your bookmarks?\n\nThis is not undoable!')){
    setCookie('rev_bookmarks', '', cookieexpiredays);
    reloadBookmarks(page,1);
    location.reload();
  }
}
function delbmk(bmk){
  var titl='oops';
  try{titl=$('id_'+bmk).innerHTML}catch(e){titl='oops'};
  if(confirm('Delete the bookmark for\n'+titl+'?')){
    var bmks = decodeURIComponent(getCookie('rev_bookmarks'))+';';
    bmks = bmks.replace(bmk+';', '');
    bmks = bmks.substring(0,(bmks.length-1));
    setCookie('rev_bookmarks', bmks, cookieexpiredays);
    bmk = myrevid+bmk.substring(bmk.indexOf(','));
    bmk = bmk.replace('nav','');
    bmk = bmk.replace(/,/g,'|');
    try{$('mrimg_'+bmk).setAttribute('data-isbmked', '0');}catch(e){}
    reloadBookmarks(page,0);
  }
  return false;
}

function reloadBookmarks(page,saveonly,forcebible){
  if(forcebible==undefined) forcebible=0;
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange=function(){
    if (xmlhttp.readyState==4 && xmlhttp.status==200 && saveonly==0){
      var ret = JSON.parse(xmlhttp.responseText);
      //alert('here');
      try{$("bmkcontent").innerHTML = ret.html;}catch(e){}
      eval(ret.jscript); // this is for Sortable
      sizenavto(Math.min((document.documentElement.clientHeight-80), ($('bnspan').scrollHeight+3)));
    }
  }
  //alert('here');
  xmlhttp.open('GET','/jsonbookmarks.php?fromloc='+page+':'+test+':'+book+':'+chap+':'+((page==0 && forcebible==1)?-1:vers),true);
  xmlhttp.send();
}

//
// functions used to scroll to and hilite where a footnote is referenced
//
function hilitefoot(fromid, id){
  var doscroll=0,
      spaceneeded = getspaceneeded(),
      toppos = Math.floor(findTopPos('ft'+id))-spaceneeded-30;
  tcolor=$('ft'+id).style.color; // tcolor is global, inited at top of this file
  if(Math.max(window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0) > (toppos-toffset)){
    scrolltopos(fromid, 'ft'+id, -(toffset+30));
    doscroll=1;
  }
  setTimeout('popfoot(\''+id+'\');', ((prfscrollynav==1 && doscroll==1)?700:200));
}
function findTopPos(id) {
  var el = document.getElementById(id);
  var de = document.documentElement;
  var bx = el.getBoundingClientRect();
  var tp = bx.top + window.pageYOffset - de.clientTop;
  return tp;
}
function popfoot(id){
  var foot = $('ft'+id);
  foot.style.color='#ff0000';
  foot.style.fontWeight='bold';
  growit(id, 7);
}
function growit(id, idx){
  var foot = $('ft'+id);
  if(idx<18){
    foot.style.fontSize = (idx*10)+'%';
    idx++;
    setTimeout('growit(\''+id+'\','+idx+')', 50);
  }else setTimeout('shrinkit(\''+id+'\','+(idx-1)+')', 280);
}
function shrinkit(id, idx){
  var foot = $('ft'+id);
  if(idx>6){
    foot.style.fontSize = (idx*10)+'%';
    idx--;
    setTimeout('shrinkit(\''+id+'\','+idx+')', 50);
  }else{
    foot.style.fontSize = '70%';
    //foot.style.color='#999999';
    foot.style.color=tcolor; // tcolor is global, inited at top of this file
    foot.style.fontWeight='normal';
  }
}

//
// get / set cookies
//

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

function setSessionCookie(cname, cvalue) {
    document.cookie = cname + "=" + cvalue + "; path=/";
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + encodeURIComponent(cvalue) + "; " + expires + "; path=/";
}

//
// footnotes management
//

function addfootnote(pre,idx){
  var tbl = $(pre+'footnotes');
  var newrow, cell, tlen;
  tlen = tbl.children.length;
  for(var ni=(tlen-1);ni>=idx;ni--){
    //$(pre+'addf'+ni).setAttribute('onclick', 'addfootnote(\''+pre+'\','+(ni+2)+');');
    $(pre+'delf'+ni).setAttribute('onclick', 'removefootnote(\''+pre+'\','+(ni+1)+');');
    $(pre+'quof'+ni).setAttribute('onclick', 'appendquotes(\''+pre+'\','+(ni+1)+');');
    $(pre+'emsf'+ni).setAttribute('onclick', 'appendems(\''+pre+'\','+(ni+1)+');');
    //$(pre+'addf'+ni).id = pre+'addf'+(ni+1);
    $(pre+'delf'+ni).id = pre+'delf'+(ni+1);
    $(pre+'quof'+ni).id = pre+'quof'+(ni+1);
    $(pre+'emsf'+ni).id = pre+'emsf'+(ni+1);
    $(pre+'fidx'+ni).id = pre+'fidx'+(ni+1);
  }
  newfn = document.createElement('div');
  //newfn.innerHTML = '&nbsp;&nbsp;<a id="'+pre+'addf'+idx+'" onclick="addfootnote(\''+pre+'\','+(idx+1)+');"><img src="/i/tbl_show.png" alt="" border="0" title="add footnote after" /></a>&nbsp;';
  //newfn.innerHTML+= '<a id="'+pre+'delf'+idx+'" onclick="removefootnote(\''+pre+'\','+idx+');"><img src="/i/tbl_hide.png" alt="" border="0" title="delete this footnote" /></a>&nbsp;';
  newfn.innerHTML+= '&nbsp;&nbsp;<a id="'+pre+'delf'+idx+'" onclick="removefootnote(\''+pre+'\','+idx+');"><img src="/i/tbl_hide.png" alt="" border="0" title="delete this footnote" /></a>&nbsp;';
  //newfn.innerHTML+= '<img src="/i/mnu_menu'+colors[0]+'.png" class="bmksorthandle" style="margin-bottom:-3px;width:1em;cursor:ns-resize;" alt="" /> ';
  newfn.innerHTML+= '<span id="'+pre+'fidx'+idx+'" class="fnidx">'+idx+'</span> ';
  newfn.appendChild(createTextEl(pre+'footnote', ((ismobile==1)?'65%':'77%')));
  newfn.innerHTML+='&nbsp;<a id="'+pre+'quof'+idx+'" onclick="appendquotes(\''+pre+'\','+idx+');" title="click to append smart quotes">&ldquo;&rdquo;</a>';
  newfn.innerHTML+= '&nbsp;<a id="'+pre+'emsf'+idx+'" onclick="appendems(\''+pre+'\','+idx+');" title="click to append emphasize <em> tags"><small>em</small></a>';
  tbl.insertBefore(newfn, tbl.children[idx]);
  renumfns(pre);
  document.getElementsByName(pre+'footnote[]')[idx].focus();
}

function removefootnote(pre,idx){
  var deleted = 0, tbl = $(pre+'footnotes');
  var ftn = trim(document.getElementsByName(pre+'footnote[]')[idx].value);
  if(ftn != ''){
    if(confirm('Are you sure you want to delete this footnote?\n\n'+ftn.substring(0, 40)+'..')){
      tbl.removeChild(tbl.children[idx]);
      document.frm.dirt.value = 1;
      deleted = 1;
    }
  }else{
    tbl.removeChild(tbl.children[idx]);
    deleted = 1;
  }
  if(deleted==1){
    var tlen = tbl.children.length;
    for(var ni=(idx+1);ni<=tlen;ni++){
      //$(pre+'addf'+ni).setAttribute('onclick', 'addfootnote(\''+pre+'\','+(ni)+');');
      $(pre+'delf'+ni).setAttribute('onclick', 'removefootnote(\''+pre+'\','+(ni-1)+');');
      $(pre+'quof'+ni).setAttribute('onclick', 'appendquotes(\''+pre+'\','+(ni-1)+');');
      $(pre+'emsf'+ni).setAttribute('onclick', 'appendems(\''+pre+'\','+(ni-1)+');');
      //$(pre+'addf'+ni).id = pre+'addf'+(ni-1);
      $(pre+'delf'+ni).id = pre+'delf'+(ni-1);
      $(pre+'quof'+ni).id = pre+'quof'+(ni-1);
      $(pre+'emsf'+ni).id = pre+'emsf'+(ni-1);
      $(pre+'fidx'+ni).id = pre+'fidx'+(ni-1);
    }
  }
  renumfns(pre);
}
function renumfns(pre){
  var tbl = $(pre+'footnotes');
  var tlen = tbl.children.length;

  var items = tbl.getElementsByClassName("fnidx");
  for (var ni=0;ni<items.length; ni++) {
    items[ni].innerHTML = (ni+1);
  }
}
// this function is for reindexing footnotes after drag-drop sorting
function fnreindex(pre, oidx, nidx, alrt){
  if(alrt==1) alert('oldidx: '+oidx+'\nnewidx: '+nidx);
  // change onclicks
  $(pre+'addf'+oidx).setAttribute('onclick', 'addfootnote(\''+pre+'\','+(nidx+1)+');');
  $(pre+'delf'+oidx).setAttribute('onclick', 'removefootnote(\''+pre+'\','+nidx+');');
  $(pre+'quof'+oidx).setAttribute('onclick', 'appendquotes(\''+pre+'\','+nidx+');');
  $(pre+'emsf'+oidx).setAttribute('onclick', 'appendems(\''+pre+'\','+nidx+');');
  // change id's
  $(pre+'addf'+oidx).id = pre+'addf'+nidx;
  $(pre+'delf'+oidx).id = pre+'delf'+nidx;
  $(pre+'quof'+oidx).id = pre+'quof'+nidx;
  $(pre+'emsf'+oidx).id = pre+'emsf'+nidx;
  $(pre+'fidx'+oidx).id = pre+'fidx'+nidx;
}

function checkfootnotes(typ, data){
  try{
    var fns = document.getElementsByName(typ+'footnote[]');
    var numfns = fns.length;
    for(var x of fns.values()){
      x.value = trim(x.value);
      if(x.value=='' || x.value=='missing footnote'){
        alert('There is a missing footnote.\n\nPlease correct the error.');
        $('div'+typ+'footnotes').style.display = 'block';
        x.value='missing footnote';
        x.focus();
        x.select();
        return false;
      }
    }
    var numtags = (data.match(/\[fn\]/g) || []).length;
    if(numfns != numtags){
      alert(((typ=='vrs')?'Verse':'Commentary')+' footnotes mismatch:\n\nThere are '+numtags+' footnote tags,\nand '+numfns+' footnotes.\n\nPlease correct the error.');
      return false;
    }
  }catch(e){}
  return true;
}

function createTextEl(nam, wid){
  var el = document.createElement('input');
  el.type = 'text';
  el.name = nam+'[]';
  //el.style.width = ((ismobile)?'65':'77')+'%';
  el.style.width = wid;
  el.style.marginTop = '2px';
  el.setAttribute("autocomplete", "off");
  // this fires on creation.  Why?
  el.onchange = setdirt();
  return el;
}

function appendquotes(pre,idx){
  var el=document.getElementsByName(pre+'footnote[]')[idx];
  doinput(el, '“', '”');
}
function appendems(pre,idx){
  var el=document.getElementsByName(pre+'footnote[]')[idx];
  doinput(el, '<em>', '</em>');
}

function doinput(el,pre,suf){
  //el.value = trim(el.value);
  var bstr, cstr, estr;
  var txt = el.value;
  var selstart = el.selectionStart;
  var selend = el.selectionEnd;
  if(selstart==selend && selend==0 && el.value!=''){
    selstart = el.value.length;
    selend=selstart;
  }
  bstr = txt.substring(0,selstart);
  cstr = txt.substring(selstart, selend);
  estr = txt.substring(selend);
  el.value = bstr+pre+cstr+suf+estr;
  el.setSelectionRange(selstart+pre.length, selend+pre.length);
  setdirt();
  el.focus();
}

function excoldiv(id){
  // for animation, set style=transition:height Xsec
  var div = $(id);
  if(!prfscrollynav) div.style.transition = 'height 0s';
  if(div.style.height=='0px'){div.style.height=(div.scrollHeight)+'px';}
  else{div.style.height=0;}
}


  //
  // preferences
  //
  function resetcookies(){
    setCookie('rev_preferences', 'x', -1);
    setCookie('rev_biblefontprefs', 'x', -1);
    setCookie('rev_lastloc', 'x', -1);
    setCookie('rev_expprefs', 'x', -1);
    setCookie('rev_timezone', 'x', -1);
    setCookie('rev_history', 'x', -1);
    setCookie('rev_bookmarks', 'x', -1);
    setCookie('rev_inapp', 'x', -1);
    setCookie('rev_isexpanded', 'x', -1);
    setCookie('rev_lastbibleloc', 'x', -1);
    setCookie('rev_lightbox_permanent', 'x', -1);
    setCookie('rev_lightbox_session', 'x', -1);
    setCookie('rev_wnblog', 'x', -1);
    setCookie('rev_rswismobile', 'x', -1);
    setCookie('rev_rswinapp', 'x', -1);
    var screenwidth = parseInt(getViewDivWidth());
    setCookie('rev_screenwidth', screenwidth, cookieexpiredays);
  }

  function saveprefs(){
    var prefs = prfviewcols+';'+prfversebreak+';'+prffontsize+';'+
                prflineheight+';'+prffontfamily+';'+prfswipenav+';'+
                prfuseoefirst+';'+prfparachoice+';'+prfnavonchap+';'+
                prfcommnewtab+';'+prfcolortheme+';'+prfcommlinkstyl+';'+
                prflexicon+';'+prfscrollynav+';'+prfshowdevitems+';'+
                prfshowcommlinks+';'+prfviewversnav+';'+prfshowpdflinks+';'+
                prfversnavwhat+';'+prflinkcommentary+';'+prfucaseot+';'+prfdiffbiblefont;
    //alert(prefs);
    setCookie('rev_preferences', prefs, cookieexpiredays);
  }

  function setcolumns(x){
    var maxcols = ((ismobile)?3:5);
    prfviewcols+=x;
    if(prfviewcols>maxcols) prfviewcols=maxcols;
    if(prfviewcols==0) prfviewcols=1;
    var divs = document.querySelectorAll("[id^='bblviewcols']");
    for(ni=0;ni<divs.length;ni++) divs[ni].className = 'col'+prfviewcols+'container';
    //$('bblviewcols').className = 'col'+prfviewcols+'container';
    saveprefs();
  }

  function setviewcols(cols) {
    prfviewcols = cols;
    saveprefs();
  }

  function setversebreak(val) {
    prfversebreak = val;
    //if(prfversebreak==1) prfuseoefirst=0;
    saveprefs();
  }

  function setversenavwhat(val) {
    prfversnavwhat = val;
    saveprefs();
  }

  function settextsize(multiplier) {
    var view = $('view');
    if (view.style.fontSize=="" || multiplier==99) {
      view.style.fontSize = (ismobile==0)?"1em":"1.3em";
      multiplier = 0;
    }
    prffontsize = (parseFloat(view.style.fontSize) + (multiplier * 0.1)).toFixed(2);
    view.style.fontSize = prffontsize + "em";
    popp.outer.style.fontSize = (.9*prffontsize)+"em";
    saveprefs();
    return false;
  }

  function setlineheight(multiplier) {
    var view = $('view');
    if (view.style.lineHeight=="" || multiplier==99) {
      view.style.lineHeight = "1.3em";
      $('nav').style.lineHeight = "1.3em";
      multiplier = 0;
    }
    prflineheight = (parseFloat(view.style.lineHeight) + (multiplier * 0.1)).toFixed(2);
    view.style.lineHeight = prflineheight + "em";
    //$('nav').style.lineHeight = prflineheight + "em";
    saveprefs();
    return false;
  }

  function setfontfamily(family, idx) {
    var fallback = ((idx<4)?', times new roman':', arial');
    prffontfamily = family;
    document.body.style.fontFamily = family+fallback;
    if(page!=0 || prfdiffbiblefont==0) $('view').style.fontFamily = family+fallback;
    saveprefs();
  }

  function setswipenav(val){
    prfswipenav = val;
    saveprefs();
    location.reload();
  }

  function setuseoefirst(yesno){
    prfuseoefirst = (yesno==true)?1:0;
    saveprefs();
  }

  function setdiffbiblefont(yesno){
    prfdiffbiblefont = (yesno==true)?1:0;
    //if(yesno==false) setCookie('rev_biblefontprefs', 'x', -1);
    saveprefs();
  }

  function setnavonchap(yesno){
    prfnavonchap = (yesno==true)?1:0;
    saveprefs();
  }

  function setcommnewtab(yesno){
    prfcommnewtab = (yesno==true)?1:0;
    saveprefs();
  }

  function setshowcommlinks(yesno){
    prfshowcommlinks = (yesno==true)?1:0;
    saveprefs();
  }

  function setcolortheme(){
    switch(prfcolortheme){
    case 0:prfcolortheme=2;break;
    case 1:prfcolortheme=0;break;
    case 2:prfcolortheme=1;break;
    }
    saveprefs();
  }

  function setcolortheme2(clr){
    prfcolortheme = clr;
    saveprefs();
  }

  function setparachoice(f) {
    var paraindent = ((f.paraindent.checked)?1:0);
    var parjustify = ((f.parjustify.checked)?1:0);

    if(paraindent==0 && parjustify==0) prfparachoice=1;
    if(paraindent==0 && parjustify==1) prfparachoice=2;
    if(paraindent==1 && parjustify==0) prfparachoice=3;
    if(paraindent==1 && parjustify==1) prfparachoice=4;

    saveprefs();
  }

  function setparachoice2(whch, val) {
    var indent = ((prfparachoice==3 || prfparachoice==4)?1:0);
    var jstify = ((prfparachoice==2 || prfparachoice==4)?1:0);
    if(whch=='a') jstify=val;
    if(whch=='i') indent=val;
    if(indent==0 && jstify==0) prfparachoice=1;
    if(indent==0 && jstify==1) prfparachoice=2;
    if(indent==1 && jstify==0) prfparachoice=3;
    if(indent==1 && jstify==1) prfparachoice=4;
    saveprefs();
    setparagraphs();
  }

  function setlexicon(choice) {
    prflexicon = choice;
    saveprefs();
  }

  function setlinkcommentary(choice) {
    prflinkcommentary = choice;
    saveprefs();
  }

  function setscrollynav(yesno){
    prfscrollynav = (yesno==true)?1:0;
    saveprefs();
  }

  function setcommlinkstyl(styl) {
    prfcommlinkstyl = styl;
    saveprefs();
  }

  function setshowdevitems(yesno){
    prfshowdevitems = (yesno==true)?1:0;
    saveprefs();
  }

  // for Rob
  function toggleismobile(yesno){
    var prfrswismobile = (yesno==true)?1:0;
    setCookie('rev_rswismobile', prfrswismobile, cookieexpiredays);
  }
  function toggleinapp(yesno){
    setCookie('rev_rswinapp', (yesno==true)?1:0, (yesno==true)?.25:-1); // 6 hours
  }

  function setviewversnav(yesno){
    prfviewversnav = (yesno==true)?1:0;
    saveprefs();
  }

  function setucaseot(yesno){
    prfucaseot = (yesno==true)?1:0;
    saveprefs();
  }

  function setshowpdflinks(yesno){
    prfshowpdflinks = (yesno==true)?1:0;
    saveprefs();
  }

  
  function savebiblefontprefs(){
    var prefs = prfbiblefontsize+';'+ prfbiblelineheight+';'+prfbiblefontfamily;
    setCookie('rev_biblefontprefs', prefs, cookieexpiredays);
  }
  function setbibletextsize(multiplier) {
    var view = $('view');
    if (view.style.fontSize=="" || multiplier==99) {
      view.style.fontSize = (ismobile==0)?"1em":"1.3em";
      multiplier = 0;
    }
    prfbiblefontsize = (parseFloat(view.style.fontSize) + (multiplier * 0.1)).toFixed(2);
    view.style.fontSize = prfbiblefontsize + "em";
    popp.outer.style.fontSize = (.9*prfbiblefontsize)+"em";
    savebiblefontprefs();
    return false;
  }

  function setbiblelineheight(multiplier) {
    var view = $('view');
    if (view.style.lineHeight=="" || multiplier==99) {
      view.style.lineHeight = "1.3em";
      $('nav').style.lineHeight = "1.3em";
      multiplier = 0;
    }
    prfbiblelineheight = (parseFloat(view.style.lineHeight) + (multiplier * 0.1)).toFixed(2);
    view.style.lineHeight = prfbiblelineheight + "em";
    savebiblefontprefs();
    return false;
  }

  function setbiblefontfamily(family, idx) {
    var fallback = ((idx<4)?', times new roman':', arial');
    prfbiblefontfamily = family;
    $('view').style.fontFamily = family+fallback;
    savebiblefontprefs();
  }

//
// popups
// hopefully it will also replace a bunch of code in findvers and findcomm
// need to encapsulate it somehow
//
var popwin, popdoc, popbody, popp, popcheckPosTimeout, popdivShowTimer;

//pophandleLinkMouseOver = function(e) {
function pophandleLinkMouseOver(popind, param) {
  //if (!e) {e = popwin.event;}
  e = popwin.event;
  popclearPositionTimer();
  var target = (e.target)?e.target:e.srcElement;
  popp.outer.style.display = 'block';
  popp.outer.style.visibility = 'hidden';
  popp.outer.style.zIndex = 101;
  popdivShowTimer = setTimeout('popp.outer.style.visibility = \'visible\';', 300);
  popp.header.innerHTML = 'Footnote ('+popind+')';
  popp.content.innerHTML = eval(param);
  poppositionPopup(target);
}

function pophandleLinkMouseOut() {
  clearTimeout(popdivShowTimer);
  popdivShowTimer = null;
  popstartPositionTimer();
}

function poppositionPopup(target) {
  var viewport = popgetWindowSize();
  var pos = popgetPosition(target);
  var x = pos.left - (popp.outer.offsetWidth/2);
  var y = pos.top - popp.outer.clientHeight;
  if (x<0) {x=4;}
  else if (x+10+popp.outer.clientWidth >= viewport.width) {x = viewport.width - popp.outer.clientWidth - 16;}

  if (y<0 || (y<(popgetScroll().y+$('header').clientHeight))) {y = pos.top + target.offsetHeight;} // above the screen
  else{y = y-2;}

  popp.outer.style.top = y + 'px';
  popp.outer.style.left = x + 'px';
}

function pophandlePopupMouseOver() {popclearPositionTimer();}
function pophandlePopupMouseOut() {popstartPositionTimer();}
function popstartPositionTimer() {popcheckPosTimeout = setTimeout(pophidePopup, 200);}
function popclearPositionTimer() {
  clearTimeout(popcheckPosTimeout);
  popcheckPosTimeout = null;
}
function pophidePopup() {popp.outer.style.display = 'none';}

function popgetPosition(node) {
  var curtop = 0;
  if (node.offsetParent) {
    do{curtop += node.offsetTop;}
    while (node = node.offsetParent);
  }
  return {left:cursorX, top:curtop};
}

function popgetWindowSize() {
  var width = 0,
      height = 0;
  if( typeof( popwin.innerWidth ) == 'number' ) {
    // Non-IE
    width = popwin.innerWidth;
    height = popwin.innerHeight;
  } else if( popdoc.documentElement && ( popdoc.documentElement.clientWidth || popdoc.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    width = popdoc.documentElement.clientWidth;
    height = popdoc.documentElement.clientHeight;
  } else if( popbody && ( popbody.clientWidth || popbody.clientHeight ) ) {
    //IE 4 compatible
    width = popbody.clientWidth;
    height = popbody.clientHeight;
  }
  return {width:width, height: height};
}

function popgetScroll() {
  var scrOfX = 0,
      scrOfY = 0;
  if( popbody && ( popbody.scrollLeft || popbody.scrollTop ) ) {
    //DOM compliant
    scrOfY = popbody.scrollTop;
    scrOfX = popbody.scrollLeft;
  } else if( popdoc.documentElement && ( popdoc.documentElement.scrollLeft || popdoc.documentElement.scrollTop ) ) {
    //IE6 standards compliant mode
    scrOfY = popdoc.documentElement.scrollTop;
    scrOfX = popdoc.documentElement.scrollLeft;
  } else if( typeof( popwin.pageYOffset ) == 'number' ) {
    //Netscape compliant
    scrOfY = popwin.pageYOffset;
    scrOfX = popwin.pageXOffset;
  }
  return {x:scrOfX, y:scrOfY };
}

function popaddEvent(obj,name,fxn) {
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
}

function initpoppopup(){
  popwin = window;
  popdoc = document
  popbody = popdoc.body;
  // create popup
  popp = {outer: popdoc.createElement('div')};
  var parts = ['header','content'],
      i, il, div, name, node = null;
  popp.outer.className = 'findvers_popup_outer';
  // build all the parts
  for (i=0,il=parts.length; i<il; i++) {
    name = parts[i];
    div = popdoc.createElement('div');
    div.className = 'findvers_popup_' + name;
    popp.outer.appendChild(div);
    popp[name] = div;
  }
  //popp.header.style.whiteSpace='nowrap';
  popbody.appendChild(popp.outer);
  popaddEvent(popp.outer,'mouseover',pophandlePopupMouseOver);
  popaddEvent(popp.outer,'mouseout',pophandlePopupMouseOut);
}

//popaddEvent(document,'DOMContentLoaded',initpoppopup);
popaddEvent(window,'load',initpoppopup);


