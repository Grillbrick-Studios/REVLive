<?php
if(!isset($page)) die('unauthorized access');

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';

$sopsstatus = (isset($_POST['temp']))?$_POST['temp']:0;
$sopslogid  = (isset($_POST['lgid']))?$_POST['lgid']:0;
$sopsresav  = (isset($_POST['sopsresav']))?$_POST['sopsresav']:0;
$sopssession  = (isset($_POST['sopssession']))?$_POST['sopssession']:'-';
$sopsmsg = '';
$sopslink = 'I\'m lost.';
$strlockeduntil=0;
$timersactive=0;

// debug
$debug=0;
if($debug==1){
  print('test: '.$test.'<br />');
  print('book: '.$book.'<br />');
  print('chap: '.$chap.'<br />');
  print('vers: '.$vers.'<br />');
}

$loc = $myrevid.'|'.$test.'|'.$book.'|'.$chap.'|'.$vers;

$sopsnavigate = 'navigate('.$mitm.',1,'.$test.','.$book.','.$chap.','.$vers.');';
$sopswhere = 'where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
$sopstimer = '';
$btitle = getbooktitle($test,$book, 0);
$scriptref = $btitle.' '.$chap.':'.$vers;
$scripture = '<a href="/'.str_replace(' ', '_',$btitle).'/'.$chap.'/'.$vers.'/_/fe">'.$scriptref.'</a>';
$stitle = 'Parked:'.(($ismobile)?'<br />':' ').'<a href="/'.str_replace(' ','-',$btitle).'/'.$chap.'/'.$vers.'/_/fe" class="comlink0">'.$scriptref.'</a>';

