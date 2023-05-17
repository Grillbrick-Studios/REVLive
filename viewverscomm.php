<?php
if(!isset($page)) die('unauthorized access');

$commlen = 2500;
$btitle = getbooktitle($test,$book, 0);
$babbr  = getbooktitle($test,$book, 1);
//$stitle = 'Commentary for:'.(($ismobile)?'<br />':' ').$btitle.' '.$chap.':'.$vers;

$hlite=0;
$myrevnotes='-';
$marginnote='';
$peernote='';
$editnote='';

$loc = $myrevid.'|'.$test.'|'.$book.'|'.$chap.'|'.$vers;

$showmyrevdot='';
if($myrevid>0){
  $bookmarks = ((isset($_COOKIE['rev_bookmarks']))?$_COOKIE['rev_bookmarks']:'');
  $arbmks = explode(';', $bookmarks??'');
  $bmk = $page.','.$test.','.$book.','.$chap.','.$vers;
  $isbmked = ((in_array($bmk, $arbmks))?1:0);
  $sql = 'select highlight, ifnull(marginnote, \'\') marginnote, ifnull(myrevnotes, \'-\') myrevnotes
          from myrevdata
          where myrevid = '.$myrevid.' and testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
  $row = rs($sql);
  $showdot=0;
  if($row){
    $hlite = $row[0];
    $marginnote = $row[1];
    if($marginnote!=''){
      $marginnote = str_replace('[br]', '<br />', $marginnote);
      $marginnote = '<div id="mn_'.$loc.'" class="marginnote" style="display:'.(($viewmrcomments==1)?'block':'none').';margin-top:6px;cursor:pointer;" onclick="rlightbox(\'note\',\''.$loc.'\',1);">'.$marginnote.'</div>';
    }else $marginnote = '<div id="mn_'.$loc.'" class="marginnote" style="display:none;margin-top:6px;cursor:pointer;" onclick="rlightbox(\'note\',\''.$loc.'\',1);">'.$marginnote.'</div>';
    $myrevnotes = $row[2];
    $showdot = (($row['myrevnotes']!='-')?1:0);
  }else $marginnote = '<div id="mn_'.$loc.'" class="marginnote" style="display:none;margin-top:6px;cursor:pointer;" onclick="rlightbox(\'note\',\''.$loc.'\',1);">'.$marginnote.'</div>';
  $showmyrevdot = '<img id="mrimg_'.$loc.'" data-havemrnote="'.$showdot.'" data-isbmked="'.$isbmked.'" src="/i/skinnybluedot.png" style="display:'.(($showdot==1 && $viewmrcomments==1)?'inline':'none').';width:.4em;" alt="You have a note for this verse." />';
  $row = rs('select ifnull(notes, \'\') from myrevusers where myrevid = '.$myrevid.' ');
  $wsdot = ((strlen($row[0])>0)?1:0);
}

$showpeerdot = '';
$havepeerwork = 0;
$canaddpeernote = 0;
if($userid>0 && $peernotes>0){
  $sql = 'select ifnull(editnote, \'\') editnote, length(ifnull(editdetails, \'\')) havepnote, ifnull(resolved, 0) resolved
          from peernotes
          where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' '.(($peernotesshowall==1)?'':'and resolved = 0 ');
  $row = rs($sql);
  $showdot=0;
  if($row){
    $peernote = $row[0];
    if($peernote!=''){
      $peernote = (($row['resolved']==1)?'&reg; ':'').str_replace('[br]', '<br />', $peernote);
      $peernote = '<div id="pn_'.$loc.'" class="peernote" style="display:'.(($viewpeernotes==1)?'block':'none').';margin-top:6px;cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$loc.'\',1);">'.$peernote.'</div>';
    }else $peernote = '<div id="pn_'.$loc.'" class="peernote" style="display:none;margin-top:6px;cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$loc.'\',1);"></div>';
    $showdot = (($row['havepnote']>0)?1:0);
  }else $peernote = '<div id="pn_'.$loc.'" class="peernote" style="display:none;margin-top:6px;cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$loc.'\',1);"></div>';
  $showpeerdot = '<img id="peerimg_'.$loc.'" data-havepeernote="'.$showdot.'" src="/i/skinnyyeldot.png" style="display:'.(($showdot==1 && $viewpeernotes==1)?'inline':'none').';width:.4em;" alt="There is a reviewernote for this verse." />';
  $row = rs('select length(ifnull(peerworknotes,\'\')) from myrevusers where myrevid = '.$myrevid.' ');
  $havepeerwork = (($row[0]>0)?1:0);
  $row = rs('select 1 from book_peer where testament = '.$test.' and book = '.$book.' and userid = '.$userid.' ');
  $canaddpeernote = (($row)?1:0);
}

