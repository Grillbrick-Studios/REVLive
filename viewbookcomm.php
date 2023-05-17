<?php
if(!isset($page)) die('unauthorized access');

//
// this is page 10, used for viewing the commentary for a book.
//
$stitle = 'Revised English Version';
?>
<div id="pagetop">
  <?if($glogid==0 && $commnewtab==0){?>
  <button onclick="history.go(-goback);" class="gobackbutton">Go Back</button>
  <?}else{?>
  <button onclick="window.close();" class="gobackbutton">Close Tab</button>
  <?}?>
  <?if($userid>0 && $canedit==1){?>
  <button onclick="navigate(<?=$mitm?>,6,<?=$test?>,<?=$book?>,1,1);return false;" style="font-size:70%">Edit</button>
  <?}
  if($showpdflinks){
    print(getexportlinks('bcom',$test,$book,99,0,1));
  }
  ?>
</div>
<?
$showedit = (($edit==1)?'inline':'none');
if($test != -1){
  if($book != 0){
    $btitle = getbooktitle($test,$book,0);
    $arcomfn = array();
    $comfncnt= 0;
    $sql = 'select ifnull(tagline, title), ifnull(comfootnotes, \'\') comfootnotes, commentary from book where testament = '.$test.' and book = '.$book.' ';
    $row = rs($sql);
    if(!$row){
      print('NO DATA');
    }elseif($row[2]==null){
      print('<p style="text-align:center;">Sorry, there is no introduction for the book of '.$btitle).'.</p>';
    }else{
      print('<br /><h3>Introduction to '.$row[0].'</h3>');  // <-- this is a jerryism
      $comfootnotes= $row['comfootnotes'];
      $commentary = $row['commentary'];
      $commentary = nvl($commentary, "-");
      $commentary = preg_replace('#<p><strong>([^<]*?)</strong><br />#', '<h5 style="font-size:1em;font-weight:bold;margin-bottom:3px;margin-top:25px;">$1</h5><p style="margin-top:0;padding-top:0">', $commentary);

      // handle new footnotes
      $comfootnotes = getfootnotes($test, $book, 0, 0, 'com');
      //

      $commentary = processcommfordisplay($commentary, 0);
      $commentary = processcomfootnotes($arcomfn, $commentary, $comfootnotes, $comfncnt, 1);
      print($commentary);
      displaycomfootnotes($comfncnt, $arcomfn, 1);
      print(appendresources($test, $book, 0, 0));
    }
  }else{
    print('<br />NO BOOK!');
  }
}else{
  print('<br />NO TESTAMENT!');
}
logview($page,$test,$book,$chap,$vers);

?>
<div style="left:0;right:0;margin:auto;margin:8px 0;text-align:center;">
  <?if($glogid==0 && $commnewtab==0){?>
  <button onclick="history.go(-goback);" class="gobackbutton">Go Back</button>
  <?}else{?>
  <button onclick="window.close();" class="gobackbutton">Close Tab</button>
  <?}?>
  <?if($userid>0 && $canedit==1){?>
  <button onclick="navigate(<?=$mitm?>,6,<?=$test?>,<?=$book?>,1,1);return false;" style="font-size:70%">Edit</button>
  <?}?>
</div>

<script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
<script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
<script>
  findcomm.enablePopups = true;
  findcomm.remoteURL    = '<?=$jsonurl?>';
  findcomm.startNodeId = 'view';
</script>

<script src="/includes/findbcom.min.js?v=<?=$fileversion?>"></script>
<script>
  findbcom.startNodeId = 'view';
</script>

<script src="/includes/findapx.min.js?v=<?=$fileversion?>"></script>
<script>
  findappx.startNodeId = 'view';
  findappx.apxidx = [<?=loadapxids()?>];
</script>

<script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
<script>
  findvers.remoteURL    = '<?=$jsonurl?>';
  findvers.startNodeId = 'view';
  findvers.navigat = false;
</script>

<script src="/includes/findstrongs.min.js?v=<?=$fileversion?>"></script>
<script>
  findstrongs.startNodeId = 'view';
  findstrongs.ignoreTags.push('noparse');
  findstrongs.lexicon = prflexicon;
</script>

<script src="/includes/findwordstudy.min.js?v=<?=$fileversion?>"></script>
<script>
  findwordstudy.startNodeId = 'view';
</script>

<script>
  addLoadEvent(findcomm.scan);
  addLoadEvent(findbcom.scan);
  addLoadEvent(findappx.scan);
  addLoadEvent(findvers.scan);
  addLoadEvent(findstrongs.scan);
  addLoadEvent(findwordstudy.scan);

  var goback = 1;
  var toffset = 0; // used for TOCs
  <?if($glogid>1){?>
  setTimeout('scrolltopos(\'toptop\',\'marker<?=$glogid?>\')', 300);
  <?}?>
</script>


