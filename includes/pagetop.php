<?php

ob_start();

if(!isset($page)) die('unauthorized access');
header('Content-Type: text/html; charset=utf-8');

$robotstr = 'index,nofollow';
if($site!='www.revisedenglishversion.com'){
  $robotstr = 'noindex,nofollow';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta id="meta" name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?=$metatitle?></title>
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
  <meta name="description"  content="<?=$metadesc?>" />
  <meta name="robots" content="<?=$robotstr?>" />
  <meta property="og:image" content="https://<?=$site?>/i/logo2.png" />
  <meta property="og:image:secure_url" content="https://<?=$site?>/i/logo2.png" />
  <?if($site=='www.revisedenglishversion.com'){?>
  <link rel="manifest" href="/includes/manifest.json?v=<?=$fileversion?>" />
  <?}?>
  <link rel="stylesheet" type="text/css" href="/includes/style.min.css?v=<?=$fileversion?>" />
<?if($colortheme>0){?>
  <link rel="stylesheet" type="text/css" href="/includes/style<?=$colors[0]?>.css?v=<?=$fileversion?>" />'.crlf);
<?}?>
  <link href="https://fonts.googleapis.com/css?family=Merriweather%7cIBM+Plex+Serif%7cCaladea%7cRoboto%7cMontserrat%7cBalsamiq+Sans&display=swap" rel="stylesheet" />
  <link rel="icon" href="/i/icon.png" />
  <link rel="apple-touch-icon" href="/i/icon.png"/>
  <script src="/includes/misc.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/quicknav.min.js?v=<?=$fileversion?>" defer></script>
  <script src="/includes/JsAnimScroll.min.js?v=<?=$fileversion?>" defer></script>
  <script src="/includes/sortable.min.js"></script><!-- no defer here -->
<?if($myrevid>0 || $page==40){?>
  <script src="/includes/myrevjs.min.js?v=<?=$fileversion?>" defer></script>
  <link rel="stylesheet" type="text/css" href="/includes/style_myrev.css?v=<?=$fileversion?>" />
<?}?>
  <?if($parachoice % 2==0){?>
  <script src="/includes/hylo.js?v=<?=$fileversion?>" defer></script>
  <?}?>
  <script>

    var site = '<?=$site?>';
    var cookieexpiredays= 180;
    var bookmarksallowed= 50;
    var ismobile      = <?=$ismobile?>;
    var isexpanded    = 0;
    var inhistory     = 0;
    var prfviewcols   = <?=$viewcols?>;
    var prfversebreak = <?=$versebreak?>;
    var prffontsize   = <?=$fontsize?>;
    var prflineheight = <?=$lineheight?>;
    var prffontfamily ='<?=$fontfamily?>';
    var prfswipenav   = <?=$swipenav?>;
    var prfuseoefirst = <?=$useoefirst?>;
    var prfparachoice = <?=$parachoice?>;
    var prfnavonchap  = <?=$navonchap?>;
    var prfcommnewtab = <?=$commnewtab?>;
    var prfcolortheme = <?=$colortheme?>;
    var prfcommlinkstyl=<?=$commlinkstyl?>;
    var prflexicon    = <?=$lexicon?>;
    var prfscrollynav = <?=$scrollynav?>;
    var prfshowdevitems=<?=$showdevitems?>;
    var prfshowcommlinks=<?=$showcommlinks?>;
    var prfviewversnav= <?=$viewversnav?>;
    var prfshowpdflinks=<?=$showpdflinks?>;
    var prfversnavwhat =<?=$versnavwhat?>;
    var prflinkcommentary=<?=$linkcommentary?>;
    var prfucaseot    = <?=$ucaseot?>;
    var prfdiffbiblefont   = <?=$diffbiblefont?>;
    var prfbiblefontsize   = <?=$biblefontsize?>;
    var prfbiblelineheight = <?=$biblelineheight?>;
    var prfbiblefontfamily ='<?=$biblefontfamily?>';

    var myrevid= <?=$myrevid?>;
    var editorcomments= <?=$editorcomments?>;
    var ednotesshowall= <?=$ednotesshowall?>;
    var viewedcomments= <?=(($editorcomments==1)?$viewedcomments:0)?>;
    var peernotes     = <?=$peernotes?>;
    var peernotesshowall= <?=$peernotesshowall?>;
    var viewpeernotes = <?=(($peernotes>0)?$viewpeernotes:0)?>;
    var viewmrcomments= <?=(($myrevid>0)?$viewmrcomments:0)?>;

    var sopsislive  = <?=$sopsislive?>;
    var sopstimeout = 60000 * <?=$sopstimeout?>;
    var sopstimeoutding = <?=$sopstimeoutding?>;
    var sopstimeoutextend = <?=$sopstimeoutextend?>;
    var sopsfirecheck= 10000;

<?if($myrevid>0){
  print('var myrevmode = \''.$myrevmode.'\';'.crlf);
  print('var myrevsort = \''.$myrevsort.'\';'.crlf);
  print('var myrevpagsiz = '.$myrevpagsiz.';'.crlf);
  print('var myrevshownotes = '.$myrevshownotes.';'.crlf);
  print('var myrevclick = '.$myrevclick.';'.crlf);
  print('var myrevshowkey = \''.$myrevshowkey.'\';'.crlf);
  print('var myrevshoweditorfirst = \''.$myrevshoweditorfirst.'\';'.crlf);
  print('var myrevsid = \''.$myrevsid.'\';'.crlf);

  print('var colors = Array(');
  for($i=0;$i<sizeof($colors);$i++){
    print('\''.$colors[$i].'\'');
    if($i<(sizeof($colors)-1)) print(',');
  }
  print(');'.crlf);

  print('var hlcolors = Array(');
  for($i=0;$i<sizeof($hilitecolors);$i++){
    print('\''.$hilitecolors[$i].'\'');
    if($i<(sizeof($hilitecolors)-1)) print(',');
  }
  print(');'.crlf);

  print('var myrevkeys = Array(');
  for($i=0;$i<sizeof($myrevkeys);$i++){
    print('\''.$myrevkeys[$i].'\'');
    if($i<(sizeof($myrevkeys)-1)) print(',');
  }
  print(');'.crlf);
}?>
    var mitm = <?=$mitm?>;
    var page = <?=$page?>;
    var test = <?=$test?>;
    var book = <?=$book?>;
    var chap = <?=$chap?>;
    var vers = <?=$vers?>;
    var vnav = <?=$vnav?>;
    var vhed = <?=$vhed?>;
    var cursorX = 0;
    var resizOL = 0;

    //window.onresize=function(){resizeOL();}

<?if($ismobile && $swipenav>0){?>

    var indrag = false;
    var startX;

    function tStart(e) {
      if(e.touches.length==1){
        indrag = true;
        startX = e.touches[0].pageX;
        cursorX = startX;
      }
    }

    function tMove(e) {
      if (indrag) {
        if((e.touches[0].pageX - startX) > 170){
          indrag = false;startX = null;
          ((prfswipenav==1)?swipenav('l'):excol(0));
        }
        if((startX - e.touches[0].pageX) > 170){
          indrag = false;startX = null;
          ((prfswipenav==1)?swipenav('r'):excol(1));
        }
    }}

    function tEnd() {indrag = false;startX = null;}

    setTimeout("$(\'view\').ontouchstart=tStart;", 200);
    setTimeout("$(\'view\').ontouchmove=tMove;", 200);
    setTimeout("$(\'view\').ontouchend=tEnd;", 200);

    function swipenav(dir){
      if(dir=='r'){
        if($('nextlinkid')) location.href=$('nextlinkid').href;
      }else{
        if($('prevlinkid')) location.href=$('prevlinkid').href;
      }
    }


<?}else{?>

// used to get cursorX position in findcomm and findvers
function getCursorX(e) {
  var ret;
  e = e || window.event;
  if (e.pageX || e.pageY) {
    ret = e.pageX;
  }else{
    ret = e.clientX +
          (document.documentElement.scrollLeft ||
          document.body.scrollLeft) -
          document.documentElement.clientLeft;
  }
  return ret;
}

document.onmousemove = function(e){
  cursorX = getCursorX(e);
}

<?}?>

  var qnavtimer=null;

  function setdblclick(){
    $('view').ondblclick=scrolltotop;
  }

  addLoadEvent(doonload); // first of several
  addLoadEvent(setdblclick);
  </script>
</head>
<body style="font-family:'<?=$fontfamily?>', 'times new roman', serif; font-size:1em; line-height:1;">
  <a id="toptop"></a>
  <div id="overlay" style="display:none"><div id="iframeholder">
    <iframe name="ifrm" id="ifrm" style="border:none;margin:0;padding:0" src="/includes/empty.htm"></iframe>
  </div></div>
<?

$hdrheight=(($ismobile)?'56':'56');  // might change this later...

if($ismobile)
  $imgsiz = '32px'; // was 41
else
  $imgsiz = '32px';
$imgstyl = 'border:0;height:'.$imgsiz.';vertical-align:middle;padding:0 2px;';
$prevlink    = '<img src="/i/mnu_prevdim'.$colors[0].'.png?v='.$fileversion.'" style="'.$imgstyl.'" alt="prev" />';
$nextlink    = '<img src="/i/mnu_nextdim'.$colors[0].'.png?v='.$fileversion.'" style="'.$imgstyl.'" alt="next" />';
$prevlinkact = '<img src="/i/mnu_prev'.$colors[0].'.png?v='.$fileversion.'" style="'.$imgstyl.'" alt="prev" />';
$nextlinkact = '<img src="/i/mnu_next'.$colors[0].'.png?v='.$fileversion.'" style="'.$imgstyl.'" alt="next" />';

switch($page){
case 1:  // editverscomm
case 5:  // viewverscomm
  setprevnextlinks('');
  break;
case 6:  // edit book
case 10: // viewbookcomm
  setprevnextlinks('/book');
  break;
case 24: // outline
  setprevnextlinks('/outline');
  break;
case 8:  // editappxintro
case 14: // viewappxintro
  setprevnextlinksAI((($test==2)?'/Information':(($test==3)?'/Appendix':'/Wordstudy')));
  $prevlink = (($showback==1 || !($glogid>0||$showclosetab==1))?$prevlink:'&nbsp;');
  $nextlink = (($showback==1 || !($glogid>0||$showclosetab==1))?$nextlink:'&nbsp;');
  break;
case 9:  // preferences
case 20: // whatsnew
case 25: // blog (list)
case 26: // blog (individual)
         // could go to prev/next..
  break;
default:
  setprevnextlinks((($page==4)?'/commentary':''));
  break;
}

$imgstyl = 'border:0;height:'.$imgsiz.';vertical-align:middle;padding:0 6px;';

$qnav = $prevlink;
$qnav.='<input type="text" name="srchtext" id="srchtext" maxlength="24" autocomplete="off" ';
$qnav.='class="srchtext" style="height:'.($hdrheight-22).'px;font-size:130%;" ';
$qnav.='onkeypress="quicknav(this, event)" onfocus="if(this.value==\''.$srchstring.'\') this.value=\'\'" ';
$qnav.='onblur="this.value=\''.$srchstring.'\'" ';
if($ismobile){
  $qnav.='onclick="callbiblenav(0);" ';
}else{
  $qnav.='onclick="try{clearTimeout(qnavtimer);}catch(e){};qnavtimer=null;callbiblenav(0);" ';
  $qnav.='onmouseenter="if(qnavtimer==null) qnavtimer=setTimeout(\'callbiblenav();$(\\\'srchtext\\\').focus();\', 900);" ';
  $qnav.='onmouseout="try{clearTimeout(qnavtimer);}catch(e){};qnavtimer=null;" ';
}
$qnav.='value="'.$srchstring.'" />'.$nextlink;

print('<div id="header"><table style="margin:0;padding:0;border-spacing:0;width:100%;height:'.$hdrheight.'px;"><tr style="width:100%;">');
print('<td style="width:10%;white-space:nowrap;">');


print('<a onclick="excol(isexpanded);" title="menu"><img src="/i/mnu_menu'.gethilite2().$colors[0].'.png" alt="menu" style="'.$imgstyl.'margin:0 0 0 3px;" /></a>');
if($screenwidth>=800) print('<span style="display:inline-block;font-family:merriweather, serif;vertical-align:middle;padding-left:30px;font-size:1.3em;color:#337efe;">REV <span style="color:#828282;">Bible</span></span>');
print('</td>');
print('<td style="width:80%;text-align:center;vertical-align:middle;white-space:nowrap;">'.$qnav.'</td>');
print('<td style="width:10%;text-align:right;white-space:nowrap">');
if($screenwidth>=800) print('<img src="/i/rev_blank.png" alt="blank" style="height:34px;vertical-align:middle;padding-right:30px;" />');
print('<a onclick="if(inhistory==1)sizenavto(0);else dobiblenav(\'navmitm=5&amp;curpage='.$page.'\');return false;" title="history"><img src="/i/mnu_history'.$colors[0].'.png" alt="prefs" style="'.$imgstyl.'margin:0 3px" /></a>');
print('</td>');
print('</tr></table></div>'.crlf);
if($ismobile==0) print('<div id="navmouseover" onmouseover="if(!isexpanded) excol(isexpanded);"></div>'.crlf);
?>

<div id="biblenav" style="height:0;top:<?=$hdrheight?>px;transition:height .4s linear;padding:0;"></div>
<div id="nav" style="left:-210px;transition:left .4s ease-in;line-height:<?=($lineheight)?>;top:<?=($hdrheight-1)?>px;font-size:<?=(($ismobile)?'1.2':'1.1')?>rem;">
<?
require_once $docroot."/nav.php";
$hyphclass = (($parachoice % 2==0 && ($page==0 || $page==4 || $page==5 || $page==9 || $page==10 || $page==14 || $page==20 || $page==25))?' class="hyphenate"':'');
print('</div>');
if($page==0)
  print('<div id="view"'.$hyphclass.' style="font-family:\''.$biblefontfamily.'\';font-size:'.$biblefontsize.'em;line-height:'.$biblelineheight.';top:'.$hdrheight.'px;">');
else
  print('<div id="view"'.$hyphclass.' style="font-family:\''.$fontfamily.'\';font-size:'.$fontsize.'em;line-height:'.$lineheight.';top:'.$hdrheight.'px;">');

$dev = (($site!='www.revisedenglishversion.com')?1:0);
if($dev==1) print('<div style="position:fixed;top:'.($hdrheight+3).'px;color:red;border:1px solid red;font-size:.7em;line-height:1">&nbsp;DEV!&nbsp;</div>');
if($superman==1 && $showpdflinks){
  $dbg = '';
  $dbg.= '<span id="rsw" style="display:block;font-size:60%;color:'.$colors[7].';padding:0;margin-left:'.(($dev==1)?'52px':'0').';line-height:1em">';
  $dbg.= 'mi:'.$mitm.', ';
  $dbg.= 'pg:'.$page.' '.$content[$page].', ';
  $dbg.= 'tt:'.$test.', ';
  $dbg.= 'bk:'.$book.', ';
  $dbg.= 'ch:'.$chap.', ';
  $dbg.= 'vs:'.$vers.' ';
  $dbg.= '</span>';
  print($dbg);
}
?>

