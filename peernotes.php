<?php

if(!isset($page) || $peernotes==0){
  print('<div style="margin:0 auto;text-align:center;">&nbsp;<br />Sorry, you do not have permission to access this page.</div>');
  return;
}
$notestruncatelen=500;

$bookfilter=((isset($_REQUEST['bookfilter']))?$_REQUEST['bookfilter']:0);
$filter=((isset($_REQUEST['filter']))?$_REQUEST['filter']:'');
$filter = trim(strip_tags($filter));
$filter = str_replace(';','',$filter);
//$filter = str_replace(':','',$filter);
$filter = str_replace('>','',$filter);
$filter = str_replace('<','',$filter);
$filter = str_replace('\'','',$filter);
$filter = str_replace('"','',$filter);

$sort=((isset($_REQUEST['sort']))?$_REQUEST['sort']:2); // last update
$sortdir=((isset($_REQUEST['sortdir']))?$_REQUEST['sortdir']:1); // desc

$filterstr = '';
if($filter!==''){
  if(left($filter, 1)=='='){
    $filter = substr($filter, 1);
    $filter = str_replace('=', '', $filter); // remove remaining, just in case
    $filterstr.= 'and (v.versetext like \'%'.$filter.'%\' or pn.editnote like \'%'.$filter.'%\' or pn.editdetails like \'%'.$filter.'%\') ';
    $filter='='.$filter;
  }else{
    $parts = explode(' ', $filter);
    $filter = ' '.$filter.' ';
    for($ni=0;$ni<sizeof($parts);$ni++){
      if(strlen($parts[$ni]) > 1){
        $filterstr.= 'and (v.versetext like \'%'.$parts[$ni].'%\' or pn.editnote like \'%'.$parts[$ni].'%\' or pn.editdetails like \'%'.$parts[$ni].'%\') ';
      }else{
        $filter = str_replace(' '.$parts[$ni].' ', ' ', $filter);
      }
    }
    $filter = trim($filter);
  }
}
$thehr = '<tr><td colspan="4"><hr style="border-top:1px solid '.$colors[3].';margin:0;"></td></tr>';

$row = rs('select ifnull(peerworknotes, \'\') from myrevusers where myrevid = '.$myrevid.' ');
if($row && strlen($row[0])>0) $wsdot=1;
else $wsdot=0;

$resolved = ((isset($_POST['resolved']))?$_POST['resolved']:0);

// get list of people with peernotes permission
$dat = dbquery('select ifnull(revusername, myrevname) username, ifnull(permissions, \'0:0:0:0:0:0:0\') permissions, if(userid=2, 1, 2) from myrevusers where userid > 0 order by 3,1 ');
$peerusers = '';
$lastuser= '';
while ($row = mysqli_fetch_array($dat)) {
  $perms = explode(':', $row['permissions']);
  if(!isset($perms[6])) $perms[6]=0;
  if($perms[6] > 0){
    $peerusers.= (($lastuser == '')?'':$lastuser.', ');
    $lastuser = $row['username'];
  }
}
$peerusers.= 'and '.$lastuser;

?>
<span class="pageheader" style="margin-bottom:3px;">REV Reviewer Notes
<a onclick="rlightbox('pnote','-1|0|0|0|0',1);" title="Reviewer notes workspace"><img src="/i/peer_workspace<?=$colors[0].(($wsdot==1)?'_YELDOT':'')?>.png" alt="" style="width:1.8em;margin:0 0 -8px 0"></a>
<a href="/bcuk" title="Back to Bible"><img src="/i/bible_icon<?=$colors[0]?>.png" style="width:1.6em;margin-bottom:-8px;" alt="Back to Bible" /></a>
</span>
<div style="margin:0 auto;text-align:center"><small><?=usermenu()?></small></div>
<?if($superman==1 && 1==2){?>
<div style="margin:0 auto;text-align:center"><small><?=adminmenu()?></small></div>
<?}?>

