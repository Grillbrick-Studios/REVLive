<?php
if(!isset($page)) die('unauthorized access');

//
// this is page 44, used for viewing the bibliography and abbreviations.
//
$bibtype = ((isset($_REQUEST['bibtype']))?$_REQUEST['bibtype']:(($chap==0)?0:1));

$stitle = 'Revised English Version';
print('<div id="pagetop"><button onclick="history.go(-goback);" class="gobackbutton" style="cursor:pointer;">Go Back</button>');
if($showpdflinks==1) print(getexportlinks('bib',$bibtype,0,1,0,0));

if($userid>0 && $appxedit==1){
  print(' <small>Edit:</small> <input type="checkbox" name="x12" id="x12" class="cbx" value="1" onclick="showedit(this.checked);"'.fixchk($edit).' />'.bibeditlink('elnk0',$showedit,-1,0));
}
print('</div>');

$bibnav='';
print('<h2 style="text-align:center;">'.(($bibtype==0)?'Abbreviations':'Bibliography').'</h2>');
if($bibtype==0)
  print('<h4 style="text-align:center;">Some commonly used abbreviations in the REV.</h4>');
else{
    $bibnav = '<p style="text-indent:0;font-size:120%;text-align:center;margin:6px 0 0 0;">';
    $bibnav2= '<p style="text-indent:0;font-size:120%;text-align:center;margin:6px 0 0 0;">';

    // get list of first letter of all entries
    $sql = 'select distinct substr(bibauthor, 1, 1) letter from bibliography where bibtype = 1 order by 1 ';
    $let = dbquery($sql);
    $str = '';
    while($row = mysqli_fetch_array($let)){
      $str.= strtoupper($row[0]);
    }
    $divide = (($ismobile && strlen($str)>12)?(int) (strlen($str)/2):99);

    // print nav menu
    for ($i=0; $i<strlen($str); $i++) {
      $chr = strtoupper(substr($str, $i, 1));
      if(preg_match('#[A-Z]#', $chr)){
        $bibnav .= '<a id="start_'.$chr.'" onclick="scrolltopos(this.id, \'dest_'.$chr.'\');">&nbsp;'.$chr.'&nbsp;</a> ';
        $bibnav2.= '<a id="start2_'.$chr.'" onclick="scrolltopos(this.id, \'dest_'.$chr.'\');">&nbsp;'.$chr.'&nbsp;</a> ';
      }
      if($i==$divide){
        $bibnav .= '<br />';
        $bibnav2.= '<br />';
      }
    }
    $bibnav .= '</p>';
    $bibnav2.= '</p>';
}
print($bibnav);

$sql = 'select bibid, bibauthor, bibtype, bibentry, flagged from bibliography
        where bibtype = '.$bibtype.'
        order by bibauthor ';
$lastnavletter='-';
$ni=1;
$qry = dbquery($sql);

$ret='<ul>';
//$ret.= '<em>Abbreviations</em>';
while($row = mysqli_fetch_array($qry)){
  $bibentry = $row['bibentry'];
  $bibentry = str_replace('[longdash]','<img src="/i/longdash.png" style="height:6px;width:60px;" alt="" />', $bibentry);
  $edtlink=(($userid>0 && $appxedit==1)?bibeditlink('elnk'.$ni,$showedit,$row['bibid'], $row['flagged']):'');
  $navletter = strtoupper(substr($row['bibauthor'],0,1));
  $navdest='';
  if($bibtype==1 && $navletter != $lastnavletter){
    $navdest = '<a id="dest_'.$navletter.'"></a>';
  }
  $ret.= '<li>'.$navdest.$edtlink.$bibentry.'</li>';
  $lastnavletter = $navletter;
  $ni++;
}
$ret.= '</ul>';

if($ni==0) $ret = '<p style="text-align:center;">Sorry, there is no bibliography data.</p>';

print($ret);

if($bibtype==1) print($bibnav2.'<br />&nbsp;');

?>
<div id="pagebot">
  <button onclick="history.go(-goback);" class="gobackbutton" style="cursor:pointer;">Go Back</button>
</div>

<script>

  var goback=1;

</script>
<?
print(processhistory($page.':0:0:'.$bibtype.':0:0', 1));
logview(210,0,0,$bibtype,0);

function bibeditlink($idx,$edt,$bid,$c){
  global $bibtype, $ismobile, $screenwidth;
  $ret = '<span id="'.$idx.'"  class="edtlink'.(($edt=='none')?'off':'on').'"><input type="image" src="/i/edit.gif" class="edtlinkon'.(($ismobile)?' edtlinkmob':' edtlinkpc').'" onclick="olOpen(\'/bibedit.php?bibid='.$bid.'&bibtype='.$bibtype.'\','.(($ismobile)?($screenwidth-50):800).', 500, 0);" alt="edit" />';
  if($c>0) $ret.= ' <img src="/i/flagedit.png" style="width:.8em;" />';
  $ret.='</span>';
  return $ret;
  //return '<input type="image" src="/i/edit.gif" id="'.$idx.'" class="edtlink'.(($edt=='none')?'off':'on').(($ismobile)?' edtlinkmob':' edtlinkpc').'" onclick="olOpen(\'/bibedit.php?bibid='.$bid.'&bibtype='.$bibtype.'\','.($screenwidth-100).', 500, 1);" alt="edit" />';
}



