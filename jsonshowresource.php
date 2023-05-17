<?
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functionsjson.php";

$resourceid = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['id']))?$_REQUEST['id']:-1));

getpreferences();

$ret = '';
$sql = 'select resourcetype, title, identifier, description, externalurl, thumbnail, source
        from resource
        where resourceid = '.$resourceid.' ';
$row = rs($sql);
if($row){
  $mtitle = $row['title'];
  switch($row['resourcetype']){
  case 1: // youtube
    $ret.= '<p style="text-indent:0">Title: <strong>&ldquo;'.$mtitle.'&rdquo;</strong></p>';
    $ret.= '<div style="width:100%;max-width:720px;text-align:left;overflow:auto;font-size:90%;">';
    $ret.= '<span class="video-wrapper"><span class="video-container">'.
           '<iframe width="640" height="385" src="https://www.youtube.com/embed/'.$row['identifier'].'?rel=0" allowfullscreen frameborder="0"></iframe>'.
           '</span></span>';
    $ret.= ((left($row['description'], 3)=='<p>')?'<p style="margin-top:4px">'.substr($row['description'], 3):$row['description']);
    $ret.= '<p style="text-indent:0"><a href="https://www.youtube.com/watch?v='.$row['identifier'].'&rel=0" target="_blank">Watch on Youtube</a> <img src="/i/popout'.$colors[0].'.png" style="width:18px" alt="popout" /></p>';

    $ret.= '</div>';
    break;
  case 2: // mp4
    $ret.= '<p>Video teaching: <strong>&ldquo;'.$mtitle.'&rdquo;</strong></p>';
    $ret.= '<p><video style="width:94% !important;max-width:720px;height:auto !important" controls="controls"><source src="'.$row['identifier'].'" type="video/mp4">'.
           'Your browser does not support the video tag.</video></p>';
    $ret.= '<div style="width:100%;max-width:720px;text-align:left;padding:0;font-size:90%;">';
    $ret.= $row['description'];
    $ret.= '</div>';
    break;
  case 3:
  case 4: // audio
    $ret.= '<p style="text-indent:0">Title: <strong>&ldquo;'.$mtitle.'&rdquo;</strong></p>';
    $ret.= '<div style="width:100%;max-width:720px;text-align:left;padding:0;font-size:90%;">';
    $ret.= '<p style="text-align:center;"><audio id="aplayerx" controls="controls" src="'.$row['identifier'].'">Sorry, your browser does not support the player. Follow <a href="'.$row['identifier'].'" target="_blank">this link</a>.</audio></p>';
    $ret.= '<a onclick="toggleplaypause(\'x\','.$resourceid.');"><img id="iplayerx" src="/i/stf_audio_play.png" style="float:left;width:74px;margin:0 5px 0 0;" alt="thumbnail" /></a>';
    $ret.= $row['description'];
    if($row['externalurl']!==null)
      $ret.= '<p style="text-indent:0;"><a href="'.$row['externalurl'].'" target="_blank">Listen on '.$row['source'].'</a> <img src="/i/popout'.$colors[0].'.png" style="width:18px" alt="popout" /></p>';
    $ret.= '</div>';
    break;
  case 5: // article
    $ret.= '<p>Article: <strong>&ldquo;'.$mtitle.'&rdquo;</strong></p>';
    $ret.= '<div style="width:100%;max-width:720px;text-align:left;padding:0;font-size:90%;">';
    if($row['thumbnail']!==null)
      $ret.= '<img src="'.$row['thumbnail'].'" style="float:left;width:200px;margin:0 5px 0 0;" alt="thumbnail" />';
    $ret.= $row['description'];
    if($row['externalurl']!==null)
      $ret.= '<p style="margin-bottom:0;"><a href="'.$row['externalurl'].'" target="_blank">View the article</a>  <img src="/i/popout'.$colors[0].'.png" style="width:18px" alt="popout" /></p>';
    $ret.= '</div>';
    break;
  case 6: // book excerpt
    $ret.= 'working on it.';
    break;
  }
  $sql = 'update resource set resviews = resviews+1 where resourceid = '.$resourceid.' ';
  $update = dbquery($sql);
}else $ret.= 'no content for resourceid: '.$resourceid;
print($ret);

mysqli_close($db);
?>

