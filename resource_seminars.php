<?php

$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functions.php";
require_once $docroot."/includes/functions_phpdocx.php";
require_once $docroot."/phpdocx/classes/CreateDocx.inc";

// necessary! used in phpdocx
global $thefont, $expfontsize;
$parachoice = 0; // no indent, no justify
$ucaseot = 0;
$thefont='arial';
//$thefont = 'times new roman';
$expfontsize = (($thefont=='arial')?24:28);

$workdir = createworkdir();
$setsize = 5; // for seminars
$restype = '4'; // seminars

print('These docs only contain Seminars.<br />');

$ni=0;
$nj=1;
$nk=0;
$plid=0;
$setnum=1;

$sql = 'select resourcetype, title, description, identifier, externalurl, playlistid
        from resource
        where resourcetype in ('.$restype.')
        and active = 1
        and finalized = 0
        order by playlistid, playlistsqn ';
$res = dbquery($sql);
while($row = mysqli_fetch_array($res)){
  if($ni%$setsize==0 || $plid!=$row['playlistid']){
    if($plid>0){
      // add closing items and save
      if($plid != $row['playlistid'])
        $htm.= '<p><b><span style="color:red;">*</span>Seminar Overall Description:</b><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</p><hr>';
      $htm.= '<p>Thank you again, and God bless you!</p>';
      loadhtm2($docx, tidify($htm));
      $docx->createDocx($docroot.'/export/'.$workdir.$ftitle);
      // get next filename
      if($plid!=$row['playlistid']) $setnum=1;
      $ftitle = 'Seminar_'.getftitle($row['playlistid']).'_'.$setnum;
      $setnum++;
      $filenames[] = $ftitle.'.docx';
      unset($docx);
      $ni=0;
    }else{
      // get first filename
      $ftitle = 'Seminar_'.getftitle($row['playlistid']).'_'.$setnum;
      $setnum++;
      $filenames[] = $ftitle.'.docx';
    }
    print($ftitle.'<br />');
    $docx = new CreateDocx($docroot.'/includes/REV_styles.docx');
    $docx->modifyPageLayout('letter',
                            array('marginTop' => 1080,
                                  'marginBottom' => 1080,
                                  'marginLeft' => 900,
                                  'marginRight' => 900,
                                  'numberCols' => 1
                                  ));
    $numbering = new WordFragment($docx, 'defaultFooter');
    $numbering->addPageNumber('page_NumberOfX', $options);
    $docx->addFooter(array('default' => $numbering));

    $htm = '<h2>Resource Update Project - Phase 3, Seminars</h2>';
    $htm.= '<h3>INSTRUCTIONS</h3>';
    $htm.= '<p>This project consists of listening to an entire seminar and providing associated information about each session: a title, a description, scripture references, teacher name, 2-3 keywords (if applicable), and a suggested topic for each session (if applicable). In addition, the entire seminar will need an overall description. This information will be used to update the REV Resource page and the new Spirit & Truth website.</p>';
    $htm.= '<p>Each seminar has a set of documents, with each document having links to 5 sessions of the seminar. As with previous resource update phases, the document has places to complete the requested information. Clicking on a link will launch the page where the session resides. After listening to the entire session, please complete the information as follows.</p>';

    $htm.= '<ul>';
    $htm.= '<li>Provide a suggested title for the session (if needed). If the teacher states a title in the teaching, use it. While not every title needs to be revised, some titles are too long or missing. If you believe that the title needs to be revised, provide a new title in the space provided.  The standard format for the teaching title will be Seminar Title | Session # | Session Title.<br /><br />(The &ldquo;|&rdquo; character is a vertical bar found on the same key as the backslash (\). Use the shift key. There is a space before and after it.)  For Example: A Journey Through the Old Testament | Session 1 | Why Study the Old Testament.<br />&nbsp;</li>';
    $htm.= '<li>Each session needs a description. We are standardizing teaching descriptions to <b>30-70</b> words. 70 words is a guideline. Some sessions may require more description, but hopefully not to exceed 80 words.<br />&nbsp;';
    $htm.= '  <ul>';
    $htm.= '  <li>In MS Word, to get a word count, select the entire description, go to Review, Word Count. The word count will then be displayed.<br />&nbsp;</li>';
    $htm.= '  <li>A description is not a full summary of the teaching but gives a sense of the main ideas. It shouldn&rsquo;t be too detailed or focused on the conclusion but rather on the nature of why the recording was made, what questions it answers, and what benefit it will offer the person if they listen to it.<br />&nbsp;</li>';
    $htm.= '  <li>Do not include the teacher&rsquo;s name in your description or repeat the title.<br />&nbsp;</li>';
    $htm.= '  <li>We are asking 1 person to review an entire seminar so that the descriptions can be most consistent for the entire seminar. Please keep that in mind as you compose them.<br />&nbsp;</li>';
    $htm.= '  </ul>';
    $htm.= '</li>';
    $htm.= '<li>List the scriptures that are taught (i.e., explained). Do not list scriptures mentioned quickly in passing. STF has a standard syntax for listing scriptures in its published materials. It is provided below. We will provide you a separate document with STF&rsquo;s standard abbreviations for the books of the Bible.<br />&nbsp;</li>';
    $htm.= '<li>Provide the teacher&rsquo;s first and last name.<br />&nbsp;</li>';
    $htm.= '<li>(Optional) Provide <b>up to <u>three</u></b> suggested keywords. Think in terms of how a person might search for a teaching and provide <b>single-term</b> keywords that are applicable. <b>Do not list any words that are in the title or description, as they are already searchable.</b><br />&nbsp;</li>';
    $htm.= '<li>(Optional) Refer to the topic list on the <a href="https://www.revisedenglishversion.com">online REV</a>, and suggest one main topic for the session from the list. Not all seminar sessions will have a relevant topic.<br /><br />Please be sure to save your work as you go. When you have fully completed reviewing all the teachings on the document, email the completed document to Sue Carlson (<a href="mailto:sscarlson@sbcglobal.net">sscarlson@sbcglobal.net</a>) and you will receive another document to begin working on. Be sure to save a copy of each document you complete, as it will be helpful in writing the overall description for the whole seminar.<br />&nbsp;</li>';
    $htm.= '<li>At the foot of the seminar&rsquo;s final document, after the last session description, etc. you will see an additional section labeled &ldquo;<b>Seminar Overall Description:</b>&rdquo; Write 70-100 words that describe the seminar. This description should not contain an outline or summary of the sessions but should explain the overall purpose for and theme of the seminar.<br />&nbsp;</li>';
    $htm.= '</ul>';

    $htm.= '<hr>';
    $htm.= '<p><b>STF standard syntax for listing scriptures:</b></p>';
    $htm.= '<ul>';
    $htm.= '<li>Books of the Bible are always abbreviated. The abbreviations are standard and consistent. We have provided you a list in a separate document.<br />&nbsp;</li>';
    $htm.= '<li>Every scripture reference that is from a DIFFERENT chapter or book is separated by a semicolon, (eg. Acts 1:8; 10:5; 1 Cor. 12:4) but if it is from the SAME chapter and book, it is separated by a comma (eg. Acts 1:8, 12, 17). A range of consecutive verses is noted by a dash (eg. Acts 1:4-8, 13).<br />&nbsp;';
    $htm.= '  <ul>';
    $htm.= '  <li>An example using Jeremiah 12:10, Matthew 6:4, and Psalms 119:100-115 would look like this: <strong>Verses: Jer. 12:10; Matt. 6:4; Ps. 119:100-115</strong><br />&nbsp;</li>';
    $htm.= '  <li>Another example using James 1:7, Exodus 25:1, Nahum 3:2, and Luke 15:26 would look like this: <strong>Verses: James 1:7; Exod. 25:1; Nah. 3:2; Luke 15:26</strong><br />&nbsp;</li>';
    $htm.= '  <li>Another example using Psalms 41:6, Psalm 41:13, Psalm 110:3, Psalm 149:2, and Proverbs 31:10 looks like this: <strong>Verses: Ps. 41:6, 13; 110:3; 149:2; Prov. 31:10.</strong><br />&nbsp;</li>';
    $htm.= '  </ul>';
    $htm.= '</li>';
    $htm.= '</ul>';
    $htm.= '<hr>';

    $htm.= '<p style="page_break_before:always;"><strong><span style="color:red;">EXAMPLE</span> of a Teaching and How to Provide the Information:</strong></p>';
    $htm.= '<p><strong>Current Title:</strong> &ldquo;A Journey Through the Old Testament | session 1 | Introduction, Why Study the Old Testament?, Overview of the Old Testament&rdquo;<br /><span style="color:blue">[CONTROL] Click here to listen</span></p>';
    $htm.= '<p><strong><span style="color:red;">*</span>Title (edit if necessary):</strong> &ldquo;A Journey Through the Old Testament | session 1 | Why Study the Old Testament?, Overview of the Old Testament&rdquo;</p>';
    $htm.= '<p><strong><span style="color:red;">*</span>Description:</strong></p>';
    $htm.= '<p>This introductory session of the seminar covers two foundational topics. It explains the reasons why we should study the Old Testament, and it presents a detailed overview of the Old Testament. Beginning with Genesis 1:1 and ending with Malachi, the final book of the Old Testament, the overview highlights turning points in humankind&rsquo;s relationship with God, how the kingdom of Israel came to be, and the condition of Israel at the close of the Old Testament.</p>';
    $htm.= '<p><strong><span style="color:red;">*</span>Verses:</strong> Rom. 15:4</p>';
    $htm.= '<p><strong><span style="color:red;">*</span>Teacher:</strong> John Schoenheit</p>';
    $htm.= '<p><strong>Suggested Keywords (optional):</strong> </p>';
    $htm.= '<p><strong><span style="color:red;">*</span>Suggested Topic:</strong> </p>';
    $htm.= '<p><strong><span style="color:red;">*</span></strong> = required</p>';
    $htm.= '<p><hr></p>';
    $htm.= '<p style="margin-bottom:0;"><strong>Thank you again for helping with this project. It will have huge and timeless impact for &ldquo;searchers&rdquo; all over the globe. God bless you!</strong></p>';
    $htm.= '<hr>';
    $htm.= '<p><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</p>';

    $nj++;
    $plid = $row['playlistid'];
  }

  $url = $row['identifier'];
  $lnk = '[CONTROL] Click here to listen';

  $htm.= '<p style="page_break_before:always;"><b>Title:</b> '.$row['title'].'<br />';
  $htm.= '<a href="'.$url.'">'.$lnk.'</a></p>';
  $htm.= '<p><b><span style="color:red">*</span>Title (edit if necessary):</b> '.$row['title'].'</p> ';
  $htm.= '<p><b><span style="color:red">*</span>Description:</b> -- type the new description here --</p> ';
  $htm.= '<p><b><span style="color:red">*</span>Verses:</b> -- type relevant verses here --</p> ';
  $htm.= '<p><b><span style="color:red">*</span>Teacher:</b> -- teacher name --</p> ';
  $htm.= '<p><b>Suggested Keywords:</b> -- type suggested keywords here --</p> ';
  $htm.= '<p><b>Suggested Topics:</b> -- type suggested topics here --</p> ';
  $htm.= '<p><b><span style="color:red">*</span></b> <small>= required.</small></p> ';
  $htm.= '<hr style="margin-top:0;">';
  $ni++;
  $nk++;
}

