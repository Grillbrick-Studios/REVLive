<?php

$sort=((isset($_REQUEST['sort']))?$_REQUEST['sort']:0);
$stitle = 'STF Resources';
$filter=((isset($_REQUEST['filter']))?$_REQUEST['filter']:'');
$filterrt=((isset($_REQUEST['filterrt']))?$_REQUEST['filterrt']:0);
$filterpl=((isset($_REQUEST['filterpl']))?$_REQUEST['filterpl']:(($test==-1)?$book:0));
$factive=((isset($_REQUEST['factive']))?$_REQUEST['factive']:1);   // default to active only
$linkedonly=((isset($_REQUEST['linkedonly']))?$_REQUEST['linkedonly']:0);
$ffinalized=((isset($_REQUEST['ffinalized']))?$_REQUEST['ffinalized']:1);
$libcat=((isset($_REQUEST['libcat']))?$_REQUEST['libcat']:0);
//print($filterpl);

if($userid>0 && $resedit==1 && left($filter, 1)=='~') $resshowedit=1;
if($resshowedit==0) $factive=1;

$filter=((isset($_REQUEST['filter']))?$_REQUEST['filter']:'');
$filter = trim(strip_tags($filter));
$filter = str_replace(';','',$filter);
//$filter = str_replace(':','',$filter);
$filter = str_replace('>','',$filter);
$filter = str_replace('<','',$filter);
$filter = str_replace('\'','',$filter);
$filter = str_replace('"','',$filter);

?>
<span class="pageheader"><?=$stitle?></span>
<div style="width:100%;max-width:720px;text-align:center;padding:0;margin:0 auto;font-size:96%;">
<form name="frm" method="post" action="/" onsubmit="validate(this);">
<a onclick="expandcollapsediv('resinst')">What is this? <span id="moreless">&raquo;</span></a>
  <div id="resinst" style="text-align:left;height:0;padding:3px;margin:0;overflow:hidden;transition:height .4s ease-in;">
    <h3 style="text-align:center;">Welcome to STF Resources!</h3>
    <img src="/i/underconstruction.png" alt="under construction" style="border:0;width:104px;float:left;">
    <p>Please pardon our dust as we are currently working to update the resource titles and descriptions. In the meantime, feel free to look around.</p>
    <p>This section of the REV contains a list of all Spirit &amp; Truth Fellowship (STF) video and audio teachings from Youtube and Podbean. There are over 1500 titles.
    You can browse the entire list, or you can filter the resources displayed by type or playlist.
    You can also search for specific words by typing into the search text box and clicking &ldquo;Go&rdquo; or pressing [ENTER].
    For example, if you want to locate resources having to do with resurrection, you would type &ldquo;resurrection&rdquo; into the box and hit [ENTER].</p>
    <p style="margin-bottom:0;">You can watch or listen to the videos and audios right here, or there are links to where they are located on Youtube, Podbean, or Castos.</p>
  </div>
<?
  if($resedit==1){
     print('<small><span style="color:'.$colors[7].'">(Hi, '.$username.'. &nbsp; Edit <input type="checkbox" name="resshowedit" value="1"'.fixchk($resshowedit).' onclick="setSessionCookie(\'rev_resedit\', ((this.checked)?1:0));document.frm.submit();">');
     if($resshowedit==1){
       print(' | <a onclick="gotopage(document.frm,-1,37)">new</a>');
       print(' | ref&rsquo;d <input type="checkbox" name="linkedonly" value="1"'.fixchk($linkedonly).' onclick="clearall();">');
       print(' | actv <select name="factive" onchange="clearall()">');
       print('<option value="0"'.fixsel(0, $factive).'> - </option>');
       print('<option value="1"'.fixsel(1, $factive).'>yes</option>');
       print('<option value="2"'.fixsel(2, $factive).'>no</option>');
       print('</select>');
       print(' | finl <select name="ffinalized" onchange="clearall()">');
       print('<option value="0"'.fixsel(0, $ffinalized).'> - </option>');
       print('<option value="1"'.fixsel(1, $ffinalized).'>yes</option>');
       print('<option value="2"'.fixsel(2, $ffinalized).'>no</option>');
       print('</select>');
     }
     print(')</span></small>');
  }
?>

  <br />
  <div style="display:inline-block;">Resource Type
  <select name="filterrt" onchange="try{document.frm.filterpl.selectedIndex=0}catch{};try{document.frm.libcat.selectedIndex=0}catch{};dopage(1);" style="margin:8px 0;">
    <option value="0"<?=fixsel(0, $filterrt);?>>all</option>
