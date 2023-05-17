<?php
if(!isset($page)) die('unauthorized access');


$pagination='';
$csrch='';
$startyear=0;
$finishyear=2000;
$baseyear=-3962;
$strbaseyear=abs($baseyear).'BC';
$pagitmcnt=50; // multiple of 10

// will process qs here..
$startyear=fixstartyear(((isset($arqs[1]))?$arqs[1]:$strbaseyear));
$evtsrch  =((isset($arqs[2]))?$arqs[2]:'~');
$evtonly  =((isset($arqs[3]))?$arqs[3]:0);
$evtcat   =((isset($arqs[4]))?$arqs[4]:0);
$evtfrst  =((substr($evtsrch,0,1)=='`')?1:0);
$evtsrch  = (($evtfrst==1)?substr($evtsrch,1):$evtsrch);

$csrch = (($evtsrch=='~')?'':str_replace('_', ' ', $evtsrch)); // search for text

if(1==2){
  print('<small>');
  print('qs: '.$qs.'<br />');
  print('startyear: '.$startyear.'<br />');
  print('evtsrch: >'.$evtsrch.'&lt;<br />');
  print('evtonly: '.$evtonly.'<br />');
  print('evtcat: '.$evtcat.'<br />');
  print('evtfrst: '.$evtfrst.'<br />');
  print('</small>');
}
?>
<style>
.tooltip {
  position: relative;
  cursor:pointer;
}

.tooltip .tooltiptext {
  visibility: hidden;
  width: 220px;
  background-color: <?=$colors[6]?>;
  color: <?=$colors[1]?>;
  text-align: center;
  border-radius: 6px;
  border:1px solid <?=$colors[1]?>;
  padding: 5px 0;
  position: absolute;
  z-index: 90;
  top: 8px;
  /*left: 140%;*/
  right: 15px;
  opacity: 0;
  transition: opacity .5s;
}
.tooltip .tooltiptext::after {
  content: "";
  position: absolute;
  top: 50%;
  left: 100%; /* To the right of the tooltip */
  margin-top: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: transparent transparent transparent black;
}

.tooltip:hover .tooltiptext {
  visibility: visible;
  opacity: 1;
}
</style>
<span class="pageheader">REV Bible Chronology</span>
<div id="chronology" style="margin:0 auto;max-width:720px;font-size:84%;">
<form name="frm" method="post" action="/">
<p style="text-align:center;margin-top:0;margin-bottom:3px;">
<a onclick="expandcollapsediv('resinst')">What is this? <span id="moreless">&raquo;</span></a>
<?//if($revch==0)
  //  print('<br /><span style="color:red;"><small>Please pardon our dust as we are still working on a few things.</small></span>');

?>
</p>
<div id="resinst" style="text-align:left;height:0;padding:3px;margin:0;overflow:hidden;transition:height .4s ease-in;">
  <h3 style="text-align:center;margin-top:3px;">Welcome to the REV Bible Chronology!</h3>
  <img src="/i/underconstruction.png" alt="under construction" style="width:104px;float:left;border:none;">
  <p>Please pardon our dust as we are currently working to update this section of the REV website. In the meantime, feel free to look around.</p>
  <p>This section of the REV website is an interactive chronology of Old and New Testament events as well as related extra-biblical events of interest.</p>
  <p>A complete set of instructions on how to navigate this chronology can be found at 3962 B.C.</p>
</div>
<?
  if($chronedit==1){
    print('<p style="text-align:center;margin-top:0;">');
    print('<small><span style="color:'.$colors[7].'">(Hi, '.$username.'. &nbsp; Show edit links <input type="checkbox" name="chronshowedit" value="1"'.fixchk($chronshowedit).' onclick="setSessionCookie(\'rev_chronedit\', ((this.checked)?1:0));location.reload();">)</span></small>');
    print('</p>');
  }
?>

