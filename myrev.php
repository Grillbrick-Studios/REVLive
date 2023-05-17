<?php

if(!isset($page) || $myrevid==0){
  print('<script>location.href="/login";</script>');
  die();
}

$oper=((isset($_REQUEST['oper']))?$_REQUEST['oper']:'nope');

if($oper=='clearall' ){$qry = dbquery('delete from myrevdata where myrevid = '.$myrevid.' ');}
if($oper=='clearsort'){$qry = dbquery('update myrevdata set sqn = 99 where myrevid = '.$myrevid.' ');}

if($oper=='delchecked'){
  for($ni=0;$ni<$_POST['vcnt'];$ni++){
    if(isset($_POST['chk_'.$ni])){
      $vrs = $_POST['chk_'.$ni];
      $arv = explode('|', substr($vrs, 4));
      $sql = 'delete from myrevdata
              where myrevid = '.$arv[0].' and testament = '.$arv[1].' and book = '.$arv[2].' and chapter = '.$arv[3].' and verse = '.$arv[4].' ';
      //print($sql.'<br />');
      $del = dbquery($sql);
    }
  }
}

if($oper=='chngchecked'){
  $colr=((isset($_REQUEST['colorchangecolor']))?$_REQUEST['colorchangecolor']:-1);
  if($colr>-1){
    for($ni=0;$ni<$_POST['vcnt'];$ni++){
      if(isset($_POST['chk_'.$ni])){
        $vrs = $_POST['chk_'.$ni];
        $arv = explode('|', substr($vrs, 4));
        $swhere = 'where myrevid = '.$arv[0].' and testament = '.$arv[1].' and book = '.$arv[2].' and chapter = '.$arv[3].' and verse = '.$arv[4].' ';
        if($colr==0){
          $sql = 'select length(ifnull(myrevnotes, \'\')) from myrevdata '.$swhere;
          $row = rs($sql);
          if($row[0]==0){
            $sql = 'delete from myrevdata '.$swhere;
            $qry = dbquery($sql);
          }else{
            $sql = 'update myrevdata
                    set highlight = '.$colr.' '.$swhere;
            $qry = dbquery($sql);
          }
        }else{
          $sql = 'update myrevdata
                  set highlight = '.$colr.' '.$swhere;
          $qry = dbquery($sql);
        }
      }
    }
  }
}

$myrevcolr = ((isset($_REQUEST['myrevcolr']))?$_REQUEST['myrevcolr']:-1);
$notestruncatelen=500;

$filter=((isset($_REQUEST['filter']))?$_REQUEST['filter']:'');
$filter = trim(strip_tags($filter));
$filter = str_replace(';','',$filter);
//$filter = str_replace(':','',$filter);
$filter = str_replace('>','',$filter);
$filter = str_replace('<','',$filter);
$filter = str_replace('\'','',$filter);
$filter = str_replace('"','',$filter);

$filterstr = '';
if($filter!==''){
  if(left($filter, 1)=='='){
    $filter = substr($filter, 1);
    $filter = str_replace('=', '', $filter); // remove remaining, just in case
    $filterstr.= 'and (v.versetext like \'%'.$filter.'%\' or rd.myrevnotes like \'%'.$filter.'%\') ';
    $filter='='.$filter;
  }else{
    $parts = explode(' ', $filter);
    $filter = ' '.$filter.' ';
    for($ni=0;$ni<sizeof($parts);$ni++){
      if(strlen($parts[$ni]) > 1){
        $filterstr.= 'and (v.versetext like \'%'.$parts[$ni].'%\' or rd.myrevnotes like \'%'.$parts[$ni].'%\') ';
      }else{
        $filter = str_replace(' '.$parts[$ni].' ', ' ', $filter);
      }
    }
    $filter = trim($filter);
  }
}
$thehr = '<tr><td colspan="4"><hr style="border-top:1px solid '.$colors[3].';margin:0;"></td></tr>';
$row = rs('select ifnull(notes, \'\') from myrevusers where myrevid = '.$myrevid.' ');
$wsdot = ((strlen($row[0])>0)?1:0);

?>
<span class="pageheader" style="margin-bottom:16px;"><?=$myrevname.'&rsquo;'.((substr($myrevname,-1,1)!='s')?'s':'')?> REV<br />
<a onclick="rlightbox('note','<?=$myrevid?>|0|0|0|0');" title="My Workspace"><img src="/i/myrev_workspace<?=$colors[0].(($wsdot==1)?'_DOT':'')?>.png" style="width:1.6em;margin-bottom:-7px;" alt="My Workspace" /></a>
<a href="/bcuk" title="Back to Bible"><img src="/i/bible_icon<?=$colors[0]?>.png" style="width:1.6em;margin-bottom:-8px;" alt="Back to Bible" /></a>
<a href="/login" title="My MyREV Account"><img src="/i/user_account_icon<?=$colors[0]?>.png" style="width:1.6em;margin-bottom:-7px;" alt="Back to Bible" /></a>
</span>

