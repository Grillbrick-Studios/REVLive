<?
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";

$navmitm = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['navmitm']))?$_REQUEST['navmitm']:-1));
$navtest = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['navtest']))?$_REQUEST['navtest']:-1));
$navbook = (int) preg_replace('/\D/', '', ((isset($_REQUEST['navbook']))?$_REQUEST['navbook']:0));
$navchap = (int) preg_replace('/\D/', '', ((isset($_REQUEST['navchap']))?$_REQUEST['navchap']:0));
$notify  = ((isset($_REQUEST['notification']))?$_REQUEST['notification']:'no notification');


$edtidx=0;
$titlfont=(($screenwidth<580)?'91':'100');

// for debugging
$dbg=0;
if($dbg==1){
  $navmitm=1;
  $navtest=1;
  $navbook=40;
  $navchap=1;
}
$showedit = (($edit==1)?"inline-block":"none");

$imgstyl  = 'height:32px;margin:4px 3px;vertical-align:middle;';
$img2styl = 'height:24px;margin:4px;vertical-align:middle;';
$img3styl = 'height:24px;margin:0;vertical-align:top;';

$cncllink = '<span style="display:inline-block;float:right;"><a onclick="popbiblenav()" title="close"><img src="/i/REV_close'.$colors[0].'.png" style="'.$imgstyl.'" /></a></span>';
$helplink = '<a onclick="location.href=\'/help\'" title="help"><img src="/i/REV_help'.$colors[0].'.png" style="'.$imgstyl.'" /></a>';
$backimg  = '<img src="/i/REV_back'.$colors[0].'.png" style="'.$imgstyl.'" />';
$histlink = '<a onclick="dobiblenav(\'navmitm=5&amp;curpage='.$page.'\');" title="history"><img src="/i/mnu_history'.$colors[0].'.png" alt="history" style="'.$imgstyl.'" /></a>';
// I forget why the third parm is 1 when myrevid>0
//$booklink = '<a onclick="dobiblenav(\'navmitm=9\');setTimeout(\'reloadBookmarks('.$page.',0,'.(($myrevid>0)?1:0).')\', 400);" title="bookmarks"><img src="/i/mnu_bookmarks'.$colors[0].'.png" alt="bookmarks" style="'.$imgstyl.'" /></a>';
$booklink = '<a onclick="dobiblenav(\'navmitm=9\');setTimeout(\'reloadBookmarks('.$page.',0,'.(($myrevid>0)?0:0).')\', 400);" title="bookmarks"><img src="/i/mnu_bookmarks'.$colors[0].'.png" alt="bookmarks" style="'.$imgstyl.'" /></a>';
$preflink = '<a onclick="dobiblenav(\'navmitm=8&amp;qs='.$qs.'\');return false;" title="quick preferences"><img src="/i/mnu_preferences'.$colors[0].'.png" alt="prefs" style="'.$imgstyl.'margin:0 3px" /></a>';
$focslink = (($ismobile)?'$(\'srchtext\').focus();':'$(\'srchtext\').focus();');
$myrevnav = '/myrev/'.$mitm.'/'.$test.'/'.$book.'/'.$chap.'/'.$vers;

$ret = '<span id="bnspan" style="display:inline-block;width:100%;height:auto">';

if(1==2){
  $ret.= '<span style="display:inline-block;color:#aaa;font-size:60%;line-height:1.2em;">';
  $ret.= 'page: '.$page.'<br />';
  $ret.= 'navmitm: '.$navmitm.'<br />';
  $ret.= 'navtest: '.$navtest.'<br />';
  $ret.= 'navbook: '.$navbook.'<br />';
  $ret.= 'navchap: '.$navchap.'<br />';
  $ret.= '</span>';
}

$ret.= '<table class="navmenu">';