$resavmessage = '';
if($sopsresav==1){
  $row = rs('select edituserid, editsession from verse where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ');
  if($row['edituserid']==$userid && $row['editsession']==$sopssession){
    // ok to save
    $resavmessage = '<span style="color:green;">Success!!</span> Your changes for '.$scriptref.' were able to be saved.<br />You may disregard the SOPS email you received.';
    $row = rs('select userid, editsession, vers, versfoot, comm, commfoot from sopssave
               where userid = '.$userid.' and testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ');
    if($row){
      $qry = dbquery('update verse set versetext = \''.$row['vers'].'\', footnotes = \''.$row['versfoot'].'\', commentary = \''.str_replace('\'', '\\\'', $row['comm']).'\' where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ');
      savfootnotes($test, $book, $chap, $vers, 'com', $row['commfoot']);
      $sopsstatus = 8;
      $sopslink = '';

      // working on this...
      $logid = logedit(1,$test,$book,$chap,$vers,$userid,'SOPS: Changes successfully saved from landing page!', 0, 'See most recent SOPS edit log for this verse', null, null, null);

    }else{
      $resavmessage = 'Sorry, your changes were not able to be saved. You will need to manually re-enter your edits for '.$scriptref.'.<br />Please see below, or refer to the SOPS email you received.';
      $sopsstatus = 9;
      $sopslink = '<a onclick="'.$sopsnavigate.'">Click here to resume editing.</a>';
    }
  }else{
    // sopssession mismatch, cannot save
    $resavmessage = 'Sorry, someone else has edited '.$scriptref.'. You will need to manually re-enter your edits.<br />Please see below, or refer to the SOPS email you received.';
    $sopsstatus = 9;
    $sopslink = '<a onclick="'.$sopsnavigate.'">Click here to resume editing.</a>';
  }
  $qry = dbquery('delete from sopssave where userid = '.$userid.' and testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ');
  //print($resavmessage);
  $sopsmsg = $resavmessage;
}


switch($sopsstatus){
case 0: // timeout nosave
case 1: // timeout save
    //$qry = dbquery('update verse set edituserid = 0, editsession = null, lockeduntil = null '.$sopswhere);
    $sopsmsg = 'Your editing session for '.$scriptref.' has timed out.';
    if($sopsstatus==1) $sopsmsg.= '<br /><br />Your changes were not saved. See below.<br />Your unsaved changes were also emailed to you.';
    $sopslink = '<a onclick="'.$sopsnavigate.'">Click here to resume editing.</a>';
    if($sopsstatus==1) $sopslink.= ' <span style="font-size:80%;color:red;">(warning: the unsaved changes below will be discarded).</span>';
    break;
case 2: // locked by other user
case 3: // locked by another user, changes emailed
    $row = rs('select myrevname from myrevusers where myrevid > 0 and userid > 0 and userid = (select edituserid from verse '.$sopswhere.') ');
    $sopsmsg = $scripture.' is currently locked by '.(($row)?$row[0]:'unknown').'.';
    if($sopsstatus==3) $sopsmsg.= '<br />See below. Your unsaved changes were also emailed to you.';
    $sopslink = '<a onclick="'.$sopsnavigate.'">Click here to try again.</a>';
    $sopstimer = getsopstimer($sopsstatus);
    break;
case 4: // same user, session mismatch
case 5: // same user, session mismatch, changes emailed
    $sopsmsg = 'You have an editing session for '.$scripture.' already open.<br />';
    $sopsmsg.= 'Either you already have another tab with the same verse open, or the session was not closed properly.';
    if($sopsstatus==5) $sopsmsg.= '<br />See below. Your unsaved changes were also emailed to you.';
    $sopslink = '&nbsp;<br /><a onclick="'.$sopsnavigate.'">Click here to try again.</a>';
    $sopstimer = getsopstimer($sopsstatus);
    break;
case 6: // verse has been edited by someone else
    $sopsmsg = $scripture.' has been edited since you opened your editing session.';
    $sopsmsg.= '<br />It is possible that you shut down your computer without properly saving your changes.';
    $sopsmsg.= '<br />See below. Your unsaved changes were also emailed to you.';
    $sopslink = '<a onclick="'.$sopsnavigate.'">Click here to try again.</a>';
    break;
case 7: // parked
    $qry = dbquery('update verse set edituserid = 0, editsession = null, lockeduntil = null '.$sopswhere);
    $sopsmsg = 'Your editing session for '.$scripture.' is parked.<br />Your changes (if any) were saved.';
    $sopslink = '<a onclick="'.$sopsnavigate.'">Click here to re-open.</a>';
    break;
}

// never autosave data !!

// print the verse
$sql = 'select versetext, footnotes from verse
        where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
$row = rs($sql);
$theverse    = $row['versetext'];
$footnotes   = $row['footnotes'];
$arfn    = array();
$fncnt   = 0;
$vsfncnt = 0;
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
    $theverse = '[['.substr($theverse,1).((strpos($theverse, ']]')>0)?'':']]');
    $theverse = str_replace(']]]]', ']]', $theverse);
    $theverse = str_replace('[[[[', '[[', $theverse);
}
if(strpos($theverse, '[[')!==false && strpos($theverse, ']]')===false) $theverse.= ']]';
if(strpos($theverse, '[')!==false && strpos($theverse, ']')===false) $theverse.= ']';
if(strpos($theverse, ']')!==false && strpos($theverse, '[')===false) $theverse = '['.$theverse;
$theverse = fixverse($theverse);
$theverse.= ' <a href="/'.str_replace(' ', '-', $btitle).'/'.$chap.'/nav'.$vers.'" class="comlink0"><img src="/i/bible_icon'.$colors[0].'.png" style="width:1.1em;margin-bottom:-3px;" alt="Bible" title="back to Bible" /></a>';
if(!$inapp || !$isios)
  $theverse.= getothertranslationlink($btitle, $chap, $vers, 1);