$showeditdot = '';
$haveedwork = 0;
if($userid>0 && $editorcomments==1){
  $sql = 'select ifnull(editnote, \'\') editnote, length(ifnull(editdetails, \'\')) haveenote, ifnull(resolved, 0) resolved
          from editnotes
          where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' '.(($ednotesshowall==1)?'':'and resolved = 0 ');
  $row = rs($sql);
  $showdot=0;
  if($row){
    $editnote = $row[0];
    if($editnote!=''){
      $editnote = (($row['resolved']==1)?'&reg; ':'').str_replace('[br]', '<br />', $editnote);
      $editnote = '<div id="bn_'.$loc.'" class="editnote" style="display:'.(($viewedcomments==1)?'block':'none').';margin-top:6px;cursor:pointer;" onclick="rlightbox(\'enote\',\''.$loc.'\',1);">'.$editnote.'</div>';
    }else $editnote = '<div id="bn_'.$loc.'" class="editnote" style="display:none;margin-top:6px;cursor:pointer;" onclick="rlightbox(\'enote\',\''.$loc.'\',1);"></div>';
    $showdot = (($row['haveenote']>0)?1:0);
  }else $editnote = '<div id="bn_'.$loc.'" class="editnote" style="display:none;margin-top:6px;cursor:pointer;" onclick="rlightbox(\'enote\',\''.$loc.'\',1);"></div>';
  $showeditdot = '<img id="edimg_'.$loc.'" data-haveednote="'.$showdot.'" src="/i/skinnyreddot.png" style="display:'.(($showdot==1 && $viewedcomments==1)?'inline':'none').';width:.4em;" alt="You have a note for this verse." />';
  $row = rs('select length(ifnull(notes,\'\')) from myrevusers where myrevid = -1 ');
  $haveedwork = (($row[0]>0)?1:0);
}

$sql = 'select versetext, heading, footnotes, ifnull(comfootnotes, \'\') comfootnotes, ifnull(commentary,\'-\') commentary from verse
        where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
$row = rs($sql);
$theverse    = $row['versetext'];
$footnotes   = $row['footnotes'];
$comfootnotes= $row['comfootnotes'];
// new footnotes table
//$footnotes = getfootnotes($test, $book, $chap, $vers, 'vrs');
$comfootnotes = getfootnotes($test, $book, $chap, $vers, 'com');
//
$commentary  = $row['commentary'];
$vsfncnt     = substr_count($row['heading'].'', '[fn]');
$arfn    = array();
$arcomfn = array();
$fncnt   = 0;
$comfncnt= 0;
$theverse = processfootnotes($arfn, $theverse, $footnotes, $fncnt, $chap, $vers);
if(left($theverse, 4)=='[br]') $theverse = substr($theverse, 4);
$theverse = str_replace('[mvh]', '[pg]', $theverse);
$theverse = str_replace('[pg][hp]', '<br /><br />', $theverse);
$theverse = str_replace('[pg]', '<br /><br />', $theverse);
$theverse = str_replace('[bq]', '<br /><br />', $theverse);
$theverse = str_replace('[/bq]', ' ', $theverse);
$theverse = str_replace('[br]', '<br />', $theverse);
$theverse = str_replace('[hp]', '<br />', $theverse);
$theverse = str_replace('[lb]', '<br />', $theverse);
$theverse = str_replace('[hpbegin]', '<br />', $theverse);
$theverse = str_replace('[hpend]', '<br />', $theverse);
$theverse = str_replace('[listbegin]', '<br />', $theverse);
$theverse = str_replace('[listend]', '<br />', $theverse);
if(substr($theverse, 0, 1)=='~'){
    // removed ~ from beginning of [[
    $theverse = '[['.substr($theverse,1).((strpos($theverse, ']]')>0)?'':']]');
    $theverse = str_replace(']]]]', ']]', $theverse);
    $theverse = str_replace('[[[[', '[[', $theverse);
}
if(strpos($theverse, '[[')!==false && strpos($theverse, ']]')===false) $theverse.= ']]';
if(strpos($theverse, '[')!==false && strpos($theverse, ']')===false) $theverse.= ']';
if(strpos($theverse, ']')!==false && strpos($theverse, '[')===false) $theverse = '['.$theverse;
$theverse = fixverse($theverse);
if($myrevid>0){
  $clink = '/'.str_replace(' ', '', $babbr).'/'.$chap.'/'.$vers.'/0';
  $theverse = '<a id="hl_'.$loc.'" style="background-color:'.$hilitecolors[$hlite].';transition:background-color .3s;" title="'.$myrevkeys[$hlite].'" onclick="showhilightdiv(\''.$loc.'\', \''.$clink.'\');" class="comlink0 hl_'.$loc.'" data-hlite="'.$hlite.'">'.$theverse.'</a>';
}
// make a link to go back to the Bible
$theverse.= ' <a href="/'.str_replace(' ', '-', $btitle).'/'.$chap.'/nav'.$vers.'" class="comlink0"><img src="/i/bible_icon'.$colors[0].'.png" style="width:1.1em;margin-bottom:-3px;" alt="Bible" title="back to Bible" /></a>';
if(!$inapp || !$isios)
  $theverse.= getothertranslationlink($btitle, $chap, $vers, 1);