switch($navmitm){
  case -1: // no choice made yet
    $ret.= '<tr><td>'.$helplink.$preflink.$booklink.$histlink.$cncllink.'</td></tr>';
    if($userid>0 && $page!=1 && ($canedit==1 || $appxedit==1)){
      $navqs = '\'navmitm='.$navmitm.'&navtest='.$navtest.'&navbook='.$navbook.'\'';
      $ret.= '<tr><td style="color:'.$colors[4].';padding-left:3px;">';
      $ret.= '<small><em><label for="x'.$edtidx.'">Edit:</label> </em><input type="checkbox" name="x'.$edtidx.'" id="x'.$edtidx++.'" class="cbx" value="1" onclick="showedit(this.checked);dobiblenav('.$navqs.');"'.fixchk($edit).' /></small>';
      $ret.= '</td></tr>';
    }
    $ret.= '<tr><td><a class="amenu" onclick="dobiblenav(\'navmitm=1\')">Bible</a></td></tr>';
    $ret.= '<tr><td><a class="amenu" onclick="dobiblenav(\'navmitm=2\')">Commentary</a></td></tr>';
    $ret.= '<tr><td style="white-space:nowrap;">'.(($userid==1 && $edit==1)?'<a class="toplink" href="/exportsql.php?test=3&amp;book=0&amp;chap=0&amp;vers=0">sql</a>&nbsp;':'').(($userid>0 && $appxedit==1)?editlink('book'.$edtidx++,$showedit,6,11,3,0,-1,0).' ':'').'<a class="amenu" onclick="dobiblenav(\'navmitm=3&navtest=3\')">Appendices</a></td></tr>';
    if($revws==1 || $showdevitems==1)
      $ret.= '<tr><td style="white-space:nowrap;">'.(($userid==1 && $edit==1)?'<a class="toplink" href="/exportsql.php?test=4&amp;book=0&amp;chap=0&amp;vers=0">sql</a>&nbsp;':'').(($userid>0 && $appxedit==1)?editlink('book'.$edtidx++,$showedit,10,11,4,0,-1,0).' ':'').'<a class="amenu" onclick="dobiblenav(\'navmitm=4&navtest=4\')">Word Studies</a></td></tr>';
    $ret.= '<tr><td style="white-space:nowrap;">'.(($userid==1 && $edit==1)?'<a class="toplink" href="/exportsql.php?test=2&amp;book=0&amp;chap=0&amp;vers=0">sql</a>&nbsp;':'').(($userid>0 && $appxedit==1)?editlink('book'.$edtidx++,$showedit,1,11,2,0,-1,0).' ':'').'<a class="amenu" onclick="dobiblenav(\'navmitm=6&navtest=2\')">Information</a></td></tr>';
    $ret.= '<tr><td style="white-space:nowrap;"><a class="amenu" onclick="location.href=\''.$myrevnav.'\'">MyREV</a></td></tr>';
    break;
  case 1: // Bible
  case 2: // Commentary
  case 3: // appendices
  case 4: // wordstudies
  case 6: // information
    $rswexportstr='';
    if($navmitm==1 && $userid==1 && $edit==1){
      $rswexportstr = ' <a class="toplink" href="/exportsql.php?test=0&amp;book=0&amp;chap=0&amp;vers=0">OT sql</a>&nbsp;<small>|</small>&nbsp;';
      $rswexportstr.= '<a class="toplink" href="/exportsql.php?test=1&amp;book=0&amp;chap=0&amp;vers=0">NT sql</a>';
    }
    $titlestr=' <span style="font-size:'.$titlfont.'%;color:'.$colors[4].';font-weight:bold;">'.(($navmitm==1)?'Bible'.$rswexportstr:(($navmitm==2)?'Commentary':(($navmitm==3)?'Appendices':(($navmitm==4)?'Word Studies':'Information')))).'</span>';
    if($navbook==0){ // no book
      $ret.= '<tr><td><a onclick="dobiblenav(\'navmitm=-1\');'.$focslink.'" title="go back">'.$backimg.'</a>'.$preflink.$booklink.$titlestr.$cncllink.'</td></tr>';
      if($navmitm>2 || $myrevid>0){
        $ret.= '<tr><td style="color:'.$colors[4].';padding-left:3px;">';
        $wscat   = (int) preg_replace('/\D/', '', ((isset($_REQUEST['wscat']))?$_REQUEST['wscat']:((isset($_COOKIE['rev_wscat']))?$_COOKIE['rev_wscat']:0)));
        if($navmitm!=2 && $userid>0 && $page!=1 && ($canedit==1 || $appxedit==1)){
          $navqs = '\'navmitm='.$navmitm.'&navtest='.$navtest.'&navbook='.$navbook.'&wscat='.$wscat.'\'';
          $ret.= '<small><em><label for="x'.$edtidx.'">Edit:</label> </em><input type="checkbox" name="x'.$edtidx.'" id="x'.$edtidx++.'" class="cbx" value="1" onclick="showedit(this.checked);dobiblenav('.$navqs.');"'.fixchk($edit).' /></small> '.(($navmitm<3)?'| ':'');
        }
        if($linkcommentary==1){
          if($myrevid>0 && $navmitm==1 && ($page==0 || $page==5) && $test<2){
            $ret.= '<small><em><label for="mrcmt">My Notes:</label> </em><input type="checkbox" name="mrcmt" id="mrcmt" class="cbx" value="1" onclick="setmrcomments(((this.checked)?1:0));showednotes(\'mrv\',this.checked,'.$myrevid.','.$test.','.$book.','.$chap.','.(($page==0)?-1:$vers).');'.(($page==1)?'':'myrevhidePopup();').'"'.fixchk($viewmrcomments).' /></small>';
          }
          if($peernotes>0 && ($navmitm==1 || $navmitm>2) && ($page==0 || $page==1 || $page==5 || $page==8 || $page==14) && $test!=2){
            $ret.= (($page!=1)?' | ':'').'<small><em><label for="peercmt">Rv Notes:</label> </em><input type="checkbox" name="peercmt" id="peercmt" class="cbx" value="1" onclick="setpeernotes(((this.checked)?1:0));showednotes(\'peer\',this.checked,'.$myrevid.','.$test.','.$book.','.$chap.','.(($page==0)?-1:$vers).');'.(($page==1)?'':'myrevhidePopup();').'"'.fixchk($viewpeernotes).' /></small>';
          }
          if($editorcomments==1 && ($navmitm==1 || $navmitm>2) && ($page==0 || $page==1 || $page==5 || $page==8 || $page==14) && $test!=2){
            $ret.= (($page!=1)?' | ':'').'<small><em><label for="edcmt">Ed Notes:</label> </em><input type="checkbox" name="edcmt" id="edcmt" class="cbx" value="1" onclick="setedcomments(((this.checked)?1:0));showednotes(\'edn\',this.checked,'.$myrevid.','.$test.','.$book.','.$chap.','.(($page==0)?-1:$vers).');'.(($page==1)?'':'myrevhidePopup();').'"'.fixchk($viewedcomments).' /></small>';
          }
        }else
          $ret.= '<a onclick="setlinkcommentary(1);location.reload();"><small>Turn on notes</small></a>';

        $ret.= '</td></tr>';
      }
      if($navmitm==4){ //word study
        $ret.= '<tr><td style="color:'.$colors[4].';"><small>&nbsp;Filter by: <select name="wscat" id="wscat" onchange="dobiblenav(\'navmitm='.$navmitm.'&navtest='.$navtest.'&navbook='.$navbook.'&wscat=\'+this[this.selectedIndex].value+\'\')">';
        $row = rs('select count(*) from book where testament = 4 and active in('.(($edit==1)?'0,1':'1').')');
        $ret.= '<option value="0"'.fixsel($wscat, 0).'>All ('.$row[0].')</option>';
        $sql = 'select wscatid, wscat, (select count(*) from book b where (b.wscatid = wsc.wscatid and b.active in('.(($edit==1)?'0,1':'1').'))) cnt
                from wscats wsc
                where wsc.wscatid in (select wscatid from book where testament = 4 and active in('.(($edit==1)?'0,1':'1').'))
                order by sqn ';
        $cats = dbquery($sql);
        while($row = mysqli_fetch_array($cats)){
          $ret.= '<option value="'.$row[0].'"'.fixsel($wscat, $row[0]).'>'.$row[1].' ('.$row[2].')</option>';
        }
        $ret.= '</select></small></td></tr>';
      }
      if($navmitm>2) // appendices, info, wordstudies
        $sql = 'select testament, book, ifnull(tagline, title) title, active from book where testament='.$navtest.' and (active=1'.(($userid>0 && $edit==1)?' or active=0':'').')'.(($navmitm==4 && $wscat!=0)?' and wscatid='.$wscat.'':'').' order by '.(($navmitm==4)?'ifnull(tagline, title)':'sqn');
      else{
        $sql = 'select testament, book, title, bwabbr, chapters from book where testament in (0,1) order by testament, book';
      }
      $bks = dbquery($sql);
      $ni=0;
      if($navmitm<3) $ret.= '<tr><td>';
      while($row = mysqli_fetch_array($bks)){
        if($navmitm<3){ // bible books

          $babbr = $row['bwabbr'];
          $btitl = $row['title'];
          if($row['book']==22) {$babbr='Son';$btitl='Song of Songs/Solomon';}
          if($row['book']==40) $ret.= (($ismobile && $screenwidth<420)?'<hr style="margin:6px 0;padding:0;">':'<br /><br />');
          $lnk ='<div class="bookstack'.(($ismobile)?' bookstackmob':'').(($row['book']==$book)?' bghilite':'').'">';
          if($row['chapters']>1) $lnk.='<a onclick="dobiblenav(\'navmitm='.$navmitm.'&navtest='.$row['testament'].'&navbook='.$row['book'].'\')"';
          else{
            if(!$navonchap) $lnk.='<a href="/'.(($navmitm==2)?'comm/':'').$babbr.'/'.$chap.'" ';
            else $lnk.='<a onclick="dobiblenav(\'navmitm='.$navmitm.'&navtest='.$row['testament'].'&navbook='.$row['book'].'&navchap=1\')" ';
          }
          $lnk.='class="amenu'.(($row['testament']==$test && $row['book']==$book)?' hilite':'').'" ';
          $lnk.=(($row['testament']==$test && $row['book']==$book)?' id="mybk"':'');
          $lnk.='style="font-size:'.(($ismobile)?'1em':'.9em').';" ';
          $lnk.='title="'.(($row['chapters']==1)?'Go to ':'').$btitl.'">'.$babbr.'</a>';
          $lnk.='</div>';
          $ret.= $lnk;
          $ni++;
        }else{ // info, appxs, wordstudies
          $ret.= '<tr><td'.(($row['testament']==$test && $row['book']==$book)?' class="bghilite"':'').'>';
          if($userid>0 && $appxedit==1) $ret.= editlink('book'.$edtidx++,$showedit,99,8,$navtest,$row['book'],1,1).' ';
          $lnk ='<a class="amenu'.(($row['testament']==$test && $row['book']==$book)?' hilite':'').'" href="/'.(($navtest==2)?'info':(($navtest==3)?'appx':'word')).'/';
          $lnk.=(($navtest==4)?str_replace('/', '_', str_replace(' ', '_', $row['title'])):$row['book']).'"';
          $lnk.=(($row['testament']==$test && $row['book']==$book)?' id="mybk"':'');
          $lnk.='>'.$row['title'].(($userid>0 && $edit==1 && $row['active']==0)?notifynotpublished:'');
          $lnk.='</a>';
          $ret.= $lnk.'</td></tr>';
        }
        $ni++;
      }
      if($navmitm<3) $ret.= '</td><tr>';
      if($ni==0)
        $ret.= '<tr><td style="font-size:70%;color:red;">&nbsp;Sorry, there are no published '.(($navmitm<3)?' books':(($navmitm==3)?'appendices':(($navmitm==4)?'word studies':'items'))).'</td></tr>';
      if($navmitm==6){
        $ret.= '<tr><td'.(($book==0&&$navchap==0)?' class="bghilite"':'').'><a class="amenu'.(($book==0&&$navchap==0)?' hilite"':'').'" href="/abbreviations">Abbreviations</a></td></tr>';
        $ret.= '<tr><td'.(($book==0&&$navchap==1)?' class="bghilite"':'').'><a class="amenu'.(($book==0&&$navchap==1)?' hilite"':'').'" href="/bibliography">Bibliography</a></td></tr>';
      }
      if($navmitm!=3) $ret.= '<tr><td style="white-space:nowrap;"><a class="amenu" onclick="dobiblenav(\'navmitm=3&navtest=3\')"><small>Appendices</small></a></td></tr>';
      if(($revws==1 || $showdevitems==1) && $navmitm!=4) $ret.= '<tr><td style="white-space:nowrap;"><a class="amenu" onclick="dobiblenav(\'navmitm=4&navtest=4\')"><small>Word Studies</small></a></td></tr>';
      if($navmitm!=6) $ret.= '<tr><td style="white-space:nowrap;"><a class="amenu" onclick="dobiblenav(\'navmitm=6&navtest=2\')"><small>Information</small></a></td></tr>';
      $ret.= '<tr><td style="white-space:nowrap;"><a class="amenu" onclick="location.href=\''.$myrevnav.'\'"><small>MyREV</small></a></td></tr>';

    }else{ // we have a book
      if($navchap==0){
        $sql = 'select title, abbr, chapters, ifnull(commentary, \'-\') commentary from book where testament='.$navtest.' and book='.$navbook.' ';
        $row = rs($sql);
        $ret.= '<tr><td><a onclick="dobiblenav(\'navmitm='.$navmitm.'&navtest='.$navtest.'\')" title="go back">'.$backimg.'</a>'.$titlestr.$cncllink.'</td></tr>';
        $comlink='';
        if($row['commentary']!='-')
          $comlink='<a href="/book/'.str_replace(' ', '_', $row['title']).'" title="View book commentary"><img src="/i/commentary'.$colors[0].'.png" alt="view" style="width:1.0em;" /></a>&nbsp;';
        $ret.= '<tr><td style="white-space:nowrap;padding-left:3px;">'.(($userid==1 && $edit==1)?'<a class="toplink" href="/exportsql.php?test='.$navtest.'&amp;book='.$navbook.'&amp;chap=0&amp;vers=0">sql</a>&nbsp;':'').(($userid>0 && $canedit==1)?editlink('book'.$edtidx++,$showedit,99,6,$navtest,$navbook,-1,0).' ':'').$comlink;
        if($navmitm==1)
          $ret.= '<a class="amenu"'.(($userid>0)?' style="width:80%;"':'').' href="/'.str_replace(' ', '', $row['abbr']).'/all"><strong>'.$row['title'].'</strong></a>';
        else
          $ret.= '<strong>'.$row['title'].'</strong>';
        $ret.= '</td></tr>';
        //$ret.= '<tr><td><small>&nbsp;Choose a '.(($navbook!=19)?'chapter':'Psalm').':</small><span style="display:inline-block;float:right;margin-right:4px;"><small>'.(($screenwidth>650)?'Choose verse':'Verse').'? </small><input type="image" src="/i/vers_'.(($navonchap)?'ON':'OFF').$colors[0].'.png" style="'.$img3styl.'" onclick="setnavonchap('.(1-$navonchap).');dobiblenav(\'navmitm='.$navmitm.'&navtest='.$navtest.'&navbook='.$navbook.'\');" /></span></td></tr>';
        $ret.= '<tr><td><small>&nbsp;'.(($navbook!=19)?'Chapter':'Psalm').':</small><span style="display:inline-block;float:right;margin-right:4px;"><small>'.(($screenwidth>650)?'Choose verse':'Verse').'? </small><input type="image" src="/i/vers_'.(($navonchap)?'ON':'OFF').$colors[0].'.png" style="'.$img3styl.'" onclick="setnavonchap('.(1-$navonchap).');dobiblenav(\'navmitm='.$navmitm.'&navtest='.$navtest.'&navbook='.$navbook.'\');" /></span></td></tr>';
        $ret.= '<tr><td>';
        $abbr = str_replace(' ', '', $row['abbr']);
        for($ni=1;$ni<=$row['chapters'];$ni++){
          $navstr=(($navonchap)?' onclick="dobiblenav(\'navmitm='.$navmitm.'&navtest='.$navtest.'&navbook='.$navbook.'&navchap='.$ni.'\')"':' href="/'.(($navmitm==2)?'comm/':'').$abbr.'/'.$ni.'"');
          $ret.= '<div class="chapstack'.(($navbook==$book && $ni==$chap)?' bghilite':'').'"><a class="amenu'.(($navbook==$book && $ni==$chap)?' hilite':'').'" style="padding:4px 0 4px 4px;"'.$navstr.'>'.$ni.'&nbsp;</a></div>';
        }
        $ret.= '</td></tr>';
      }else{
        $sql = 'select title, abbr, chapters from book where testament='.$navtest.' and book='.$navbook.' ';
        $row = rs($sql);
        $ret.= '<tr><td><a onclick="dobiblenav(\'navmitm='.$navmitm.'&navtest='.$navtest.'&navbook='.(($row['chapters']==1)?'0':$navbook).'\')" title="go back">'.$backimg.'</a>'.$titlestr.$cncllink.'</td></tr>';
        $ret.= '<tr><td><a class="amenu" href="/'.str_replace(' ', '', $row['abbr']).'/'.$navchap.'"><strong>'.$row['title'].' '.$navchap.'</strong></a></td></tr>';
        //$ret.= '<tr><td><small>&nbsp;Choose a verse:</small><br />';
        $ret.= '<tr><td><small>&nbsp;Verse:</small><br />';
        $abbr = str_replace(' ', '', $row['abbr']);
        $sql = 'select count(verse) cnt from verse where testament='.$navtest.' and book='.$navbook.' and chapter='.$navchap.' ';
        $row = rs($sql);
        for($ni=1;$ni<=$row['cnt'];$ni++){
          $ret.= '<div class="chapstack"><a class="amenu" style="padding:4px 0 4px 4px;" href="/'.(($navmitm==2)?'comm/':'').$abbr.'/'.$navchap.'/nav'.$ni.'">'.$ni.'&nbsp;</a></div>';
        }
        $ret.= '</td></tr>';
      }
    }
    break;
  case 5: // history
    $titlestr=' <span style="font-size:'.$titlfont.'%;color:'.$colors[4].';">History</span>';
    $ret.= '<tr><td><a onclick="dobiblenav(\'navmitm=-1\');'.$focslink.'" title="go back">'.$backimg.'</a>'.' '.$preflink.' '.$booklink.$titlestr.$cncllink.'</td></tr>';
    //$history is a global, loaded in checkmyrevlogin
    if($history=='')
      $ret.= '<tr><td>-none-</td></tr>';
    else{
      $comstr = (($screenwidth<600)?'Comm':'Commentary');
      if(right($history, 1)==='~') $history = substr($history, 0, -1);
      $arhistory = explode('~', $history);
      $cpg = (int) preg_replace('/\D/', '', ((isset($_REQUEST['curpage']))?$_REQUEST['curpage']:0));
      $startpos = (($cpg==17 || $cpg==9 || $cpg==3)?0:1);
      if(sizeof($arhistory)>$startpos){
        for($ni=$startpos;$ni<count($arhistory);$ni++){
          $artmp = explode(':', $arhistory[$ni]);
          $p=$artmp[0];$t=$artmp[1];$b=$artmp[2];$c=$artmp[3];$v=$artmp[4];$h=((isset($artmp[5]))?$artmp[5]:0);$cas=((isset($artmp[6]))?$artmp[6]:0);
          $href='';
          $link='unknown';
          switch($p){
          case 0:  //viewbible
            $bnam = getbooktitle($t, $b, 1);
            if($h==0){
              $href = '/'.$bnam.(($c>0)?'/'.$c.(($v>0)?'/nav'.$v:''):'/all');
              $link = 'Bible: '.$bnam.' '.(($c>0)?$c.(($v>0)?':'.$v:''):'');
            }else{
              $href = '/'.$bnam.'/'.$c.'/head'.$v;
              $sql = 'select heading from outline where testament = '.$t.' and book = '.$b.' and chapter = '.$c.' and verse = '.$v.' and link=1 ';
              $row = rs($sql);
              if($row){
                $hed = str_replace('~', '', $row[0]);
                $hed = str_replace('[br]', ' ', $hed);
              }else $hed = 'unknown';
              $link = 'Outline: '.$bnam.' '.$c.':'.$v.', <em>'.$hed.'</em>';
            }
            break;
          case 3:  //srch
            $sstxt = str_replace('^', ':', $v);
            $href = '/srch/?srchtest='.$t.'&srchwhat='.$b.'&srchhow='.$c.'&srchtxt='.$sstxt.'&inckjv='.$h.'&srchcase='.$cas;
            $link = 'Search'.(($b==1)?'':' Cmtry').': &ldquo;'.str_replace('_', ' ', $sstxt).'&rdquo;';
            break;
          case 4:  //viewcomm
            $bnam = getbooktitle($t, $b, 1);
            $href = '/comm/'.$bnam.(($c>0)?'/'.$c.(($v>0)?'/nav'.$v:''):'/all');
            $link = 'Browse '.$comstr.': '.$bnam.' '.(($c>0)?$c.(($v>0)?':'.$v:''):'');
            break;
          case 5:  //viewverscomm
            $bnam = getbooktitle($t, $b, 1);
            $href = '/'.$bnam.'/'.$c.'/'.$v.'/tc';   // append flag for $showtochapter button
            $link = $comstr.' on: '.$bnam.' '.(($c>0)?$c.(($v>0)?':'.$v:''):'');
            break;
          case 9:  //prefs
            $href = '/pref';
            $link = 'Preferences';
            break;
          case 10: //viewbookcomm
            $bnam = getbooktitle($t, $b, 1);
            $href = '/book/'.$bnam;
            $link = 'Book '.(($screenwidth<600)?'Intro':'Introduction').': '.$bnam;
            break;
          case 14: //viewappxintro
            $sql = 'select ifnull(tagline,title), active from book where testament = '.$t.' and book = '.$b.' ';
            $row = rs($sql);
            if($row['active']==1 || ($userid>0 && $edit==1)){
              $href= '/'.(($t==2)?'info':(($t==3)?'appx':'word')).'/'.(($t==4)?str_replace(' ', '_', str_replace('/','_',$row[0])):$b);
              $link = (($t==2)?'Info: ':(($t==3)?'':'Wordstudy: ')).(($row)?$row[0]:'unknown').(($userid>0 && $edit==1 && $row['active']==0)?notifynotpublished:'');
            }else $href = 'unknown';
            break;
          case 20: //whatsnew
            $href = '/wnew';
            $link = 'What&rsquo;s New';
            break;
          case 24: //outline
            $bnam = getbooktitle($t, $b, 1);
            $href = '/outline/'.$bnam;
            $link = 'Outline: '.$bnam;
            break;
          case 25: //revblog
            $href = '/blog';
            $link = 'REV Blog List';
            break;
          case 26: //viewrevblog
            $href = '/blog/'.$b;
            $sql = 'select blogtitle from revblog where blogid = '.$b.' ';
            $row = rs($sql);
            $link = 'Blog: '.(($row)?$row[0]:'unknown');
            break;
          case 29: //donate
            $href = '/dont';
            $link = 'Donate';
            break;
          case 34: //chronology
            $href = '/chronology/'.$t;
            $link = 'Chronology '.(($t<0)?abs($t).' BC':abs($t).' AD');
            break;
          case 30: //export
            $href = '/expt';
            $link = 'Export the REV';
            break;
          case 33: //topic
            $sql = 'select ifnull(topic, \'unknown\') from topic where topicid = '.$b.' ';
            $row = rs($sql);
            if($row){
              $topic = $row[0];
              $href = '/topic/'.$b.'/'.$topic;
              $link = 'Topic: '.$topic;
            }else{
              $href = '/topic';
              $link = 'Topic List';
            }
            break;
          case 36: //resources
            $href = '/reso';
            $link = 'Resources';
            break;
          case 41: //MyREV
            $href = $myrevnav;
            $link = 'MyREV';
            break;
          case 44: //bib and abbr
            if($c==1){
              $href = '/bibliography';
              $link = 'Info: Bibliography';
            }else{
              $href = '/abbreviations';
              $link = 'Info: Abbreviations';
            }
            break;
          }
          if(strpos($href, 'unknown')===FALSE && strpos($link, 'unknown')===FALSE){
            $href = str_replace(' ', '', $href);
            $href = str_replace('.', '', $href);
            $ret.= '<tr style="padding:0;margin:0;"><td style="padding:0;margin:0;"><a class="amenu" href="'.$href.'">'.$link.'</a></td></tr>';
          }
        }
      } else $ret.= '<tr><td>-none-</td></tr>';
    }
    break;
  case 8: // quickprefs
    $imgstyl = 'height:'.(($ismobile)?'32':'32').'px;margin:0 5px;vertical-align:middle;';
    $ret.= '<tr><td><a onclick="dobiblenav(\'navmitm=-1\');'.$focslink.'" title="go back">'.$backimg.'</a>'.$booklink.$histlink.$cncllink.'</td></tr>';
    $ret.= '<tr><td><table class="prftable">';
    $ret.= '<tr><td colspan="2"><b>Quick Settings</b><span style="display:inline-block;float:right;"><a href="/preferences/'.str_replace('preferences/','',$qs).'"><span style="font-size:14px;">(All Settings)</span></a></span></td></tr>';

    if($userid>0 && $canedit==1){
      $ret.= '<tr><td>Edit:</td><td><input type="checkbox" name="x'.$edtidx.'" id="x'.$edtidx++.'" class="cbx" value="1" onclick="showedit(this.checked);sizenavto(0);"'.fixchk($edit).' /></td></tr>';
    }

    if($page==0){
      $ret.= '<tr><td style="vertical-align:top">Bible Text Mode:</td><td><small>';
      $ret.= '<input type="radio" name="versebreak" id="vb1" value="1"'.fixrad($versebreak==1).' onclick="setversebreak(this.value);location.reload();"> <label for="vb1">Verse Break</label><br />';
      $ret.= '<input type="radio" name="versebreak" id="vb0" value="0"'.fixrad($versebreak==0).' onclick="setversebreak(this.value);location.reload();"> <label for="vb0">Paragraph</label><br />';
      $ret.= '<input type="radio" name="versebreak" id="vb2" value="2"'.fixrad($versebreak==2).' onclick="setversebreak(this.value);location.reload();"> <label for="vb2">Reading</label>';
      $ret.= '</small></td></tr>';
    }

    if($page==0 && $diffbiblefont==1){
      $inbible = 'bible';
      $fontlabel = 'Bible ';
      $thefont = $biblefontfamily;
    }else{
      $inbible = '';
      $fontlabel = 'System ';
      $thefont = $fontfamily;
    }
    $ret.= '<tr><td>'.$fontlabel.'Font:</td><td>';
    $ret.= '<select name="fontfamily" onchange="set'.$inbible.'fontfamily(this.value, this.selectedIndex);">
            <optgroup label="Serifed Fonts">
              <option value="merriweather"'.fixsel($thefont, "merriweather").'>Merriweather</option>
              <option value="times new roman"'.fixsel($thefont, "times new roman").'>Times New Roman</option>
              <option value="caladea"'.fixsel($thefont, "caladea").'>Caladea</option>
              <option value="ibm plex serif"'.fixsel($thefont, "ibm plex serif").'>IBM Plex Serif</option>
            </optgroup>
            <optgroup label="Non-Serifed Fonts">
              <option value="arial"'.fixsel($thefont, "arial").'>Arial</option>
              <option value="roboto"'.fixsel($thefont, "roboto").'>Roboto</option>
              <option value="montserrat"'.fixsel($thefont, "montserrat").'>Montserrat</option>
              <option value="balsamiq sans"'.fixsel($thefont, "balsamiq sans").'>Balsamiq Sans</option>
            </optgroup>
          </select>';
    $ret.= '</td></tr>';

    $ret.= '<tr><td>Size:</td><td>';
    $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/fontbigger.png" onclick="return set'.$inbible.'textsize(1);" title="increase" style="'.$imgstyl.'" />';
    $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/fontsmaller.png" onclick="return set'.$inbible.'textsize(-1);" title="decrease" style="'.$imgstyl.'" />';
    $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/reset2.png" onclick="return set'.$inbible.'textsize(99);" title="reset" style="'.$imgstyl.'" />';
    $ret.= '</td></tr>';

    $ret.= '<tr><td>Spacing:</td><td>';
    $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/spacing_inc.png" onclick="return set'.$inbible.'lineheight(1);" title="increase" style="'.$imgstyl.'" />';
    $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/spacing_dec.png" onclick="return set'.$inbible.'lineheight(-1);" title="decrease" style="'.$imgstyl.'" />';
    $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/reset2.png" onclick="return set'.$inbible.'lineheight(99);" title="reset" style="'.$imgstyl.'" />';
    $ret.= '</td></tr>';

    if($page==0){
      $ret.= '<tr><td>Bible Columns:</td><td>';
      $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/columns_less.png" onclick="return setcolumns(-1);" title="less columns" style="'.$imgstyl.'" />';
      $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/columns_more.png" onclick="return setcolumns(1);" title="more columns" style="'.$imgstyl.'" />';
      $ret.= '</td></tr>';
    }

    // Rob only, for now
    if(1==2 && $userid==1){
      $ret.= '<tr><td>Alignment:</td><td>';
      $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/align_left.png" onclick="return setparachoice2(\'a\',0);" title="align left" style="'.$imgstyl.'" />';
      $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/align_justify.png" onclick="return setparachoice2(\'a\',1);" title="justify" style="'.$imgstyl.'" />';
      $ret.= '</td></tr>';

      $ret.= '<tr><td>Indentation:</td><td>';
      $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/align_noindent.png" onclick="return setparachoice2(\'i\',0);" title="no indent" style="'.$imgstyl.'" />';
      $ret.= '<input type="image" name="x'.$edtidx++.'" src="/i/align_indent.png" onclick="return setparachoice2(\'i\',1);" title="indent" style="'.$imgstyl.'" />';
      $ret.= '</td></tr>';
    }

    $ret.= '<tr><td>Lexicon Site:</td><td>';
    $ret.= '<select name="lexicon" onchange="setlexicon(this.value);location.reload();">';
    $ret.= '  <option value="1"'.fixsel($lexicon, '1').'>BlueLetterBible.org</option>';
    $ret.= '  <option value="2"'.fixsel($lexicon, '2').'>BibleHub.com</option>';
    $ret.= '  <option value="3"'.fixsel($lexicon, '3').'>StudyLight.org</option>';
    $ret.= '</select></td></tr>';

    $ret.= '<tr><td>Theme:</td><td>';
    $ret.= '<input type="image" src="/i/mnu_theme_day'.$colors[0].'.png" style="'.$imgstyl.'" title="Day theme" onclick="setcolortheme2(0);location.reload();" />';
    $ret.= '<input type="image" src="/i/mnu_theme_sep'.$colors[0].'.png" style="'.$imgstyl.'" title="Sepia theme" onclick="setcolortheme2(2);location.reload();" />';
    $ret.= '<input type="image" src="/i/mnu_theme_drk'.$colors[0].'.png" style="'.$imgstyl.'" title="Dark theme" onclick="setcolortheme2(1);location.reload();" />';
    $ret.= '</td></tr>';

    if($page==0){
      $ret.= '<tr><td>Bible Text Only:</td><td><small>';
      // NOTE: due to name change, this switch is backwards! 0=yes, 1=no
      $ret.= '<input type="radio" name="linkcommentary" value="0"'.fixrad($linkcommentary==0).' onclick="setlinkcommentary(0);location.reload();" /> Yes&nbsp;&nbsp;&nbsp;';
      $ret.= '<input type="radio" name="linkcommentary" value="1"'.fixrad($linkcommentary==1).' onclick="setlinkcommentary(1);location.reload();" /> No';
      $ret.= '</small></td></tr>';
    }
      
    if($page==41 && $myrevid>0){
      $ret.= '<tr><td>MyREV PopUp:</td><td><small>';
      $ret.= '<input type="radio" id="myrevpref1" name="myrevclick" value="1"'.fixrad($myrevclick==1).' onclick="setmyrevclick(1);"> Yes&nbsp;&nbsp;&nbsp;';
      $ret.= '<input type="radio" id="myrevpref2" name="myrevclick" value="1"'.fixrad($myrevclick==0).' onclick="setmyrevclick(0);"> No';
      $ret.= '</small></td></tr>';
      $ret.= '<tr><td>MyREV Editor First:</td><td><small>';
      $ret.= '<input type="radio" id="myreved1" name="myreveditfirst" value="1"'.fixrad($myrevshoweditorfirst==1).' onclick="setmyrevshoweditorfirst(1);"> Yes&nbsp;&nbsp;&nbsp;';
      $ret.= '<input type="radio" id="myreved2" name="myreveditfirst" value="1"'.fixrad($myrevshoweditorfirst==0).' onclick="setmyrevshoweditorfirst(0);"> No';
      $ret.= '</small></td></tr>';
    }
    
    if($editorcomments==1 && ($page==0 || $page==5 || $page==14)){
      $ret.= '<tr><td>Show All Ednotes :</td><td><small>';
      $ret.= '<input type="radio" id="ednotesall1" name="ednotesshowall" value="1"'.fixrad($ednotesshowall==1).' onclick="setednotesshowall(1);location.reload();"> Yes&nbsp;&nbsp;&nbsp;';
      $ret.= '<input type="radio" id="ednotesall2" name="ednotesshowall" value="1"'.fixrad($ednotesshowall==0).' onclick="setednotesshowall(0);location.reload();"> No';
      $ret.= '</small></td></tr>';
    }
    if($peernotes>0 && ($page==0 || $page==5 || $page==14)){
      $ret.= '<tr><td>Show All Rev&rsquo;r notes :</td><td><small>';
      $ret.= '<input type="radio" id="peernotesall1" name="peernotesshowall" value="1"'.fixrad($peernotesshowall==1).' onclick="setpeernotesshowall(1);location.reload();"> Yes&nbsp;&nbsp;&nbsp;';
      $ret.= '<input type="radio" id="peernotesall2" name="peernotesshowall" value="1"'.fixrad($peernotesshowall==0).' onclick="setpeernotesshowall(0);location.reload();"> No';
      $ret.= '</small></td></tr>';
    }

    $ret.= '</table></td></tr>';
    break;
  case 9: // bookmarks
    $titlestr=' <span style="font-size:'.$titlfont.'%;color:'.$colors[4].';">Bookmarks</span>';
    $ret.= '<tr><td><a onclick="dobiblenav(\'navmitm=-1\');'.$focslink.'" title="go back">'.$backimg.'</a>'.$titlestr.$cncllink.'</td></tr>';
    $ret.= '<tr><td><div id="bmkcontent" style="overflow-y:auto;">one moment..</div></td></tr>';
    break;
  case 10: // notify
    $notify = str_replace('\'', '', $notify);
    $ret.= '<tr><td style="text-align:center;">'.$notify.'</td></tr>';
    break;
}
$ret.= '</table>';
$ret.= '<span style="display:block;text-align:center;font-family:merriweather;vertical-align:middle;font-size:.8em;color:#337efe;">REV <span style="color:#828282;">Bible</span></span>';
$ret.= '</span>';

if($navmitm!=1) logview(500,$navmitm,0,0,0,'');

print($ret);

mysqli_close($db);

?>