<p style="text-align:center;margin-top:0;">
  <!--
  <small>Category</small> <select name="evtcat" id="evtcat" onchange="chronnav('cat', this.options[this.selectedIndex].value)">
    <option value="0"<?=fixsel(0, $evtcat)?> onchange="">-- all --</option>
    <?
      $sql = 'select catid, categoryname from category where cattype = 0 order by sqn, categoryname ';
      $cat = dbquery($sql);
      while($row = mysqli_fetch_array($cat)){
        print('<option value="'.$row[0].'"'.fixsel($row[0], $evtcat).'>'.$row[1].'</option>');
      }
    ?>
  </select>
  <?if($chronshowedit==1){?>
     <a onclick="olOpen('/categoryedit.php?cattype=0',480, 600)" title="edit categories"><img src="/i/edit.gif" style="width:13px;opacity:.30;" alt="edit" /></a>
  <?}?>
  <br />
  -->
  <input type="checkbox" name="evtonlyx" value="1"<?=fixchk($evtonly)?> onclick="evtonly=((this.checked)?1:0);chronnav('xxx',0);"> <small>Show only years with events</small><br />
  <input type="text" name="csrch" value="<?=$csrch?>" style="width:110px;" maxlength="12" onfocus="this.select();" autocomplete="off" onkeydown="handlekeydown();" />
  <input type="button" name="btncsrch" value="Go" style="padding:0 .1rem;" onclick="chronnav('sch', '`'+document.frm.csrch.value);">
  <input type="button" name="btnrst" value="Reset" style="padding:0 .1rem" onclick="location.href='/chronology';">
  </p>

<?

if($evtfrst==1){
  // get first beginyear of first event matching criteria
  $evtcatsql = (($evtcat==0 )?'':'and bibleyear in (select beginyear from chronevent where id in (select eventid from category_assoc where catid = '.$evtcat.')) ');
  $evtsrchsql= (($csrch=='') ?'':'and bibleyear > '.$baseyear.' and bibleyear in (select beginyear from chronevent where eventtitle like \'%'.$csrch.'%\' or longdesc like \'%'.$csrch.'%\') ');
  $evtonlsql = (($evtonly==0)?'':'and bibleyear in (select beginyear from chronevent) ');
  $sql = 'select bibleyear from chronology where bibleyear >= '.$baseyear.' '.$evtcatsql.$evtonlsql.$evtsrchsql.' order by bibleyear ';
  $row = rs($sql);
  if($row) $startyear = $row[0]; else $startyear = $finishyear; // not sure
}

// debug
if(1==2){
  print('<small>');
  print('startyear: '.$startyear.'<br />');
  print('evtsrch: >'.$evtsrch.'&lt;<br />');
  print('evtonly: '.$evtonly.'<br />');
  print('evtcat: '.$evtcat.'<br />');
  print('evtfrst: '.$evtfrst.'<br />');
  print('</small>');
}

$evtcatsql = (($evtcat==0 )?'':'and bibleyear in (select beginyear from chronevent where id in (select eventid from category_assoc where catid = '.$evtcat.'))' );
$evtsrchsql= (($csrch=='') ?'':'and bibleyear in (select beginyear from chronevent where eventtitle like \'%'.$csrch.'%\' or longdesc like \'%'.$csrch.'%\') ');
$evtonlsql = (($evtonly==0)?'':'and bibleyear in (select beginyear from chronevent where 1=1 '.$evtcatsql.$evtsrchsql.') ');

$sql = 'select bibleyear, ahyear, bumpbcad from chronology where bibleyear >= '.$startyear.' '.$evtonlsql.' order by bibleyear limit '.$pagitmcnt.' ';

if(1==2 && $userid==1) print('<small>'.$sql.'</small><br />');

$pagination = pagination($startyear);
print($pagination.(($screenwidth<480)?'<p>&nbsp;</p>':''));
print('<table style="width:100%;margin:0;padding:0;border-spacing:0;">');
$cho = dbquery($sql);
$ni=0;

while($row = mysqli_fetch_array($cho)){
  print(assembleyear($row, $ni));
}
if($ni==0) print('<tr><td style="text-align:center;color:red;">Sorry, no results found..</td></tr>');
print('</table>');
print($pagination);

