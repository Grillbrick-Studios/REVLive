// functions for MyREV users

//
// lightbox functions for editor
//
function rlightbox(fil,loc,edt){
  if(edt===undefined) edt=0;
  var txt='', src, rlightbox=document.createElement('DIV');
  switch(fil){
  case 'note':
    src = '/myrevnotes.php?loc='+loc+'&edt='+edt;
    break;
  case 'enote':
    src = '/editnote.php?loc='+loc;
    break;
  case 'pnote':
    src = '/peernote.php?loc='+loc;
    break;
  case 'keys':
    src = '/myrevkeys.php';
    break;
  case 'tut':
    src = '/myrevtutorial.php';
    break;
  default:
    alert('I\'m lost');
  }
  rlightbox.id='lightbox';
  rlightbox.onclick=function(){try{this.children[0].children[0].children[0].contentWindow.olClose();}catch(e){}};
  txt = '<div id="resdiv" style="position:relative;top:50%;transform:translateY(-50%);height:90%;overflow:hidden;width:90%;max-width:1024px;left:0;right:0;margin:auto;padding:0 10px;background-color:'+colors[2]+';border:1px solid '+colors[6]+';border-radius:8px;">';
  txt+= '<div style="position:relative;height:100%;overflow-x:hidden;overflow-y:scroll;z-index:88;">';
  txt+= '<iframe src="'+src+'" frameborder="0" allowfullscreen '+
        'style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:88;min-height:440px;"></iframe>';
  txt+= '</div>';
  txt+= '</div>';
  rlightbox.innerHTML = txt;
  rlightbox.style.zIndex=99;
  document.body.appendChild(rlightbox);
  rlightbox.style.transition='opacity .5s';
  setTimeout('rlbfadein();', 1);
}
function rlbfadein(){
  var lb = document.getElementById('lightbox');
    lb.style.opacity=1;
}
function rlbfadeout(){
  var lb = document.getElementById('lightbox');
  lb.style.opacity=0;
  setTimeout("closerlb()", 800);
}
function closerlb(){
  var lb = document.getElementById('lightbox');
  lb.parentNode.removeChild(lb);
}

//
// preferences
//
function savemyrevprefs(){
var prefs = myrevmode+';'+myrevsort+';'+myrevpagsiz+';'+myrevshownotes+';'+myrevclick+';'+ednotesshowall+';'+myrevshowkey+';'+myrevshoweditorfirst+';'+viewedcomments+';'+viewmrcomments+';'+peernotesshowall+';'+viewpeernotes;
setCookie('rev_myrevprefs', prefs, cookieexpiredays);
//setCookie('myrevsid', myrevsid, cookieexpiredays);
}

function setmyrevmode(val) {
myrevmode = val;
savemyrevprefs();
}

function setedcomments(val) {
viewedcomments = val;
savemyrevprefs();
}

function setpeernotes(val) {
viewpeernotes = val;
savemyrevprefs();
}

function setpeernotesshowall(val) {
peernotesshowall = val;
savemyrevprefs();
}

function setednotesshowall(val) {
ednotesshowall = val;
savemyrevprefs();
}

function setmrcomments(val) {
viewmrcomments = val;
savemyrevprefs();
}

function setmyrevsort(val) {
myrevsort = val;
savemyrevprefs();
}

function setmyrevpagsiz(val) {
myrevpagsiz = val;
savemyrevprefs();
}

function setmyrevshownotes(val) {
myrevshownotes = val;
savemyrevprefs();
}

function setmyrevclick(val) {
myrevclick = val;
savemyrevprefs();
}

function setmyrevshowkey(val) {
myrevshowkey = val;
savemyrevprefs();
}

function setmyrevshoweditorfirst(val) {
myrevshoweditorfirst = val;
savemyrevprefs();
}

//
// functions for myrevdiv
//
var myrevcheckPosTimeout;

function myrevhandlemouseover() {myrevclearPositionTimer();}
function myrevhandlemouseout() {myrevstartPositionTimer();}
function myrevstartPositionTimer(dur) {
  if(dur===undefined) dur=100;
  myrevcheckPosTimeout = setTimeout('myrevhidePopup()', dur);
  }
function myrevclearPositionTimer() {
  clearTimeout(myrevcheckPosTimeout);
  myrevcheckPosTimeout = null;
}
function myrevhidePopup(divid) {
  if(divid===undefined) divid = 'myrevdiv';
  try{
    $(divid).style.opacity = 0;
    setTimeout('$(\''+divid+'\').style.display = \'none\'', 400);
  }catch(e){}
}

