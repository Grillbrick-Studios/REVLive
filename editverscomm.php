<?php
if(empty($userid) || $userid==0) die('unauthorized access');
if($canedit==0) die('Sorry, you do not have access to edit the Bible or commentary');

ini_set('memory_limit','256M'); // needed for the older htmlDiff()
$isweb = (($site=='revdev.test')?0:1);

$isbig = (($screenwidth>599)?1:0);
$goback = ((isset($_REQUEST['gobackx']))?$_REQUEST['gobackx']:1);
$btitle = getbooktitle($test,$book,(($isbig==1)?0:1));
//$stitle = 'Editing: '.$btitle.' '.$chap.':'.$vers;
$stitle = 'Editing: <a onclick="navsavehref(\'/'.str_replace(' ','-',$btitle).'/'.$chap.'/'.$vers.'/c/fe\');" class="comlink0">'.$btitle.' '.$chap.':'.$vers.'</a>';

$row = rs('select bookfinalized from book where testament = '.$test.' and book = '.$book.' ');
$bookfinal  = $row[0];

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';

// sops, set some defaults in case $sopsislive==0
$sops = (isset($_POST['sops']))?$_POST['sops']:0;
$editsession='';
$strnewlockeduntil = '';
$checkchange = 0;
$sopslogid = 0;

$logid=0;
$tocerror='';
$loc = $myrevid.'|'.$test.'|'.$book.'|'.$chap.'|'.$vers;

