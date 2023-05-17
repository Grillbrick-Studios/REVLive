<?php
if(empty($userid) || $userid==0) die('unauthorized access');
//ini_set('memory_limit','768M');     //

$goback = ((isset($_REQUEST['goback']))?$_REQUEST['goback']:1);
$btitle = getbooktitle($test,$book,0);
$stitle = 'Editing: '.$btitle;

$tocerror='';
$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';
if($oper=="savbk"){
  // get last bookfinal
  $row = rs('select bookfinalized from book where testament = '.$test.' and book = '.$book.' ');
  $lastbookfinal  = $row[0];

  // grab original commentary
  $row = rs('select ifnull(commentary, \'-\'), ifnull(comfootnotes, \'~~\') footnotes
            from book
            where testament = '.$test.'
            and book = '.$book.' ');
  $beforecomm = $row[0];
  $beforecomm = processsqlcomm($beforecomm, 1, 'no commentary');
  $beforecomm = replacgreekhtml($beforecomm);
  $beforecomm = substr($beforecomm, 1, strlen($beforecomm)-2);
  if($beforecomm === '-' || $beforecomm=='null') $beforecomm = '';
  $beforecomm = undoTOC($beforecomm);

  //$beforefoot = explode('~~', $row['footnotes'].'');
  $beforefoot = explode('~~', getfootnotes($test, $book, 0, 0, 'com').'');
  $beforefoot = processsqlfoot($beforefoot);
  if($beforefoot=='~~' || $beforefoot=='~~~~~~~~') $beforefoot='';

  $marker = '~~~';
  $edttitle   = processsqltext($_POST['edttitle'], 50, 0, 'missing book title!');
  $edttagline = processsqltext($_POST['edttagline'], 1000, 1, '');
  $edtabbr    = processsqltext($_POST['edtabbr'], 10, 0, '-');
  $bwabbr     = processsqltext($_POST['bwabbr'], 7, 1, '');
  $sqn        = processsqlnumb($_POST['sqn'], 99, 0, 0);
  $active     = processsqlnumb(((isset($_POST['active']))?$_POST['active']:0), 1, 0, 0);
  $outlinepub = processsqlnumb(((isset($_POST['outlinepub']))?$_POST['outlinepub']:0), 1, 0, 0);
  $outlinefinl= processsqlnumb(((isset($_POST['outlinefinl']))?$_POST['outlinefinl']:0), 1, 0, 0);
  $bookfinal  = processsqlnumb(((isset($_POST['bookfinal']))?$_POST['bookfinal']:0), 1, 0, 0);
  $whatsnew   = processsqlnumb(((isset($_POST['whatsnew']))?$_POST['whatsnew']:0), 1, 0, 0);
  $edtalias   = processsqltext($_POST['edtalias'], 500, 1, '');
  $metadesc   = processsqltext($_POST['metadesc'], 500, 1, '');
  $comfootnotes= processsqlfoot(((isset($_POST['comfootnote']))?$_POST['comfootnote']:''));
  $commentary = processsqlcomm($_POST['commentary'], 1, 'no commentary');

  $aftercomm = $commentary;
  if($aftercomm != 'null') $aftercomm = substr($commentary, 1, strlen($commentary)-2);
  if($aftercomm === '-' || $aftercomm=='null') $aftercomm = '';
  if($beforecomm==$aftercomm) $commdiff = null;
  else $commdiff = htmlDiff($beforecomm, $aftercomm);

  $afterfoot = $comfootnotes;
  if($beforefoot===$afterfoot || ($beforefoot.'~~')===$afterfoot) $footdiff = null;
  else $footdiff = htmlDiff($beforefoot, $afterfoot);

  $commdiff = str_replace('\\', '\\\\', $commdiff??'');
  $commdiff = str_replace('\'', '\\\'', $commdiff??'');
  $logcomment = '';
  if($bookfinal != $lastbookfinal){
    $logcomment = '!Book status changed to '.(($bookfinal==1)?'LOCKED':'UNLOCKED').'!';
  }

  $logid = logedit($page,$test,$book,$chap,$vers,$userid,$logcomment.((isset($_POST['comment']))?$_POST['comment'].' ':''), $whatsnew, null, $commdiff,null, str_replace('\'', '\\\'', $footdiff??''));

  $commentary = handleTOC($commentary);
  if($tocerror==''){
    $pos = strpos($commentary,$marker);
    // having name is required or ckeditor will remove the anchor
    if($pos!==false) $commentary = substr_replace($commentary, '<a id="marker'.$logid.'" name="marker'.$logid.'"></a>', $pos, strlen($marker));
    $commentary = str_replace('~~~', '', $commentary);
    $sql = 'update book set
            title = '.$edttitle.',
            tagline = '.$edttagline.',
            abbr = '.$edtabbr.',
            bwabbr = '.$bwabbr.',
            sqn = '.$sqn.',
            active = '.$active.',
            outlinepublished = '.$outlinepub.',
            outlinefinalized = '.$outlinefinl.',
            bookfinalized = '.$bookfinal.',
            aliases = '.$edtalias.',
            metadesc = '.$metadesc.',
            comfootnotes = null,
            commentary = '.$commentary.'
            where testament = '.$test.'
            and book = '.$book.' ';
    $update = dbquery($sql);
    savfootnotes($test, $book, 0, 0, 'com', $comfootnotes);

    if($sqlerr=='') $sqlerr = datsav;

    if(isset($_POST['chkclearlog'])){
      $clr = dbquery('delete from editlogs where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' and whatsnew=0 ');
    }
    if(isset($_POST['chkclearlogempty'])){
      $clr = dbquery('delete from editlogs where testament = '.$test.' and book = '.$book.' and chapter = '.$chap.' and verse = '.$vers.' and versdiff is null and commdiff is null and footdiff is null and (comment is null or comment = \'formatting\') ');
    }
  }else $sqlerr='NOT SAVED!: '.$tocerror;
  logview($page,$test,$book,$chap,$vers);
  $goback++;
}

$row = rs('select title, tagline, abbr, bwabbr, sqn, active, outlinepublished, outlinefinalized, bookfinalized, aliases, metadesc, commentary
          from book
          where testament = '.$test.'
          and book = '.$book.' ');
$edttitle  = $row['title'];
$edttagline= $row['tagline'];
$edtabbr   = $row['abbr'];
$bwabbr    = $row['bwabbr'];
$sqn       = $row['sqn'];
$active    = $row['active'];
$outlinepub= $row['outlinepublished'];
$outlinefinl=$row['outlinefinalized'];
$bookfinal = $row['bookfinalized'];
$edtalias  = $row['aliases'];
$metadesc  = $row['metadesc'];
$arcomfootnotes= explode('~~', getfootnotes($test, $book, 0, 0, 'com').'');
$commentary= $row['commentary'];
$commentary= preg_replace('#<br /> </li>#', '<br />&nbsp;</li>', $commentary??'');
$commentary = undoTOC($commentary);
?>
  <form name="frm" action="/" method="post">
  <span class="pageheader"><?=$stitle?></span>
  <table style="width:96%;font-size:90%;border-collapse:separate;border-spacing:5px;">
    <tr><td>&nbsp;<?=printsqlerr($sqlerr)?></td></tr>
    <tr>
      <td>Book title <small>(plain text only)</small><br />
        <input type="text" name="edttitle" size="60" value="<?=$edttitle?>" onchange="setdirt();">
      </td></tr>
    <tr>
      <td>Tagline <small>(plain text only)</small><br />
        <input type="text" name="edttagline" value="<?=$edttagline?>" size=60 onchange="setdirt();">
        <input type="hidden" name="active" value="<?=$active?>">
        <input type="hidden" name="sqn" value="<?=$sqn?>">
      </td></tr>
    <tr>
      <td>
        Outline: Published
        <input type="checkbox" name="outlinepub" value="1"<?=fixchk($outlinepub)?> onclick="setdirt();">
        &nbsp;Finalized
        <input type="checkbox" name="outlinefinl" value="1"<?=fixchk($outlinefinl)?> onclick="setdirt();">
      </td></tr>
    <tr>
      <td>
        Bible text locked
        <input type="checkbox" name="bookfinal" value="1"<?=fixchk($bookfinal)?> onclick="setdirt();">
        <span style="color:red;font-size:90%">If this is checked, the REV Bible text cannot be edited.</span>
      </td></tr>
<?if($superman){?>
    <tr>
      <td>Abbr
        <input type="text" name="edtabbr" size=5 value="<?=$edtabbr?>" onchange="setdirt();">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        BW Abbr
        <input type="text" name="bwabbr" value="<?=$bwabbr?>" size=5 onchange="setdirt();"><br />
      </td></tr>
    <tr>
      <td>Aliases
        <input type="text" name="edtalias" size=50 value="<?=$edtalias?>" onchange="setdirt();"><br />
      </td></tr>
<?}?>
    <tr>
      <td>Introduction
        <textarea name="commentary"><?=$commentary?></textarea>

        <span style="display: inline-block; margin-top:8px; font-style:italic">Footnotes</span>
        <!--<a onclick="$('divcomfootnotes').style.display='block';addfootnote('com',0);"><img src="/i/tbl_show.png" alt="" border="0" title="<?=(($arcomfootnotes[0]=='')?'add':'insert')?> footnote" /></a>-->
        <?if($arcomfootnotes[0]!=''){?>
        <a onclick="$('divcomfootnotes').style.display=($('divcomfootnotes').style.display=='none'?'block':'none');return false;"><small>show/hide</small></a>
        <?}?>
        &nbsp;&nbsp;&nbsp;<a onclick="olOpen('/resourcebyref.php?navstr=<?=$test.','.$book.',0,0'?>',<?=(($ismobile==1)?$screenwidth+20:600)?>, 600);" title="Assign Resources"><img src="/i/tv.png" width="16" /></a>
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
        <button onclick="history.go(-<?=$goback?>);return false;" style="text-align:center;font-size:80%">Back</button>
        <input type="submit" name="xc" value="Submit" onclick="return validate(document.frm);" style="text-align:center;font-size:80%" />&nbsp;&nbsp;
        <small>Comment <input type="text" id="txtcomment" name="comment" value="" size="60" maxlength="200" style="margin-top:2px">
        <a onclick=" doinput($('txtcomment'),'&ldquo;','&rdquo;');" title="click to insert smart quotes">&ldquo;&rdquo;</a>
        <input type="checkbox" name="whatsnew" value="1"> <small>Flag for "What's New"</small></small>
      </td></tr>
    <tr>
      <td style="padding-left:34px"><small>custom meta description</small>&nbsp;<input type="text" name="metadesc" value="<?=$metadesc?>" size="60" maxlength="500" style="margin-top:2px">
      </td></tr>
  </table>
<?
print(displayedits($page,$test,$book,$chap,$vers));
if($superman){
  print('<input type="checkbox" name="chkclearlog" value="1" onclick="setdirt()"><small>check to clear edit log.</small><br />');
  print('<input type="checkbox" name="chkclearlogempty" value="1" onclick="setdirt()"><small>check to clear empty logs.</small><br />');
}
if(!$superman){?>
    <input type="hidden" name="edtabbr" value="<?=$edtabbr?>">
    <input type="hidden" name="bwabbr" value="<?=$bwabbr?>">
    <input type="hidden" name="edtalias" value="<?=$edtalias?>">
<?}?>
  <input type="hidden" name="mitm" value="<?=$mitm?>" />
  <input type="hidden" name="page" value="6" />
  <input type="hidden" name="test" value="<?=$test?>" />
  <input type="hidden" name="book" value="<?=$book?>" />
  <input type="hidden" name="oper" value="" />
  <input type="hidden" name="dirt" value="0" />
  <input type="hidden" name="goback" value="<?=$goback?>" />
  </form>

  <script>
    var checkforchanges = true;

    function validate(f){
      if(!checkfootnotes('com', CKEDITOR.instances.commentary.getData())) return false;
      checkforchanges = false;
<?if($superman){?>
      if(f.chkclearlog.checked){
        if(!confirm('Are you sure you want to delete ALL the edit logs?')) return false;
      }
<?}?>
      f.oper.value = "savbk";
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

<? // this is php, not js
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