function initmyrevpopup(divid){
  myrev = {divmyrev: document.createElement('div')};
  document.body.appendChild(myrev.divmyrev);
  myrev.divmyrev.id=divid;
  myrev.divmyrev.style.position='absolute';
  myrev.divmyrev.style.display='none';
  myrev.divmyrev.style.zIndex=89;
  myrev.divmyrev.style.opacity=0;
  myrev.divmyrev.style.transition='.4s';
  myrev.divmyrev.style.whiteSpace='nowrap';
  myrev.divmyrev.style.backgroundColor=colors[2];
  myrev.divmyrev.style.padding='6px 6px 2px 6px';
  myrev.divmyrev.style.border='1px solid '+colors[1]+'';
  myrev.divmyrev.style.borderRadius=4+'px';
  myrev.divmyrev.style.boxShadow='0 3px 5px rgba(50,50,50,0.5)';
  myrev.divmyrev.innerHTML='';
}

var gbmstr, gqry, grefr, gbmktimeout;
function gethilightdivcontents(qry, hlit, clink, hvcom, hvmrws, chngtxt, enlink, hvedwk, pnlink, hvprwk){
  var htm = '';
  if(clink!=''){
    var arl = clink.split('/');
    var arqry = qry.split('|');
    var refr = arl[1]+' '+arl[2]+((arqry[1]<2)?':'+arl[3]:'');
    var myrevid = arqry[0];
    try{sizenavto(0);}catch(e){}
    try{hvmrnot = $('mrimg_'+qry).getAttribute('data-havemrnote');}catch(e){hvmrnot=0;}
    try{isbmked = $('mrimg_'+qry).getAttribute('data-isbmked');}catch(e){isbmked=0;}
    htm = '<span style="display:inline-block;position:relative;width:100%;text-align:center;font-size:90%;font-weight:bold;padding:2px 0;"><a href="/myrev" class="comlink0" style="cursor:default;">'+refr+'</a>';
    var bmstr = page+'|'+arqry[1]+'|'+arqry[2]+'|'+arqry[3]+'|'+((page==0)?'nav':'')+arqry[4];
    htm+= '<a id="bmk" style="display:inline-block;position:absolute;top:1px;right:3px;" onclick="'+((isbmked==0)?'add':'del')+'bookmark();" ';
    refr+= ((page==5)?' commentary':'');
    htm+= 'title="'+((isbmked==1)?refr+' is bookmarked.':'Click to bookmark '+refr+'.')+'"><img id="bmkimg" src="/i/mnu_bookmarks'+((isbmked==1)?'_FILL':'')+colors[0]+'.png" alt="bookmarks" style="height:18px;" /></a>';
    gbmstr = bmstr; gqry=qry; grefr=refr;
    htm+= '</span><br />';
    if(arl[4]==1){
      clink = '/'+arl[1]+'/'+arl[2]+'/'+arl[3];
      htm += '<div style="white-space:nowrap;border-bottom:1px solid '+colors[3]+';margin-top:6px;margin-bottom:4px;">';
      htm += '<span style="display:inline-block;"><a href="'+clink.replace(' ', '-')+'" title="REV Commentary" target="'+((prfcommnewtab==1)?'_blank':'_self')+'"><img src="/i/myrev_commentary'+colors[0]+((hvcom==1)?'_DOT':'')+'.png" style="width:2.5em;margin:0 4px 0px 4px;" alt="REV Commentary" /></a></span>';
      htm += '<span style="display:inline-block;"><a onclick="rlightbox(\'note\',\''+qry+'\');myrevhidePopup();" title="My Notes on '+refr+'"><img src="/i/myrev_notes'+colors[0]+((hvmrnot==1)?'_DOT':'')+'.png" style="width:2.5em;margin:0 5px 0 8px;" alt="edit" /></a></span>';
      htm += '<span style="display:inline-block;"><a onclick="rlightbox(\'note\',\''+myrevid+'|0|0|0|0\');myrevhidePopup();" title="My Workspace"><img src="/i/myrev_workspace'+colors[0]+((hvmrws==1)?'_DOT':'')+'.png" style="width:2.5em;margin:0 3px 0 3px;" alt="edit" /></a></span>';
      htm += '</div>';
    }
  }
  htm+= '<div id="pallete">';
  if(myrevshowkey==1)
    htm += handlecolors(qry, hlit, 0, chngtxt);
  else
    htm += handlecolorsnocap(qry, hlit, 0, chngtxt);
  htm+= '</div>';
  htm+= '<div style="overflow:hidden;">';
  htm+= '<span style="display:'+((myrevshowkey==1)?'inline-block':'none')+';float:left;margin-left:3px;" id="dolabels"><a onclick="rlightbox(\'keys\',\'\');" title="edit captions"><img src="/i/myrev_editkeys'+colors[0]+'.png" style="width:.7em;margin-bottom:-2px;" alt="Edit Captions" /></a></span>';
  htm+= '<span style="display:inline-block;float:right;"><a onclick="changepallete(\''+qry+'\','+hlit+',0,'+chngtxt+')" title="legend" class="comlink0" style="color:'+colors[7]+';"><span id="legend" style="margin-right:4px;">'+((myrevshowkey==1)?'&laquo;':'&raquo;')+'</span></a></span>';
  htm+= '</div>';
  //htm+= '<span style="display:block;text-align:right;"><a onclick="changepallete(\''+qry+'\','+hlit+',0,'+chngtxt+')" title="legend" class="comlink0" style="color:'+colors[7]+';"><span id="legend">'+((myrevshowkey==1)?'&laquo;':'&raquo;')+'</span></a></span>';
  //alert(pnlink);
  //alert(peernotes);
  //alert(viewpeernotes);
  var both=0;
  if(pnlink==1 && peernotes>0 && viewpeernotes==1){
    try{hvprnot = $('peerimg_'+qry).getAttribute('data-havepeernote');}catch(e){hvprnot=0;}
    if(hvprnot==1 || canaddpeernote==1)
      pnonclick='rlightbox(\'pnote\',\''+qry+'\',1)';
    else
      pnonclick='alert(\'Sorry, you do not have permission\\nto add a reviewer note to this book.\')';
    htm+= '<hr style="margin:0 0 2px 0;padding:0" />';
    htm+= '<span  style="display:inline-block;margin-top:4px;"><a class="comlink0" style="color:'+colors[7]+';font-size:92%;" href="/pnotes"><small>Review<br />notes</small></a></span>';
    htm += '<span style="display:inline-block;float:right;"><a onclick="rlightbox(\'pnote\',\'-1|0|0|0|0\',1);myrevhidePopup();" title="Reviewer Workspace"><img src="/i/peer_workspace'+colors[0]+((hvprwk==1)?'_YELDOT':'')+'.png" style="width:2.5em;margin:0 4px 0 3px;" alt="edit" /></a></span>';
    htm += '<span style="display:inline-block;float:right;"><a onclick="'+pnonclick+';myrevhidePopup();" title="Reviewer Note"><img src="/i/peer_notes'+colors[0]+((hvprnot==1)?'_YELDOT':'')+'.png" style="width:2.5em;margin:0 5px 0 8px;" alt="edit" /></a></span>';
    both=1;
  }
  if(enlink==1 && editorcomments==1 && viewedcomments==1){
    try{hvednot = $('edimg_'+qry).getAttribute('data-haveednote');}catch(e){hvednot=0;}
    htm+= '<hr style="margin:'+((both==1)?'6px':'0')+' 0 2px 0;padding:0" />';
    htm+= '<span  style="display:inline-block;margin-top:4px;"><a class="comlink0" style="color:'+colors[7]+';font-size:92%;" href="/enotes"><small>Editor<br />notes</small></a></span>';
    htm += '<span style="display:inline-block;float:right;"><a onclick="rlightbox(\'enote\',\'-1|0|0|0|0\',1);myrevhidePopup();" title="Editor Workspace"><img src="/i/editor_workspace'+colors[0]+((hvedwk==1)?'_REDDOT':'')+'.png" style="width:2.5em;margin:0 4px 0 3px;" alt="edit" /></a></span>';
    htm += '<span style="display:inline-block;float:right;"><a onclick="rlightbox(\'enote\',\''+qry+'\',1);myrevhidePopup();" title="Editor Note"><img src="/i/editor_notes'+colors[0]+((hvednot==1)?'_REDDOT':'')+'.png" style="width:2.5em;margin:0 5px 0 8px;" alt="edit" /></a></span>';
  }
  return htm;
}
function addbookmark(){
  var bmks = decodeURIComponent(getCookie('rev_bookmarks'));
  var artmp = gbmstr.split('|');
  var bmk = artmp[0]+','+artmp[1]+','+artmp[2]+','+artmp[3]+','+((artmp[0]==0 && artmp[4]>0)?'nav':'')+artmp[4];
  bmks = bmk+((bmks=='')?'':';')+bmks;
  bmks = checknumbmks(bmks);
  setCookie('rev_bookmarks', bmks, cookieexpiredays);
  $('bmk').onclick = delbookmark;
  $('mrimg_'+gqry).setAttribute('data-isbmked', 1);
  $('bmkimg').src='/i/mnu_bookmarks_FILL'+colors[0]+'.png';
  $('bmk').title = 'Click to un-bookmark '+grefr;
  reloadBookmarks(page,1); // save to database only
  dobiblenav('navmitm=10&notification=\''+grefr+' has been bookmarked.\'');
  gbmktimeout = setTimeout('sizenavto(0);', 2500);
}