?>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="oper" value="" />
</form>
</div>
<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
<script>
  findcomm.enablePopups = true;
  findcomm.remoteURL    = '<?=$jsonurl?>';
  findcomm.startNodeId  = 'chronology';
</script>

<script src="/includes/findbcom.min.js?v=<?=$fileversion?>"></script>
<script>
  findbcom.startNodeId  = 'chronology';
</script>

<script src="/includes/findapx.min.js?v=<?=$fileversion?>"></script>
<script>
  findappx.startNodeId = 'chronology';
  findappx.apxidx = [<?=loadapxids()?>];
</script>

<script src="/includes/findyear.min.js?v=<?=$fileversion?>"></script>
<script>
  findyear.startNodeId = 'chronology';
  //findyear.linkClassName = 'comlink0';
</script>

<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<script>
  findvers.startNodeId = 'chronology';
  findvers.remoteURL = '<?=$jsonurl?>';
  findvers.navigat = false;
</script>

<script>
  var startyear= '<?=$startyear?>';
  var csrch= '<?=$csrch?>';
  var evtonly = <?=$evtonly?>;
  var evtcat = <?=$evtcat?>;
  var pagitmcnt= <?=$pagitmcnt?>;
  var baseyear= <?=$baseyear?>;

  function chronnav(whch, val){
    switch(whch){
    case 'pag': // from paging navigation
      startyear = Math.floor(val/pagitmcnt)*pagitmcnt;
      if(startyear<baseyear) startyear = baseyear;
      break;
    case 'cat': // cat search, not using cats for now
      startyear = '<?=$strbaseyear?>';
      csrch = '`';
      evtcat = val;
      break;
    case 'yar': // used in findyear.js
      startyear = val.replace(/\s/g, '_');
      csrch = ''; // maybe `
      break;
    case 'sch':
      if(isyear(val)){
        startyear = val.replace(/\s/g, '_');
        //csrch = '';
      }else{
        csrch = val.replace(' ', '_');
        if(csrch=='') startyear = '<?=$strbaseyear?>';
      }
      break;
    }
    navstr = '/'+startyear+'/'+((csrch=='')?'~':csrch)+'/'+evtonly+'/'+evtcat;
    //alert(navstr);
    location.href='/chronology'+navstr;
  }

  function handlekeydown(e){
    var f = document.frm;
    e = e || window.event;
    if(e.keyCode==13){
      e.preventDefault();
      chronnav('sch', '`'+f.csrch.value);
    }
  }

  function isyear(yr){
    yr = yr.replace(/\W/g, '');
    yr = yr.replace(/\s/g, '_');
    yr = yr.toLowerCase();
    if(yr!='' && !isNaN(yr.substring(0,1)) && (yr.slice(-2)=='bc' || yr.slice(-2)=='ad' || yr.slice(-1)=='b' || yr.slice(-1)=='a'))
      return true;
    else return false;

  }

  addLoadEvent(findcomm.scan);
  addLoadEvent(findbcom.scan);
  addLoadEvent(findappx.scan);
  addLoadEvent(findvers.scan);
  addLoadEvent(findyear.scan);

  function expandcollapsediv(id){
    excoldiv(id); // in misc.js
    var div = $(id);
    if(div.style.height=='0px'){
      $('moreless').innerHTML='&raquo;';
    }else{
      $('moreless').innerHTML='&laquo;';
    }
  }