<div style="width:100%;max-width:720px;text-align:center;padding:0;margin:0 auto;font-size:90%;">
  <a onclick="expandcollapsediv('resinst')" style="display:block;margin-top:10px;">What is this? <span id="moreless">&raquo;</span></a>
  <div id="resinst" style="text-align:left;height:0;padding:3px;margin:0;overflow:hidden;transition:height .4s ease-in;">
    <h3 style="text-align:center;">Welcome to Reviewer Notes!</h3>
    <img src="/i/underconstruction.png" alt="under construction" style="border:0;width:104px;float:left;">
    <p>This page is where Reviewer Notes are managed. Reviewer Notes are comments about a scripture or commentary entry that are visible to editors and other reviewers.</p>
    <!--<p>When you make a new editor note you have the option of making it &ldquo;Open&rdquo; or &ldquo;AddTo&rdquo; format. Once a note is created, the format type cannot be changed. An &ldquo;Open&rdquo; editor note allows all of the content be edited at any time, whereas with an &ldquo;AddTo&rdquo; note, any new content will be added to the existing note in a listed format with the person&rsquo;s name and date/time.</p>-->
    <p>There are several Reviewers for the REV (<?=$peerusers?>) and each one has the same permissions and abilities. Each reviewer is to operate within an honor system and to respect all margin and reviewer notes in the REV.
       Your reviewer note workspace is yours alone, editors and other reviewers cannot see it.</p>
    <p style="margin-bottom:0;">When the issue raised in a reviewer note has been properly addressed, check the &ldquo;Resolved&rdquo; box at the bottom and then click &ldquo;Save.&rdquo; This will indicate to the other reviewers and editors that the specific question(s) or concern(s) on that verse have been resolved.</p>
  </div>
</div>

<div style="margin:0 auto;max-width:1024px;font-size:90%;">
<form name="frm" method="post" action="/">
<div style="text-align:center;margin:8px auto;line-height:1.7;">
<?
  if($linkcommentary==0){
    // NOTE: due to name change, this switch is backwards! 0=yes, 1=no
    print('<p style="text-align:center;color:red;font-size:90%;">The &ldquo;Bible Text Only&rdquo; setting is turned on.<br />With it on, your MyREV verses will not be highlighted.<br />Click <a onclick="setlinkcommentary(1);location.reload();">here</a> to turn it off.</p>');
  }
?>
  <table style="text-align:center;margin:8px auto;">
    <tr>
      <td style="text-align:left;">What</td>
      <td style="text-align:left;">
        <select name="bookfilter" id="bookfilter" onchange="resetpage(document.frm,0);">
          <option value="0"<?=fixsel($bookfilter, 0)?>>Everything</option>
          <option value="-1"<?=fixsel($bookfilter, -1)?>>Whole Bible</option>
          <option value="-2"<?=fixsel($bookfilter, -2)?>>Old Testament</option>
          <option value="-3"<?=fixsel($bookfilter, -3)?>>New Testament</option>
          <option value="-4"<?=fixsel($bookfilter, -4)?>>Gospels</option>
          <option value="-5"<?=fixsel($bookfilter, -5)?>>Church Epistles</option>
          <option value="-6"<?=fixsel($bookfilter, -6)?>>Appendices</option>
          <option value="-7"<?=fixsel($bookfilter, -7)?>>Word Studies</option>
          <optgroup label="Individual Books"></optgroup>
          <?
          $dat = dbquery('select book, title from book where testament in (0,1) order by testament, book ');
          while ($row = mysqli_fetch_array($dat)) {
            print('<option value="'.$row[0].'"'.fixsel($bookfilter, $row[0]).'>'.$row[1].'</option>'.crlf);
          }
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <td style="text-align:left;">Resolved&nbsp;</td>
      <td style="text-align:left;">
        <small>
        <input type="radio" name="resolved" value="0"<?=fixrad(($resolved==0))?> onclick="resetpage(document.frm,1);"> No
        <input type="radio" name="resolved" value="1"<?=fixrad(($resolved==1))?> onclick="resetpage(document.frm,1);"> Yes
        <input type="radio" name="resolved" value="2"<?=fixrad(($resolved==2))?> onclick="resetpage(document.frm,1);"> Either
        </small>
      </td>
    </tr>
    <tr>
      <td style="text-align:left;">Sort</td>
      <td style="text-align:left;">
        <select name="sort" id="sort" onchange="resetpage(document.frm,0);">
          <option value="0"<?=fixsel($sort, 0)?>>Canon</option>
          <option value="1"<?=fixsel($sort, 1)?>>Entry Date</option>
          <option value="2"<?=fixsel($sort, 2)?>>Last Update</option>
        </select><small>
        <input type="radio" name="sortdir" value="0"<?=fixrad(($sortdir==0))?> onclick="resetpage(document.frm,0);" /> ASC
        <input type="radio" name="sortdir" value="1"<?=fixrad(($sortdir==1))?> onclick="resetpage(document.frm,0);" /> DESC
        </small>
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

