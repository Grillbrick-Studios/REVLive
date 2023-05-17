<?php
if(empty($userid) || $userid==0) die('unauthorized access');
if($appxedit==0) die('Sorry, you do not have access to edit appendices or word studies');

ini_set('memory_limit','256M'); // needed for the older htmlDiff()

$goback = ((isset($_REQUEST['goback']))?$_REQUEST['goback']:1);
$btitle = getbooktitle($test,$book,0);
$stitle = 'Editing: '.$btitle;
$loc = $myrevid.'|'.$test.'|'.$book.'|'.$chap.'|'.$vers;

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
$tocerror='';
if($oper=="savapxintro"){

  // grab original commentary and footnotes
  $row = rs('select ifnull(commentary, \'-\'), ifnull(comfootnotes, \'~~\') footnotes
            from verse
            where testament = '.$test.'
            and book = '.$book.'
            and chapter = '.$chap.'
            and verse = '.$vers.' ');
  $beforecomm = $row[0];
  $beforecomm = processsqlcomm($beforecomm, 1, 'no commentary');
  $beforecomm = replacgreekhtml($beforecomm);
  if($beforecomm!='null') $beforecomm = substr($beforecomm, 1, strlen($beforecomm)-2);
  if($beforecomm === '-' || $beforecomm=='null') $beforecomm = '';
  $beforecomm = undoTOC($beforecomm);

  $beforefoot = explode('~~', $row['footnotes'].'');
  $beforefoot = processsqlfoot($beforefoot);
  if($beforefoot=='~~' || $beforefoot=='~~~~~~~~') $beforefoot='';

  $marker = '~~~';
  $msg='';
  $edttitle    = processsqltext($_POST['edttitle'], 50, 0, 'missing book title!');
  $edttagline  = processsqltext($_POST['edttagline'], 1000, 1, '');
  $edtabbr     = processsqltext('-', 1, 0, '-');
  $bwabbr      = processsqltext('-', 1, 0, '-');
  $active      = processsqlnumb(((isset($_POST['active']))?$_POST['active']:0), 1, 0, 0);
  $wscatid     = processsqlnumb(((isset($_POST['wscatid']))?$_POST['wscatid']:0), 99, 0, 0);
  $whatsnew    = processsqlnumb(((isset($_POST['whatsnew']))?$_POST['whatsnew']:0), 1, 0, 0);
  $metadesc    = processsqltext($_POST['metadesc'], 500, 1, '');
  $comfootnotes= processsqlfoot(((isset($_POST['comfootnote']))?$_POST['comfootnote']:''));
  $commentary  = processsqlcomm($_POST['commentary'], 1, 'no commentary');

  $aftercomm = $commentary;
  if($aftercomm != 'null') $aftercomm = substr($commentary, 1, strlen($commentary)-2);
  if($aftercomm === '-' || $aftercomm=='null') $aftercomm = '';
  if($beforecomm===$aftercomm) $commdiff = null;
  else $commdiff = htmlDiff($beforecomm, $aftercomm);
  $commdiff = str_replace('\\', '\\\\', $commdiff??'');
  $commdiff = str_replace('\'', '\\\'', $commdiff??'');
  if(strlen($commdiff)==0) $commdiff = null;

  $afterfoot = $comfootnotes;
  if($beforefoot===$afterfoot || ($beforefoot.'~~')===$afterfoot) $footdiff = null;
  else $footdiff = htmlDiff(str_replace('~~', ' ~~ ', $beforefoot), str_replace('~~', ' ~~ ', $afterfoot));
  if(trim($footdiff??'')==='') $footdiff=null;

  if(!is_null($commdiff) || !is_null($footdiff) || $_POST['comment']!='')
    $logid = logedit($page,$test,$book,$chap,$vers,$userid,isset($_POST['comment'])?$_POST['comment']:'', $whatsnew, null, $commdiff,null, str_replace('\'', '\\\'', $footdiff??''));

  $commentary = handleTOC($commentary);

  if($tocerror==''){
    // handle whatsnew marker
    $pos = strpos($commentary,$marker);
    if($pos!==false) $commentary = substr_replace($commentary, '<a id="marker'.$logid.'" name="marker'.$logid.'"></a>', $pos, strlen($marker));
    $commentary = str_replace('~~~', '', $commentary);

    $sql = 'update book set
            title = '.$edttitle.',
            tagline = '.$edttagline.',
            abbr = '.$edtabbr.',
            bwabbr = '.$bwabbr.',
            active = '.$active.',
            wscatid = '.$wscatid.',
            commentary = null
            where testament = '.$test.'
            and book = '.$book.' ';
    //print($sql);
    $update = dbquery($sql);
    $msg.= $sqlerr;$sqlerr = '';
    $sql = 'update verse set
            heading = null,
            versetext = \'-\',
            paragraph = 0,
            microheading = 0,
            footnotes = \'~~\',
            metadesc = '.$metadesc.',
            comfootnotes = null,
            commentary = '.$commentary.'
            where testament = '.$test.'
            and book = '.$book.'
            and chapter = 1
            and verse = 1 ';
    $update = dbquery($sql);
    savfootnotes($test, $book, 1, 1, 'com', $comfootnotes);

    $msg.= $sqlerr;$sqlerr = '';
    $sqlerr = $msg;
    if($sqlerr=='') $sqlerr = datsav.'&nbsp;&nbsp;';

  }else $sqlerr = 'NOT SAVED!: '.$tocerror;
  logview($page,$test,$book,$chap,$vers);
  $goback++;
}

$row = rs('select b.title, b.tagline, b.active, b.wscatid, v.metadesc, v.commentary
          from book b
          inner join verse v on (v.testament = b.testament and b.book = v.book)
          where b.testament = '.$test.'
          and b.book = '.$book.' ');
$edttitle  = $row['title'];
$edttagline= $row['tagline'];
$active    = $row['active'];
$wscatid   = $row['wscatid'];
$metadesc  = $row['metadesc'];
$arcomfootnotes= explode('~~', getfootnotes($test, $book, 1, 1, 'com'));
$commentary= $row['commentary'];
$commentary= preg_replace('#<br /> </li>#', '<br />&nbsp;</li>', $commentary);
$commentary = undoTOC($commentary);
$urlprefix = (($test==2)?'info':(($test==3)?'appx':'word'));

if($myrevid>0){
  $sql = 'select highlight, ifnull(myrevnotes, \'-\') myrevnotes
          from myrevdata
          where myrevid = '.$myrevid.' and testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
  $row = rs($sql);
  if($row){
    $hlite = $row[0];
    $myrevnotes = $row[1];
  }else $myrevnotes='-';
  $nonote=(($myrevnotes=='-')?1:0);
  $myrevbutton = '&nbsp; <a onclick="rlightbox(\'note\',\''.$loc.'\',1);" title="Edit MyREV note"><img id="myr_'.$loc.'" src="/i/myrev_notes'.$colors[0].(($nonote==0)?'_DOT':'').'.png" style="width:1.2em;margin-bottom:-3px;" alt="edit" /></a>';
}else
  $myrevbutton = '';

if($peernotes>0){
  $sql = 'select ifnull(editnote, \'\') editnote, if(length(ifnull(editdetails, \'\'))>0,1,0), resolved
          from peernotes
          where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
  $row = rs($sql);
  if($row){
    if($row['resolved']==0 || $ednotesshowall==1)
      $peernote = (($row['resolved']==1)?'&reg; ':'').str_replace('[br]', '<br />', $row[0]);
    else
      $peernote = '';
    $havedetail = $row[1];
  }else{
    $peernote = '';
    $havedetail = 0;
  }
  $havepnote = (($row)?1:0);
  $sql = 'select ifnull(peerworknotes, \'\') workspace
          from myrevusers
          where myrevid = '.$myrevid.' ';
  $row = rs($sql);
  $havepeerwork = (($row[0]!='')?1:0);
  $peernotebutton = '&nbsp; <a onclick="rlightbox(\'pnote\',\''.$loc.'\',1);" title="Edit Reviewer note"><img id="pnn_'.$loc.'" src="/i/peer_notes'.$colors[0].(($havedetail==1)?'_YELDOT':'').'.png" style="width:1.2em;margin-bottom:-3px;" alt="edit" /></a>';
  $peernotebutton.= '&nbsp; <a onclick="rlightbox(\'pnote\',\'-1|0|0|0|0\',1);" title="Reviewer workspace"><img id="pnw_-1|0|0|0|0" src="/i/peer_workspace'.$colors[0].(($havepeerwork==1)?'_YELDOT':'').'.png" style="width:1.2em;margin-bottom:-3px;" alt="edit" /></a>';
  $peernotedisplay= '&nbsp; <span id="pn_'.$loc.'" class="peernote" style="display:'.(($peernote=='' || $viewpeernotes==0)?'none':'inline-block').';margin-left:3px;cursor:pointer;" onclick="rlightbox(\'pnote\',\''.$loc.'\',1);">'.$peernote.'</span>';
}else{
  $peernotebutton = '';
  $peernotedisplay='';
}

if($editorcomments==1){
  $sql = 'select ifnull(editnote, \'\') editnote, if(length(ifnull(editdetails, \'\'))>0,1,0), resolved
          from editnotes
          where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' ';
  $row = rs($sql);
  if($row){
    if($row['resolved']==0 || $ednotesshowall==1)
      $editnote = (($row['resolved']==1)?'&reg; ':'').str_replace('[br]', '<br />', $row[0]);
    else
      $editnote = '';
    $havedetail = $row[1];
  }else{
    $editnote = '';
    $havedetail = 0;
  }
  $haveenote = (($row)?1:0);
  $sql = 'select ifnull(notes, \'\') workspace
          from myrevusers
          where myrevid = -1 ';
  $row = rs($sql);
  $havewks = (($row[0]!='')?1:0);
  $editorbutton = '&nbsp; <a onclick="rlightbox(\'enote\',\''.$loc.'\',1);" title="Edit Editor note"><img id="edn_'.$loc.'" src="/i/editor_notes'.$colors[0].(($havedetail==1)?'_REDDOT':'').'.png" style="width:1.2em;margin-bottom:-3px;" alt="edit" /></a>';
  $editorbutton.= '&nbsp; <a onclick="rlightbox(\'enote\',\'-1|0|0|0|0\',1);" title="Editor workspace"><img id="edw_'.$loc.'" src="/i/editor_workspace'.$colors[0].(($havewks==1)?'_REDDOT':'').'.png" style="width:1.2em;margin-bottom:-3px;" alt="edit" /></a>';
  $editorbutton.= $peernotedisplay.'<span id="bn_'.$loc.'" class="editnote" style="display:'.(($editnote=='' || $viewedcomments==0)?'none':'inline-block').';margin-left:3px;cursor:pointer;" onclick="rlightbox(\'enote\',\''.$loc.'\',1);">'.$editnote.'</span>';
}else
  $editorbutton = '';
?>
<form name="frm" action="/" method="post">

  <span class="pageheader"><?=$stitle?></span>
  <table style="width:96%;font-size:90%;border-collapse:separate;border-spacing:5px;">
    <tr><td><?=printsqlerr($sqlerr).'<small><a href="/'.$urlprefix.'/'.$book.'">Back to '.$btitle.'</a></small>'?></td></tr>
    <tr>
      <td><?=(($test==2)?'Introduction title':(($test==3)?'Appendix title':'Study name'))?> <small>(no HTML)</small><br />
        <input type="text" name="edttitle" size="60" value="<?=$edttitle?>" onchange="setdirt();">
      </td></tr>
    <?if($test!=4){?>
    <tr>
      <td>Tagline <small>(plain text only)</small><br />
        <input type="text" name="edttagline" size="60" value="<?=$edttagline?>" onchange="setdirt();">
      </td></tr>
      <?}else{?>
      <tr><td><input type="hidden" name="edttagline" value="">
        Study Category <select name="wscatid" id="wscatid">
          <option value="0"<?=fixsel($wscatid, 0)?>>None</option>
          <?
            $sql = 'select wscatid, wscat from wscats order by sqn ';
            $cats = dbquery($sql);
            while($row = mysqli_fetch_array($cats)){
              print('<option value="'.$row[0].'"'.fixsel($wscatid, $row[0]).'>'.$row[1].'</option>');
            }
          ?>
        </select>
        <small>&nbsp; <a onclick="olOpen('/wscats.php',<?=(($ismobile==1)?$screenwidth+20:600)?>, 600);" title="Manage WS Cats"><img src="/i/edit.gif" /></a> <span style="color:red;"><small>(do this first)</small></span>
        </small>
        </td></tr>
      <?}?>
    <tr>
      <td>
        Public
        <input type="checkbox" name="active" value="1"<?=fixchk($active)?> onclick="setdirt();">
        <span style="color:red;font-size:60%;">NOTICE: Public means it can be browsed to. If this article is not public, it still may be in use.</span>
      </td></tr>
    <tr>
      <td>Content<?=$myrevbutton.$peernotebutton.$editorbutton?>
        <textarea name="commentary"><?=$commentary?></textarea>
        <span style="display: inline-block; margin-top:8px; font-style:italic">Footnotes</span>
        <!--<a onclick="$('divcomfootnotes').style.display='block';addfootnote('com',0);"><img src="/i/tbl_show.png" alt="" border="0" title="<?=(($arcomfootnotes[0]=='')?'add':'insert')?> footnote" /></a>-->
        <?if($arcomfootnotes[0]!=''){?>
        <a onclick="$('divcomfootnotes').style.display=($('divcomfootnotes').style.display=='none'?'block':'none');return false;"><small>show/hide</small></a>
        <?}?>
        &nbsp;&nbsp;&nbsp;<a onclick="olOpen('/resourcebyref.php?navstr=<?=$navstring?>',<?=(($ismobile==1)?$screenwidth+20:600)?>, 600);" title="Assign Resources"><img src="/i/tv.png" width="16" /></a>
        <br />
        <div id="divcomfootnotes" style="display:none;">
          <div id="comfootnotes">
          <?
          for($ni=0, $size=count($arcomfootnotes);$ni<$size;$ni++){
            if($arcomfootnotes[$ni] != ''){?>
            <div>&nbsp;
              <!--<a id="comaddf<?=$ni?>" onclick="addfootnote('com',<?=($ni+1)?>);"><img src="/i/tbl_show.png" alt="" title="add footnote after" /></a>-->
              <a id="comdelf<?=$ni?>" onclick="removefootnote('com',<?=($ni)?>);"><img src="/i/tbl_hide.png" alt="" title="delete this footnote" /></a>
              <!--<img src="/i/mnu_menu<?=$colors[0]?>.png" class="bmksorthandle" style="margin-bottom:-3px;width:1em;cursor:ns-resize;" alt="" />-->
              <span id="comfidx<?=$ni?>" class="fnidx"><?=($ni+1)?></span>
              <input type="text" name="comfootnote[]" style="width:<?=(($ismobile)?'65':'77')?>%; margin-top:2px;" value="<?=$arcomfootnotes[$ni]?>" onchange="setdirt()" autocomplete="off" />
              <a id="comquof<?=$ni?>" onclick="appendquotes('com',<?=$ni?>);" title="click to append smart quotes">&ldquo;&rdquo;</a>
              <a id="comemsf<?=$ni?>" onclick="appendems('com',<?=$ni?>);" title="click to append emphasize &lt;em> tags"><small>em</small></a>
            </div>
          <?}}?>
          </div><br />
        </div>
        <br />
        <button id="btnback" onclick="history.go(-<?=$goback?>);return false;" style="text-align:center;font-size:80%">Back</button>
        <input type="submit" name="btnsbmt" id="btnsbmt" value="Submit" onclick="return validate(document.frm);" style="text-align:center;font-size:80%" />&nbsp;&nbsp;
        <small>Comment <input type="text" id="txtcomment" name="comment" value="" size="60" maxlength="200" style="margin-top:2px">
        <a onclick="doinput($('txtcomment'),'&ldquo;','&rdquo;');" title="click to insert smart quotes">&ldquo;&rdquo;</a>
        <input type="checkbox" name="whatsnew" value="1"> <small>Flag for "What's New"</small></small>
      </td></tr>
    <tr>
      <td style="padding-left:34px"><small>custom meta description</small>&nbsp;<input type="text" name="metadesc" value="<?=$metadesc?>" size="60" maxlength="500" style="margin-top:2px">
      </td></tr>
  </table>
  <div style="text-align:center;margin:0 auto;">
<?=displayedits($page,$test,$book,$chap,$vers)?>
  </div>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="<?=$page?>" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="chap" value="<?=$chap?>" />
  <input type="hidden" name="vers" value="<?=$vers?>" />
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="dirt" value="0" />
  <input type="hidden" name="goback" value="<?=$goback?>" />
  </form>
  <script>
    var checkforchanges = true;

    function validate(f){
      if(!checkfootnotes('com', CKEDITOR.instances.commentary.getData())) return false;
      $('btnsbmt').value = 'Pls wait..';
      setTimeout('$(\'btnsbmt\').disabled=true', 200);
      setTimeout('$(\'btnback\').disabled=true', 200);
      checkforchanges = false;
      f.oper.value = "savapxintro";
      return true;
    }
    function checkdirt(){
      if(!checkforchanges) return;
      var f = document.frm;
      var dirty = 0;
      if(f.dirt.value==1) dirty = 1;
      for (var i in CKEDITOR.instances) {
        if(CKEDITOR.instances[i].checkDirty()) dirty = 1;
      }
      if(dirty == 1)return 'You have unsaved changes.\nIf you continue those changes will be LOST.';
      else return;
    }

    function setdirt(){
      var f = document.frm;
      f.dirt.value = 1;
    }

    function reloadmyrevnotes(qry){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var mrimg = $('myr_'+qry);
          var img = '/i/myrev_notes'+colors[0]+((ret.myrevnotes)?'_DOT':'')+'.png';
          mrimg.src = img;
        }
      }
      xmlhttp.open('GET', '/jsonmyrevtasks.php?task=data&ref='+qry,true);
      xmlhttp.send();
    }

    function reloadeditnotes(qry){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var resolved = ret.resolved;
          try{ // to catch if user edited workspace
            var mrimg = $('edn_'+qry);
            var img = '/i/editor_notes'+colors[0]+((ret.editdetails)?'_REDDOT':'')+'.png';
            mrimg.src = img;
            var bnspan = $('bn_'+qry);
            bnspan.innerHTML = ret.editnote;
            bnspan.style.display = ((ret.editnote=='' || resolved==1)?'none':'inline-block');
          }catch(e){}
        }
      }
      xmlhttp.open('GET', '/jsonmyrevtasks.php?task=edata&ref='+qry,true);
      xmlhttp.send();
    }

    function reloadpeernotes(qry){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var resolved = ret.resolved;
          try{ // to catch if user edited workspace
            if(qry!='-1|0|0|0|0'){ // if not workspace
              var mrimg = $('pnn_'+qry);
              var img = '/i/peer_notes'+colors[0]+((ret.peerdetails)?'_YELDOT':'')+'.png';
              mrimg.src = img;
              var bnspan = $('pn_'+qry);
              bnspan.innerHTML = ret.peernote;
              bnspan.style.display = ((ret.editnote=='' || resolved==1)?'none':'inline-block');
            }else{
              var mrimg = $('pnw_'+qry);
              var img = '/i/peer_workspace'+colors[0]+((ret.peerwork==1)?'_YELDOT':'')+'.png';
              mrimg.src = img;
            }
          }catch(e){}
        }
      }
      xmlhttp.open('GET', '/jsonmyrevtasks.php?task=pdata&ref='+qry,true);
      xmlhttp.send();
    }



    window.onbeforeunload = checkdirt;

    //
    // Used if an editor is viewing the page
    // They can manage their flags
    //
    function handleflag(logid, ni, donone){
      var xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
          var ret = JSON.parse(xmlhttp.responseText);
          var don = ret.donone;
          var flg = ret.flagged;
          //alert('don: '+don);
          //alert('flg: '+flg);
          if(don==1){
            $('td'+ni).style.backgroundColor=colors[6];
          }else{
            if($('td'+ni).innerHTML.includes('None'))
              $('td'+ni).style.backgroundColor=colors[6];
            var td = $('eflag'+ni);
            var tdc= ((flg==0)?'transparent':'#ffdddd');
            td.style.backgroundColor=tdc;
            var fg = $('iflag'+ni);
            var sfg= ((flg==0)?'30':'100');
            fg.style.opacity=sfg+'%';
          }
        }
      }
      var qs = '?id='+logid+'&doocmt=0&donone='+donone;
      //alert(qs);
      xmlhttp.open('GET','/jsonflagedit.php'+qs,true);
      xmlhttp.send();
    }

    //
    // making footnotes sortable
    //
    function makesortable(pre){
      var myfnsort = new Sortable(document.getElementById(pre+'footnotes'), {
        animation: 150,
        handle: '.bmksorthandle',
        direction: 'vertical',
        touchStartThreshold: 5,
        onEnd: function (evt) {
          var oldidx = evt.oldIndex;
          var newidx = evt.newIndex;
          if(newidx > oldidx){
            // moving down
            fnreindex(pre, oldidx, newidx, 0);
            for(var i=newidx;i>oldidx;i--){fnreindex(pre, i, i-1, 0);}
          }else{
            // moving up
            for(var i=oldidx-1;i>=newidx;i--){fnreindex(pre, i, i+1, 0);}
            fnreindex(pre, oldidx, newidx, 0);
          }
          renumfns(pre);
        },
      });
    }

    //setTimeout('makesortable("com");', 500);

  </script>
  <script src="/ckeditor/ckeditor.js?v=<?=$fileversion?>"></script>
  <?require_once $docroot.'/includes/commentaryeditor.php';?>

<?  // this is php, not js
   function processsqlfoot($fns){
     if(is_null($fns) || !isset($fns) || empty($fns) || $fns=='' || $fns[0]=='')
       return '';
     else{
       $ret='';
       for($ni=0, $size=count($fns); $ni<$size;$ni++){
         $ret.=fixfoot($fns[$ni]);
       }
       return substr($ret, 0, strlen($ret)-2);
     }
   }

   function fixfoot($ftn){
     $ret = $ftn;
     $ret = str_replace('<i>', '<em>', $ret);
     $ret = str_replace('</i>', '</em>', $ret);
     $ret = trim(strip_tags($ret.'', '<em>'));  // allow <em>s
     $ret = preg_replace('#"+#', '', $ret); // remove double quotes
     $ret = str_replace("'", "&rsquo;", $ret);
     // I should not have to do this...
     $ret = str_replace("“", "&ldquo;", $ret);
     $ret = str_replace("”", "&rdquo;", $ret);
     $ret = str_replace("‘", "&lsquo;", $ret);
     $ret = str_replace("’", "&rsquo;", $ret);
     if($ret=='') $ret = 'missing footnote';
     $ret.= '~~';
     return $ret;
   }

?>