?>
  <form name="frm" method="post" action="/">
  <span class="pageheader"><?=$stitle?></span><br />
    <div style="margin:0 auto; max-width:800px;">
      <?
        print($theverse);
        displayfootnotes($fncnt, $arfn, 0, $chap);
      ?>
    </div>
    <div style="margin:0 auto;margin-top:20px;max-width:800px;font-size:90%;">
    <?if($userid==1)
        print('<span style="font-size:70%;color:'.$colors[7].'">sopsstatus: '.$sopsstatus.'</span><br />');
      print('<span style="color:red;">'.$sopsmsg.'</span><br />');
      if($sopsstatus==8){
        print('&nbsp;<br />Click <a onclick="navigate('.$mitm.',5,'.$test.','.$book.','.$chap.','.$vers.');">here</a> to check the changes.');
      }else{
        if($sopsstatus==1){
          print('<br /><span style="color:red;">If the verse has not been edited by someone else, you can try to <a onclick="tryresave(document.frm);">save your changes</a>.</span><br />');
        }
      };
      print($sopstimer.'<br />');
      print($sopslink.'<br />');
      if($sopsstatus!=8) print('&nbsp;<br /><a href="/" target="_blank">Click here</a> to open a new tab.');
      if($debug==1)
        print('debug:<br /><span id="debugx"></span>');
      ?>
    </div>
    <input type="hidden" name="mitm" value="<?=$mitm?>" />
    <input type="hidden" name="page" value="13" />
    <input type="hidden" name="test" value="<?=$test?>" />
    <input type="hidden" name="book" value="<?=$book?>" />
    <input type="hidden" name="chap" value="<?=$chap?>" />
    <input type="hidden" name="vers" value="<?=$vers?>" />
    <input type="hidden" name="temp" value="<?=$sopsstatus?>" />
    <input type="hidden" name="lgid" value="<?=$sopslogid?>" />
    <input type="hidden" name="sopssession" value="<?=$sopssession?>" />
    <input type="hidden" name="sopsresav" value="0">
  </form>

<?
if($sopsstatus != 8 && $sopslogid != 0){
  $sql = 'select versdiff, footdiff, commdiff, commfootdiff from editlogs where logid = '.$sopslogid.' ';
  $row = rs($sql);
  $versdiff = str_replace('</del></del>', '</del>', $row['versdiff']??'');
  $footdiff = str_replace('</del></del>', '</del>', $row['footdiff']??'');
  $commdiff = str_replace('</del></del>', '</del>', $row['commdiff']??'');
  $commfootdiff = str_replace('</del></del>', '</del>', $row['commfootdiff']??'');
  $heading = '<br />Here are the changes that we noticed you did not save.
              If you added something, it will be <ins>underlined and in GREEN</ins>.
              If you deleted something, it will be <del>strikethrough and in RED</del>.<br />';
              //If applicable, to see the exact commentary that was left unsaved, please see the SOPS email, section "Unsaved Commentary." If you want, you may copy and paste from it.<br />';

  $diffs = '';
  if(!is_null($versdiff) && $versdiff!='')    {$diffs.= '<br />Verse Differences:<br /><div class="data">'.$versdiff.'</div>';}
  if(!is_null($footdiff) && $footdiff!='')    {$diffs.= '<br />Verse Footnote Differences:<br /><div class="data">'.str_replace('~~','<br />', $footdiff).'</div>';}
  if(!is_null($commdiff) && $commdiff!='')    {$diffs.= '<br />Commentary Differences:<br /><div class="data">'.$commdiff.'</div>';}
  if(!is_null($commfootdiff) && $commfootdiff!=''){$diffs.= '<br />Commentary Footnote Differences:<br /><div class="data">'.str_replace('~~','<br />', $commfootdiff).'</div>';}
  print('<style>del {text-decoration:line-through;color:#EA3C18;}ins {text-decoration:underline;color:#17922A;}.data{border:1px solid black;padding:5px;}</style>');
  print('<div style="font-size:80%;max-width:800px;margin:0 auto;">'.$heading.$diffs.'</div>');
}

function getsopstimer($sts){
  global $sopswhere, $timezone, $strlockeduntil,$timersactive;
  $currenttime = gmdate("Y-m-d H:i:s", time());
  $currentobjtime = new DateTime($currenttime);

  $row = rs('select ifnull(lockeduntil, UTC_TIMESTAMP) from verse '.$sopswhere);
  $lockeduntil = new DateTime($row[0]);
  $strlockeduntil = $lockeduntil->format('Y-m-d H:i:s');
  $datprint = convertTZ($strlockeduntil, $timezone);

  $interval = $currentobjtime->diff($lockeduntil);
  $dattime = substr('0'.$interval->i, -2).':'.substr('0'.$interval->s, -2);

  if($currentobjtime>=$lockeduntil){
    $qry = dbquery('update verse set edituserid = 0, editsession = null, lockeduntil = null '.$sopswhere);
    $expired=1;
    $dattime = '00:00';
  }else $expired=0;

  switch($sts){
  case 2: // locked by other user
  case 3: // same user did not properly close
      $lockstr = 'Locked until:';
      $timersactive=1;
      break;
  case 3: // same user did not properly close
      $lockstr = 'Current session '.(($expired==0)?'will expire':'expired').' at:';
      $timersactive=1;
      break;
  case 4: // session mismatch
      $lockstr = 'Current session will expire at:';
      $timersactive=1;
      break;
  case 9: // unable to save
      return '';
      break;
  default:
      $lockstr = 'I\'m lost';
  }
  return $lockstr.' <span id="timeuntil">'.$datprint.'</span><br />Remaining time: <span id="expires">'.$dattime.'</span>';
}

