<?php
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";


$test = 1;
$book = 40;
$chap = 27;
$vers = 33;

$goback = ((isset($_REQUEST['gobackx']))?$_REQUEST['gobackx']:1);
$btitle = getbooktitle($test,$book,0);
$stitle = 'Editing: <a onclick="navsavehref(\'/'.str_replace(' ','-',$btitle).'/'.$chap.'/'.$vers.'/c/fe\');" class="comlink0">'.$btitle.' '.$chap.':'.$vers.'</a>';

$oper = (isset($_POST['oper']))?$_POST['oper']:'nada';

$row = rs('select v.heading superscript, ifnull(oln.heading,\'none\') heading, v.versetext, v.paragraph, v.style,
          ifnull(v.footnotes, \'~~~~~~~~\') footnotes, ifnull(v.comfootnotes, \'\') comfootnotes, v.metadesc, v.commentary
          from verse v
          left join outline oln on (oln.testament = v.testament and oln.book = v.book and oln.chapter = v.chapter and oln.verse = v.verse and oln.link=1)
          where v.testament = '.$test.'
          and v.book = '.$book.'
          and v.chapter = '.$chap.'
          and v.verse = '.$vers.' ');

$superscript= $row['superscript'];
$heading    = $row['heading'];
$heading    = (($heading=='none')?'-':'&ldquo;').$heading.(($heading=='none')?'-':'&rdquo;');
$edtverse   = $row['versetext'];
$paragraph  = $row['paragraph'];
$style      = $row['style'];
$footnotes  = $row['footnotes'];
$arfootnotes= explode('~~', $footnotes);
$arcomfootnotes= explode('~~', getfootnotes($test,$book,$chap,$vers, 'com'));


$commentary = $row['commentary'];
$commentary = preg_replace('#<br /> </li>#', '<br />&nbsp;</li>', $commentary??'');
if($commentary=='') $commentary = ' ';
$commentary = undoTOC($commentary);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta id="meta" name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit demo</title>
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
  <link rel="stylesheet" type="text/css" href="/includes/style.min.css?v=<?=$fileversion?>" />
<?if($colortheme>0){?>
  <link rel="stylesheet" type="text/css" href="/includes/style<?=$colors[0]?>.css?v=<?=$fileversion?>" />'.crlf);
<?}?>
  <link href="https://fonts.googleapis.com/css?family=Merriweather%7cIBM+Plex+Serif%7cCaladea%7cRoboto%7cMontserrat%7cBalsamiq+Sans&display=swap" rel="stylesheet" />
  <script src="/includes/misc.min.js?v=<?=$fileversion?>"></script>
  <script src="/includes/sortable.min.js"></script><!-- no defer here -->
  <script>
  var ismobile=0;
  function setdirt(){
  }

  </script>
</head>
<body>
  <h2 style="text-align:center;">Footnote demo</h2>
  <div id="view">
  <div id="edit">
  <form name="frm" action="/" method="post">
  <br />
  <span style="display: inline-block; margin-top:8px; font-style:italic">Verse</span>
  <textarea name="edtverse" style="width:100%;" onchange="setdirt()"><?=$edtverse?></textarea>

  <span style="display: inline-block; margin-top:8px; font-style:italic;font-size:80%">Verse Footnotes</span>
  <?if($arfootnotes[0]!=''){?>
  <a onclick="$('divvrsfootnotes').style.display=($('divvrsfootnotes').style.display=='none'?'block':'none');return false;"><small>show/hide</small></a>
  <?}?>
  <br />
  <div id="divvrsfootnotes" style="display:none;">
    <div id="vrsfootnotes">
    <?
    for($ni=0, $size=count($arfootnotes);$ni<$size;$ni++){
      if($arfootnotes[$ni] != ''){?>
      <div>&nbsp;
        <a id="vrsdelf<?=$ni?>" onclick="removefootnote('vrs',<?=($ni)?>);"><img src="/i/tbl_hide.png" alt="" title="delete this footnote" /></a>
        <span id="vrsfidx<?=$ni?>" class="fnidx"><?=($ni+1)?></span>
        <input type="text" name="vrsfootnote[]" style="width:<?=(($ismobile)?'65':'77')?>%; margin-top:2px;" value="<?=$arfootnotes[$ni]?>" onchange="setdirt()" autocomplete="off" />
        <a id="vrsquof<?=$ni?>" onclick="appendquotes('vrs',<?=$ni?>);" title="click to append smart quotes">&ldquo;&rdquo;</a>
        <a id="vrsemsf<?=$ni?>" onclick="appendems('vrs',<?=$ni?>);" title="click to append emphasize &lt;em> tags"><small>em</small></a>
      </div>
    <?}}?>
    </div>
  </div>

  <span style="display: inline-block; margin-top:8px; font-style:italic">Commentary</span>

  <textarea name="commentary" style="width:100%;height:330px;"><?=$commentary?></textarea>
  <span style="display: inline-block; margin-top:8px; font-style:italic;font-size:80%">Commentary Footnotes</span>
  <?if($arcomfootnotes[0]!=''){?>
  <a onclick="$('divcomfootnotes').style.display=($('divcomfootnotes').style.display=='none'?'block':'none');return false;"><small>show/hide</small></a>
  <?}?>
  <br />
  <div id="divcomfootnotes" style="display:none;">
    <div id="comfootnotes">
    <?
    for($ni=0, $size=count($arcomfootnotes);$ni<$size;$ni++){
      if($arcomfootnotes[$ni] != ''){?>
      <div>&nbsp;
        <a id="comdelf<?=$ni?>" onclick="removefootnote('com',<?=($ni)?>);"><img src="/i/tbl_hide.png" alt="" title="delete this footnote" /></a>
        <span id="comfidx<?=$ni?>" class="fnidx"><?=($ni+1)?></span>
        <input type="text" name="comfootnote[]" style="width:<?=(($ismobile)?'65':'77')?>%; margin-top:2px;" value="<?=$arcomfootnotes[$ni]?>" onchange="setdirt()" autocomplete="off" />
        <a id="comquof<?=$ni?>" onclick="appendquotes('com',<?=$ni?>);" title="click to append smart quotes">&ldquo;&rdquo;</a>
        <a id="comemsf<?=$ni?>" onclick="appendems('com',<?=$ni?>);" title="click to append emphasize &lt;em> tags"><small>em</small></a>
      </div>
    <?}}?>
    </div><br />
  </div>

  <input type="hidden" name="dirt" value="0" />

  <br />
  <table>
    <tr>
      <td style="vertical-align:bottom;">
      <input type="submit" name="btnsbt" id="btnsbt" value="Submit" onclick="return validate(document.frm);" style="text-align:center;font-size:80%" />&nbsp;
      </td>
    </tr>
  </table>

</form></div></div>
  <script>
    function validate(f){
      if(!checkfootnotes('vrs', CKEDITOR.instances.edtverse.getData())) return false;
      if(!checkfootnotes('com', CKEDITOR.instances.commentary.getData())) return false;
      if(confirm('This page will reload.\nNothing will be saved.')) location.reload();
      return false;
    }
  </script>
  <script src="/ckeditor/ckeditor.js?v=<?=$fileversion?>"></script>
  <script>
    CKEDITOR.replace( 'edtverse',
    {
      toolbar :
      [
        { name: 'document', items : [ <?=(($superman==1)?'\'Source\',':'')?>'AutoCorrect','Undo','Redo'] },
        { name: 'basicstyles', items : [ 'Bold','-','Italic' ] },
        { name: 'tools', items : [ 'PasteFootnote','PasteParagraph','PasteMidVerseHeader','PasteMidVerseSuperscript','SpecialChar' ] }
      ],
      height : '2.7em',
      enterMode : CKEDITOR.ENTER_BR,
      shiftEnterMode : CKEDITOR.ENTER_P
    }
    );
  </script>
  <?
require_once $docroot.'/includes/commentaryeditor.php';

?>
  </body></html>


