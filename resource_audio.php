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
//$setsize = 10;
$setsize = 5; // for podbean

// resourcetype
// 1=youtube
// 2=mp4 (not currently used)
// 3=Podbean OR Castos
// 4=Seminar (stfpodcast.com)
// 5=article
// 6=book excerpt (not yet used)

//$restype = '1'; // yt only this time
$restype = '3'; // podbean only this time

print('These docs only contain Podbean Audio Teachings.<br />');

$ni=0;$nj=1;
$sql = 'select resourcetype, title, description, identifier, externalurl, publishedon
        from resource
        where resourcetype in ('.$restype.')
        and active = 1
        and finalized = 0
        order by publishedon desc ';
// sql for podbean only
$sql = 'select resourcetype, title, description, identifier, externalurl, publishedon
        from resource
        where resourcetype in ('.$restype.')
        and source = \'podbean\'
        and active = 1
        and finalized = 0
        order by publishedon desc ';
$res = dbquery($sql);
while($row = mysqli_fetch_array($res)){
  if($ni%$setsize==0){
    if($nj>1){
      // add closing items and save
      $htm.= '<p>Thank you again, and God bless you!</p>';
      loadhtm2($docx, tidify($htm));
      $docx->createDocx($docroot.'/export/'.$workdir.$ftitle);
      unset($docx);
    }
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

    $ftitle = 'AudioResourceSet_'.$nj;
    $filenames[] = $ftitle.'.docx';

    $htm = '<h2>Resource Update Project</h2>';
    $htm.= '<h3>INSTRUCTIONS</h3>';
    $htm.= '<p>This project consists of listening to audio teachings and providing associated information about each teaching: a description, scriptures taught, the teacher&rsquo;s name, 2-3 keywords, and the main topic of the teaching. This information will be used to update the REV Resource page and the channel that holds the recordings.</p>';
    $htm.= '<p>This document contains links to 5 audio teachings and places to complete the requested information. Clicking on a link will launch the page where the teaching resides. After listening to the entire teaching, please complete the information as follows.</p>';

    $htm.= '<ul>';
    $htm.= '<li>Each teaching has a title and there is no revision needed.</li>';
    $htm.= '<li>Each teaching has a description, most of which are too long. We have provided the current description in this document. We are standardizing teaching descriptions to <strong>30-70</strong> words. Please revise or re-write the current description to that length.';
    $htm.=   '<ul>';
    $htm.=   '<li>In MS Word, to get a word count, select the entire description, go to Review, Word Count. The count will display.</li>';
    $htm.=   '<li>A description is not a full summary of the teaching but gives a sense of what the teaching is about and why someone might want to watch it. It shouldn&rsquo;t be too detailed or focused on the conclusion but rather on the nature of why the recording was made, what questions it answers, and what benefit it will offer the person if they listen to it.</li>';
    $htm.=   '<li>Do not include the teacher&rsquo;s name in your description or repeat the title.</li>';
    $htm.=   '<li>If the existing description fits our criteria of <strong>30-70</strong> words and reads well, simply enter &ldquo;none&rdquo; after &ldquo;Suggested Description.&rdquo;</li>';
    $htm.=   '</ul>';
    $htm.= '</li>';
    $htm.= '<li>List the scriptures taught (explained). Do not list scriptures mentioned in passing. STF has a standard syntax for listing scriptures in its published materials. It is provided below. We will provide you a separate document with STF&rsquo;s standard abbreviations for the books of the Bible.</li>';
    $htm.= '<li>Provide the teacher&rsquo;s first and last name. In most cases, the teacher&rsquo;s name will already be populated for you.</li>';
    $htm.= '<li>Provide <strong>up to three</strong> suggested keywords. Think in terms of how a person might search for a teaching and provide <strong>single-term</strong> keywords that are applicable. <strong>Do not list any words that are in the title or description, as they are already searchable</strong>.</li>';
    $htm.= '<li>Refer to the topic list on the <a href="https://www.revisedenglishversion.com/topics">online REV</a>, and suggest one or two main topics for the teaching from the list.</li>';
    $htm.= '<li></li>';
    $htm.= '</ul>';

    $htm.= '<p>Please be sure to save your work as you go. When you have fully completed reviewing all the teachings, email the completed document to <strong>Sue Carlson</strong> (<a href="mailto:sscarlson@sbcglobal.net?subject=RUP Phase 2">sscarlson@sbcglobal.net</a>) and you will receive another document to begin working on.</p>';
    $htm.= '<hr>';
    $htm.= '<p><strong>STF standard syntax for listing scriptures:</strong></p>';
    $htm.= '<ul>';
    $htm.= '<li>Books of the Bible are always abbreviated. The abbreviations are standard and consistent. We have provided you a list in a separate document.</li>';
    $htm.= '<li>Every scripture reference that is from a DIFFERENT chapter or book is separated by a semicolon, (eg. Acts 1:8; 10:5; 1 Cor. 12:4) but if it is from the SAME chapter and book, it is separated by a comma (eg. Acts 1:8, 12, 17). A range of consecutive verses is noted by a dash (eg. Acts 1:4-8, 13).';
    $htm.= '  <ul>';
    $htm.= '  <li>An example using Jeremiah 12:10, Matthew 6:4, and Psalms 119:100-115 would look like this: <strong>Verses: Jer. 12:10; Matt. 6:4; Ps. 119:100-115</strong></li>';
    $htm.= '  <li>Another example using James 1:7, Exodus 25:1, Nahum 3:2, and Luke 15:26 would look like this: <strong>Verses: James 1:7; Exod. 25:1; Nah. 3:2; Luke 15:26</strong></li>';
    $htm.= '  <li>Another example using Psalms 41:6, Psalm 41:13, Psalm 110:3, Psalm 149:2, and Proverbs 31:10 looks like this: <strong>Verses: Ps. 41:6, 13; 110:3; 149:2; Prov. 31:10.</strong></li>';
    $htm.= '  </ul>';
    $htm.= '</li>';
    $htm.= '</ul>';
    $htm.= '<hr>';

    $htm.= '<p><strong>EXAMPLE of a Teaching and How to Provide the Information:</strong></p>';
    $htm.= '<p><strong>Title:</strong> &ldquo;It&rsquo;s Okay To Enjoy Your Life&rdquo;<br /><strong>Publish Date:</strong> 11/6/2019<br /><span style="color:blue">[CONTROL] Click here to listen</span></p>';
    $htm.= '<p><strong>Current Description:</strong></p>';
    $htm.= '<p>There is a belief within Christian denominations that we don&rsquo;t deserve and should not pursue happiness in this life, or that the Bible says we aren&rsquo;t supposed to enjoy ourselves-life should only be taken seriously and sober-mindedly at all times. But is that really God&rsquo;s intention for us?</p>';
    $htm.= '<p>In this teaching on It&rsquo;s Okay To Enjoy Your Life, John Schoenheit explores a godly and biblical perspective on happiness in this life-how God relates to it, what He wants us to experience in this life, and how He designed us both to labor and to feel joy and rejoice as His children dancing before Him. We pray this teaching will inspire deep joy and happiness in your life and in your walk with our Heavenly Father and the Lord Jesus Christ!</p>';
    $htm.= '<p><strong>Suggested Description:</strong></p>';
    $htm.= '<p>There is a belief among many Christians that we don&rsquo;t deserve and should not pursue enjoyment in this life, that life should only be taken seriously and sober-mindedly at all times. But is that really God&rsquo;s intention for us? Scripture illustrates God&rsquo;s viewpoint that we are to enjoy our work and the fruit of it, and even to approach life with humor.</p>';
    $htm.= '<p><strong><span style="color:red;">*</span>Verses:</strong> Mark 6:45-48; Titus 2:6; Phil. 3:1; 4:4; Eccles. 2:24-25; 3:4, 12-13, 22; 5:18; 8:15; 9:7; 11:9</p>';
    $htm.= '<p><strong><span style="color:red;">*</span>Teacher:</strong> John Schoenheit</p>';
    $htm.= '<p><strong>Suggested Keywords (optional):</strong> contentment</p>';
    $htm.= '<p><strong><span style="color:red;">*</span>Suggested Topic:</strong> Happiness, Joy</p>';
    $htm.= '<p><strong><span style="color:red;">*</span></strong> = required</p>';
    $htm.= '<p><hr></p>';
    $htm.= '<p style="margin-bottom:0;"><strong>Thank you again for helping with this project. It will have huge and timeless impact for &ldquo;searchers&rdquo; all over the globe. God bless you!</strong></p>';
    $htm.= '<hr>';
    $htm.= '<p><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;</p>';

    $nj++;
  }

  switch($row['resourcetype']){
  case 1: //youtube
    $url = 'https://www.youtube.com/watch?v='.$row['identifier'].'&rel=0';
    $lnk = '[CONTROL] Click here to watch';
    break;
  case 3: // podbean
  case 4: // seminar
    $url = $row['externalurl'];
    $lnk = '[CONTROL] Click here to listen';
    break;
  default:
    $url = $row['externalurl'];
    $lnk = 'Click to ???';
    break;
  }
  $teacher = 'teacher name';
  $htm.= '<p><b>Title:</b> &ldquo;'.$row['title'].'&rdquo;<br />';
  $htm.= '<b>Publish Date:</b> '.date_format(date_create($row['publishedon']),"n/j/Y").'<br />';
  $htm.= '<a href="'.$url.'">'.$lnk.'</a></p>';
  //$htm.= '<p><b>Suggested Title:</b> -- type the new title here --</p> ';
  $htm.= '<p><b>Current Description:</b></p><blockquote>'.fixdesc($row['description']).'</blockquote>';
  $htm.= '<p><b><span style="color:red">*</span>Suggested Description:</b> type the new description here</p> ';
  $htm.= '<p><b><span style="color:red">*</span>Verses:</b> type important verses used in the teaching here</p> ';
  $htm.= '<p><b><span style="color:red">*</span>Teacher:</b> '.$teacher.'</p> ';
  $htm.= '<p><b>Suggested Keywords (optional):</b> type two or three keywords that do not appear in the description here</p> ';
  $htm.= '<p><b><span style="color:red;">*</span>Suggested Topic:</b> type a suggested topic here</p> ';
  $htm.= '<p><b><span style="color:red">*</span></b> <small>= required.</small></p> ';
  $htm.= '<hr style="margin-top:0;">';

  $ni++;
}

// add closing items and save
$htm.= '<p>Thank you again, and God bless you!</p>';
loadhtm2($docx, tidify($htm));
$docx->createDocx($docroot.'/export/'.$workdir.$ftitle);
unset($docx);



$zipfilename = "REV_Audio_Resources.zip";
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

print('Done..<br />'.$ni.' resources processed<br />'.($nj-1).' files generated<br /><br /><a href="/export/'.$workdir.$zipfilename.'">click here</a> to download.');


//
//
//
function fixdesc($desc){
  global $teacher;
  if(strpos($desc, 'Teacher')!==false && strpos($desc, 'Teacher')<12){
    $teacher = substr($desc, 0, strpos($desc, '</p>')+4);
    $teacher = str_replace('Teacher:', '', $teacher);
    $teacher = str_replace('Teacher', '', $teacher);
    $teacher = strip_tags($teacher);
    $teacher = str_replace('  ', ' ', $teacher);
    $teacher = trim($teacher);
    $desc = trim(substr($desc, strpos($desc, '</p>')+4));
  }
  $ret = str_replace('<b>', '', $desc);
  $ret = str_replace('<strong>', '', $ret);
  $ret = str_replace('</b>', '', $ret);
  $ret = str_replace('</strong>', '', $ret);
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