$commentary = nvl($commentary, '-');
if($commentary=='-'){ // no commentary, so get links to prev/next verses with commentary
  $commentary = '<p>There is no REV commentary for '.$btitle.' '.$chap.':'.$vers.'.</p>';
  $commentary.= '<p>To continue reading the Bible, <a href="/'.str_replace(' ', '', $btitle).'/'.$chap.'/nav'.$vers.'">click here</a>.</p>';
  $sql = 'select testament, book, chapter, verse
          from verse
          where testament<2
          and book>='.$book.'
          and ((book='.$book.' and chapter>='.$chap.') or (book>'.$book.'))
          and ((book='.$book.' and chapter='.$chap.' and verse>'.$vers.') or (book='.$book.' and chapter>'.$chap.') or (book>'.$book.'))
          and commentary is not null
          order by testament, book, chapter, verse ';
  $row = rs($sql);
  if($row){
    $babbr = getbooktitle($row['testament'], $row['book'], 0);
    $url = '/'.str_replace(' ', '', $babbr).'/'.$row['chapter'].'/'.$row['verse'];
    $commentary.= '<p>The next verse with commentary is <a href="'.$url.'">'.$babbr.' '.$row['chapter'].':'.$row['verse'].'</a>.</p>';
  }else{}
  $sql = 'select testament, book, chapter, verse
          from verse
          where testament<2
          and book<='.$book.'
          and ((book='.$book.' and chapter<='.$chap.') or (book<'.$book.'))
          and ((book='.$book.' and chapter='.$chap.' and verse<'.$vers.') or (book='.$book.' and chapter<'.$chap.') or (book<'.$book.'))
          and commentary is not null
          order by testament desc, book desc, chapter desc, verse desc ';
  $row = rs($sql);
  if($row){
    $babbr = getbooktitle($row['testament'], $row['book'], 0);
    $url = '/'.str_replace(' ', '', $babbr).'/'.$row['chapter'].'/'.$row['verse'];
    $commentary.= '<p>The previous verse with commentary is <a href="'.$url.'">'.$babbr.' '.$row['chapter'].':'.$row['verse'].'</a>.</p>';
  }else{}
}

$commentary = preg_replace('#<p><strong>([^<]*?)</strong><br />#', '<h5 style="font-size:1em;font-weight:bold;margin-bottom:3px;margin-top:25px;">$1</h5><p style="margin-top:0;padding-top:0">', $commentary);
$commentary = processcommfordisplay($commentary, 0);
$commentary = processcomfootnotes($arcomfn, $commentary, $comfootnotes, $comfncnt, $vers);

$fromurl = ((isset($_SERVER['HTTP_REFERER']))?strtolower($_SERVER['HTTP_REFERER']):'x');
//print($fromurl);
$fromcom = ((strpos($fromurl, '/comm/') > 0)?1:0);
$frombiblenav = ((strpos($fromurl, '/nav') > 0 || strpos($fromurl, '/head') > 0)?1:0);