function delbookmark(){
  if(confirm('Delete the bookmark for\n'+grefr+'?')){
    var bmk = gbmstr.replace(/\|/g, ',');
    var bmks = decodeURIComponent(getCookie('rev_bookmarks'))+';';
    bmks = bmks.replace(bmk+';', '');
    bmks = bmks.substring(0,(bmks.length-1));
    setCookie('rev_bookmarks', bmks, cookieexpiredays);
    $('bmk').onclick = addbookmark;
    $('mrimg_'+gqry).setAttribute('data-isbmked', '0');
    $('bmkimg').src='/i/mnu_bookmarks'+colors[0]+'.png';
    $('bmk').title = 'Click to bookmark '+grefr;
    reloadBookmarks(page,1);
    dobiblenav('navmitm=10&notification=\''+grefr+' has been removed.\'');
    gbmktimeout = setTimeout('sizenavto(0);', 2500);
  }
}

function handlecolors(qry, hlit, islocal, chngtxt){
  var ret = '';
  for(var ni=0;ni<hlcolors.length;ni++){
    ret+= '<span style="display:inline-block;width:144px;height:20px;border:'+((hlit==ni)?'1px solid red':'1px solid '+colors[3])+';border-radius:4px;';
    ret+= 'color:'+colors[7]+';background-color:'+hlcolors[ni]+';font-size:70%;cursor:pointer;margin:1px 4px;padding:8px 2px 0 2px;" ';
    ret+= 'onclick="do'+((islocal==1)?'local':'')+'hilight(\''+qry+'\','+ni+','+chngtxt+');" />'+myrevkeys[ni]+'</span><br />\n';
  }
  return ret;
}