// add closing to last file and save
$htm.= '<p><b><span style="color:red;">*</span>Seminar Overall Description:</b><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</p><hr>';
$htm.= '<p>Thank you again, and God bless you!</p>';
loadhtm2($docx, tidify($htm));
$docx->createDocx($docroot.'/export/'.$workdir.$ftitle);
unset($docx);



$zipfilename = "REV_Seminar_Resources.zip";
$zip = new ZipArchive();
$zipfile = $docroot."/export/".$workdir.$zipfilename;

if ($zip->open($zipfile, ZipArchive::CREATE)!==TRUE) {
    exit("cannot open <$zipfile>\n");
}
$cnt=0;

foreach ($filenames as $filename) {
  $zip->addFile('./export/'.$workdir.$filename, $filename);
  $cnt++;
}
$zip->close();
foreach ($filenames as $filename) {
  unlink($docroot.'/export/'.$workdir.$filename);
}

print('<br />Done..<br />'.$nk.' resources processed<br />');
print(($nj-1).' files generated<br /><br />');
print('<a href="/export/'.$workdir.$zipfilename.'">click here</a> to download.');


//
//
//
//
function getftitle($pid){
  $ret='';
  switch($pid){
  case 17: $ret='OT_Journey'; break;
  case 18: $ret='New_Life_in_Christ'; break;
  case 19: $ret='Death_Resurrection'; break;
  case 20: $ret='Creation_Evolution'; break;
  case 21: $ret='Book_Rev'; break;
  case 22: $ret='Trinity_Errors'; break;
  default: $ret='unknown'; break;
  }
  return $ret;
}

function loadhtm2($docx, $html){
  $docx->embedHTML($html, array('strictWordStyles' => false,
                                'downloadImages' => true)); //,
                                /*
                                'wordStyles' => array('<h2>' => 'rBookHead',    // must be IDs here
                                                      '<h3>' => 'rChapHead',
                                                      '<h4>' => 'rH4',
                                                      '<p>'  => 'rNormal',
                                                      '.rVersHead' => 'rVersHead',
                                                      '.rVersHeadSm' => 'rVersHeadSm',
                                                      '.rQuote' => 'rQuote',
                                                      '.footnote' => 'rFootNote', // for viewverscomm only
                                                      '.rHP' => 'rHP',
                                                      // the above are all block styles (p's)
                                                      // the below are inline styles (spans)
                                                      '.rNotInText' => 'rNotInText',
                                                      '.rOTQuote' => 'rOTQuote'
                                                      )));
                                 */
}