<?
  $sql = 'select resourcetype, count(*)
          from resource
          where 1=1 '.
          (($factive==1)?'and active = 1 ':(($factive==2)?'and active = 0 ':'')).
          (($ffinalized==0)?'':(($resedit==0 || $ffinalized==1)?'and finalized = 1 ':'and finalized = 0 ')).'
        group by resourcetype
          order by 1 ';
  $res = dbquery($sql);
  while($row = mysqli_fetch_array($res)){
    switch($row[0]){
    case 1: print('<option value="1"'.fixsel(1, $filterrt).'>Youtube ('.$row[1].')</option>');break;
    case 2: print('<option value="2"'.fixsel(2, $filterrt).'>MP4 ('.$row[1].')</option>');break;
    case 3: print('<option value="3"'.fixsel(3, $filterrt).'>Audio ('.$row[1].')</option>');break;
    case 4: print('<option value="4"'.fixsel(4, $filterrt).'>Seminars ('.$row[1].')</option>');break;
    case 5: print('<option value="5"'.fixsel(5, $filterrt).'>Article ('.$row[1].')</option>');break;
    case 6: print('<option value="6"'.fixsel(6, $filterrt).'>Book Excerpt ('.$row[1].')</option>');break;
    case 7: print('<option value="7"'.fixsel(7, $filterrt).'>Library ('.$row[1].')</option>');break;
    }
  }

$filterstr = '';
if($filter!==''){
  if(left($filter, 1)=='~'){ // look for resourceid
    $filter = substr($filter, 1);
    $filterstr.= 'and r.resourceid= \''.$filter.'\' ';
    $filter='~'.$filter;
  }else if(left($filter, 1)=='!'){ // search source
    $filter = substr($filter, 1);
    $filter = str_replace('!', '', $filter);
    $filterstr.= 'and (r.source like \''.$filter.'%\') ';
    $filter='!'.$filter;
  }else if(left($filter, 1)=='='){ // exact phrase
    $filter = substr($filter, 1);
    $filter = str_replace('=', '', $filter);
    $filterstr.= 'and (r.title like \'%'.$filter.'%\' or r.description like \'%'.$filter.'%\' or r.keywords like \'%'.$filter.'%\') ';
    $filter='='.$filter;
  }else{
    $parts = explode(' ', $filter);
    $filter = ' '.$filter.' ';
    for($ni=0;$ni<sizeof($parts);$ni++){
      if(strlen($parts[$ni]) > 1){
        $filterstr.= 'and (r.title like \'%'.$parts[$ni].'%\' or r.description like \'%'.$parts[$ni].'%\' or r.keywords like \'%'.$parts[$ni].'%\') ';
      }else{
        $filter = str_replace(' '.$parts[$ni].' ', ' ', $filter);
      }
    }
    $filter = trim($filter);
  }
}

$arwnblog   = explode(';', (isset($_COOKIE['rev_wnblog']))?$_COOKIE['rev_wnblog']:((time()-(3*86400)).';'.(time()-(3*86400)).';'.(time()-(3*86400))));
if(sizeof($arwnblog)==2) array_push($arwnblog, (time()-(3*86400)));
$arwnblog[2] = time();
$arwnblog = join(';', $arwnblog);

?>
  </select>&nbsp;</div><br />
  <div style="display:inline-block;">Search
  <input type="text" name="filter" value="<?=$filter?>" size="20" maxlength="24" autocomplete="off" onfocus="this.select();" />
  <input type="submit" name="btnsrch" value="Go" /></div><br />

