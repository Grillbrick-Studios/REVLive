<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

$id = nvl(((isset($_REQUEST['id']))?(int) preg_replace('/\D/', '', $_REQUEST['id']):0), 0);
$typ= (isset($_REQUEST['typ']))?$_REQUEST['typ']:"0";

if($id==0) exit(); // || ($typ!='pic' && $typ!='doc')) exit();

//print('id: '.$id.'<br />');
//print('typ: '.$typ.'<br />');
//print('This will be either a pic or a doc');
//exit();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  <meta id="meta" name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Chronology Content</title>
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
  <script src="/includes/misc.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/quicknav.js?v=<?=$fileversion?>" defer></script>
  <script src="/includes/JsAnimScroll.min.js?v=<?=$fileversion?>" defer></script>
  <link rel="stylesheet" type="text/css" href="/includes/style.css?v=<?=$fileversion?>" />
  <?if($colortheme>0){
  print('<link rel="stylesheet" type="text/css" href="/includes/style'.$colors[0].'.css?v='.$fileversion.'" />'.crlf);
  }?>
  <link href="https://fonts.googleapis.com/css?family=Merriweather%7cIBM+Plex+Serif%7cCaladea%7cRoboto%7cMontserrat%7cBalsamiq+Sans&display=swap" rel="stylesheet" />
  <script>
    var ismobile      = <?=$ismobile?>;
    var cookieexpiredays= 180;
    var isexpanded    = 0;
    var inhistory     = 0;
    var prfversebreak = <?=$versebreak?>;
    var prfcommnewtab = <?=$commnewtab?>;   // issues if 1...
    var prflexicon = <?=$lexicon?>;
    var prfparachoice = <?=$parachoice?>;
    var page     = <?=$page?>;
    var prffontsize   = <?=$fontsize?>;
    var prflineheight = <?=$lineheight?>;
    var prffontfamily ='<?=$fontfamily?>';
    var prfscrollynav = <?=$scrollynav?>;
    var prfcolortheme = <?=$colortheme?>;
    var mitm = <?=$mitm?>;
    var test = <?=$test?>;
    var book = <?=$book?>;
    var chap = <?=$chap?>;
    var vers = <?=$vers?>;
    var vnav = <?=$vnav?>;
    var cursorX = 0;

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

    document.onmousemove = function(e){cursorX = getCursorX(e);}

    addLoadEvent(doonload);
    setTimeout("$(\'view\').ondblclick=scrolltotop;", 1100);

    var picexpanded=((ismobile)?1:0);
    function sizpic(){
      var pic = $('evtpic');
      if(!prfscrollynav) pic.style.transition = 'width 0s';
      if(picexpanded == 0){
        pic.style.width = '99%';
      }else{
        pic.style.width = '50%';
      }
      //alert(pic.style.width);
      picexpanded = 1-picexpanded;
    }


  </script>
  <!--<base target="_blank"> issues..-->
</head>
<body style="font-family:'<?=$fontfamily?>', 'times new roman', serif;font-size:<?=$fontsize?>em;line-height:<?=$lineheight?>;">
<a id="toptop"></a>
<div id="header" style="display:none;"></div>
<div id="view" style="font-family:'<?=$fontfamily?>', 'times new roman', serif;font-size:90%;line-height:<?=$lineheight?>;top:0;">
<?
//print('ismobile: '.$ismobile);

  $sql = 'select eventtitle, ifnull(picfilnam, \'nofile.jpg\') picfilnam,
          bibleyear, ahyear,
          ifnull(piccaption, \'-\') piccaption, ifnull(longdesc, \'-\') longdesc
          from chronevent ce
            join chronology ch on ch.bibleyear = ce.beginyear
          where ce.id = '.$id.' ';
  $row = rs($sql);
  $evttitle = $row['eventtitle'];
  $filnam = $row['picfilnam'];
  $bibleyear = $row['bibleyear'];
  $ahyear = $row['ahyear'];
  $piccap= $row['piccaption'];
  $evtdesc= $row['longdesc'];
  if($evtdesc!='-') $evtdesc = processcommfordisplay($evtdesc, 0);

  print('<p><strong>'.fixbyear($bibleyear).', '.$ahyear.' AH</strong></p>');
  print('<p>'.$evttitle.'</p>');
  print('<hr />');
  if(file_exists($_SERVER['DOCUMENT_ROOT'].'/i/chronologyimages/'.$filnam)){
    print('<div id="evtpic" style="float:right;width:'.(($ismobile)?99:(($evtdesc=='-')?'99':'50')).'%;padding:0 4px;transition:.6s">');
    print('<img src="/i/chronologyimages/'.$filnam.'" style="cursor:pointer;width:100%;" onclick="sizpic();" />');
    if($piccap!='-')
      print('<span style="display:block;font-weight:bold;text-align:center;">'.$piccap.'</span>');
    print('</div>');
  }
  if($evtdesc!='-') print($evtdesc);
?>
</div>

  <script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
  <script>
    findcomm.enablePopups = true;
    findcomm.remoteURL    = '<?=$jsonurl?>';
    findcomm.startNodeId  = 'view';
  </script>

  <script src="/includes/findbcom.min.js?v=<?=$fileversion?>"></script>
  <script>
    findbcom.startNodeId  = 'view';
  </script>

  <script src="/includes/findapx.min.js?v=<?=$fileversion?>"></script>
  <script>
    findappx.startNodeId = 'view';
    findappx.apxidx = [<?=loadapxids()?>];
  </script>

<script src="/includes/findyear.min.js?v=<?=$fileversion?>"></script>
<script>
  findyear.startNodeId = 'chronology';
  //findyear.linkClassName = 'comlink0';
</script>

  <script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
  <script>
    findvers.startNodeId = 'view';
    findvers.remoteURL = '<?=$jsonurl?>';
    findvers.navigat = false;
  </script>

  <script src="/includes/findstrongs.min.js?v=<?=$fileversion?>"></script>
  <script>
    findstrongs.startNodeId = 'view';
    findstrongs.ignoreTags.push('noparse');
    findstrongs.lexicon = prflexicon;
  </script>

  <script src="/includes/findwordstudy.min.js?v=<?=$fileversion?>"></script>
  <script>
    findwordstudy.startNodeId = 'view';
  </script>

  <script>

    addLoadEvent(findcomm.scan);
    addLoadEvent(findbcom.scan);
    addLoadEvent(findappx.scan);
    //addLoadEvent(findyear.scan);  // not sure...
    addLoadEvent(findvers.scan);
    addLoadEvent(findstrongs.scan);
    addLoadEvent(findwordstudy.scan);
  </script>
</body>
</html>
<?
  function fixbyear($yr){
  if($yr<0) $ret = abs($yr).' BC';
  else $ret = abs($yr).' AD';
  return $ret;
}
 ?>
