<?php
if(!isset($page)) die('unauthorized access');

//
// Here, $test is the identifier for blogs in general: 9
// $book holds the blogid
// I SHOULD have made the blogs another testament, like appx's, info's, and ws's
// ..but I didn't
//

$stitle = 'REV Blog';

if($inapp)
  $back ='<button onclick="history.go(-1);" class="gobackbutton">Go Back</button>';
else
  $back='<button onclick="window.close();" class="gobackbutton">Close Tab</button>';
?>
<div style="margin:0 auto;text-align:center;"><span class="pageheader"><?=$stitle?></span><?=$back?></div>
<?
$showedit = (($edit==1)?'inline':'none');
if($book != -1){
  $sql = 'select blogdate, blogtitle, blogtext, active from revblog where blogid = '.$book.' ';
  $row = rs($sql);
  if($row){
    $active = $row['active'];
    if($userid==0 && $active==0){
      print('Blog entry not found!');
    }else{
      print('<h3>'.$row['blogtitle']);
      if($userid>0 && $canedit==1) print(editlink('elnk0',$showedit,$mitm,27,9,$book,0,0).(($active)?'':notifynotpublished));
      print('</h3><i>Posted on: '.converttouserdate($row['blogdate'], $timezone).'</i><br />');
      $commentary = $row['blogtext'];
      $commentary = (($commentary)?$commentary:'No Content!');
      $commentary = preg_replace('#<br /> </li>#', '<br />&nbsp;</li>', $commentary);
      print($commentary).'<br />';
      print('<div style="margin:0 auto;text-align:center;">'.$back.'</div>');
      // these are being stored, but not viewed anywhere.
      logview($page,$test,$book,$chap,$vers);
    }
  }else{
    print('Blog entry not found!');
  }
}else{
  mainmenu();
}

?>

  <script src="/includes/bbooks.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/findcomm.min.js?v=<?=$fileversion?>"></script>
  <script>
    findcomm.enablePopups = true;
    findcomm.remoteURL    = '<?=$jsonurl?>';
    findcomm.startNodeId = 'view';
  </script>

  <script src="/includes/findapx.min.js?v=<?=$fileversion?>"></script>
  <script>
    findappx.startNodeId = 'view';
    findappx.apxidx = [<?=loadapxids()?>];
  </script>

  <script src="/includes/findvers.min.js?v=<?=$fileversion?>"></script>
  <script>
    findvers.startNodeId = 'view';
    findvers.remoteURL = '<?=$jsonurl?>';
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

    addLoadEvent(findcomm.scan);
    addLoadEvent(findappx.scan);
    addLoadEvent(findvers.scan);
    addLoadEvent(findstrongs.scan);
    addLoadEvent(findwordstudy.scan);
  </script>