<?
if($filterrt!=6){ // no playlists for Excerpts (6)
?>
  <div style="display:inline-block;">Playlist
  <select name="filterpl" onchange="document.frm.filter.value='';dopage(1);" style="margin:8px 0;max-width:<?=($screenwidth-40)?>px;">
    <option value="0"<?=fixsel(0, $filterpl);?>>- none -</option>
<?

  switch($filterrt){
  case 1: // youtube
  case 2: // MP4
    $plwhere = 'and pl.pltypeid in (1,2) ';break;
  case 3: // Podbean or Castos
    $plwhere = 'and pl.pltypeid=3 ';break;
  case 4: // Seminar
    $plwhere = 'and pl.pltypeid=5 ';break;
  case 5: // article
    $plwhere = 'and pl.pltypeid=4 ';break;
  case 7: // library
    $plwhere = 'and pl.pltypeid=7 ';break;
  default:
    $plwhere = '';break;
  }
  
  // need to exclude inactive and infinalized
  $sql = 'select pl.playlistid, pl.pltypeid, pl.playlisttitle, 
          (select count(*) from resource r 
            where 1=1 '. 
            (($factive==1)?'and r.active = 1 ':(($factive==2)?'and r.active = 0 ':'')).
            (($ffinalized==0)?'':(($resedit==0 || $ffinalized==1)?'and r.finalized = 1 ':'and r.finalized = 0 ')).
            'and r.playlistid in (select playlistid from playlist pl2 where pl2.playlistid = pl.playlistid)) plcnt 
          from playlist pl where 1=1 '.$plwhere.'order by pl.pltypeid, pl.sqn, pl.playlisttitle ';
  //print($sql);        
  $ply = dbquery($sql);
  $lastplt=0;
  while($row = mysqli_fetch_array($ply)){
    if($row['pltypeid'] !== $lastplt){
      switch($row['pltypeid']){
      case 1: $lbl='Youtube Playlists';break;
      case 3: $lbl='Audio Playlists';break;
      case 4: $lbl='Article Playlists';break;
      case 5: $lbl='Seminars';break;
      case 7: $lbl='Library';break;
      default: $lbl = 'unknown';break;
      };
      print('<optgroup label="'.$lbl.'" style="background-color:#ddd"></optgroup>');
      $lastplt = $row['pltypeid'];
    }
    print('<option value="'.$row[0].'"'.fixsel($row[0], $filterpl).'>'.$row['playlisttitle'].' ('.$row['plcnt'].')</option>');
  }

print('</select>');
if($resedit==1 && $resshowedit==1)
  print(' <a onclick="gotopage(document.frm,0,38);"><img src="/i/edit.gif" width="14" alt="edit" /></a>');
print('</div><br />');
}
if($filterrt==7){
?>
    Library Category
    <select name="libcat" id="libcat" onchange="try{document.frm.filterpl.selectedIndex=0}catch{};dopage(1);" style="margin:8px 0;">
    <option value="0"<?=fixsel(0, $libcat)?> onchange="">-- all --</option>
    <?
        $sql = 'select catid, categoryname from category where cattype = 1 order by sqn, categoryname ';
        $cat = dbquery($sql);
        while($row = mysqli_fetch_array($cat)){
          print('<option value="'.$row[0].'"'.fixsel($row[0], $libcat).'>'.$row[1].'</option>');
        }
    ?>
    </select>
    <?if($resshowedit==1){?>
       <a onclick="olOpen('/categoryedit.php?cattype=1',480, 600)" title="edit categories"><img src="/i/edit.gif" style="width:13px;opacity:.30;" alt="edit" /></a>
    <?}?>
    <br />
<?
}

//print('>>'.$filter.'&lt;&lt; filterstr: '.$filterstr.'&lt;&lt;');

$where = 'where 1=1 '.
       (($factive==1)?'and active = 1 ':(($factive==2)?'and active = 0 ':'')).
       (($filterrt>0)?'and r.resourcetype = \''.$filterrt.'\' ':' ').
       $filterstr.
       (($filterpl>0)?'and r.playlistid = '.$filterpl.' ':'').
       (($linkedonly==1)?'and resourceid in (select resourceid from resourceassign) ':'').
       (($ffinalized==0)?'':(($resedit==0 || $ffinalized==1)?'and finalized = 1 ':'and finalized = 0 ')).
       (($libcat>0)?'and resourceid in (select eventid from category_assoc where catid = '.$libcat.') ':'');

$pagnum    = ((isset($_REQUEST['pagnum']))?$_REQUEST['pagnum']:1);
$pagitmcnt = 10;
$sql = 'select count(*) from resource r '.$where.' ';
$row = rs($sql);
$rsss= $row[0];
$pagtot = ceil($rsss/$pagitmcnt);
$limit  = 'limit '.(($pagnum-1)*$pagitmcnt).', '.$pagitmcnt.' ';

$pagination = pagination($pagnum, $pagitmcnt, $pagtot);
if($pagtot>0){
  print($pagination);
}else print('<p style="text-align:center;"><span style="color:red">Sorry, no results found.</span></p>');

$olopenwidth = ((($screenwidth-20)>600)?600:$screenwidth-20);
$vidinitsiz = (($screenwidth<420)?160:210);

if($filterpl>0){
  $sql = 'select playlisttitle, description, pltypeid, ifnull(thumbnail, \'\') thumbnail from playlist where playlistid = '.$filterpl.' ';
  $rpl = rs($sql);
  print('<table class="gridtable" style="width:100%;text-align:left;margin:0 0 28px 0;"><tr><td colspan="2" style="padding:0;">');
  if($rpl['thumbnail']!='')
    print('<img src="'.$rpl['thumbnail'].'" style="width:'.$vidinitsiz.'px;max-width:100%;float:left;margin:3px;" alt="playlist thumbnail" />');
  else
    print('<img src="/i/stf_audio.png" style="width:60px;max-width:100%;float:left;margin:0 4px 1px 0;" alt="resource thumbnail" />');// break;

  $strplid = (($resedit==1 && $resshowedit==1)?' <span style="font-size:80%;color:'.$colors[7].';">(ID: '.$filterpl.')</span>':'');

  print('<p style="margin-top:0;text-indent:0;">Playlist: <strong>'.$rpl['playlisttitle'].'</strong>'.$strplid.'</p>'.$rpl['description'].'</td></tr></table>');
}

$sql = 'select r.publishedon, r.resourceid, r.resourcetype, r.title, r.description, r.resviews,
       r.source, r.playlistid, r.keywords, r.identifier, r.externalurl, r.duration,
       ifnull(r.thumbnail, \'nopic\') thumbnail, r.active, r.finalized, r.editcomment, r.edituserid, r.content
       from resource r '.$where.' ';
$sql.= (($filterpl>0)?'order by playlistsqn ':'order by 1 desc, 2 desc ');
$sql.= $limit;
//print('<tr><td colspan="2">'.$sql.'</td></td>');

$res = dbquery($sql);
$ni=0;
while($row = mysqli_fetch_array($res)){
  print(assembleresource($row, $ni, $page));
  $ni++;
}
if($pagtot>0) print($pagination);

?>

  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="pagnum" value="<?=$pagnum?>" />
  <input type="hidden" name="resourceid" value="" />
  <input type="hidden" name="sort" value="<?=$sort?>" />
  <input type="hidden" name="oper" value="" />
</form>
</div>

<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<!-- this is for the scripture references in the descriptions -->
<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<script>
  findvers.startNodeId = 'view';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
  findvers.ignoreTags = ['h1','h2','h3','h4', 'noparse'];
  addLoadEvent(findvers.scan);

  function dopage(pnum){
    document.frm.pagnum.value=pnum;
    document.frm.submit();
  }

  function gotopage(f, id, pg){
    f.page.value=pg;
    f.resourceid.value=id;
    f.submit();
  }

  function clearall(){
    var f = document.frm;
    f.filter.value='';
    f.filterrt.selectedIndex=0;
    try{f.filterpl.selectedIndex=0}catch{};
    f.pagnum.value=1;
    f.submit();
  }
  function validate(f){
    var srch = f.filter.value;
    srch = srch.replace(/</gi,'');
    srch = srch.replace(/>/gi,'');
    srch = srch.replace(/'/gi,'');
    srch = srch.replace(/"/gi,'');
    //srch = srch.replace(/:/gi,'');
    srch = srch.replace(/;/gi,'');
    srch = srch.replace(/\//gi,'');
    srch = srch.replace(/&/gi,'');
    f.filter.value = srch;
    f.pagnum.value=1;
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
  var goback=0; // necessary for resource display
  setCookie('rev_wnblog', '<?=$arwnblog?>', cookieexpiredays);

</script>

<?
  logview($page,0,0,0,0);

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

function fixrow($r){
  $t = $r[0];
  $b = $r[1];
  $c = $r[2];
  $v = $r[3];
  $ret=getbooktitle($t,$b, 0);
  $href='/'.$ret;
  if($t<2 && $c>0){$ret.=' '.$c.':'.$v;$href.='/'.$c.'/'.$v.'/1';}
  if($t<2 && $c==0){$ret.=' Bk Cmtry';$href='/book'.$href.'/ct';}
  if($t==2){$href='/info/'.$b.'/ct';}
  if($t==3){$href='/appx/'.$b.'/ct';}
  if($t==4){$ret='WordStudy: '.$ret;$href='/word'.$href.'/ct';}
  $ret='<a href="'.$href.'" target="_blank">'.$ret.'</a>';
  return $ret;
}