function convertTZ($date, $userTimeZone = 'America/New_York'){
  //$format = 'n/j/Y g:i:s A';
  $format = 'g:i:s A';
  $serverTimeZone = 'UTC';
  try {
    $dateTime = new DateTime($date ?? '', new DateTimeZone($serverTimeZone));
    $dateTime->setTimezone(new DateTimeZone($userTimeZone));
    return $dateTime->format($format);
  } catch (Exception $e) {
    return '';
  }
}
?>
<script>
  var toffset = 0;  // used for TOC
  var tz_offset = new Date().getTimezoneOffset();
  tz_offset = ((tz_offset==0)?0:-tz_offset);

  var timersactive = <?=$timersactive?>;
  var debug = <?=$debug?>;

  function tryresave(f){
    f.sopsresav.value=1;
    f.submit();
  }

  if(timersactive==1){

    var timelockeduntil = parseDate("<?=$strlockeduntil?>" + " GMT");

    var countdown = setInterval(function(){
      var now = new Date().getTime();
      // Find the millisec between now and the count down date
      var millisec = timelockeduntil - now;

      // Time calculations for days, hours, minutes and seconds
      var days = Math.floor(millisec / (1000 * 60 * 60 * 24));
      var hours = Math.floor((millisec % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((millisec % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((millisec % (1000 * 60)) / 1000);

      $('expires').style.color = ((millisec<20000)?'green':'red')
      if(hours==0)
        $('expires').innerHTML = ('0'+ minutes).slice(-2) + ":" + ('0'+seconds).slice(-2);
      else
        $('expires').innerHTML = hours + ":" + ('0'+ minutes).slice(-2) + ":" + ('0'+seconds).slice(-2);

      if(debug==1) $('debugx').innerHTML = 'timelockeduntil: '+timelockeduntil+'<br />now:'+now+'<br />millisec: '+millisec;

      if(millisec<=0){
        clearInterval(countdown);
        try{clearTimeout(sopsupdatetimer);}catch(e){}
        $('expires').innerHTML = '00:00';
      }
    }, 1000);

    sopsupdatetimer= setTimeout('sopsupdate(5);', 10000); // 10 seconds

    function sopsupdate(task){
        var qry='<?=$loc?>';
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange=function(){
          if (xmlhttp.readyState==4 && xmlhttp.status==200){
            var ret = JSON.parse(xmlhttp.responseText);
            switch(ret.sopstask){
            case 'inquire':
              $('timeuntil').innerHTML = ret.displayexpires;
              timelockeduntil = parseDate(ret.expires + " GMT");
              break;
            default:

            }
          }
        }
        //alert('/jsonmyrevtasks.php?task=sops'+task+'&ref='+qry);
        xmlhttp.open('GET', '/jsonmyrevtasks.php?task=sopstask'+task+'&ref='+qry,true);
        xmlhttp.send();
      sopsupdatetimer= setTimeout('sopsupdate(5);', 10000); // 10 seconds
    }

    function parseDate(date) {
      const parsed = Date.parse(date);
      if(!isNaN(parsed)) {return parsed;}
      return Date.parse(date.replace(/-/g, '/').replace(/[a-z]+/gi, ' '))+60000*tz_offset;
    }

  }

</script>
  <script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
  <script>
    findvers.startNodeId = 'body'; // start with body to catch refs in vs footnotes
    findvers.remoteURL = '<?=$jsonurl?>';
    findvers.navigat = false;
    addLoadEvent(findvers.scan);
  </script>