if(($fromcom==0 && $frombiblenav==1) || strpos($fromurl, $site)===FALSE || $fromedit==1){
  $backstr = '<button onclick="location.href=\'/'.str_replace(' ', '', $btitle).'/'.$chap.'/nav'.$vers.'\';" class="gobackbutton" style="margin-top:1px;">Go Back</button>';
}else{
  $backstr = '<button onclick="history.go(-goback);" class="gobackbutton" style="margin-top:1px;">Go Back</button>';
}
//
// top buttons
//
print('<a id="top"></a>'.crlf.'<div id="pagetop">'.crlf);
if(($glogid==0 && $commnewtab==0) || $glogid==-1 || $inapp==1){
  print($backstr);
}else{
  print('<button onclick="window.close();" class="gobackbutton">Close Tab</button>');
}
if($userid>0 && $canedit==1){
  print('&nbsp;<button onclick="navigate('.$mitm.',1,'.$test.','.$book.','.$chap.','.$vers.');return false;" style="font-size:70%">Edit</button>');
  //print('&nbsp;<a onclick="rlightbox(\'enote\',\''.$loc.'\',1);"><img src="/i/commentary'.$colors[0].'.png" style="width:'.(($ismobile)?'1':'.8').'rem;" /></a>');
}
if($showpdflinks)
  print(getexportlinks('vers',$test,$book,$chap,$vers, 1));
print('</div>'.crlf);

//
// sticky div
//
$theverse = $showmyrevdot.$showpeerdot.$showeditdot.$theverse;
if($screenwidth>420){
  $hdrheight=(($ismobile)?'56':'56');
  print('<div id="topverse" style="position:-webkit-sticky;position:sticky;z-index:80;top:'.$hdrheight.'px;padding:7px 0;background-color:'.$colors[2].';">'.$theverse);
}else{
  print('<div>'.$theverse.'</div>');
}

displayfootnotes($fncnt, $arfn, 0, $chap);
print($marginnote);
print($peernote);
print($editnote);

if($screenwidth>420){
  print('<hr style="margin-bottom:0;" /></div>');
}else{
  print('<hr />');
}
print('<div id="commtext" class="col1container">');

//
// myrevnotes
//
if($myrevid>0){
  $nonote=(($myrevnotes=='-')?1:0);
  if($myrevnotes=='-'){$myrevnotes = '<small>You have no notes for this verse.</small>';}
  $myrevbutton = ' <a onclick="rlightbox(\'note\',\''.$loc.'\');" title="Edit my note"><img id="mrimgicon_'.$loc.'" src="/i/myrev_notes'.$colors[0].(($nonote==0)?'_DOT':'').'.png" style="width:1.8em;margin-bottom:-8px;" alt="edit" /></a>';
  $workspacebutton = ' <a onclick="rlightbox(\'note\',\''.$myrevid.'|0|0|0|0\');" title="My workspace"><img src="/i/myrev_workspace'.$colors[0].(($wsdot==1)?'_DOT':'').'.png" style="width:1.8em;margin-bottom:-8px;" alt="My Workspace" /></a>';
  print('<small><a onclick="expandcollapsediv(\'myrev\')">My notes <span id="moreless">'.(($myrevshownotes==1)?'&laquo;':'&raquo;').'</span></a></small>');
  print('<div id="myrev" style="text-align:left;height:'.(($myrevshownotes==1)?'auto':'0').';padding:3px 0;margin:0;overflow:hidden;transition:height .4s ease-in;">');
    print('<div id="myrevnotes" style="font-size:90%;margin-bottom:4px;">');
    print($myrevnotes);
    print('</div>');
    print($myrevbutton.'&nbsp;&nbsp;'.$workspacebutton.'&nbsp;&nbsp;&nbsp;<span style="display:inline-block;margin-bottom:6px;"><small><a href="/myrev" title="go to MyREV">MyREV</a></small></span>');
  print('</div>');
  print('<hr style="border-top:1px solid '.$colors[3].';margin-top:2px;">');
}
print($commentary);
displaycomfootnotes($comfncnt, $arcomfn, $vers);
print(appendresources($test, $book, $chap, $vers));
print('</div><hr />');

//
// bottom buttons
//
print('<div id="pagebot">'.crlf);
print('Commentary for:'.(($ismobile)?'<br />':' ').'<noparse>'.$btitle.' '.$chap.':'.$vers.'</noparse><br />'.crlf);
if(($glogid==0 && $commnewtab==0) || $glogid==-1 || $inapp==1){
  print($backstr);
}else{
  print('<button onclick="window.close();" class="gobackbutton">Close Tab</button>');
}
if($userid>0 && $canedit==1){
  print('&nbsp;<button onclick="navigate('.$mitm.',1,'.$test.','.$book.','.$chap.','.$vers.');return false;" style="font-size:70%">Edit</button>');
}
if($userid>0)
  print(displayedits(1,$test,$book,$chap,$vers));
