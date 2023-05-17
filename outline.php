<?php
if(!isset($page)) die('unauthorized access');

//
// this is page 24, used for viewing a book outline.
//
$stitle = 'Revised English Version';
?>
<div id="pagetop">
  <?if($glogid==0 && $commnewtab==0){?>
  <button onclick="history.go(-goback);" class="gobackbutton">Go Back</button>
  <?}else{?>
  <button onclick="window.close();" class="gobackbutton">Close Tab</button>
  <?}?>
  <?if($userid>0 && ($canedit==1 || $appxedit==1)){?>
  <button onclick="olOpen('/manageheadings.php?test=<?=$test?>&book=<?=$book?>&chap=1','<?=$screenwidth-100?>', 500,1);" style="font-size:70%">Edit</button>
  <?}
  $babbr = getbooktitle($test,$book,1);
  $sql = 'select ifnull(tagline,concat(\'The Book of \', title)) tagline, outlinepublished, outlinefinalized from book where testament = '.$test.' and book = '.$book.' ';
  $roww = rs($sql);
  $btitle = $roww['tagline'];
  $outlinepub= $roww['outlinepublished'];
  $outlinefinl= $roww['outlinefinalized'];
  if($showpdflinks && ($outlinepub==1 || $canedit==1 || $appxedit==1)){
    print(getexportlinks('outl',$test,$book,1,0,1));
  }
  ?>
</div>
<?

print('<h3 style="text-align:center;">Outline for '.$btitle.'</h3>');
if($outlinepub==0 && $canedit==0 && $appxedit==0){
  print('<p style="text-align:center;">Sorry, there is no outline data for this book yet.</p>');
  exit();
}
if($outlinepub==0 && ($canedit==1 || $appxedit==1)){
    print('<p style="text-align:center;color:red;font-size:80%;">Not published..</p>');
}
if($outlinefinl==0 && ($canedit==1 || $appxedit==1)){
    print('<p style="text-align:center;color:red;font-size:80%;">Not finalized..</p>');
}

$sql = 'select chapter, verse, level, heading, reference, link from outline
        where testament = '.$test.' and book = '.$book.' and inoutline=1
        order by chapter, verse, level ';
$lastlvl=0;
$ni=0;
$qry = dbquery($sql);

$ret= crlf.'<ol style="display:table;margin:0 auto;max-width:500px;">';
while($row = mysqli_fetch_array($qry)){
  $lvl = $row['level'];
  if($lastlvl==1 && $lvl==0) $ret.= '</ol></li>';
  if($lastlvl==0 && $lvl==1) $ret.= '<ol type="A">';
  $heading = str_replace('~','',$row['heading']);
  $heading = str_replace('[br][br]','[br]',$heading);
  $heading = str_replace('[br]','<br />',$heading);
  if($row['link']==1){
    $heading = '<a href="/'.str_replace(' ', '_', $babbr).'/'.$row['chapter'].'/'.(($outlinepub==1)?'head':'nav').$row['verse'].((!$inapp && ($commnewtab==1 || $outlinepub==0))?'/ct" target="_blank"':'"').'>'.$heading.'</a>';
  }
  //$ret.= '<li>'.$heading.' <small>('.$babbr.' '.$row['reference'].')</small>';
  $ret.= crlf.'<li>'.$heading.' <small>('.$row['reference'].')</small>';
  $lastlvl = $lvl;
  $ni++;
}
$ret.= crlf.'</ol>';
if($lastlvl==1) $ret.= '</ol>';

if($ni==0) $ret = '<p style="text-align:center;">Sorry, there is no outline data for this book yet.</p>';

print($ret);

logview($page,$test,$book,$chap,$vers);

?>
<div id="pagebot">
  <?if($glogid==0 && $commnewtab==0){?>
  <button onclick="history.go(-goback);" class="gobackbutton">Go Back</button>
  <?}else{?>
  <button onclick="window.close();" class="gobackbutton">Close Tab</button>
  <?}?>
</div>

<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<script>
  findvers.remoteURL    = '<?=$jsonurl?>';
  findvers.startNodeId = 'view';
  findvers.navigat = false;

  addLoadEvent(findvers.scan);
  var goback=1;

</script>