<div style="margin:0 auto;max-width:1024px;font-size:90%;">
<form name="frm" method="post" action="/">
<div style="text-align:center;margin:8px auto;line-height:1.7;">
<?
  switch($myrevcolr){
  case -1:
    $filtercaption = 'All Items';
    $filtersql = '';
    break;
  //case  0:
  //  $filtercaption = 'No Color';
  //  $filtersql = 'and rd.highlight = '.$myrevcolr.' ';
  //  break;
  case 10:
    $filtercaption = 'All w/Notes';
    $filtersql = 'and rd.myrevnotes is not null ';
    break;
  case 11:
    $filtercaption = 'All w/Color';
    $filtersql = 'and rd.highlight between 1 and 5 ';
    break;
  case 12:
    $filtercaption = 'Margin Notes';
    $filtersql = 'and rd.marginnote is not null ';
    break;
  default:
    //$filtercaption = '&nbsp;';
    $filtercaption = $myrevkeys[$myrevcolr];
    $filtersql = 'and rd.highlight = '.$myrevcolr.' ';
    break;
  }

  if($linkcommentary==0){
    // NOTE: due to name change, this switch is backwards! 0=yes, 1=no
    print('<p style="text-align:center;color:red;font-size:90%;">The &ldquo;Bible Text Only&rdquo; setting is turned on.<br />With it on, your MyREV verses will not be highlighted.<br />Click <a onclick="setlinkcommentary(1);location.reload();">here</a> to turn it off.</p>');
  }
  ?>
  <table style="text-align:center;margin:8px auto;">
    <tr>
      <td style="text-align:left;">Display</td>
      <td style="text-align:left;">
        <select name="myrevmode" id="myrevmode" onchange="setmyrevmode(this[this.selectedIndex].value);document.frm.submit();">
          <option value="compact"<?=fixsel('compact', $myrevmode)?>>Compact</option>
          <option value="full"<?=fixsel('full', $myrevmode)?>>Full</option>
        </select>&nbsp;
      </td>
    </tr>
    <tr>
      <td style="text-align:left;">Filter <a onclick="rlightbox('keys','');" title="Edit Captions"><img src="/i/myrev_editkeys<?=$colors[0]?>.png" style="width:.9em;margin-bottom:-3px;" alt="Edit Captions" /></a></td>
      <td style="text-align:left;">
        <?
          print('<styledd><ul><li class="styledd-menu-parent" style="color:'.$colors[1].';background-color:'.(($myrevcolr>0 && $myrevcolr<6)?$hilitecolors[$myrevcolr]:$colors[2]).'"><span style="display:inline-block;min-width:50px;margin:0 4px;">'.$filtercaption.'&nbsp;</span>');
          print('<ul class="styledd-menu">');
          print('<li style="border-bottom:1px solid '.$colors[3].';"><a onclick="document.frm.myrevcolr.value=-1;resetpage(document.frm,0);" style="color:'.$colors[4].';background-color:'.$colors[2].';">All Items</a></li>'.crlf);
          print('<li style="border-bottom:1px solid '.$colors[3].';"><a onclick="document.frm.myrevcolr.value=10;resetpage(document.frm,0);" style="color:'.$colors[4].';background-color:'.$colors[2].';">All w/Notes</a></li>'.crlf);
          print('<li style="border-bottom:1px solid '.$colors[3].';"><a onclick="document.frm.myrevcolr.value=12;resetpage(document.frm,0);" style="color:'.$colors[4].';background-color:'.$colors[2].';">w/Margin Notes</a></li>'.crlf);
          print('<li style="border-bottom:1px solid '.$colors[3].';"><a onclick="document.frm.myrevcolr.value=11;resetpage(document.frm,0);" style="color:'.$colors[4].';background-color:'.$colors[2].';">All w/Color</a></li>'.crlf);
          for($i=0;$i<sizeof($hilitecolors);$i++){
            print('<li'.(($i==0)?' style="border-bottom:1px solid '.$colors[3].';"':'').'><a onclick="document.frm.myrevcolr.value='.$i.';resetpage(document.frm,0);" style="color:'.$colors[4].';background-color:'.(($i>0)?$hilitecolors[$i]:$colors[2]).';">'.$myrevkeys[$i].'&nbsp;</a></li>'.crlf);
          }
          print('</ul></li></ul></styledd>');
        ?>
      </td>
    </tr>
    <tr>
      <td style="text-align:left;">Sort</td>
      <td style="text-align:left;">
        <select name="myrevsort" id="myrevsort" onchange="setmyrevsort(this[this.selectedIndex].value);document.frm.submit();">
          <option value="canon"<?=fixsel('canon', $myrevsort)?>>Canon</option>
          <option value="color"<?=fixsel('color', $myrevsort)?>>Color</option>
          <option value="mysort"<?=fixsel('mysort', $myrevsort)?>>Custom</option>
          <option value="recent"<?=fixsel('recent', $myrevsort)?>>Newest First</option>
        </select><?
        //if($myrevsort=='mysort') print('Clear my custom sort: <input type="checkbox" id="chkclearsort" name="chkclearsort" value="1" />');
        if($myrevsort=='mysort') print(' <a onclick="valclearsort(document.frm);" title="Reset my custom sort"><img src="/i/myrev_trash'.$colors[0].'.png" style="width:1.3em;margin-bottom:-3px;" alt="" /></a>');
      ?>
      </td>
    </tr>
    <tr>
      <td style="text-align:left;">Search</td>
      <td style="text-align:left;">
        <input type="text" name="filter" value="<?=$filter?>" size="16" maxlength="24" autocomplete="off" onfocus="this.select();" />
        <input type="submit" name="btnsrch" value="Go" onclick="resetpage(document.frm,1);return false;" />
      </td>
    </tr>
  </table>