print('</div>'.crlf);

logview($page,$test,$book,$chap,$vers);
?>

  <script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/findmycomm.min.js?v=<?=$fileversion?>"></script>
  <script>
    findmycomm.enablePopups = true;
    findmycomm.remoteURL    = '<?=$jsonurl?>';
    findmycomm.startNodeId  = 'myrevnotes';
    findmycomm.mrlightbox   = 1;
  </script>

  <script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
  <script>
    findcomm.enablePopups = true;
    findcomm.remoteURL    = '<?=$jsonurl?>';
    findcomm.startNodeId  = 'commtext';
  </script>

  <script src="/includes/findbcom.min.js?v=<?=$fileversion?>"></script>
  <script>
    findbcom.startNodeId  = 'commtext';
  </script>

  <script src="/includes/findapx.min.js?v=<?=$fileversion?>"></script>
  <script>
    findappx.startNodeId = 'commtext';
    findappx.apxidx = [<?=loadapxids()?>];
  </script>

  <script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
  <script>
    findvers.startNodeId = 'body'; // start with body to catch refs in vs footnotes
    findvers.remoteURL = '<?=$jsonurl?>';
    findvers.navigat = false;
  </script>

  <script src="/includes/findstrongs.min.js?v=<?=$fileversion?>"></script>
  <script>
    findstrongs.startNodeId = 'commtext';
    findstrongs.ignoreTags.push('noparse');
    findstrongs.lexicon = prflexicon;
  </script>

  <script src="/includes/findwordstudy.min.js?v=<?=$fileversion?>"></script>
  <script>
    findwordstudy.startNodeId = 'commtext';
  </script>

  <script>