function showchronologycontent(id, typ){
  var txt='', lightbox=document.createElement('DIV');
  lightbox.id='lightbox';
  txt = '<div id="resdiv" style="position:relative;top:50%;transform:translateY(-50%);max-height:'+(window.innerHeight-50)+'px;min-height:'+(window.innerHeight-50)+'px;overflow-y:auto;width:90%;max-width:90%;left:0;right:0;margin:auto;padding:0 10px;color:'+colors[1]+';background-color:'+colors[2]+';border:1px solid '+colors[6]+';border-radius:8px;text-align:center;line-height:1.3em;">';
  txt+= '<div style="position:sticky;z-index:90;top:-1px;background-color:'+colors[2]+';padding:7px 0;">';
  txt+= '<span style="display:inline-block;width:10%;">&nbsp;</span><h3 style="display:inline-block;width:80%;text-align:center;margin:0;">Chronology Event Details</h3>';
  txt+= '<span style="display:inline-block;width:10%;text-align:right;cursor:pointer;" onclick="trashlightbox();"><img src="/i/redx.png" style="width:20px;" alt="" /></span>';
  txt+= '</div>';
  txt+= '<iframe name="cfrm" id="cfrm" style="width:100%;height:'+(window.innerHeight-150)+'px;border:none;margin:0;padding:0" src="/chronologycontent.php?id='+id+'&typ='+typ+'"></iframe>';
  txt+= '<p style="margin:9px 0;"><input type="button" class="gobackbutton" value="Close" onclick="trashlightbox();" /></p>';
  txt+= '</div>';

  lightbox.innerHTML = txt;
  document.body.appendChild(lightbox);
  lightbox.style.transition = 'opacity .7s';
  setTimeout('$(\'lightbox\').style.opacity=1', 1);
  return false;
}
function trashlightbox(){
  var lb = $('lightbox');
  lb.style.transition = 'opacity .3s';
  lb.style.opacity = 0;
  setTimeout('$(\'lightbox\').parentNode.removeChild($(\'lightbox\'))', 600);
}
</script>
<?
$cuk = processhistory($page.':'.$startyear.':0:0:0', 1);
print($cuk);

logview($page,0,0,0,0, fixselectyear($startyear));

function assembleyear($r, &$n){
  global $colors, $chronshowedit, $pagitmcnt;
  $borderstr = '1px solid '.$colors[3];
  $ret='';
  $byear = $r['bibleyear'];
  $bbcad = $r['bumpbcad'];
  $edityear = (($chronshowedit==1)?'<br /><a onclick="olOpen(\'/chronyearedit.php?edityear='.$byear.'\', 480, 600, 0);" title="Edit BC/AD offset"><img src="/i/edit.gif" style="width:13px;opacity:.30;" alt="Edit BC/AD Year" /></a>':'');
  if($byear!=0){
    $ret.= '<tr><td style="margin:0;padding:0;"><table style="height:4em;min-height:4em;border:0;border-spacing:0;">';
    $ret.= '<tr>';
    $ret.= '<td style="position:relative;width:70px;min-width:4em;padding:0;">';
    $ret.= '<div style="position:absolute;right:0;left:0;top:-'.($bbcad+3).'em;bottom:3em;font-size:90%;border-top:'.$borderstr.';border-left:'.$borderstr.';border-right:'.$borderstr.';">'.fixbyear($byear).$edityear.'</div>';
    $ret.='</td>';
    $ret.= '<td style="position:relative;font-size:90%;min-width:4.5em;vertical-align:top;padding:0;border-top:'.$borderstr.';border-right:'.$borderstr.';">'.$r['ahyear'].' AH</td>';
    $ret.= '<td style="width:90%;vertical-align:top;padding:0;border-top:'.$borderstr.';border-right:'.$borderstr.';">'.displayevents($byear).'</td>';
    $ret.= '</tr>';
    $ret.= '</table></td></tr>';
    $n++;
  }
  return $ret;
}