</div>
<?
//$where = 'rd.myrevid = '.$myrevid.' '.(($myrevcolr>-1)?'and rd.highlight = '.$myrevcolr.' ':'').$filterstr;
$where = 'rd.myrevid = '.$myrevid.' '.$filtersql.$filterstr;
$comimg = '<img src="/i/commentary'.$colors[0].'.png" style="width:'.(($ismobile)?'1':'.8').'rem;" />';


  $pagnum    = ((isset($_REQUEST['pagnum']))?$_REQUEST['pagnum']:1);
  $pagitmcnt = $myrevpagsiz;
  $sql = 'select count(*) from myrevdata rd
          join verse v on v.testament = rd.testament and v.book = rd.book and v.chapter = rd.chapter and v.verse = rd.verse
          where '.$where.' ';
  $row = rs($sql);
  $rsss= $row[0];
  $pagtot = ceil($rsss/$pagitmcnt);
  $limit  = 'limit '.(($pagnum-1)*$pagitmcnt).', '.$pagitmcnt.' ';

  $pagination = pagination($pagnum, $pagitmcnt, $pagtot);
  if($pagtot>0) print($pagination);

  $sclr=(($myrevcolr>-1)?'and rd.highlight = '.$myrevcolr:'').' ';
  $sql = 'select 1 from myrevdata where myrevid = '.$myrevid.' limit 1';
  $row = rs($sql);
  $havedata = (($row[0]==1)?1:0);

  print('<div id="myitemsheader" style="display:table;margin:0 auto;">');
  if($myrevmode=='compact'){
    print('<div class="divtr">');
      print('<div class="divtd" style="min-width:100px;padding:0 4px;">Scripture</div>');
      print('<div class="divtd" style="width:50px;">Color</div>');
      print('<div class="divtd" style="text-align:center;">Notes</div>');
      print('<div class="divtd"><a onclick="toggleall();" style="cursor:pointer;" title="Check/uncheck all"><img src="/i/myrevchk'.$colors[0].'.png" style="width:1.3em;" alt="" /></a></div>');
      if($myrevsort=='mysort')
        print('<div class="divtd" style="padding:0 14px;border-bottom:none;"></div>');
    print('</div>'.crlf);
  }else{
    print('<div class="divtr">');
      print('<div class="divtd" style="width:49%;">Scripture</div>');
      print('<div class="divtd" style="width:49%;">Notes</div>');
      print('<div class="divtd" style="width:2%;"><a onclick="toggleall();" style="cursor:pointer;" title="Check/uncheck all"><img src="/i/myrevchk'.$colors[0].'.png" style="width:1.3em;" alt="" /></a></div>');
      if($myrevsort=='mysort')
        print('<div class="divtd" style="padding:0 20px;border-bottom:none;"></div>');
    print('</div>'.crlf);
  }
  print('</div>');

  print('<div id="myitems" style="display:table;margin:0 auto;border-spacing:6px;table-layout: fixed;">');
  $sort=(($myrevsort=='color')?'rd.highlight, ':(($myrevsort=='mysort')?'rd.sqn, ':(($myrevsort=='recent')?'rd.lastupdate desc, ':'')));
  // $sortint is used in the MSW & PDF exports
  $sortint=(($myrevsort=='color')?1:(($myrevsort=='mysort')?2:(($myrevsort=='recent')?3:0)));
  $sql = 'select b.title, b.abbr, rd.testament, rd.book, rd.chapter, rd.verse, rd.highlight, rd.myrevnotes,
          ifnull(rd.marginnote, \'\') marginnote,
          if(v.versetext=\'-\', v.commentary, v.versetext) versetext
          from myrevdata rd
          join book b on b.testament = rd.testament and b.book = rd.book
          join verse v on v.testament = rd.testament and v.book = rd.book and v.chapter = rd.chapter and v.verse = rd.verse
          where '.$where.'
          order by '.$sort.'rd.testament, rd.book, rd.chapter, rd.verse '.$limit;
  //print($sql);
  $dat = dbquery($sql);
  $ni=0;
  while ($row = mysqli_fetch_array($dat)) {
    $nqry = $myrevid.'|'.$row['testament'].'|'.$row['book'].'|'.$row['chapter'].'|'.$row['verse'];
    print('<div class="divtr" data-itm="'.$nqry.'">');
    $tst = $row['testament'];
    if($tst<2){
      $href = '/'.str_replace(' ', '', $row['title']).'/'.$row['chapter'].'/nav'.$row['verse'];
      $sref = $row['title'].' '.$row['chapter'].':'.$row['verse'];
      $sabr = $row['abbr'].' '.$row['chapter'].':'.$row['verse'];
    }else{
      $href = '/'.(($tst==3)?'appx':(($tst==2)?'info':'word')).'/'.(($tst==4)?$row['title']:$row['book']);
      $sref = $row['title'];
      $sabr = $sref;
    }

    if($myrevmode=='compact'){
      print('<div class="divtd" style="min-width:100px;"><span style="display:inline-block;margin-top:5px;">'.$sabr.'</span> <span style="float:right;"><a href="'.$href.'" title="Go there" target="'.(($inapp)?'_self':'_blank').'"><img src="/i/'.(($inapp)?'myrev_go':'popout').''.$colors[0].'.png" style="width:1.8em" alt="go there" /></a></span></div>');
      $caption = $myrevkeys[$row['highlight']];
      if($caption=='') $caption = '&nbsp;';
      print('<div class="divtd" style="width:50px;white-space:nowrap;"><span style="display:inline-block;transition:background-color .3s;font-size:80%;padding:3px;color:'.$colors[7].';background-color:'.$hilitecolors[$row['highlight']].';width:99%;cursor:pointer;border: 1px solid '.$colors[3].';border-radius:4px;" onclick="showhilightdiv(\'hl_\',\''.$nqry.'\');" class="hl_'.$nqry.'" data-hlite="'.$row['highlight'].'">'.$caption.'</span></div>');
      print('<div class="divtd" id="nt_'.$nqry.'" style="text-align:center;cursor:pointer;" onclick="rlightbox(\'note\',\''.$nqry.'\');"><img src="/i/myrev_notes'.$colors[0].((strlen($row['myrevnotes'].'')>0)?'_DOT':'').'.png" style="width:2em;margin:-4px 0 -8px 0;" alt="edit" /></div>');
      print('<div class="divtd" style="white-space:nowrap;"><input type="checkbox" class="chkcls" name="chk_'.$ni.'" id="chk_'.$ni.'" value="chk_'.$nqry.'" style="margin-top:4px;" /></div>');
      if($myrevsort=='mysort')
        print('<div class="divtd" style="border-bottom:none;"><img src="/i/mnu_menu'.$colors[0].'.png" class="sorthandle" style="margin:0 0 -3px 0;" alt="" /></div>');
    }else{
      $verse = $row['versetext'];
      $verse = str_replace('[pg]', ' ', $verse);
      $verse = str_replace('[hp]', ' ', $verse);
      $verse = str_replace('[hpbegin]', ' ', $verse);
      $verse = str_replace('[hpend]', ' ', $verse);
      $verse = str_replace('[lb]', ' ', $verse);
      $verse = str_replace('[listbegin]', ' ', $verse);
      $verse = str_replace('[listend]', ' ', $verse);
      $verse = str_replace('[bq]', ' ', $verse);
      $verse = str_replace('[/bq]', ' ', $verse);
      $verse = str_replace('[br]', ' ', $verse);
      $verse = str_replace('[fn]', ' ', $verse);
      $verse = str_replace('[mvh]', ' ', $verse);
      $verse = str_replace('[mvs]', ' ', $verse);
      $verse = str_replace('~', '', $verse);
      if(substr($verse, 0, 1)=='~'){
          // removed ~ from beginning of [[
          $verse = '[['.substr($verse,1).((strpos($verse, ']]')>0)?'':']]');
          $verse = str_replace(']]]]', ']]', $verse);
          $verse = str_replace('[[[[', '[[', $verse);
      }
      if(strpos($verse, '[[')!==false && strpos($verse, ']]')===false) $verse.= ']]';
      if(strpos($verse, '[')!==false && strpos($verse, ']')===false) $verse.= ']';
      if(strpos($verse, ']')!==false && strpos($verse, '[')===false) $verse = '['.$verse;
      $comparelink = '';
      if($tst>1) $verse = truncateHtml($verse, 400);
      if($tst<2 && !$inapp)
        $comparelink = getothertranslationlink($row['title'], $row['chapter'], $row['verse'], 1);

      $notes = $row['myrevnotes'];
      // have to think about coloring the mnotes
      $marginnote = str_replace('[br]', '<br />', $row['marginnote']);
      if($marginnote!=='')
        $marginnote = '<div id="mn_'.$nqry.'" class="marginnote mnotedarr" onclick="rlightbox(\'note\',\''.$nqry.'\',1);" style="margin-top:0;cursor:pointer;">'.$marginnote.'</div>';
      else
        $marginnote = '<div id="mn_'.$nqry.'" class="marginnote mnotedarr" onclick="rlightbox(\'note\',\''.$nqry.'\',1);" style="display:none;margin-top:0;cursor:pointer;"></div>';

      $edtlink = '<a onclick="rlightbox(\'note\',\''.$nqry.'\');" title="edit"><img src="/i/myrev_notes'.$colors[0].((strlen($notes??'')>0)?'_DOT':'').'.png" style="width:2em;float:left;margin-right:4px;" alt="edit" /></a> ';
      if(strlen($notes??'') > $notestruncatelen) $notes = truncateHtml($notes, $notestruncatelen);
      if(substr($notes??'', 0, 3)=='<p>'){
        $notes = '<p class="spc" style="margin-top:0;">'.$edtlink.substr($notes,3);
        $edtlink='';
      }else if(strlen($notes??'')>0){
        $notes = '<div style="display:inline;">'.$edtlink.'</div>'.$notes;
        $edtlink='';
      }
      print('<div class="divtd" style="width:49%;vertical-align:top;">'.$marginnote.'<a href="'.$href.'" title="Go there" target="'.(($inapp)?'_self':'_blank').'">'.$sref.'</a> <div style="display:'.(($tst>1)?'inline-block;padding:0 4px;':'inline;').'transition:background-color .3s;background-color:'.$hilitecolors[$row['highlight']].';cursor:pointer;" class="hl_'.$nqry.'" title="'.$myrevkeys[$row['highlight']].'" onclick="showhilightdiv(\'hl_\',\''.$nqry.'\');" data-hlite="'.$row['highlight'].'">'.$verse.'</div>'.$comparelink.'</div>');
      print('<div class="divtd" style="width:49%;vertical-align:top;" id="nt_'.$nqry.'">'.$notes.' '.$edtlink.'</div>');
      print('<div class="divtd" style="width:2%; vertical-align:top;"><input type="checkbox" class="chkcls" name="chk_'.$ni.'" id="chk_'.$ni.'" value="chk_'.$nqry.'" /></div>');
      if($myrevsort=='mysort')
        print('<div class="divtd" style="border-bottom:none;"><img src="/i/mnu_menu'.$colors[0].'.png" class="sorthandle" alt="" /></div>');
    }
    print('</div>'.crlf);
    $ni++;
  }
  print('</div>'); // myitems
  print('<table id="withchecked" style="margin:0 auto;border-spacing:6px;border-collapse:separate;">');
  if($ni>0){
    print('<tr><td colspan="4" style="text-align:right;padding-right:'.(($myrevsort=='mysort')?'36':'4').'px;">With checked &raquo;&nbsp;&nbsp;&nbsp;');

    $clrtmp = '';$clrlis = '';
    for($i=0;$i<sizeof($hilitecolors);$i++){
      $clrtmp.= '<span style="display:inline-block;background-color:'.$hilitecolors[$i].';height:100%;width:6px;cursor:default;'.(($i>0)?'border-left:1px solid '.$colors[3].';':'').'">&nbsp;</span>';
      $clrlis.= '<li style="background-color:'.(($i==0)?$colors[2]:$hilitecolors[$i]).';'.(($i==0)?'border-bottom:1px solid '.$colors[3].';':'').'min-width:70px;"><a style="color:'.$colors[4].';" onclick="valcolorchange(document.frm,'.$i.');">'.(($myrevkeys[$i])?$myrevkeys[$i]:'&nbsp;').'</a></li>';
    }
    print('<styledd><ul><li class="styledd-menu-parent">'.$clrtmp.'<ul class="styledd-menu">'.$clrlis.'</ul></li></ul></styledd>&nbsp;&nbsp;');

    if(!$inapp){
      print('<a onclick="return valexport(document.frm, \'msw\')" title="Export to MSW"><img src="/i/myrevmsw'.$colors[0].'.png" style="width:1.7em;margin-bottom:-7px;" alt="MSW" /></a>&nbsp;');
      print('<a onclick="return valexport(document.frm, \'pdf\')" title="Export as PDF"><img src="/i/myrevpdf'.$colors[0].'.png" style="width:1.7em;margin-bottom:-8px;" alt="PDF" /></a>&nbsp;');
    }
    print('<a onclick="return valdel(document.frm)" title="Delete"><img src="/i/myrev_trash'.$colors[0].'.png" style="width:1.6em;margin-bottom:-6px;" alt="Delete" /></a>');

  print('</td></tr>');
  print($thehr);
  print('</table>');
  print($pagination);
  ?>
  <div style="text-align:center;margin:8px auto;font-size:80%;">
  Page size:
  <select name="myrevpagsiz" id="myrevpagsiz" onchange="setmyrevpagsiz(this[this.selectedIndex].value);resetpage(document.frm,0);">
    <option value="5"<?=fixsel(5, $myrevpagsiz)?>>5</option>
    <option value="10"<?=fixsel(10, $myrevpagsiz)?>>10</option>
    <option value="20"<?=fixsel(20, $myrevpagsiz)?>>20</option>
    <option value="50"<?=fixsel(50, $myrevpagsiz)?>>50</option>
    <option value="9999"<?=fixsel(9999, $myrevpagsiz)?>>All</option>
  </select></div>
    <?
  }else{
    print('<div style="max-width:1024px;margin:20px auto;text-align:center;color:red;">');
    if($havedata==0)
      print('You have no highlighted verses or notes.');
    else if($havedata==1 && $filter!='')
      print('Your search returned no results.');
    else
      print('You have no verses highlighted with that color.');
    print('</div>');
  }
?>
  <div style="text-align:left;margin:8px auto;font-size:90%;line-height:2.2;max-width:320px;">
    &nbsp;<br />When I click a Bible verse:<br />
    <input type="radio" id="myrevpref1" name="myrevclick" value="0"<?=fixrad($myrevclick==0)?> onclick="setmyrevclick(0)"> <label for="myrevpref1">Go to REV Commentary</label><br />
    <input type="radio" id="myrevpref2" name="myrevclick" value="1"<?=fixrad($myrevclick==1)?> onclick="setmyrevclick(1)"> <label for="myrevpref2">Show the MyREV PopUp</label><br />
    <input type="checkbox" id="myrevshoweditorfirst" name="myrevshoweditorfirst" value="1"<?=fixchk($myrevshoweditorfirst)?> onclick="setmyrevshoweditorfirst(((this.checked)?1:0))"> <label for="myrevshoweditorfirst">Show the editor first</label><br />
    <a onclick="valclearall(document.frm);"><img src="/i/myrev_trash<?=$colors[0]?>.png" style="width:1.8em;margin-bottom:-6px;" alt="" /></a> Clear all my verses<br />
    <a onclick="rlightbox('tut','');">Tutorial</a><br />
  </div>
<input type="hidden" name="mitm" value="<?=$mitm?>" />
<input type="hidden" name="page" value="<?=$page?>" />
<input type="hidden" name="test" value="<?=$test?>" />
<input type="hidden" name="book" value="<?=$book?>" />
<input type="hidden" name="chap" value="<?=$chap?>" />
<input type="hidden" name="vers" value="<?=$vers?>" />
<input type="hidden" name="oper" value="">
<input type="hidden" name="vcnt" value="<?=$ni?>">
<input type="hidden" name="colorchangecolor" value="-1" />
<input type="hidden" name="pagnum" value="<?=$pagnum?>" />
<input type="hidden" name="myrevcolr" value="<?=$myrevcolr?>">
</form>
</div>

<script>
<?if($myrevsort=='mysort'){?>
  var mysort = new Sortable(document.getElementById('myitems'), {
    // options here
    animation: 150,
    handle: '.sorthandle',
    direction: 'vertical',
    touchStartThreshold: 5,
    onEnd: function (evt) {
      var itemEl = evt.item;  // dragged HTMLElement
      //evt.to;                 // target list
      //evt.from;               // previous list
      evt.oldIndex;           // element's old index within old parent
      evt.newIndex;           // element's new index within new parent
      //evt.clone;              // the clone element
      //evt.pullMode;           // when item is in another sortable: `"clone"` if cloning, `true` if moving
      //itemEl.setAttribute('idx',evt.newIndex)
      //alert(itemEl.getAttribute('idx'));
      //alert(evt.oldIndex);
      //alert(evt.newIndex);
      var myitems = $("myitems");
      var refs = '';
      var items = myitems.getElementsByClassName("divtr");
      for (var ni=0;ni<items.length; ni++) {
        if(ni>0) refs+= '~';
        refs+= items[ni].getAttribute('data-itm');
      }
      updatesort(refs);
      //alert(refs);
    },
  });

  function updatesort(refs){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function(){
      if (xmlhttp.readyState==4 && xmlhttp.status==200){
        var ret = JSON.parse(xmlhttp.responseText);
        //alert(ret.spanclass);
      }
    }
    xmlhttp.open('GET', '/jsonmyrevtasks.php?task=sort&refs='+refs+'&offset=<?=(($pagnum-1)*$pagitmcnt)?>', true);
    xmlhttp.send();
  }


<?}?>


    window.onresize=function(){resizemyitemsstuff();};
    window.onload=function(){resizemyitemsstuff();};

    function resizemyitemsstuff(){
      $('myitemsheader').style.width = ($('myitems').offsetWidth)+'px';
      $('withchecked').style.width = ($('myitems').offsetWidth)+'px';
    }

</script>

<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findmycomm.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findbcom.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findapx.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findstrongs.min.js?v=<?=$fileversion?>"></script>
<script>
  var myrevid = <?=$myrevid?>;
  var chngtxt = <?=(($myrevmode=='full')?0:1)?>;

  findmycomm.enablePopups = true;
  findmycomm.remoteURL    = '<?=$jsonurl?>';
  findmycomm.startNodeId  = 'view';
  findmycomm.mrlightbox   = 1;
  addLoadEvent(findmycomm.scan);

  findcomm.enablePopups = true;
  findcomm.remoteURL    = '<?=$jsonurl?>';
  findcomm.startNodeId  = 'view';
  addLoadEvent(findcomm.scan);

  findbcom.startNodeId  = 'view';
  addLoadEvent(findbcom.scan);

  findappx.startNodeId = 'view';
  findappx.apxidx = [<?=loadapxids()?>];
  addLoadEvent(findappx.scan);

  findvers.startNodeId = 'view';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
  addLoadEvent(findvers.scan);

  var prflexicon    = <?=$lexicon?>;
  findstrongs.startNodeId = 'view';
  findstrongs.ignoreTags.push('noparse');
  findstrongs.lexicon = prflexicon;
  addLoadEvent(findstrongs.scan);

  function toggleall(){
    if(<?=(($ni==0)?1:0)?>==1) return;
    var checked = $('chk_0').checked;
    for(i=0;i<<?=$ni?>;i++){
      $('chk_'+i).checked = !checked;
    }
  }

  function valcolorchange(f,idx){
    var havechecked = 0;
    for(var i=0;i<<?=$ni?>; i++){
      if(f['chk_'+i].checked){
        havechecked = 1;
        break;
      }
    }
    if(havechecked==0){
      alert('No verses are checked.');
      return;
    }
    var msg = '\nAre you sure you want to change the color\nof the checked verses?';
    if(idx==0)
      msg+= '\n\nYou have chosen to clear the highlight color from the selected verses.\nNote that this will remove verses from your list that do not have notes.';
    if(confirm(msg + '\n\nThis is not un-doable!')){
      f.colorchangecolor.value = idx;
      f.oper.value = 'chngchecked';
      f.submit();
    }else return;
  }

  function valdel(f){
    var havechecked = 0;
    for(var i=0;i<<?=$ni?>; i++){
      if(f['chk_'+i].checked){
        havechecked = 1;
        break;
      }
    }
    if(havechecked==0){
      alert('No verses are checked.');
      return false;
    }
    if(confirm('\nAre you sure you want to delete\nthe checked verses from your list?\n\nthis will remove all your data for the verse,\nthe highlighting, margin note, and MyREV note.\n\nThis is not un-doable!')){
      f.oper.value = 'delchecked';
      f.submit();
      return true;
    }else
      return false;
  }

  function valexport(f, expto){
    var myrevid = <?=$myrevid?>;
    var havechecked = 0;
    var myitems = $("myitems");
    var dat='';
    var items = myitems.getElementsByClassName("chkcls");
    for (var ni=0;ni<items.length; ni++) {
      if(items[ni].checked){
        havechecked = 1;
        dat+= '~'+items[ni].value.substring(4);
      }
    }
    //alert(dat);
    if(havechecked==0){
      alert('No verses are checked.');
      return false;
    }
    if(expto=='msw') // msw
      var href = '/docx_phpdocx.php?what=myrev&sort=<?=$sortint?>&dat='+myrevid+'|0|0|0|0'+dat;
    else{ // pdf
      var href = '/pdf.php?what=myrev&sort=<?=$sortint?>&dat='+myrevid+'|0|0|0|0'+dat;
    }
    location.href=href;
    return false;
  }

  function dopage(pnum){
    document.frm.pagnum.value=pnum;
    document.frm.submit();
  }

  function resetpage(f, check){
    if(check){
      filt = trim(f.filter.value);
      if(filt.length>0 && filt.length<3){
        alert('Please enter at least three characters to search for.');
        f.filter.focus();
        f.filter.select();
        return false;
      }
    }
    f.pagnum.value=1;
    f.submit();
  }

  function valclearall(f){
    if(confirm('\nAre you sure you want to remove all your verses?\n\nThis will remove all of your verses and notes.\n\nThis is not undoable!')){
      f.oper.value='clearall';
      f.submit();
    }
  }
  function valclearsort(f){
    if(confirm('\nAre you sure you want to reset your custom sort?')){
      f.oper.value='clearsort';
      f.submit();
    }
  }

  function showhilightdiv(prefix,qry){
    // added the prefix to use the popup to color margin notes.
    // will finish later.
    event.stopPropagation();
    var rdiv = $('myrevdiv');
    if(rdiv.style.display=='block'){
      myrevhidePopup();
      return;
    }
    var classname = prefix+qry;
    hlit = document.getElementsByClassName(classname)[0].getAttribute('data-hlite');
    rdiv.innerHTML=gethilightdivcontents(qry, hlit, '',0,0,0,<?=(($myrevmode=='compact')?1:0)?>,0,0,0);   // 10 parms
    rdiv.style.visibility='hidden';
    rdiv.style.display='block';
    var dims = gethilightdivcoords(rdiv);
    rdiv.style.top  = dims.top+'px';
    rdiv.style.left = dims.left+'px';
    rdiv.style.visibility='visible';
    rdiv.style.opacity=1;
  }

  function reloadmyrevnotes(qry){
    var notestruncatelen = (<?=$notestruncatelen?>-4);
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function(){
      if (xmlhttp.readyState==4 && xmlhttp.status==200){
        var ret = JSON.parse(xmlhttp.responseText);
        var notes = ret.myrevnotes;
        var mnnote = ret.marginnote;
        var lcolor = ret.color;
        if(notes.length==0 && mnnote.length==0 && lcolor==0) location.reload();
        if(myrevmode=='full'){
          var edtlink = '<a onclick="rlightbox(\'note\',\''+qry+'\');" title="edit"><img src="/i/myrev_notes'+colors[0]+((notes.length>0)?'_DOT':'')+'.png" style="width:2em;float:left;margin-right:4px;" alt="edit" /></a>';
          // this sorta works
          if(notes.length > notestruncatelen) notes = truncateHTML(notes, notestruncatelen);
          if(notes.substring(0, 3)=='<p>'){
            notes = '<p class="spc" style="margin-top:0">'+edtlink+notes.substring(3);
            edtlink='';
          }else if(notes.length>0){
            notes = '<div style="display:inline;">'+edtlink+'</div>'+notes;
            edtlink='';
          }
          try{
            var rddiv = $('nt_'+qry);
            rddiv.innerHTML = edtlink+notes;
            var mndiv = $('mn_'+qry);
            mndiv.innerHTML = mnnote.replace(/\[br\]/g, '<br />');
            mndiv.style.display = ((mnnote=='')?'none':'block');
            setTimeout('findmycomm.scan();', 100);
            setTimeout('findcomm.scan();', 200);
            setTimeout('findbcom.scan();', 300);
            setTimeout('findappx.scan();', 400);
            setTimeout('findvers.scan();', 500);
            setTimeout('findstrongs.scan();', 600);
          }catch(e){location.reload()}
        }else{
          var iconimg = '<img src="/i/myrev_notes'+colors[0]+((notes.length>0)?'_DOT':'')+'.png" style="width:2em;margin:-4px 0 -8px 0;" alt="edit" />';
          var rddiv = $('nt_'+qry);
          rddiv.innerHTML = iconimg;
        }
      }
    }
    xmlhttp.open('GET', '/jsonmyrevtasks.php?task=data&ref='+qry,true);
    xmlhttp.send();
  }

  setTimeout('initmyrevpopup(\'myrevdiv\');', 500);

  function truncateHTML(text, length) {
    var truncated = text.substring(0, length);
    // Remove line breaks and surrounding whitespace
    truncated = truncated.replace(/(\r\n|\n|\r)/gm,"").trim();
    // If the text ends with an incomplete start tag, trim it off
    truncated = truncated.replace(/<(\w*)(?:(?:\s\w+(?:={0,1}(["']{0,1})\w*\2{0,1})))*$/g, '');
    // If the text ends with a truncated end tag, fix it.
    var truncatedEndTagExpr = /<\/((?:\w*))$/g;
    var truncatedEndTagMatch = truncatedEndTagExpr.exec(truncated);
    if (truncatedEndTagMatch != null) {
        var truncatedEndTag = truncatedEndTagMatch[1];
        // Check to see if there's an identifiable tag in the end tag
        if (truncatedEndTag.length > 0) {
            // If so, find the start tag, and close it
            var startTagExpr = new RegExp(
                "<(" + truncatedEndTag + "\\w?)(?:(?:\\s\\w+(?:=([\"\'])\\w*\\2)))*>");
            var testString = truncated;
            var startTagMatch = startTagExpr.exec(testString);

            var startTag = null;
            while (startTagMatch != null) {
                startTag = startTagMatch[1];
                testString = testString.replace(startTagExpr, '');
                startTagMatch = startTagExpr.exec(testString);
            }
            if (startTag != null) {
                truncated = truncated.replace(truncatedEndTagExpr, '</' + startTag + '>');
            }
        } else {
            // Otherwise, cull off the broken end tag
            truncated = truncated.replace(truncatedEndTagExpr, '');
        }
    }
    truncated+= '...';
    // Now the tricky part. Reverse the text, and look for opening tags. For each opening tag,
    //  check to see that he closing tag before it is for that tag. If not, append a closing tag.
    var testString = reverseHtml(truncated);
    var reverseTagOpenExpr = /<(?:(["'])\w*\1=\w+ )*(\w*)>/;
    var tagMatch = reverseTagOpenExpr.exec(testString);
    while (tagMatch != null) {
        var tag = tagMatch[0];
        var tagName = tagMatch[2];
        var startPos = tagMatch.index;
        var endPos = startPos + tag.length;
        var fragment = testString.substring(0, endPos);
        // Test to see if an end tag is found in the fragment. If not, append one to the end
        //  of the truncated HTML, thus closing the last unclosed tag
        if (!new RegExp("<" + tagName + "\/>").test(fragment)) {
            truncated += '</' + reverseHtml(tagName) + '>';
        }
        // Get rid of the already tested fragment
        testString = testString.replace(fragment, '');
        // Get another tag to test
        tagMatch = reverseTagOpenExpr.exec(testString);
    }
    return truncated;
}

function reverseHtml(str) {
    var ph = String.fromCharCode(206);
    var result = str.split('').reverse().join('');
    while (result.indexOf('<') > -1) {
        result = result.replace('<',ph);
    }
    while (result.indexOf('>') > -1) {
        result = result.replace('>', '<');
    }
    while (result.indexOf(ph) > -1) {
        result = result.replace(ph, '>');
    }
    return result;
}

</script>

<?
logview(300,0,0,0,0);

function pagination($pnum, $pitmcnt, $ptot){
  global $colors;
  $ret='<div style="text-align:center;margin:8px auto;font-size:90%;">';
  if($pnum-1 > 0){
    $ret.= ' <a onclick="dopage('.($pnum-1).');">&laquo;prev</a> ';
  }else{
    $ret.= ' <span style="color:'.$colors[7].'">&laquo;prev</span> ';
  }
  $ret.= 'Page ';
  $ret.= '<select onchange="dopage(this.selectedIndex+1);">';
  for($ni=1;$ni<=$ptot;$ni++){
    $ret .= '<option'.fixsel($ni, $pnum).'>'.$ni.'</option>';
  }
  $ret.= '</select>';
  $ret.= ' of '.$ptot;
  if($pnum+1 <= $ptot){
    $ret.= ' <a onclick="dopage('.($pnum+1).');">next&raquo;</a> ';
  }else{
    $ret.= ' <span style="color:'.$colors[7].'">next&raquo;</span> ';
  }
  $ret.= '</div>';
  return $ret;
}
?>
