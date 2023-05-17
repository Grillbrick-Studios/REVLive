function quicknav(ctl, e){
  if (e.keyCode!=13) return;

  ctl.value = trim(ctl.value);
  var srchtxt = ctl.value;
  if(srchtxt=='') return;
  srchtxt = srchtxt.replace(/\./g,' ');
  srchtxt = srchtxt.replace(/:/g,' ');
  srchtxt = srchtxt.replace(/;/g,' ');
  srchtxt = srchtxt.replace(/-/g,' ');
  srchtxt = srchtxt.replace(/\//g,' ');
  srchtxt = srchtxt.replace(/\\/g,'');
  srchtxt = srchtxt.replace(/~/g,' ');
  srchtxt = srchtxt.replace(/%/g,' ');
  srchtxt = srchtxt.replace(/"/g,'');
  srchtxt = srchtxt.replace(/'/g,'');
  srchtxt = srchtxt.replace(/ +(?= )/g,'');
  srchtxt = trim(srchtxt);
  ctl.value = srchtxt;
  if(srchtxt=='') return;

  arsrch = srchtxt.split(' ');
  if(arsrch.length>1 && arsrch[0].substring(0,4)=='intr'){
    // user typed 'intr(oduction) book_abbr'
    arsrch.shift();
    arsrch.push('i');
  }
  if(isFinite(String(arsrch[0]))){
    // 1st element is an integer, ie: 1 Cor, etc
    var tmp = arsrch.shift();
    arsrch[0] = tmp+((typeof(arsrch[0])!='undefined')?arsrch[0]:'');
  };
  if((arsrch[0]=='c' || arsrch[0]=='comm') && isFinite(String(arsrch[1]))){
    var tmp = arsrch.shift(); // hold the 'c'
    var tmp2= arsrch.shift(); // hold the number
    arsrch[0] = tmp2+((typeof(arsrch[0])!='undefined')?arsrch[0]:'');
    arsrch.unshift(tmp);
  };

  if(arsrch.length==1 && arsrch[0].substring(0,4)=='intr'){
    // if user types intr(oduction), take them to about the REV
    srchtxt = 'info/1';
    location.href = '/'+srchtxt;
    return;
  }
  
  var tmp = arsrch[arsrch.length-1].toLowerCase();
  if(tmp=='c' || tmp=='comm'){
    switch(arsrch.length){
    case 2: // two words, book comm
      arsrch.unshift('bk');
      arsrch.pop();
      break;
    case 3: // comm on chapter
      arsrch.unshift('c');
      arsrch.pop();
      break;
    default:break;
    }
  }
  //if(arsrch.length==2 && (tmp=='i' || tmp.substring(0,4)=='intr')){
  //  arsrch.unshift('bk');
  //  arsrch.pop();
 // }
  if(arsrch.length==2 && (tmp=='i' || tmp.substring(0,4)=='intr')){
    arsrch.unshift('bk');
    arsrch.pop();
  }
  if(arsrch.length==2 && (tmp=='o' || tmp.substring(0,4)=='outl')){
    arsrch.unshift('outl');
    arsrch.pop();
  }
  //alert(arsrch[0]);
  var cmd, word, concat=0;
  switch(arsrch[0].toLowerCase()){
    case 'i': cmd = 'info';concat=1;break;
    case 'a': cmd = 'appx';concat=1;break;
    case 'e': if(editorcomments==1){arsrch[0] = 'enot';arsrch.length=1;}break;
    case 'w': cmd = 'word';concat=1;break;
    case 't': cmd = 'topi';concat=1;break;
    case 'o':
    case 'outl': cmd='outl';concat=1;break;  
    case 'play':
    case 'pl':cmd='play'; concat=1;break;
    case 'bc':
    case 'bk':cmd = 'book';concat=1;break;
    case 'c':
    case 'com': arsrch[0] = 'comm';break;
    case 'b': arsrch[0] = 'blog';arsrch.length=1;break;
    case 'm': arsrch[0] = 'myre';arsrch.length=1;break;
    case 'wn':
    case 'n': arsrch[0] = 'wnew';arsrch.length=1;break;
    case 'ch':
    case 'chr': arsrch[0] = 'chro';arsrch.length=2;break;
    case 'h': arsrch[0] = 'help';arsrch.length=1;break;
    case 's': arsrch[0] = 'srch';arsrch.length=1;break;
    case 'p': arsrch[0] = 'pref';arsrch.length=1;break;
    case 'd': arsrch[0] = 'dont';arsrch.length=1;break;
    case 'bib':arsrch[0]= 'bibl';arsrch.length=1;break;
    case 'hi':
    case 'hist': if($('biblenav').style.display=='none') popbiblenav();dobiblenav('navmitm=5');return;break;
  }
  if(concat==1){
    arsrch.shift();
    word = arsrch.join('_');
    arsrch[0] = cmd;
    arsrch[1] = ((word)?word:'list_all');
    arsrch.length = 2;
  }
  if(arsrch.length >4) arsrch.length = 4;
  if(arsrch.length==4 && arsrch[0]=='comm' && !isNaN(arsrch[3])) arsrch[3] = 'nav'+arsrch[3];
  if(arsrch.length==4 && isNaN(arsrch[0]) && !isNaN(arsrch[3])) arsrch.length = 3;  // 20200902 added to trap range entry
  if(arsrch.length==3 && arsrch[0]!='comm' && !isNaN(arsrch[2])) arsrch[2] = 'nav'+arsrch[2];
  if(arsrch.length==1 &&
     arsrch[0]!='comm' &&
     arsrch[0]!='blog' &&
     arsrch[0]!='wnew' &&
     arsrch[0]!='help' &&
     arsrch[0]!='srch' &&
     arsrch[0]!='pref' &&
     arsrch[0]!='chro' &&
     arsrch[0]!='myre' &&
     arsrch[0]!='enot' &&
     arsrch[0]!='dont')
    arsrch[1] = 'all';

  srchtxt = arsrch.join('/');
  //alert(srchtxt);
  location.href = '/'+srchtxt;
}

function popbiblenav(){
  var bn = $('biblenav');
  if(bn.style.height=='0px'){dobiblenav('curpage='+page);
  }else{sizenavto(0);}
}

function callbiblenav(doblur){
  if(arguments.length==0) doblur=1;
  try{clearTimeout(gbmktimeout)}catch(e){}
  if((page==44 || (page==0||page==1||page==4||page==5||page==8||page==10||page==13||page==24||page==41||(page==14&&(test==2||test==3||test==4))) && test>-1)){
    //console.log(page);
    var nmitm=((page==0||page==1||page==5||page==10||page==13||page==24||page==41)?1:((page==4)?2:((test==3)?3:4))),
        ntest=test,
        nbook=0,
        nchap=0;
    //if(page==8 && (test==3 || test==4)) {nmitm=4}
    if(page==8) {nmitm=test}
    if(page==14 && test==2) {nmitm=6;nchap=2}
    if(page==44) {nmitm=6;ntest=2;nchap=chap}
    dobiblenav('navmitm='+nmitm+'&navtest='+ntest+'&navbook='+nbook+'&navchap='+nchap);
    if(doblur) $('srchtext').blur();
    if(page==0 || page==4 || page==14) setTimeout('handlescroll();', 500);
  }else popbiblenav();
}

function handlescroll(){ // for small phones
  try{
    var mybkpos = $('mybk').getBoundingClientRect().top,
        bn = $('biblenav');
    if(mybkpos>bn.clientHeight) bn.scrollTop=mybkpos-bn.clientHeight;
  }catch(e){}
}

function dobiblenav(qs){
  if(isexpanded) excol(isexpanded);
  var bn = $('biblenav');
  if(parseInt(bn.style.height)<24) bn.style.height='24px';
  if(bn.innerHTML=='') bn.innerHTML='<small>one moment..</small>';
  bn.style.overflowY = 'scroll';
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange=function(){
    if (xmlhttp.readyState==4 && xmlhttp.status==200){
      bn.innerHTML = xmlhttp.responseText;
      if(qs.indexOf('navmitm=5')==0) inhistory=1;
      if(qs.indexOf('wscat')>0){
        var wscat=parseInt(qs.substring(qs.indexOf('wscat')+6));
        setCookie('rev_wscat', wscat, 1);
      }
      var bns=$('bnspan');
      sizenavto(Math.min((document.documentElement.clientHeight-80), (bns.scrollHeight+3)));
      //try{myrevhidePopup();}catch(e){}
    }
  }
  xmlhttp.open('GET','/jsonbiblenav.php?'+qs,true);
  xmlhttp.send();
}
function sizenavto(siz, cnt){
  if(cnt===undefined) cnt=0;
  cnt++;
  var bn = $('biblenav');
  if(prfscrollynav==0) bn.style.transition = 'height 0s';
  bn.style.height=siz+'px';
  if(siz<3){
    inhistory=0;
    try{bn.innerHTML='';}catch(e){}
  }
}

//
// for resizing resource videos
//
var resinitsiz = ((window.innerWidth<520)?160:210);
var playingres='-';

function sizres(id, identifier){
  var vid = $('video'+id);
  if(!prfscrollynav) vid.style.transition = 'width 0s';
  goback++;
  if(vid.style.width == resinitsiz+'px'){
    if(playingres!='-'){
      if(playingres.substr(0,5)=='video') sizres(playingres.replace(/[^\d.]/g, ''),'');
      else toggleplaypause(playingres.replace(/[^\d.]/g, ''), id);
      setTimeout('sizres('+id+',\''+identifier+'\')', 500);
      return;
    }
    vid.style.width = '720px';
    $('exconvid'+id).src = '/i/contractvideo.png';
    setTimeout('loadvid('+id+',\''+identifier+'\')', ((prfscrollynav)?350:100));
    playingres = 'video'+id;
    }
  else{
    $('frm'+id).src = '/includes/videoload.php?id='+id;
    setTimeout('$(\'video'+id+'\').style.width = '+resinitsiz+'+\'px\'', 260);
    $('exconvid'+id).src = '/i/expandvideo.png';
    playingres = '-';
    }
}
function stopifplaying(id){
  if(playingres!='-'){
    var pid = playingres.replace(/[^\d.]/g, '');
    if(playingres.substr(0,5)=='video')
      sizres(pid,'');
    else{
      var ctl =$('aplayer'+pid);
      if(!ctl.paused){
        ctl.pause();
        playingres='-';
      }
    }
  }
}

function loadvid(id,identifier){
  incresviews(id);
  $('frm'+id).src='https://www.youtube.com/embed/'+identifier+'?rel=0&autoplay=1';
}

function incresviews(id){
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange=function(){}
  xmlhttp.open('GET','/jsonincresviews.php?id='+id,true);
  xmlhttp.send();
}

switch(prfcolortheme){
  case 0:var colors = Array('','#000','#fff','#ccc','#525252','blue','#ddd','#aaa');break;
  case 1:var colors = Array('_LOD','#ddd','#000','#666','#ddd','yellow', '#666','#aaa');break;
  case 2:var colors = Array('_SEP','#5f4b32','#fbf0d9','#bda78e','#5f4b32','blue','#dbd0b9','#bda78e');break;
}


function showresource(id){
  var txt='', lightbox=document.createElement('DIV');
  lightbox.id='lightbox';
  txt = '<div id="resdiv" style="position:relative;top:50%;transform:translateY(-50%);max-height:'+(window.innerHeight-50)+'px;overflow-y:auto;width:90%;max-width:720px;left:0;right:0;margin:auto;padding:0 10px;color:'+colors[1]+';background-color:'+colors[2]+';border:1px solid '+colors[6]+';border-radius:8px;text-align:center;line-height:1.3em;">';
  txt+= '<div style="position:sticky;z-index:999;top:-1px;background-color:'+colors[2]+';padding:7px 0;">';
  txt+= '<span style="display:inline-block;width:10%;">&nbsp;</span><h3 style="display:inline-block;width:80%;text-align:center;margin:0;">STF Resource</h3>';
  txt+= '<span style="display:inline-block;width:10%;text-align:right;cursor:pointer;" onclick="trashlb();"><img src="/i/redx.png" style="width:20px;" alt="" /></span>';
  txt+= '</div>';
  txt+= '[[content]]';
  txt+= '<p style="margin:9px 0;"><input type="button" class="gobackbutton" value="Close" onclick="trashlb();" /></p>';
  txt+= '</div>';
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange=function(){
    if (xmlhttp.readyState==4 && xmlhttp.status==200){
      txt = txt.replace('[[content]]', xmlhttp.responseText);
      lightbox.innerHTML = txt;
      document.body.appendChild(lightbox);
      lightbox.style.transition = 'opacity .7s';
      setTimeout('$(\'lightbox\').style.opacity=1', 1);
    }
  }
  xmlhttp.open('GET','/jsonshowresource.php?id='+id,true);
  xmlhttp.send();
}
function trashlb(){
  var lb = $('lightbox');
  lb.style.opacity = 0;
  setTimeout('$(\'lightbox\').parentNode.removeChild($(\'lightbox\'))', 300);
}

function toggleplaypause(id, resid) {
  var ctl =$('aplayer'+id);
  var img2=$('ispeaker'+id);
  if(ctl.paused){
    if(playingres!='-'){
      if(playingres.substr(0,5)=='video') sizres(playingres.replace(/[^\d.]/g, ''),'');
      else{
        aid=playingres.replace(/[^\d.]/g, '');
        playingres='-';
        toggleplaypause(aid, resid);
      }
    }
    incresviews(resid);
    ctl.play();
    img2.src='/i/audio_stop.png';
    playingres='audio'+id;
  }else{
    ctl.pause();
    img2.src='/i/audio.png';
    playingres='-';
  }
}

function stopothers(id, resid) {
  if(playingres!='-'){
    if(playingres.substr(0,5)=='video') sizres(playingres.replace(/[^\d.]/g, ''),'');
    else{
      aid=playingres.replace(/[^\d.]/g, '');
      if(id != aid){
        $('aplayer'+aid).pause();
        $('ispeaker'+aid).src='/i/audio.png';
      }
    }
  }
  $('ispeaker'+id).src='/i/audio_stop.png';
  incresviews(resid);
  playingres='audio'+id;
}

function stopplayer(id) {
  $('ispeaker'+id).src='/i/audio.png';
}