<?if($myrevid>0){?>

    var canaddpeernote = <?=$canaddpeernote?>;
    function showhilightdiv(qry, clink){
      event.stopPropagation();
      var rdiv = $('myrevdiv');
      if(rdiv.style.display=='block'){
        myrevhidePopup();
        return;
      }
      var hvedws = <?=(($haveedwork==1)?1:0)?>;
      var hvpnws = <?=(($havepeerwork==1)?1:0)?>;
      var classname = 'hl_'+qry;
      hlit = document.getElementsByClassName(classname)[0].getAttribute('data-hlite');

      //rdiv.innerHTML=gethilightdivcontents(qry, hlit, clink, 0,0,0,<?=(($editorcomments==1)?1:0)?>,<?=(($haveedwork==1)?1:0)?>);
      rdiv.innerHTML=gethilightdivcontents(qry, hlit, clink, 0,0,0,<?=(($editorcomments==1)?1:0)?>,hvedws,<?=(($peernotes>0)?1:0)?>,hvpnws);
      rdiv.style.visibility='hidden';
      rdiv.style.display='block';
      var dims = gethilightdivcoords(rdiv);
      rdiv.style.top  = dims.top+'px';
      rdiv.style.left = dims.left+'px';
      rdiv.style.visibility='visible';
      rdiv.style.opacity=1;
    }

    function reloadmyrevnotes(qry){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var tmp = $('mrimgicon_'+qry);
          var img = '/i/myrev_notes'+colors[0]+((ret.myrevnotes)?'_DOT':'')+'.png';
          tmp.src = img;
          tmp = $('mrimg_'+qry);
          tmp.style.display = ((ret.myrevnotes)?'inline':'none');
          var mndiv = $('mn_'+qry);
          mndiv.innerHTML = ret.marginnote.replace(/\[br\]/g, '<br />');
          mndiv.style.display = ((ret.marginnote=='')?'none':'block');
          var rddiv = $('myrevnotes');
          var notes = ((ret.myrevnotes)?ret.myrevnotes:'<small>You have no notes for this verse.<small>')
          rddiv.innerHTML = notes;
          setTimeout('findmycomm.scan();', 100);
          setTimeout('findcomm.scan();', 200);
          setTimeout('findbcom.scan();', 300);
          setTimeout('findappx.scan();', 400);
          setTimeout('findvers.scan();', 500);
          setTimeout('findstrongs.scan();', 600);
          $('myrev').style.height = (rddiv.scrollHeight+69)+'px';
        }
      }
      xmlhttp.open('GET', '/jsonmyrevtasks.php?task=data&ref='+qry,true);
      xmlhttp.send();
    }
    function reloadeditnotes(qry){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var note = ret.editnote.replace(/\[br\]/g, '<br />');
          var resolved = ret.resolved;
          try{ // to catch from edworkspace
            var bndiv = $('bn_'+qry);
            bndiv.innerHTML = ((resolved==1)?'&reg; ':'')+note;
            bndiv.style.display = (((ednotesshowall==1 || resolved==0) && note!='')?'block':'none');
            var edimg = $('edimg_'+qry);
            edimg.style.display=(((ednotesshowall==1 || resolved==0) && ret.editdetails!='')?'inline':'none');
            edimg.setAttribute('data-haveednote', ((ret.editdetails!='')?1:0));
          }catch(e){}
        }
      }
      xmlhttp.open('GET', '/jsonmyrevtasks.php?task=edata&ref='+qry,true);
      xmlhttp.send();
    }
    function reloadpeernotes(qry){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var note = ret.peernote;
          var resolved = ret.resolved;
          try{
            var pnspan = $('pn_'+qry);
            pnspan.innerHTML = ((resolved==1)?'&reg; ':'')+note.replace(/\[br\]/g, '<br />');
            pnspan.style.display=(((peernotesshowall==1 || resolved==0) && note!='')?'block':'none');
            var peerimg = $('peerimg_'+qry);
            peerimg.style.display=(((peernotesshowall==1 || resolved==0) && ret.peerdetails!='')?'inline':'none');
            peerimg.setAttribute('data-havepeernote', ((ret.peerdetails!='')?1:0));
          }catch(e){} //document.location.reload()}
        }
      }
      xmlhttp.open('GET', '/jsonmyrevtasks.php?task=pdata&ref='+qry,true);
      xmlhttp.send();
    }

    function expandcollapsediv(id){
      excoldiv(id); // in misc.js
      var div = $(id);
      if(div.style.height=='0px'){
        $('moreless').innerHTML='&raquo;';
        setmyrevshownotes(0);
      }else{
        $('moreless').innerHTML='&laquo;';
        setmyrevshownotes(1);
      }
    }

    var div = $('myrev');
    if(div.style.height=='auto'){div.style.height = div.scrollHeight+'px';}
    setTimeout('initmyrevpopup(\'myrevdiv\');', 500);

  <?if($userid > 0){?>
    //
    // Used if an editor is viewing the page
    // They can manage their flags
    //
    function handleflag(logid, ni, donone){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var don = ret.donone;
          var flg = ret.flagged;
          //alert('don: '+don);
          //alert('flg: '+flg);
          if(don==1){
            $('td'+ni).style.backgroundColor=colors[6];
          }else{
            if($('td'+ni).innerHTML.includes('None'))
              $('td'+ni).style.backgroundColor=colors[6];
            var td = $('eflag'+ni);
            var tdc= ((flg==0)?'transparent':'#ffdddd');
            td.style.backgroundColor=tdc;
            var fg = $('iflag'+ni);
            var sfg= ((flg==0)?'30':'100');
            fg.style.opacity=sfg+'%';
          }
        }
      }
      var qs = '?id='+logid+'&doocmt=0&donone='+donone;
      //alert(qs);
      xmlhttp.open('GET','/jsonflagedit.php'+qs,true);
      xmlhttp.send();
    }


<?}}?>
    var myrevid = <?=$myrevid?>;
    addLoadEvent(findmycomm.scan);
    addLoadEvent(findcomm.scan);
    addLoadEvent(findbcom.scan);
    addLoadEvent(findappx.scan);
    addLoadEvent(findvers.scan);
    addLoadEvent(findstrongs.scan);
    addLoadEvent(findwordstudy.scan);

    var goback = 1;
    var toffset = ((window.innerWidth>420)?$('topverse').clientHeight-2:0);  // used for TOC
    <?if($glogid>1){?>
    function scrolltown(){
      //var toffset = ((window.innerWidth>420)?$('topverse').clientHeight-2:0);
      setTimeout('scrolltopos(\'toptop\',\'marker<?=$glogid?>\',-'+toffset+')', 300);
    }
    addLoadEvent(scrolltown);
    <?}?>
    //findvers.scan();

  </script>