//
// sops
//
if($sopsislive==1){
  $currenttime = gmdate("Y-m-d H:i:s", time());
  $currentobjtime = new DateTime($currenttime ?? '');
  $sopswhere = 'where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
  $row = rs('select ifnull(edituserid, 0), ifnull(editsession, \'null\'), ifnull(lockeduntil, UTC_TIMESTAMP) from verse '.$sopswhere);
  $lastuid = $row[0];
  $editsession = $row[1];
  $cursession = (isset($_POST['cursession']))?$_POST['cursession']:'nada';

  $tmp = new DateTime($currenttime ?? '');
  $newlockeduntil = $tmp->add(new DateInterval('PT'.$sopstimeout.'M'));
  $strnewlockeduntil = $newlockeduntil->format('Y-m-d H:i:s');

  $lockeduntil = new DateTime($row[2] ?? '');
  $expired = (($currentobjtime > $lockeduntil)?1:0);

  if(1==2){
    print('<small><b>Before</b><br />');
    print('sops: '.$sops.'<br />');
    print('userid: '.$userid.'<br />');
    print('lastuid: '.$lastuid.'<br />');
    print('sopstimeout: '.$sopstimeout.'<br />');
    print('editsession: '.$editsession.'<br />');
    print('cursession: '.$cursession.'<br />');
    print('currenttime: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$currenttime.'<br />');
    print('currentobjtime: '.$currentobjtime->format('Y-m-d H:i:s').'<br />');
    print('newlkuntil: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  '.$strnewlockeduntil.'<br />');
    print('ct&lt;lu: '.(($currentobjtime <= $lockeduntil)?'true':'false').'<br />');
    print('expired: '.$expired.'<br />');
    print('</small>');
    //die();
  }

  if($editsession == $cursession){ // same session, same user
    if($expired==1 || $sops==1){
      $checkchange = (($oper=='savvrs')?1:0);
    }else{ // update and let them continue
      $qry = dbquery('update verse set lockeduntil = \''.$strnewlockeduntil.'\' '.$sopswhere);
    }
  }elseif($userid == $lastuid){ // same user, different session
    if($expired == 1){ // their other session has expired, let them continue
      $qry = dbquery('update verse set lockeduntil = \''.$strnewlockeduntil.'\' '.$sopswhere);
    }else{ // they have another open session
      $sops=(($oper=='savvrs')?5:4);
      $checkchange = (($oper=='savvrs')?1:0);
    }
  }elseif($lastuid != 0){ // another user
    if($expired == 1){ // the other user's session has expired, let them continue
      $editsession = keygen(20);
      $qry = dbquery('update verse set edituserid = '.$userid.', editsession = \''.$editsession.'\', lockeduntil = \''.$strnewlockeduntil.'\' '.$sopswhere);
    }else{ // locked by another user
      $sops=(($oper=='savvrs')?3:2);
      $checkchange = (($oper=='savvrs')?1:0);
    }
  }elseif($lastuid==0 && $oper=='savvrs'){ // somebody else edited and saved, possibly sleeping laptop scenario
    $sops=6;
    $checkchange = 1;
  }else{ // verse is free to edit
    $editsession = keygen(20);
    $qry = dbquery('update verse set edituserid = '.$userid.', editsession = \''.$editsession.'\', lockeduntil = \''.$strnewlockeduntil.'\' '.$sopswhere);
  }
  if(1==2){
    print('<small><b>After</b><br />');
    print('sops: '.$sops.'<br />');
    print('userid: '.$userid.'<br />');
    print('lastuid: '.$lastuid.'<br />');
    print('sopstimeout: '.$sopstimeout.'<br />');
    print('editsession: '.$editsession.'<br />');
    print('cursession: '.$cursession.'<br />');
    print('currenttime: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$currenttime.'<br />');
    print('currentobjtime: '.$currentobjtime->format('Y-m-d H:i:s').'<br />');
    print('newlkuntil: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  '.$strnewlockeduntil.'<br />');
    print('ct&lt;lu: '.(($currentobjtime <= $lockeduntil)?'true':'false').'<br />');
    print('expired: '.$expired.'<br />');
    print('</small>');
    //die();
  }

}
//
// end sops
//

if($oper=="savvrs"){
    // grab original versetext, commentary, footnotes, and commfootnotes
    $row = rs('select versetext, ifnull(commentary, \'-\') commentary, ifnull(footnotes, \'~~\') footnotes, ifnull(comfootnotes, \'~~\') commfootnotes
              from verse
              where testament = '.$test.'
              and book = '.$book.'
              and chapter = '.$chap.'
              and verse = '.$vers.' ');
    $beforevers = $row[0];
    $beforevers = processsqlvers($beforevers, 0, '-');
    $beforevers = substr($beforevers, 1, strlen($beforevers)-2);

    $beforefoot = explode('~~', $row['footnotes'].'');
    $beforefoot = processsqlfoot($beforefoot);
    if($beforefoot=='~~' || $beforefoot=='~~~~~~~~') $beforefoot='';

    //$befcomfoot = explode('~~', $row['commfootnotes'].'');
    $befcomfoot = explode('~~', getfootnotes($test, $book, $chap, $vers, 'com').'');
    //print('bef: '.$befcomfoot[0].'<br />');
    $befcomfoot = processsqlfoot($befcomfoot);
    if($befcomfoot=='~~' || $befcomfoot=='~~~~~~~~') $befcomfoot='';

    $beforecomm = $row[1];
    $beforecomm = processsqlcomm($beforecomm, 1, 'no commentary');
    $beforecomm = replacgreekhtml($beforecomm);
    if($beforecomm!='null') $beforecomm = substr($beforecomm, 1, strlen($beforecomm)-2);
    if($beforecomm === '-' || $beforecomm=='null') $beforecomm = '';
    $beforecomm = undoTOC($beforecomm);

    $marker = '~~~';
    $superscript = processsqltext($_POST['superscript'], 280, 1, '');
    $edtverse    = (($bookfinal==1)?'\''.$beforevers.'\'':processsqlvers($_POST['edtverse'], 0, '-'));
    $style       = processsqlnumb(((isset($_POST['style']))?$_POST['style']:1), 9, 0, 1);
    $paragraph   = processsqlnumb(((isset($_POST['paragraph']))?$_POST['paragraph']:0), 1, 0, 0);
    $whatsnew    = processsqlnumb(((isset($_POST['whatsnew']))?$_POST['whatsnew']:0), 1, 0, 0);
    $footnotes   = (($bookfinal==1)?$beforefoot:processsqlfoot(((isset($_POST['vrsfootnote']))?$_POST['vrsfootnote']:'')));
    //$comfootnotes= processsqlfoot(((isset($_POST['comfootnote']))?$_POST['comfootnote']:''));
    $comfootnotes= getaftercommfootnotes();
    $metadesc    = processsqltext($_POST['metadesc'], 500, 1, '');
    $commentary  = processsqlcomm($_POST['commentary'], 1, 'no commentary');
    $commentary  = str_replace('<ins>', '', $commentary);
    $commentary  = str_replace('</ins>', '', $commentary);
    $commentary  = str_replace('<del>', '', $commentary);
    $commentary  = str_replace('</del>', '', $commentary);

    $aftercomm = $commentary;
    if($aftercomm!= 'null') $aftercomm = substr($commentary, 1, strlen($commentary)-2);
    if($aftercomm === '-' || $aftercomm=='null') $aftercomm = '';
    if($beforecomm===$aftercomm) $commdiff = '';
    else $commdiff = htmlDiff($beforecomm, $aftercomm);
    $commdiff = str_replace('\\', '\\\\', $commdiff);
    $commdiff = str_replace('\'', '\\\'', $commdiff);
    if(strlen($commdiff)==0) $commdiff = null;

    $aftervers = substr($edtverse, 1, strlen($edtverse)-2);
    if($beforevers===$aftervers) $versdiff = null;
    else $versdiff = htmlDiff($beforevers, $aftervers);

    $afterfoot = $footnotes;
    if($beforefoot===$afterfoot || ($beforefoot.'~~')===$afterfoot) $footdiff = null;
    else $footdiff = htmlDiff(str_replace('~~', ' ~~ ', $beforefoot), str_replace('~~', ' ~~ ', $afterfoot));

    $aftcomfoot = $comfootnotes;
    //print('bef: '.$befcomfoot.'<br />');
    //print('aft: '.$aftcomfoot.'<br />');
    //die();
    if($befcomfoot===$aftcomfoot || ($befcomfoot.'~~')===$aftcomfoot) $commfootdiff = null;
    else $commfootdiff = htmlDiff(str_replace('~~', ' ~~ ', $befcomfoot), str_replace('~~', ' ~~ ', $aftcomfoot));

    $commentary = handleTOC($commentary);

    if($tocerror==''){
      if($checkchange==1 && (!is_null($versdiff) || !is_null($commdiff) || !is_null($footdiff) || !is_null($commfootdiff))){
        // sleeping? laptop and the data changed.
        // since it's here, do -something- with the data

        // save changes to sopssave table
        $update = dbquery('delete from sopssave where userid = '.$userid.' and testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ');
        $update = dbquery('insert into sopssave (userid, testament, book, chapter, verse, editsession, vers, versfoot, comm, commfoot) values(
                          '.$userid.', '.$test.', '.$book.', '.$chap.', '.$vers.', \''.$editsession.'\',
                          \''.$aftervers.'\',
                          \''.$afterfoot.'\',
                          \''.$aftercomm.'\',
                          \''.$aftcomfoot.'\'
                          )'
                          );



        require $docroot.'/includes/load_phpmailer.php';
        $row = rs('select myrevemail from myrevusers where myrevid = '.$myrevid.' ');
        $toemail= (($row)?$row[0]:'rswoods@swva.net');
        $btitle = getbooktitle($test,$book,0);
        $reference = $btitle.' '.$chap.':'.$vers;
        $subject = 'Your Recent Unsaved Changes for '.$reference;
        $emlbody = '<html><head><style>del {text-decoration:line-through;color:#EA3C18;}ins {text-decoration:underline;color:#17922A;}.data{border:1px solid black;padding:5px;}</style></head>
                    <body style="width:640px;border:1px solid black;padding:0;">
                    <img src="cid:sandt_rev_logo" />
                    <div style="padding:8px;">[content]</div>
                    <p style="text-align:center;font-size:80%;color:#aaa;">Copyright &copy; '.date('Y').' Spirit & Truth<br />PO Box 1737, Martinsville, IN 46151 US</p>
                    </body></html>';

        $body= $username.', it seems that you made changes in the commentary or Bible text that were not properly saved for <strong>'.$reference.'</strong>.
               Please see the changes below that we noticed you did not save.
               If you added something, it will be <ins>underlined and in GREEN</ins>.
               If you deleted something, it will be <del>strikethrough and in RED</del>.
               If you want to save your changes, please click <a href="https://'.$site.'/'.str_replace(' ', '-', $btitle).'/'.$chap.'/'.$vers.'">here</a> and re-input your changes.
               When finished, be sure to click &ldquo;Submit&rdquo; to properly save them.<br />';
               //If applicable, for unsaved commentary changes, you can copy/paste from the &ldquo;Unsaved Commentary&rdquo; section found below the &ldquo;Commentary Differences&rdquo; section.

        if(!is_null($versdiff))    {$body.= '<br />Verse Differences:<br /><div class="data">'.$versdiff.'</div>';}
        if(!is_null($footdiff))    {$body.= '<br />Verse Footnote Differences:<br /><div class="data">'.str_replace('~~','<br />', $footdiff).'</div>';}
        if(!is_null($commdiff)){
          $body.= '<br />Commentary Differences:<br /><div class="data">'.$commdiff.'</div>';
          //$body.= '<br />Unsaved Commentary:<br /><div class="data">'.$aftercomm.'</div>';
        }
        if(!is_null($commfootdiff)){$body.= '<br />Commentary Footnote Differences:<br /><div class="data">'.str_replace('~~','<br />', $commfootdiff).'</div>';}
        $body.= '<p>Thanks, and God bless you.</p>';
        $emlbody = str_replace('[content]', $body, $emlbody);
        if($isweb==1)
          sendemail($toemail, 'revisedenglishversion@gmail.com', $subject, $emlbody);
        else
          sendsmtpemail($toemail, 'revisedenglishversion@gmail.com', $subject, $emlbody);
        // log edit with comment there were unsaved changes
        $sopslogid = logedit($page,$test,$book,$chap,$vers,$userid,'SOPS: unsaved changes.', $whatsnew, str_replace('\'', '\\\'', $versdiff??''), $commdiff, str_replace('\'', '\\\'', $footdiff??''), str_replace('\'', '\\\'', $commfootdiff??''));

      }else{
        // only log edit if something has changed
        if(!is_null($commdiff) || !is_null($versdiff) || !is_null($footdiff) || !is_null($commfootdiff) || $_POST['comment']!='')
          $logid = logedit($page,$test,$book,$chap,$vers,$userid,isset($_POST['comment'])?$_POST['comment']:'', $whatsnew, str_replace('\'', '\\\'', $versdiff??''), $commdiff, str_replace('\'', '\\\'', $footdiff??''), str_replace('\'', '\\\'', $commfootdiff??''));
        // need to swap <strong>.$marker
        $commentary = str_replace('<strong>'.$marker, $marker.'<strong>', $commentary);
        $pos = strpos($commentary,$marker);
        // having name is required or ckeditor will remove the anchor
        if($pos!==false) $commentary = substr_replace($commentary, '<a id="marker'.$logid.'" name="marker'.$logid.'"></a>', $pos, strlen($marker));
        $commentary = str_replace('~~~', '', $commentary);

        // save the data
        $sql = 'update verse set
                heading = '.$superscript.',
                versetext = '.$edtverse.',
                paragraph = '.$paragraph.',
                style = '.$style.',
                footnotes = \''.(($footnotes=='')?'~~':$footnotes).'\',
                comfootnotes = null,
                metadesc = '.$metadesc.',
                commentary = '.$commentary.'
                where testament = '.$test.'
                and book = '.$book.'
                and chapter = '.$chap.'
                and verse = '.$vers.' ';
        $update = dbquery($sql);
        savfootnotes($test, $book, $chap, $vers, 'com', $comfootnotes);

        if($sqlerr==''){
          $sqlerr = datsav.'&nbsp;&nbsp;';
          if($sopsislive==1) $qry = dbquery('update verse set lockeduntil = \''.$strnewlockeduntil.'\' '.$sopswhere);
        }
      }
    }else $sqlerr = 'NOT SAVED!: '.$tocerror;
    logview($page,$test,$book,$chap,$vers);
    $goback++;
} // end of savvrs
// sops
if($sops>0){
?>
  <script>
    function sopsredirect(sops){
      document.frmnav.temp.value = sops;
      document.frmnav.lgid.value = <?=$sopslogid?>;
      document.frmnav.sopssession.value = '<?=$cursession?>';
      navigate(1,13,<?=$test?>,<?=$book?>,<?=$chap?>,<?=$vers?>);
    }
    setTimeout('sopsredirect(<?=$sops?>);', 300);
  </script>

<?
return;
}
// end sops

$row = rs('select v.heading superscript, ifnull(oln.heading,\'none\') heading, v.versetext, v.paragraph, v.style,
          ifnull(v.footnotes, \'~~~~~~~~\') footnotes, v.metadesc, v.commentary
          from verse v
          left join outline oln on (oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1)
          where v.testament = '.$test.'
          and v.book = '.$book.'
          and v.chapter = '.$chap.'
          and v.verse = '.$vers.' ');

$superscript= $row['superscript'];
$heading    = $row['heading'];
$heading    = (($heading=='none')?'-':'&ldquo;').$heading.(($heading=='none')?'-':'&rdquo;');
$edtverse   = $row['versetext'];
$paragraph  = $row['paragraph'];
$style      = $row['style'];
$footnotes  = $row['footnotes'];
$arfootnotes= explode('~~', $footnotes);
//$arcomfootnotes= explode('~~', $row['comfootnotes']);
$arcomfootnotes= explode('~~', getfootnotes($test,$book,$chap,$vers, 'com'));


$metadesc   = $row['metadesc'];
$commentary = $row['commentary'];
$commentary = preg_replace('#<br /> </li>#', '<br />&nbsp;</li>', $commentary??'');
if($commentary=='') $commentary = ' ';
$commentary = undoTOC($commentary);

if($myrevid>0){
  $sql = 'select highlight, ifnull(myrevnotes, \'-\') myrevnotes
          from myrevdata
          where myrevid = '.$myrevid.' and testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
  $row = rs($sql);
  if($row){
    $hlite = $row[0];
    $myrevnotes = $row[1];
  }else $myrevnotes='-';
  $nonote=(($myrevnotes=='-')?1:0);
  $myrevbutton = '&nbsp; <a onclick="rlightbox(\'note\',\''.$loc.'\',1);" title="Edit MyREV note"><img id="myr_'.$loc.'" src="/i/myrev_notes'.$colors[0].(($nonote==0)?'_DOT':'').'.png" style="width:1.2em;margin-bottom:-3px;" alt="edit" /></a>';
}else
  $myrevbutton = '';

if($peernotes>0){
  $sql = 'select ifnull(editnote, \'\') editnote, if(length(ifnull(editdetails, \'\'))>0,1,0), resolved
          from peernotes
          where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
  $row = rs($sql);
  if($row){
    if($row['resolved']==0 || $ednotesshowall==1)
      $peernote = (($row['resolved']==1)?'&reg; ':'').str_replace('[br]', '<br />', $row[0]);
    else
      $peernote = '';
    $havedetail = $row[1];
  }else{
    $peernote = '';
    $havedetail = 0;
  }
  $havepnote = (($row)?1:0);
  $sql = 'select ifnull(peerworknotes, \'\') workspace
          from myrevusers
          where myrevid = '.$myrevid.' ';
  $row = rs($sql);
  $havepeerwork = (($row[0]!='')?1:0);
  $peernotebutton = '&nbsp; <a onclick="rlightbox(\'pnote\',\''.$loc.'\',1);" title="Edit Reviewer note"><img id="pnn_'.$loc.'" src="/i/peer_notes'.$colors[0].(($havedetail==1)?'_YELDOT':'').'.png" style="width:1.2em;margin-bottom:-3px;" alt="edit" /></a>';
  $peernotebutton.= '&nbsp; <a onclick="rlightbox(\'pnote\',\'-1|0|0|0|0\',1);" title="Reviewer workspace"><img id="pnw_-1|0|0|0|0" src="/i/peer_workspace'.$colors[0].(($havepeerwork==1)?'_YELDOT':'').'.png" style="width:1.2em;margin-bottom:-3px;" alt="edit" /></a>';
  $peernotedisplay= '&nbsp; <span id="pn_'.$loc.'" class="peernote" style="display:'.(($peernote=='' || $viewpeernotes==0)?'none':'inline-block').';margin-left:3px;cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$loc.'\',1);">'.$peernote.'</span>';
}else{
  $peernotebutton = '';
  $peernotedisplay='';
}

if($editorcomments==1){
  $sql = 'select ifnull(editnote, \'\') editnote, if(length(ifnull(editdetails, \'\'))>0,1,0), resolved
          from editnotes
          where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
  $row = rs($sql);
  if($row){
    if($row['resolved']==0 || $ednotesshowall==1)
      $editnote = (($row['resolved']==1)?'&reg; ':'').str_replace('[br]', '<br />', $row[0]);
    else
      $editnote = '';
    $havedetail = $row[1];
  }else{
    $editnote = '';
    $havedetail = 0;
  }
  $haveenote = (($row)?1:0);
  $sql = 'select ifnull(notes, \'\') workspace
          from myrevusers
          where myrevid = -1 ';
  $row = rs($sql);
  $havewks = (($row[0]!='')?1:0);
  $editorbutton = '&nbsp; <a onclick="rlightbox(\'enote\',\''.$loc.'\',1);" title="Edit Editor note"><img id="edn_'.$loc.'" src="/i/editor_notes'.$colors[0].(($havedetail==1)?'_REDDOT':'').'.png" style="width:1.2em;margin-bottom:-3px;" alt="edit" /></a>';
  $editorbutton.= '&nbsp; <a onclick="rlightbox(\'enote\',\'-1|0|0|0|0\',1);" title="Editor workspace"><img id="edw_'.$loc.'" src="/i/editor_workspace'.$colors[0].(($havewks==1)?'_REDDOT':'').'.png" style="width:1.2em;margin-bottom:-3px;" alt="edit" /></a>';
  $editorbutton.= $peernotedisplay.'<span id="bn_'.$loc.'" class="editnote" style="display:'.(($editnote=='' || $viewedcomments==0)?'none':'inline-block').';margin-left:3px;cursor:pointer;" onclick="rlightbox(\'enote\',\''.$loc.'\',1);">'.$editnote.'</span>';
}else
  $editorbutton = '';

setprevnextlinksbyverseedit();
?>
  <div id="edit">
  <form name="frm" action="/" method="post">
  <span class="pageheader"><?=$prevlink.'&nbsp;'?><?=$stitle?><?='&nbsp;'.$nextlink?></span>
  <?
    print(printsqlerr($sqlerr));
    if($sopsislive==1){
      print('<small><a onclick="navsavehref(\'/'.str_replace(' ','-',$btitle).'/'.$chap.'/nav'.$vers.'\');">Back to Bible</a></small> <span style="color:'.$colors[7].';font-size:70%;">AutoPark: <span id="expires"></span> | <a onclick="extendsops();" style="color:'.$colors[7].';">Extend</a></span>');
      if($superman==1) print(' &nbsp; <span style="font-size:70%;"><a onclick="firesopsnow()" style="color:red;">fire now!</a></span>');
    }else{
      print('<small><a href="/'.str_replace(' ','-',$btitle).'/'.$chap.'/nav'.$vers.'">Back to Bible</a></small> <span style="color:'.$colors[7].';font-size:70%;font-style:italic;">(SOPS is off.)</span>');
    }
    ?>
  <br />
  <span style="display: inline-block; margin-top:8px; font-style:italic">Verse</span><?=getothertranslationlink($btitle, $chap, $vers, 1)?>&nbsp;&nbsp;
  <small>Style</small>
  <select name="style" onchange="setdirt();" style="margin-right:20px;">
    <option value="1"<?=fixsel(1, $style)?>>Prose</option>
    <option value="2"<?=fixsel(2, $style)?>>Poetry</option>
    <option value="3"<?=fixsel(3, $style)?>>Poetry_NB</option>
    <option value="4"<?=fixsel(4, $style)?>>BR_Poetry</option>
    <option value="5"<?=fixsel(5, $style)?>>BR_Poetry_NB</option>
    <option value="6"<?=fixsel(6, $style)?>>List</option>
    <option value="7"<?=fixsel(7, $style)?>>List_END</option>
    <option value="8"<?=fixsel(8, $style)?>>BR_List</option>
    <option value="9"<?=fixsel(9, $style)?>>BR_List_END</option>
  </select>
  <span style="white-space:nowrap;">
    <small>Para</small><input type="checkbox" name="paragraph" value="1"<?=fixchk($paragraph)?> onclick="setdirt()"  style="margin-right:20px;" />
    <small><span style="white-space:normal;">Hdg: <?=$heading?></small>
    <a onclick="olOpen('/manageheadings.php?test=<?=$test?>&book=<?=$book?>&chap=<?=$chap?>','<?=$screenwidth-100?>', 500,1);goback++;" title="Manage Verse Headings"><img src="/i/outline<?=$colors[0]?>.png" style="width:1.6em;margin-bottom:-9px;" title="Manage Verse Headings" /></a>&nbsp;&nbsp;&nbsp;
  </span>
  <span style="white-space:nowrap;">
    <small>Superscript</small>
    <input type="text" name="superscript" style="width:<?=(($isbig)?'250':'180')?>px;" value="<?=$superscript?>" onchange="setdirt();" />
  </span></span>
  <br />

<?if($bookfinal==1){
    $arfn    = array();
    $vsfncnt = 0;
    $fncnt   = 0;
    //print($footnotes);
    $theverse = processfootnotes($arfn, $edtverse, $footnotes, $fncnt, $chap, $vers);
    //print($fncnt);
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
    print('<span style="color:red;font-size:80%;"><em>'.$btitle.' has been locked. The verse text cannot be edited.</em></span>');
    print('<div style="margin-top:4px;padding-top:4px;border-top:1px solid black;font-size:90%;">'.$theverse.'</div>');
    displayfootnotes($fncnt, $arfn, 0, $chap);
  }else{?>

  <textarea name="edtverse" style="width:100%;" onchange="setdirt()"><?=$edtverse?></textarea>

  <span style="display: inline-block; margin-top:8px; font-style:italic;font-size:80%">Verse Footnotes</span>
  <!--<a onclick="$('divvrsfootnotes').style.display='block';addfootnote('vrs',0);"><img src="/i/tbl_show.png" alt="" title="<?=(($arfootnotes[0]=='')?'add':'insert')?> footnote" /></a>-->
  <?if($arfootnotes[0]!=''){?>
  <a onclick="$('divvrsfootnotes').style.display=($('divvrsfootnotes').style.display=='none'?'block':'none');return false;"><small>show/hide</small></a>
  <?}?>
  <br />
  <div id="divvrsfootnotes" style="display:none;">
    <div id="vrsfootnotes">
    <?
    for($ni=0, $size=count($arfootnotes);$ni<$size;$ni++){
      if($arfootnotes[$ni] != ''){?>
      <div>&nbsp;
        <!--<a id="vrsaddf<?=$ni?>" onclick="addfootnote('vrs',<?=($ni+1)?>);"><img src="/i/tbl_show.png" alt="" title="add footnote after" /></a>-->
        <a id="vrsdelf<?=$ni?>" onclick="removefootnote('vrs',<?=($ni)?>);"><img src="/i/tbl_hide.png" alt="" title="delete this footnote" /></a>
        <!--<img src="/i/mnu_menu<?=$colors[0]?>.png" class="bmksorthandle" style="margin-bottom:-3px;width:1em;cursor:ns-resize;" alt="" />-->
        <span id="vrsfidx<?=$ni?>" class="fnidx"><?=($ni+1)?></span>
        <input type="text" name="vrsfootnote[]" style="width:<?=(($ismobile)?'65':'77')?>%; margin-top:2px;" value="<?=$arfootnotes[$ni]?>" onchange="setdirt()" autocomplete="off" />
        <a id="vrsquof<?=$ni?>" onclick="appendquotes('vrs',<?=$ni?>);" title="click to append smart quotes">&ldquo;&rdquo;</a>
        <a id="vrsemsf<?=$ni?>" onclick="appendems('vrs',<?=$ni?>);" title="click to append emphasize &lt;em> tags"><small>em</small></a>
      </div>
    <?}}?>
    </div>
  </div>
<?}?>

  <span style="display: inline-block; margin-top:8px; font-style:italic">Commentary</span>
  <?=$myrevbutton.$peernotebutton.$editorbutton?>

  <textarea name="commentary" style="width:100%;height:330px;"><?=$commentary?></textarea>
  <span style="display: inline-block; margin-top:8px; font-style:italic;font-size:80%">Commentary Footnotes</span>
  <!--<a onclick="$('divcomfootnotes').style.display='block';addfootnote('com',0);"><img src="/i/tbl_show.png" alt="" title="<?=(($arcomfootnotes[0]=='')?'add':'insert')?> footnote" /></a>-->
  <?if($arcomfootnotes[0]!=''){?>
  <a onclick="$('divcomfootnotes').style.display=($('divcomfootnotes').style.display=='none'?'block':'none');return false;"><small>show/hide</small></a>
  <?}?>
  &nbsp;&nbsp;&nbsp;<a onclick="olOpen('/resourcebyref.php?navstr=<?=$navstring?>',<?=(($isbig==0)?$screenwidth+20:600)?>, 600);" title="Assign Resources"><img src="/i/tv<?=$colors[0]?>.png" width="16" /></a>
  <br />
  <div id="divcomfootnotes" style="display:none;">
    <div id="comfootnotes">
    <?
    for($ni=0, $size=count($arcomfootnotes);$ni<$size;$ni++){
      if($arcomfootnotes[$ni] != ''){?>
      <div>&nbsp;
        <!--<a id="comaddf<?=$ni?>" onclick="addfootnote('com',<?=($ni+1)?>);"><img src="/i/tbl_show.png" alt="" title="add footnote after" /></a>-->
        <a id="comdelf<?=$ni?>" onclick="removefootnote('com',<?=($ni)?>);"><img src="/i/tbl_hide.png" alt="" title="delete this footnote" /></a>
        <!--<img src="/i/mnu_menu<?=$colors[0]?>.png" class="bmksorthandle" style="margin-bottom:-3px;width:1em;cursor:ns-resize;" alt="" />-->
        <span id="comfidx<?=$ni?>" class="fnidx"><?=($ni+1)?></span>
        <input type="text" name="comfootnote[]" style="width:<?=(($ismobile)?'65':'77')?>%; margin-top:2px;" value="<?=$arcomfootnotes[$ni]?>" onchange="setdirt()" autocomplete="off" />
        <a id="comquof<?=$ni?>" onclick="appendquotes('com',<?=$ni?>);" title="click to append smart quotes">&ldquo;&rdquo;</a>
        <a id="comemsf<?=$ni?>" onclick="appendems('com',<?=$ni?>);" title="click to append emphasize &lt;em> tags"><small>em</small></a>
      </div>
    <?}}?>
    </div><br />
  </div>

  <input type="hidden" name="dirt" value="0" />
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="1" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="gobackx" value="<?=$goback?>" />
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="sops" value="0" />
  <input type="hidden" name="cursession" value="<?=$editsession?>" />
  <audio id="sopsding" src="includes/resource/sopsding.mp3" preload="auto"></audio>

  <br />
  <table>
    <tr>
      <td style="vertical-align:bottom;">
      <?
        if($sopsislive==1){
          print('<input type="button" id="btnback" onclick="navsavehref(\'/'.str_replace(' ','-',$btitle).'/'.$chap.'/'.$vers.'/c/fe\');" style="text-align:center;font-size:80%" value="Back" />&nbsp;');
          print('<input type="button" name="btnsops" id="btnsops" value="Park" onclick="sopssave(document.frm,7);" style="text-align:center;font-size:80%" />&nbsp;');
        }else{
          print('<button id="btnback" onclick="history.go(-goback);return false;" style="text-align:center;font-size:80%">Back</button>&nbsp;');
        }
        print('<input type="submit" name="btnsbt" id="btnsbt" value="Submit" onclick="return validate(document.frm);" style="text-align:center;font-size:80%" />&nbsp;');
        $textboxsize = (($isbig)?'400':'170');
      ?>
      </td>
      <td>
        <small>Comment</small><br />
        <input id="txtcomment" type="text" name="comment" value="" maxlength="200" style="width:<?=$textboxsize?>px;margin-top:2px" autocomplete="off" />&nbsp;
        <a onclick="doinput($('txtcomment'),'&ldquo;','&rdquo;');" title="click to insert smart quotes">&ldquo;&rdquo;</a>
        <span style="white-space:nowrap;"><input type="checkbox" name="whatsnew" id="whatsnew" value="1" /> <label for="ecmt1" style="font-size:10pt;">What&rsquo;s New</label></span>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td style="font-size:9pt">
        <span style="white-space:nowrap;"><input type="radio" name="cmt" id="ecmt1" value="1" title="modify translation" onclick="document.frm.comment.value='modify translation'" /><label for="ecmt1">MT</label></span>
        <span style="white-space:nowrap;"><input type="radio" name="cmt" id="ecmt2" value="2" title="modify commentary" onclick="document.frm.comment.value='modify commentary'" /><label for="ecmt2">MC</label></span>
        <span style="white-space:nowrap;"><input type="radio" name="cmt" id="ecmt3" value="3" title="initial commentary" onclick="document.frm.comment.value='Initial Commentary: &ldquo;&rdquo;'" /><label for="ecmt3">IC</label></span>
        <span style="white-space:nowrap;"><input type="radio" name="cmt" id="ecmt4" value="4" title="fix scripture reference" onclick="document.frm.comment.value='fix scripture reference'" /><label for="ecmt4">FSR</label></span>
        <span style="white-space:nowrap;"><input type="radio" name="cmt" id="ecmt5" value="5" title="fix commentary reference" onclick="document.frm.comment.value='fix commentary reference'" /><label for="ecmt5">FCR</label></span>
        <span style="white-space:nowrap;"><input type="radio" name="cmt" id="ecmt6" value="6" title="fix section heading" onclick="document.frm.comment.value='fixed section heading to match verse text'" /><label for="ecmt6">FSH</label></span>
        <span style="white-space:nowrap;"><input type="radio" name="cmt" id="ecmt7" value="7" title="fix commentary grammar" onclick="document.frm.comment.value='fix commentary grammar'" /><label for="ecmt7">FCG</label></span>
      </td>
    </tr>
    <tr>
      <td style="text-align:right"><small><?=(($isbig)?'meta description':'meta')?></small></td>
      <td>
        <input type="text" name="metadesc" value="<?=$metadesc?>" maxlength="500" style="width:<?=$textboxsize?>px;margin-top:2px" />
      </td>
    </tr>
  </table>


<?
print('</form></div>');
if($isbig){
  print('<div style="text-align:center;margin:0 auto;">');
  print(displayedits($page,$test,$book,$chap,$vers));
  print('</div>');

?>

<br /><br />
<table style="font-size:80%;border-collapse:separate;border-spacing:5px;">
  <tr>
    <td colspan="2" style="color:red;">Instructions for verse formatting, sort of:<br />Note, these tags or markers are for VERSES ONLY, and not the commentary.</td>
  </tr>
  <tr>
    <td>style: Prose</td>
    <td>
      This is flowing text.  See almost any verse in the NT.
    </td>
  </tr>
  <tr>
    <td>style: Poetry</td>
    <td>
      This is formatted text.  See Proverbs.
    </td>
  </tr>
  <tr>
    <td>style: Poetry_NB</td>
    <td>
      This is poetry with no small vertical space at the end of the verse.  See Ezra 2
    </td>
  </tr>
  <tr>
    <td>style: BR_Poetry</td>
    <td>
      This is poetry with an extra linebreak before the verse.  See Judges 5:6.
    </td>
  </tr>
  <tr>
    <td style="white-space:nowrap">style: BR_Poetry_NB</td>
    <td>
      This is poetry with an extra linebreak before the verse and no vertical space after the verse.  See Ezra 2:36.
    </td>
  </tr>
  <tr>
    <td>[hp]</td>
    <td>This indicates a break in any of the poetry-type verse.  See Ex 15, 1 Sam 2:1ff, or any of Proverbs.
      If you need to start or end HP somewhere in the middle of a verse, use [hpbegin] or [hpend] to indicate where you want the HP to start/stop.
      See Exodus 15:1 for [hpbegin] and Judges 5:31 for [hpend].
    </td>
  </tr>
  <tr><td colspan="2">&nbsp;<br />The following tags are applicable only to prose verses</td></tr>
  <tr>
    <td>[pg]</td>
    <td>This starts a new paragraph in the middle of a verse.  See Matt 1:6</td>
  </tr>
  <tr>
    <td>[bq]</td>
    <td>This indicates a block quote.  It indents a block of verses (or part of a verse).  It must be closed ( [/bq] ) somewhere in the chapter it is opened.
      See Ezra 1:2, 1:4.
    </td>
  </tr>
  <tr>
    <td>[br]</td>
    <td>This indicates a break, a hard return.  Note that you can also insert a break by simply pressing [ENTER] in the middle of a verse.
      Do not press enter at the beginning or end of a verse.  At the beginning or end of verses you must use [br].
      There are currently no examples in the database.
    </td>
  </tr>
  <tr>
    <td>[mvh]</td>
    <td>This indicates a mid-verse-header.  See Mark 3:19</td>
  </tr>
  <tr>
    <td>[fn]</td>
    <td>This indicates a footnote location.</td>
  </tr>
</table>
<?}else{?>
    <br /><small>You're on a small screened device. To see the instructions and/or previous edits for this verse, use a larger device.</small>
<?}?>
  <script>
    var checkforchanges = true;
    var goback=<?=$goback?>;
    var toffset=0;
    var tz_offset = new Date().getTimezoneOffset();
    tz_offset = ((tz_offset==0)?0:-tz_offset);
    var sopsupdatetimer,
        sopssavetimer,
        countdown,
        sopschanges=0,
        timelockeduntil = parseDate("<?=$strnewlockeduntil?>" + " GMT");

    function extendsops(){
      //sopstimeout = sopsorigtimeout + (sopstimeoutextend*60*1000);
      timelockeduntil = Date.parse(new Date().toLocaleString()) + sopstimeout + (sopstimeoutextend*60*1000);
      sopsupdate(document.frm, 2);
    }

    function firesopsnow(){
      timelockeduntil = Date.parse(new Date().toLocaleString());
      sopsupdate(document.frm, 2);
    }

    function startsopstimer(){
      countdown = setInterval(function(){
        var now = new Date().getTime();
        // Find the millisec between now and the count down date
        var millisec = timelockeduntil - now;

        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(millisec / (1000 * 60 * 60 * 24));
        var hours = Math.floor((millisec % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((millisec % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((millisec % (1000 * 60)) / 1000);

        if(millisec<=0){
          clearInterval(countdown);
          $('expires').innerHTML = 'Parking..';
          sopssave(document.frm,1);
          return;
        }
        $('expires').style.color = ((millisec<20000)?'red':'green')
        if(document.frm.dirt.value==1 && millisec<20000)
          notify();
        else document.title = origtitle;
        if(hours>0)
          $('expires').innerHTML = hours + ":" +('0'+ minutes).slice(-2) + ":" + ('0'+seconds).slice(-2);
        else
          $('expires').innerHTML = ('0'+minutes).slice(-2) + ":" + ('0'+seconds).slice(-2);
      }, 1000);
    }

    var notifyidx = 1;
    var origtitle = document.title;
    var bookfinal = <?=$bookfinal?>;

    function notify(){
      notifyidx = 1-notifyidx;
      if(notifyidx==1){
        if(sopstimeoutding==1){
          var snd = $('sopsding');
          if(snd.paused) snd.play();
          else snd.currentTime=0;
        }
        document.title = '!!!';
      }else document.title = origtitle;
    }

    if(sopsislive==1) addLoadEvent(startsopstimer);

    function validate(f){
      if($('whatsnew').checked && trim($('txtcomment').value) == ''){
        alert('Since you\'re marking this as whatsnew, you must leave a comment');
        $('txtcomment').focus();
        return false;
      }
      if(bookfinal==0 && !checkfootnotes('vrs', CKEDITOR.instances.edtverse.getData())) return false;
      if(!checkfootnotes('com', CKEDITOR.instances.commentary.getData())) return false;
      disablebuttons();
      checkforchanges = false;
      f.oper.value = "savvrs";
      return true;
    }

    function disablebuttons(){
      $('btnsbt').value = 'Pls wait..';
      setTimeout('$(\'btnsbt\').disabled=true', 200);
      setTimeout('$(\'btnback\').disabled=true', 200);
      try{setTimeout('$(\'btnsops\').disabled=true', 200);}catch(e){};
    }

    function checkdirt(){
      if(!checkforchanges) return;
      var f = document.frm;
      var dirty = 0;
      if(f.dirt.value==1) dirty = 1;
      for (var i in CKEDITOR.instances) {
        if(CKEDITOR.instances[i].checkDirty()) dirty = 1;
      }
      if(sopsislive==1 && dirty==0) sopsupdate(document.frm,4); // release it
      if(dirty == 1) return 'x'; // custom messages not supported
      else return;
    }

    function navsavehref(where){
      var f = document.frm;
      if(f.dirt.value==1){
        if(confirm('You have unsaved changes.\nWant to leave anyway?')){
          sopsupdate(f,4);
          checkforchanges = false;
          releasestr = 'location.href=\''+where+'\'';
        }else releasestr = '';
      }else{
        sopsupdate(f,4);
        checkforchanges = false;
        releasestr = 'location.href=\''+where+'\'';
      }
    }

    function navsaveform(m,p,t,b,c,v){
      var f = document.frm;
      var navstr = m+','+p+','+t+','+b+','+c+','+v;
      if(f.dirt.value==1){
        if(confirm('You have unsaved changes.\nWant to leave anyway?')){
          sopsupdate(f,4);
          checkforchanges = false;
          releasestr = 'navigate('+navstr+');';
        }else releasestr = '';
      }else{
        sopsupdate(f,4);
        checkforchanges = false;
        releasestr = 'navigate('+navstr+');';
      }
    }

    var releasestr = '';
    var prevlinkurl = $('prevlinkid').href;
    var nextlinkurl = $('nextlinkid').href;

    function setdirt(){
      var f = document.frm;
      f.dirt.value = 1;
      if(sopsislive==1){
        sopschanges = 1;
        if($('prevlinkid')) $('prevlinkid').setAttribute("href", "javascript: navsavehref('"+prevlinkurl+"');");
        if($('nextlinkid')) $('nextlinkid').setAttribute("href", "javascript: navsavehref('"+nextlinkurl+"');");
        var now = Date.parse(new Date().toLocaleString());
        if((now+sopstimeout) > timelockeduntil) timelockeduntil = now + sopstimeout;
      }
    }

    function extendfrompopup(){
      var now = Date.parse(new Date().toLocaleString());
      if((now+sopstimeout) > timelockeduntil) timelockeduntil = now + sopstimeout;
      sopsupdate(document.frm, 8);
    }

    window.onbeforeunload = checkdirt;

    function reloadmyrevnotes(qry){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var mrimg = $('myr_'+qry);
          var img = '/i/myrev_notes'+colors[0]+((ret.myrevnotes)?'_DOT':'')+'.png';
          mrimg.src = img;
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
          var resolved = ret.resolved;
          try{ // to catch if user edited workspace
            var mrimg = $('edn_'+qry);
            var img = '/i/editor_notes'+colors[0]+((ret.editdetails)?'_REDDOT':'')+'.png';
            mrimg.src = img;
            var bnspan = $('bn_'+qry);
            bnspan.innerHTML = ret.editnote;
            bnspan.style.display = ((ret.editnote=='' || resolved==1)?'none':'inline-block');
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
          var resolved = ret.resolved;
          try{ // to catch if user edited workspace
            if(qry!='-1|0|0|0|0'){ // if not workspace
              var mrimg = $('pnn_'+qry);
              var img = '/i/peer_notes'+colors[0]+((ret.peerdetails)?'_YELDOT':'')+'.png';
              mrimg.src = img;
              var bnspan = $('pn_'+qry);
              bnspan.innerHTML = ret.peernote;
              bnspan.style.display = ((ret.editnote=='' || resolved==1)?'none':'inline-block');
            }else{
              var mrimg = $('pnw_'+qry);
              var img = '/i/peer_workspace'+colors[0]+((ret.peerwork==1)?'_YELDOT':'')+'.png';
              mrimg.src = img;
            }
          }catch(e){}
        }
      }
      xmlhttp.open('GET', '/jsonmyrevtasks.php?task=pdata&ref='+qry,true);
      xmlhttp.send();
    }

    if(sopsislive==1) sopsupdatetimer= setTimeout('sopsupdate(document.frm, 1);', sopsfirecheck); // in pagetop.php

    function sopsupdate(f, sopstask){
      try{clearTimeout(sopsupdatetimer)}catch(e){};
      var dirty = sopschanges;
      var now = Date.parse(new Date().toLocaleString());
      if(sopstask>1 || (dirty==1 && (now+sopstimeout)>timelockeduntil)){
        if(sopstask==8) sopstask=1; // sopstask==8 is from popups
        var qry='<?=$loc?>';
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange=function(){
          if (xmlhttp.readyState==4 && xmlhttp.status==200){
            var ret = JSON.parse(xmlhttp.responseText);
            switch(ret.sopstask){
            case 'extend':
              sopschanges = 0;
              timelockeduntil = parseDate(ret.expires + " GMT");
              break;
            case 'release':
              if(releasestr != '') eval(releasestr);
              break;
            default:
              sopschanges = 0;
            }
          }
        }
        //alert('/jsonmyrevtasks.php?task=sopstask'+sopstask+'&timelockeduntil='+timelockeduntil+'&ref='+qry);
        xmlhttp.open('GET', '/jsonmyrevtasks.php?task=sopstask'+sopstask+'&timelockeduntil='+timelockeduntil+'&ref='+qry,true);
        xmlhttp.send();
      }
      sopsupdatetimer= setTimeout('sopsupdate(document.frm, 1);', sopsfirecheck); // in pagetop.php
    }

    function sopssave(f,sopstask){
      checkforchanges = false;
      var dirty = sopscheckdirt(f);
      if(dirty == 1){
        disablebuttons();
        f.oper.value = 'savvrs';
        f.sops.value = sopstask;
        f.submit();
      }else{
        document.frmnav.temp.value = ((sopstask==1)?0:sopstask);
        navigate(1,13,<?=$test?>,<?=$book?>,<?=$chap?>,<?=$vers?>);
      }
    }

    function sopscheckdirt(f){
      var dirty = 0;
      if(f.dirt.value==1) dirty = 1;
      for (var i in CKEDITOR.instances) {
        if(CKEDITOR.instances[i].checkDirty()) dirty = 1;
      }
      return dirty;
    }

    function parseDate(date) {
      const parsed = Date.parse(date);
      if(!isNaN(parsed)) {return parsed;}
      return Date.parse(date.replace(/-/g, '/').replace(/[a-z]+/gi, ' '))+60000*tz_offset;
    }

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

    //
    // making footnotes sortable (not used..)
    //
    function makesortable(pre){
      var myfnsort = new Sortable(document.getElementById(pre+'footnotes'), {
        animation: 150,
        handle: '.bmksorthandle',
        direction: 'vertical',
        touchStartThreshold: 5,
        onEnd: function (evt) {
          var oldidx = evt.oldIndex;
          var newidx = evt.newIndex;
          if(newidx > oldidx){
            // moving down
            fnreindex(pre, oldidx, newidx, 0);
            for(var i=newidx;i>oldidx;i--){fnreindex(pre, i, i-1, 0);}
          }else{
            // moving up
            for(var i=oldidx-1;i>=newidx;i--){fnreindex(pre, i, i+1, 0);}
            fnreindex(pre, oldidx, newidx, 0);
          }
          renumfns(pre);
        },
      });
    }

    //setTimeout('makesortable("com");', 500);

  </script>
  <script src="/ckeditor/ckeditor.js?v=<?=$fileversion?>"></script>
<?if($bookfinal==0){?>
  <script>
    CKEDITOR.replace( 'edtverse',
    {
      toolbar :
      [
        { name: 'document', items : [ <?=(($superman==1)?'\'Source\',':'')?>'AutoCorrect','Undo','Redo'] },
        { name: 'basicstyles', items : [ 'Bold','-','Italic','RemoveFormat' ] },
        { name: 'tools', items : [ 'PasteFootnote','PasteParagraph','PasteMidVerseHeader','PasteMidVerseSuperscript','SpecialChar' ] }
      ],
      height : ((ismobile==1)?'4.2em':'2.7em'),
      enterMode : CKEDITOR.ENTER_BR,
      shiftEnterMode : CKEDITOR.ENTER_P
    }
    );
    CKEDITOR.instances.edtverse.on('change', function () {
      // operations
      setdirt();
    });
    //setTimeout('makesortable("vrs");', 500);
  </script>
  <?}
require_once $docroot.'/includes/commentaryeditor.php';?>

<? // this is php, not js

function getaftercommfootnotes(){
  $footnotes = ((isset($_POST['comfootnote']))?$_POST['comfootnote']:'');
  if(is_null($footnotes) || !isset($footnotes) || empty($footnotes) || $footnotes=='')
    //return '~~';
    return '';
  $ret='';
  for($ni=0, $size=count($footnotes); $ni<$size;$ni++){
    $ret.=fixfoot($footnotes[$ni]);
  }
  return substr($ret, 0, strlen($ret)-2);
}

function processsqlfoot($fns){
  if(is_null($fns) || !isset($fns) || empty($fns) || $fns=='' || $fns[0]=='')  // the last condition is questionable..
    return '';
  else{
    $ret='';
    for($ni=0, $size=count($fns); $ni<$size;$ni++){
      $ret.=fixfoot($fns[$ni]);
    }
    return substr($ret, 0, strlen($ret)-2);
  }
}

function fixfoot($ftn){
  $ret = $ftn;
  $ret = str_replace('<i>', '<em>', $ret);
  $ret = str_replace('</i>', '</em>', $ret);
  $ret = trim(strip_tags($ret.'', '<em>'));  // allow <em>s
  $ret = preg_replace('#"+#', '', $ret); // remove double quotes
  $ret = str_replace("'", "&rsquo;", $ret);
  // I should not have to do this...
  $ret = str_replace("“", "&ldquo;", $ret);
  $ret = str_replace("”", "&rdquo;", $ret);
  $ret = str_replace("‘", "&lsquo;", $ret);
  $ret = str_replace("’", "&rsquo;", $ret);

  if($ret=='') $ret = 'missing footnote';
  $ret.= '~~';
  return $ret;
}

function setprevnextlinksbyverseedit(){
  global $prevlink, $nextlink, $prevlinkact, $nextlinkact, $colors, $mitm, $sopsislive;
  global $page, $test, $book, $chap, $vers;
  // gotta redefine them here.
  $prevlink    = '<img src="/i/mnu_prevdim'.$colors[0].'.png" style="border:0;vertical-align:middle;height:1em" alt="prev" />';
  $nextlink    = '<img src="/i/mnu_nextdim'.$colors[0].'.png" style="border:0;vertical-align:middle;height:1em" alt="next" />';
  $prevlinkact = '<img src="/i/mnu_prev'.$colors[0].'.png" style="border:0;vertical-align:middle;height:1em" alt="prev" />';
  $nextlinkact = '<img src="/i/mnu_next'.$colors[0].'.png" style="border:0;vertical-align:middle;height:1em" alt="next" />';
  if($test > -1 && $book > 0 && $chap > 0){
    $sql = 'select count(*) from verse where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' ';
    $row = rs($sql);
    $numvrs =$row[0];
    if($sopsislive==1)
      $link = '<a onclick="navsaveform('.$mitm.','.$page.','.$test.','.$book.','.$chap.',[vs]);" title="[tit]">';
    else
      $link = '<a onclick="navigate('.$mitm.','.$page.','.$test.','.$book.','.$chap.',[vs]);" title="[tit]">';
    if($numvrs > $vers){
      $nextlink = str_replace('[vs]', ($vers+1), str_replace('[tit]', 'next', $link)).$nextlinkact.'</a>';
    }
    if($vers>1){
      $prevlink = str_replace('[vs]', ($vers-1), str_replace('[tit]', 'previous', $link)).$prevlinkact.'</a>';
    }
  }
}
?>