function handlecolorsnocap(qry, hlit, islocal, chngtxt){
  var ret = '';
  for(var ni=0;ni<hlcolors.length;ni++){
    ret+= '<span style="display:inline-block;width:40px;height:40px;border:'+((hlit==ni)?'1px solid red':'1px solid '+colors[3])+';border-radius:4px;background-color:'+hlcolors[ni]+';cursor:pointer;margin:4px'+((ni>2)?' 4px 1px 4px':'')+';"';
    ret+= ' onclick="do'+((islocal==1)?'local':'')+'hilight(\''+qry+'\','+ni+','+chngtxt+');" title="'+myrevkeys[ni]+'" />&nbsp;</span>'+((ni==2)?'<br />':'')+'\n';
  }
  return ret;
}

function changepallete(qry, hlit, x, chngtxt){
  myrevshowkey = 1-myrevshowkey;
  setmyrevshowkey(myrevshowkey);
  var pallete = document.getElementById('pallete');
  var legend  = document.getElementById('legend');
  var dolabels= document.getElementById('dolabels');
  if(myrevshowkey==1)
    pallete.innerHTML = handlecolors(qry, hlit, x, chngtxt);
  else
    pallete.innerHTML = handlecolorsnocap(qry, hlit, x, chngtxt);
  legend.innerHTML = ((myrevshowkey==1)?'&laquo;':'&raquo;');
  dolabels.style.display = ((myrevshowkey==1)?'inline-block':'none');
}

function gethilightdivcoords(rdiv){
  var bwid = window.innerWidth;
  var bhit = window.scrollY+window.innerHeight;

  var x = cursorX+12;
  if (x+16+rdiv.clientWidth >= bwid)
    x = bwid-rdiv.clientWidth-16;

  var y = getCursorY()+12;
  if ((y+rdiv.clientHeight) > bhit)
    y = y-rdiv.clientHeight-20;

  return {left:x, top:y};
}

function dohilight(qry, idx, chngtxt){
  if(chngtxt==undefined) chngtxt=0;
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange=function(){
    if(xmlhttp.readyState==4 && xmlhttp.status==200){
      var ret = JSON.parse(xmlhttp.responseText);
      var spans = document.getElementsByClassName(ret.spanclass);
      for(var i=0; i<spans.length; i++) {
        spans[i].style.backgroundColor = ret.color;
        spans[i].setAttribute('data-hlite', idx);
        spans[i].title = myrevkeys[idx];
        if(chngtxt==1)
          spans[i].innerHTML=myrevkeys[idx]+'&nbsp;';
      }
      myrevhidePopup();
    }
  }
  xmlhttp.open('GET', '/jsonmyrevtasks.php?task=hlit&ref='+qry+'|'+idx,true);
  xmlhttp.send();
}