switch($bookfilter){
case 0:
  $bookfilterstr='and pn.testament in (0,1,3,4) ';break;
case -1:
  $bookfilterstr='and pn.testament in (0,1) ';break;
case -2:
  $bookfilterstr='and pn.testament in (0,1) and pn.book between 1 and 39 ';break;
case -3:
  $bookfilterstr='and pn.testament in (0,1) and pn.book between 40 and 66 ';break;
case -4:
  $bookfilterstr='and pn.testament in (0,1) and pn.book between 40 and 43 ';break;
case -5:
  $bookfilterstr='and pn.testament in (0,1) and pn.book between 45 and 53 ';break;
case -6:
  $bookfilterstr='and pn.testament = 3 ';break;
case -7:
  $bookfilterstr='and pn.testament = 4 ';break;
default:
  $bookfilterstr='and pn.testament in (0,1) and pn.book = '.$bookfilter.' ';break;
}
switch($resolved){
case 0:
  $resolvedfilter='and pn.resolved = 0 ';break;
case 1:
  $resolvedfilter='and pn.resolved = 1 ';break;
default:
  $resolvedfilter='';break;
}


$where = '1=1 '.$bookfilterstr.$filterstr.$resolvedfilter;
$comimg = '<img src="/i/commentary'.$colors[0].'.png" style="width:'.(($ismobile)?'1':'.8').'rem;" />';


  $pagnum    = ((isset($_REQUEST['pagnum']))?$_REQUEST['pagnum']:1);
  $pagitmcnt = $myrevpagsiz;
  $sql = 'select count(*) from peernotes pn
          join verse v on v.testament = pn.testament and v.book = pn.book and v.chapter = pn.chapter and v.verse = pn.verse
          where '.$where.' ';
  $row = rs($sql);
  $rsss= $row[0];
  $pagtot = ceil($rsss/$pagitmcnt);
  $limit  = 'limit '.(($pagnum-1)*$pagitmcnt).', '.$pagitmcnt.' ';

  $pagination = pagination($pagnum, $pagitmcnt, $pagtot);
  if($pagtot>0) print($pagination);

  $sql = 'select 1 from peernotes limit 1';
  $row = rs($sql);
  $havedata = (($row[0]==1)?1:0);

  print('<div id="myitemsheader" style="display:table;margin:0 auto;">');
  print('<div class="divtr">');
  print('<div class="divtd" style="width:49%;">Scripture</div>');
  print('<div class="divtd" style="width:49%;">Notes</div>');
  print('</div>'.crlf);
  print('</div>');
  print('<div id="myitems" style="display:table;margin:0 auto;border-spacing:6px;table-layout: fixed;">');

  $ascdesc = (($sortdir==0)?' asc ':' desc ');
  switch($sort){
  case 1: //entrydate
    $sortstr = 'pn.notedate'.$ascdesc.' ';
    break;
  case 2: //lastupdate
    $sortstr = 'pn.lastupdate'.$ascdesc.' ';
    break;
  default: //canon
    $sortstr = 'pn.testament'.$ascdesc.', pn.book'.$ascdesc.', pn.chapter'.$ascdesc.', pn.verse'.$ascdesc.' ';
    break;
  }
  $sql = 'select b.title, b.abbr, pn.testament, pn.book, pn.chapter, pn.verse, pn.notedate, pn.lastupdate,
          ifnull(ifnull(mr.revusername, mr.myrevname), \'unknown\') author,
          ifnull(ifnull(mr2.revusername, mr2.myrevname), \'unknown\') lastauthor,
          pn.resolved,
          pn.editdetails,
          ifnull(pn.editnote,\'\') editnote,
          if(v.versetext=\'-\', v.commentary, v.versetext) versetext
          from peernotes pn
          join book b on b.testament = pn.testament and b.book = pn.book
          join verse v on v.testament = pn.testament and v.book = pn.book and v.chapter = pn.chapter and v.verse = pn.verse
          left join myrevusers mr on (mr.userid = pn.author and mr.myrevid > 0)
          left join myrevusers mr2 on (mr2.userid = pn.lastauthor and mr2.myrevid > 0)
          where '.$where.'
          order by '.$sortstr.$limit;

  //print('<br /><span style="display:block;color:'.$colors[7].';font-size:.7em;line-height:1.2em;">'.str_replace(crlf, '<br />', str_replace('<', '&lt;', str_replace('&', '&amp;', $sql))).'</span>');

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
    if($tst>1) // chop long content
      $verse = truncateHtml($verse, 400);
    $comparelink = '';
    if($tst<2 && !$inapp)
      $comparelink = getothertranslationlink($row['title'], $row['chapter'], $row['verse'], 1);
    $notes = $row['editdetails'];
    // have to think about coloring the mnotes
    if($row['editnote']!=''){
      $editnote = str_replace('[br]', '<br />', $row['editnote']);
      $editnote = '<div id="pn_'.$nqry.'" class="peernote pnotedarr" style="cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$nqry.'\',1);">'.$editnote.'</div>';
    }else{
      $editnote = '<div id="pn_'.$nqry.'" class="peernote pnotedarr" style="display:none;cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$nqry.'\',1);"></div>';
    }
    $edtlink = '<a onclick="rlightbox(\'pnote\',\''.$nqry.'\');" title="edit"><img src="/i/peer_notes'.$colors[0].((strlen($notes??'')>0)?'_YELDOT':'').'.png" style="width:2em;float:left;margin-right:4px;" alt="edit" /></a> ';
    if(strlen($notes??'') > $notestruncatelen){
      $readmore = '<a onclick="rlightbox(\'pnote\',\''.$nqry.'\');" title="read more"> ..more</a> ';
      $notes = truncateHtml($notes, $notestruncatelen, '', false, true, $readmore);
      //* Tidy
      $tcfg = array(
                   'new-inline-tags'  => 'noparse',
                   'indent'           => false,
                   'output-xhtml'     => true,
                   'wrap'             => 99999,
                   'preserve-entities'=> 1,
                   'show-body-only'   => 1
                   );
      $tidy = new tidy;
      $tidy->parseString($notes, $tcfg, 'utf8');
      $tidy->cleanRepair();
      $notes = $tidy; //.$readmore;
    }
    if(substr($notes??'', 0, 3)=='<p>'){
      $notes = '<p class="spc" style="margin-top:0;">'.$edtlink.substr($notes,3);
      $edtlink='';
    }else if(strlen($notes??'')>0){
      $notes = '<div style="display:inline;">'.$edtlink.'</div>'.$notes;
      $edtlink='';
    }

    $notedate = rtrim(date('n/j/y g:ia', strtotime(converttouserdate($row['notedate'], $timezone))), 'm');
    $lastdate = rtrim(date('n/j/y g:ia', strtotime(converttouserdate($row['lastupdate'], $timezone))), 'm');
    $notedateinfo = (($notes=='')?'<br /><br />':'').'<span style="display:block;width:100%;font-size:70%;color:'.$colors[7].';">';
    $notedateinfo.= 'Entered by: '.$row['author'].' on '.$notedate.'<br />';
    $notedateinfo.= 'Updated by: <span id="edauthor_'.$nqry.'">'.$row['lastauthor'].' on '.$lastdate.'</span><br />';
    $notedateinfo.= 'Resolved: <span id="edresolved_'.$nqry.'" style="font-weight:bold;">'.(($row['resolved']==1)?'<span style="color:#5a5">Yes</span>':'<span style="color:#a33">No</span>').'</span></span>';

    print('<div class="divtd" style="width:49%;vertical-align:top;">'.$editnote.'<a href="'.$href.'" title="Go there" target="'.(($inapp)?'_self':'_blank').'">'.$sref.'</a> <div style="display:'.(($tst>1)?'inline-block;padding:0 4px;':'inline;').'" >'.$verse.'</div>'.$comparelink.'</div>');
    print('<div class="divtd" style="width:49%;vertical-align:top;"><div id="nt_'.$nqry.'" style="font-size:93%;">'.$notes.' '.$edtlink.'</div>'.$notedateinfo.'</div>');
    print('</div>'.crlf);

    $ni++;
  }
  print('</div>'); // myitems
  if($ni>0){
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
      print('There are no reviewer notes.');
    else
      print('Your search returned no results.');
    print('</div>');
  }
?>
<input type="hidden" name="mitm" value="<?=$mitm?>" />
<input type="hidden" name="page" value="<?=$page?>" />
<input type="hidden" name="test" value="<?=$test?>" />
<input type="hidden" name="book" value="<?=$book?>" />
<input type="hidden" name="chap" value="<?=$chap?>" />
<input type="hidden" name="vers" value="<?=$vers?>" />
<input type="hidden" name="vcnt" value="<?=$ni?>">
<input type="hidden" name="pagnum" value="<?=$pagnum?>" />
</form>
</div>

<script>

    window.onresize=function(){resizemyitemsstuff();};
    window.onload=function(){resizemyitemsstuff();};

    function resizemyitemsstuff(){
      $('myitemsheader').style.width = ($('myitems').offsetWidth)+'px';
    }

</script>

<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<script>
  var myrevid = <?=$myrevid?>;
  prfcommnewtab=1;

  findcomm.enablePopups = true;
  findcomm.remoteURL    = '<?=$jsonurl?>';
  findcomm.startNodeId  = 'view';
  addLoadEvent(findcomm.scan);

  findvers.startNodeId = 'view';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
  addLoadEvent(findvers.scan);

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

  function reloadpeernotes(qry){
    var notestruncatelen = 1300;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange=function(){
      if (xmlhttp.readyState==4 && xmlhttp.status==200){
        var ret = JSON.parse(xmlhttp.responseText);
        var note = ret.peernote;
        try{ // margin note
          var bndiv = $('pn_'+qry);
          bndiv.innerHTML = note.replace(/\[br\]/g, '<br />');
          bndiv.style.display = ((note!='')?'block':'none');
        }catch(e){}
        var detl = ret.peerdetails;
        var edtlink = '<a onclick="rlightbox(\'pnote\',\''+qry+'\');" title="edit"><img src="/i/peer_notes'+colors[0]+((detl.length>0)?'_YELDOT':'')+'.png" style="width:2em;float:left;margin-right:4px;" alt="edit" /></a>';
        // this sorta works
        if(detl.length > notestruncatelen) detl = truncateHTML(detl, notestruncatelen);
        if(detl.substring(0, 3)=='<p>'){
          detl = '<p class="spc" style="margin-top:0">'+edtlink+detl.substring(3);
          edtlink='';
        }else if(detl.length>0){
          detl = '<div style="display:inline;">'+edtlink+'</div>'+detl;
          edtlink='';
        }
        // not handling workspace...
        try{ // note details
          var rddiv = $('nt_'+qry);
          rddiv.innerHTML = edtlink+((detl.length==0)?'<br /><br />':'')+detl;
        }catch(e){}
        try{ // author/date
          var rddiv = $('edauthor_'+qry);
          rddiv.innerHTML = ret.author+' on '+ new Date(ret.lastupdate.replace(/-/g, "/")+' UTC').toLocaleString('en-US', {day: 'numeric', year: '2-digit', month: 'numeric', hour: 'numeric', minute: 'numeric'});
        }catch(e){}
        try{ // resolved
          var rddiv = $('edresolved_'+qry);
          rddiv.innerHTML = ((ret.resolved==1)?'<span style="color:#5a5">Yes</span>':'<span style="color:#a33">No</span>');
        }catch(e){}

        setTimeout('findcomm.scan();', 200);
        setTimeout('findvers.scan();', 500);
      }
    }
    xmlhttp.open('GET', '/jsonmyrevtasks.php?task=pdata&ref='+qry,true);
    xmlhttp.send();
  }

  function truncateHTML(text, length) { // javascript version
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
  function expandcollapsediv(id){
    excoldiv(id); // in misc.js
    var div = $(id);
    if(div.style.height=='0px'){
      $('moreless').innerHTML='&raquo;';
    }else{
      $('moreless').innerHTML='&laquo;';
    }
  }


</script>

<?
logview(312,0,0,0,0);

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