function displayevents($byear){
  global $chronshowedit, $csrch, $evtcat, $colors, $eventcolors, $ismobile;
  $evtsrchsql= (($csrch=='')?'':'and (eventtitle like \'%'.$csrch.'%\' or longdesc like \'%'.$csrch.'%\') ');
  $evtcatsql = (($evtcat==0)?'':'and (id in (select eventid from category_assoc where catid = '.$evtcat.')) ');
  $newevent = '<a onclick="olOpen(\'/chronologyedit.php?id=-1&beginyear='.$byear.'\', 999, 600, 1);" title="new event"><img src="/i/edit.gif" style="width:13px;opacity:.30;" alt="new event" /></a>';
  $sql = 'select id, beginyear, endyear, eventtitle, colorindex,
          tooltip, onebased, if(isnull(longdesc),0,1) longdesc, if(isnull(picfilnam), \'x\', picfilnam) picfilnam, piccaption
          from chronevent
          where ('.$byear.' between beginyear and endyear) '.$evtsrchsql.$evtcatsql.'
          order by beginyear, sqn ';
  $evts= dbquery($sql);
  $flagcount=0;
  $rowcount=0;
  $events = Array();
  while($row = mysqli_fetch_array($evts)){
    $events[] = $row;
  }
  $arsiz = sizeof($events);
  if($arsiz>0){ // we have events
    for($nj=0;$nj<$arsiz;$nj++){
      $events[$nj]['haveflag'] = (($byear==$events[$nj]['beginyear'])?1:0);
      $rowcount+= $events[$nj]['haveflag'];
    }
    $ret = crlf.'<table style="width:100%;border-spacing:0;">'.crlf;
    $lasttd='';
    for($nj=0;$nj<$arsiz;$nj++){
      if($nj==0 || $flagcount>0) $ret.= '<tr>';
      $evtid = $events[$nj]['id'];
      $evtcolor = $eventcolors[$events[$nj]['colorindex']];

      if($events[$nj]['haveflag']==1){ // event starts, show flag
        $ret.= '<td colspan="'.($arsiz-$nj).'" style="position:relative;width:100%;border-bottom:1px dotted '.$colors[7].';background-color:'.$evtcolor.';padding:2px 4px;">';
        $ret.= '<div style="position:relative;z-index:89;padding:1px;margin-right:-12px;">';
        if($events[$nj]['picfilnam']!='x'){
          if(file_exists($_SERVER['DOCUMENT_ROOT'].'/i/chronologyimages/'.$events[$nj]['picfilnam'])){
            $ret.= '<a onclick="return showchronologycontent('.$evtid.',\'pic\')" title="'.(($events[$nj]['piccaption']!='')?$events[$nj]['piccaption']:'Picture for chronology event').'"><img src="/i/chronologyimages/'.$events[$nj]['picfilnam'].'" style="width:80px;float:right;" alt="Picture" /></a>';
          }
        }
        $ret.= $events[$nj]['eventtitle'];
        if($events[$nj]['longdesc']==1) $ret.=' <a onclick="return showchronologycontent('.$evtid.',\'doc\')" title="More information"><img src="/i/assign.png" alt="More Information" style="width:1.2em;" /></a>';
        if($chronshowedit==1)
          $ret.= ' <a onclick="olOpen(\'/chronologyedit.php?id='.$evtid.'&beginyear='.$byear.'\', 999, 600, 1);" title="edit"><img src="/i/edit.gif" style="width:13px;opacity:.30;" alt="edit" /></a>';
        $ret.='</div>';
        $ret.='</td>';
        $flagcount++;
        $ret.= '<td rowspan="'.($rowcount-$flagcount+2).'" class="tooltip" style="height:3.7em;padding:2px 1px;'.(($nj>0)?'border-right:1px solid '.$colors[7].';':'').'background-color:'.$evtcolor.';"';
        if($events[$nj]['endyear']>$byear){
          $ret.= ' onclick="chronnav(\'sch\', \''.fixyearlink($events[$nj]['endyear']).'\')" title="End of event"';
        }
        $ret.= '>';
        if($events[$nj]['tooltip']!='')
          $ret.= '<span class="tooltiptext" style="background-color:'.$evtcolor.';">'.processtooltip($events[$nj]['tooltip'], $byear, $events[$nj]['beginyear'], $events[$nj]['endyear'], $events[$nj]['onebased']).'</span>';
        $ret.= '&nbsp;&nbsp;</td>'.(($flagcount==1)?'!~~~!':'');
      }else{
        $tmp = '<td rowspan="!!flags!!" class="tooltip" style="height:3.7em;padding:2px 1px;'.(($nj==0)?'':'border-right:1px solid '.$colors[7].';').'background-color:'.$evtcolor.';"'.((!$ismobile)?' onclick="chronnav(\'sch\',\''.fixyearlink($events[$nj]['beginyear']).'\')"':'').'>';
        if($events[$nj]['tooltip']!='')
          $tmp.= '<span class="tooltiptext" style="background-color:'.$evtcolor.';">'.processtooltip($events[$nj]['tooltip'], $byear, $events[$nj]['beginyear'], $events[$nj]['endyear'], $events[$nj]['onebased']).'</span>';
        $tmp.= '&nbsp;&nbsp;</td>';
        $lasttd = $tmp.$lasttd;
      }


      if($events[$nj]['haveflag']==1) $ret.= '</tr>'.crlf;
    }
    if($flagcount==0) $ret.= '<td style="width:100%;vertical-align:top;padding:0;border-right:1px solid '.$colors[7].';">&nbsp;'.(($chronshowedit==1)?$newevent:'').'</td>'.$lasttd.crlf;
    if($flagcount>0) $ret.=  '<tr style="height:100%;"><td style="border-right:1px solid '.$colors[7].';">&nbsp;'.(($chronshowedit==1)?$newevent:'').'</td>'.crlf;
    $ret = str_replace('!~~~!', $lasttd, $ret).'</tr>';
    $ret = str_replace('!!flags!!', ($flagcount+1), $ret);
    $ret.= '</table>'.crlf;
  }else $ret='&nbsp;'.(($chronshowedit==1)?$newevent:'');
  return $ret;
}

function processtooltip($tip, $cyr, $byr, $eyr, $obs){
  $ret = $tip;
  $adjust = (($cyr>0 && $byr<0)?-1:0);
  if(strpos($tip, '~~~')!==false) $ret = str_replace('~~~', abs(($cyr-$byr))+$obs+$adjust, $ret);
  if(strpos($tip, '---')!==false) $ret = str_replace('---', abs(($eyr-$cyr))-$adjust, $ret);
  if(strpos($tip, '!!!')!==false){
    $yr = abs(($cyr-$byr))+$obs+$adjust;
    $mod= $yr % 100;
    //if($yr<11 || $yr>13){
    if($mod<11 || $mod>13){
      switch(right($yr,1)){
      case '1': $tmp=$yr.'<sup>st</sup>';break;
      case '2': $tmp=$yr.'<sup>nd</sup>';break;
      case '3': $tmp=$yr.'<sup>rd</sup>';break;
      default : $tmp=$yr.'<sup>th</sup>';break;
      }
    }else $tmp=$yr.'<sup>th</sup>';
    $ret = str_replace('!!!', $tmp, $ret);
  }
  return $ret;
}

function fixbyear($yr){
  if($yr<0) $ret = abs($yr).' BC';
  else $ret = abs($yr).' AD';
  return $ret;
}
function fixyearlink($yr){
  if($yr<0) $ret = abs($yr).'bc';
  else $ret = abs($yr).'ad';
  return $ret;
}

function pagination($syr){
  global $colors, $pagitmcnt, $baseyear, $csrch, $evtcat, $evtonly, $finishyear;
  // need to change first, prev, next, last based on csrch, evtcat, evtonly
  // this will be fun..

  // first, assume $csrch, $evtcat, $evtonly are not set
  $lastyear = $finishyear-$pagitmcnt;
  $frstlink = (($syr>$baseyear)?' <a onclick="chronnav(\'pag\','.$baseyear.');">&laquo;first</a> ':' <span style="color:'.$colors[7].'">&laquo;first</span> ');
  $prevlink = (($syr>$baseyear)?' <a onclick="chronnav(\'pag\','.($syr-1).');">&laquo;prev</a> ':' <span style="color:'.$colors[7].'">&laquo;prev</span> ');
  $nextlink = (($syr<$lastyear)?' <a onclick="chronnav(\'pag\','.($syr+$pagitmcnt).');">next&raquo;</a> ':' <span style="color:'.$colors[7].'">next&raquo;</span> ');
  $lastlink = (($syr<$lastyear)?' <a onclick="chronnav(\'pag\', '.$lastyear.');">last&raquo;</a> ':' <span style="color:'.$colors[7].'">last&raquo;</span> ');

  if($csrch!='' || $evtcat>0 || $evtonly>0){
    $evtcatsql = (($evtcat==0 )?'':'and bibleyear in (select beginyear from chronevent where id in (select eventid from category_assoc where catid = '.$evtcat.')) ');
    $evtonlsql = (($evtonly==0)?'':'and bibleyear in (select beginyear from chronevent) ');
    $evtsrchsql= (($csrch=='') ?'':'and bibleyear in (select beginyear from chronevent where eventtitle like \'%'.$csrch.'%\' or longdesc like \'%'.$csrch.'%\') ');

    $sql = 'select ifnull(min(bibleyear),0), ifnull(max(bibleyear),0) from chronology where bibleyear >= '.$baseyear.' and bibleyear < '.$syr.' '.$evtcatsql.$evtonlsql.$evtsrchsql.' ';
    $row = rs($sql);
    $frstlink = (($row[0]!=0)?' <a onclick="chronnav(\'sch\',\''.fixyearlink($row[0]).'\');">&laquo;first</a> ':' <span style="color:'.$colors[7].'">&laquo;first</span> ');
    $prevlink = (($row[1]!=0)?' <a onclick="chronnav(\'sch\',\''.fixyearlink($row[1]).'\');">&laquo;prev</a> ':' <span style="color:'.$colors[7].'">&laquo;prev</span> ');

    $sql = 'select ifnull(min(bibleyear),0), ifnull(max(bibleyear),0) from chronology where bibleyear > '.$syr.' and bibleyear < '.$finishyear.' '.$evtcatsql.$evtonlsql.$evtsrchsql.' ';
    $row = rs($sql);
    $nextlink = (($row[0]!=0)?' <a onclick="chronnav(\'sch\',\''.fixyearlink($row[0]).'\');">next&raquo;</a> ':' <span style="color:'.$colors[7].'">next&raquo;</span> ');
    $lastlink = (($row[1]!=0)?' <a onclick="chronnav(\'sch\',\''.fixyearlink($row[1]).'\');">last&raquo;</a> ':' <span style="color:'.$colors[7].'">last&raquo;</span> ');
  }

  $ret='<div style="text-align:center;margin:4px auto;font-size:90%;">';
  $ret.= $frstlink;
  $ret.= $prevlink;
  $ret.= '<select onchange="chronnav(\'pag\','.$baseyear.'+(this.selectedIndex*'.$pagitmcnt.'));">';
  for($ni=(floor($baseyear/$pagitmcnt)*$pagitmcnt);$ni<$finishyear;$ni+=$pagitmcnt){
    $ret .= '<option'.fixsel($ni, floor($syr/$pagitmcnt)*$pagitmcnt).'>'.fixselectyear((($ni>$baseyear)?$ni:$baseyear)).'</option>';
  }
  $ret.= '</select>';
  $ret.= $nextlink;
  $ret.= $lastlink;
  $ret.= '</div>';
  return $ret;
}


function fixselectyear($ni){
  global $pagitmcnt;
  $yr = $ni;
  if($yr<0) $ret = abs($yr).' BC';
  else if($yr>0) $ret = abs($yr).' AD';
  else $ret='1 AD';
  return $ret;
}

function fixstartyear($yr){
  global $baseyear;
  if(is_numeric($yr)) return $yr;
  if(strtolower(right($yr, 2))=='bc' || strtolower(right($yr, 1))=='b'){
    // BC year
    $plusminus=-1;
  }else $plusminus=1;
  $ret = preg_replace('/\D/', '', $yr);
  if($ret=='') return $baseyear;
  return $plusminus * $ret;
}
?>

