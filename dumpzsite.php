<?php
if(empty($userid) || empty($superman) || $userid==0 || $superman==0) {print('<h3>unauthorized access</h3>');return;}

error_reporting(E_ALL);

// increase script timeout value
ini_set('max_execution_time', 5000);
ini_set('memory_limit','768M');     //

print('<span class="pageheader">Backup the REV Website</span></span>');

print('<div style="margin:0 auto;text-align:center"><small>'.usermenu().'</small></div>');
print('<div style="margin:0 auto;text-align:center"><small>'.adminmenu().'</small></div>');


print('<br /><div style="margin:0 auto;width:480px"><table><tr><td>');

if(!isset($_POST['go'])){
?>
<form name="frm" method="post" action="/">
  <p>When you click "Go", the entire REV website and database will be backed up.  It takes 20 to 30 seconds.</p>
  <input type="checkbox" name="inclib" value="1"> include library files<br />
  <input type="submit" name="btnx" id="btnx" value="Go" onclick="this.value='please wait.';setTimeout('$(\'btnx\').disabled=true',300);"><p>
  <input type="hidden" name="go" value="1">
  <input type="hidden" name="page" value="32">
</form>
<?
}else{

$inclib = ((isset($_POST['inclib']))?$_POST['inclib']:0);

$workdir = createworkdir();
$unique = getuserfiletimestamp($timezone, 2);
$archive = 'export/'.$workdir.'REV_backup'.$unique.'.zip';
$zip = new ZipArchive();
if ($zip->open($archive, ZipArchive::CREATE) !== TRUE) {
    die ("Could not open archive");
}

$fullname = '';
$fil = 'REV_dbdump'.$unique.'.sql';

$dir  = 'export/'.substr($workdir, 0, -1);
$name = $fil;

show("<h3>Backing up entire REV website</h3>");
show("<b>Backing up database..</b><br />");
$dbresult = backup_database( $dir, $name, $dbserv, $dbuser, $dbpass, $dbname);

$numFiles = 0;
show("<br /><b>Backing up site files</b><br />");

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("./"));
$numFiles = 0;
foreach ($iterator as $key=>$value){
  //print($key.'<br />');
  if(strpos(str_replace('/', '\\', $key), 'export\\tmp_') == false &&
     (strpos(str_replace('/', '\\', $key), 'export\\library') == false || $inclib==1) &&
     strpos(str_replace('/', '\\', $key), 'export\\expdown') == false){
    $tmp = str_replace('\\','/', realpath($key));
    $tmp2 = str_replace('\\','/', $key);
    $tmp2 = str_replace('./','', $tmp2);
    if((right($tmp2, 1) != '.' && strpos($tmp2, 'export/'.$workdir) == false)){
      $zip->addFile($tmp, $tmp2) or die ("ERROR: Could not add file: $key");
      $numFiles++;
      if ($numFiles%250 == 0) {show( "$numFiles<br />" );}
    }else{
      //show( "Not backing up dotfiles: $key" );
    }
  }else{
    //show("Not backing up backup file(s) -> $key");
  }
}
// close and save archive
if($dbresult == 'success'){
  show("<b>Done with $numFiles site files</b><br />Adding database backup file<br />");
  $zip->addFile('./'.$fullname, preg_replace('#\/tmp_(.*?)\/#', '/', $fullname));
  $numFiles++;
  // apparently this next line executes before the previous line is done?
  //unlink($docroot.'/'.$fullname);
}else{
  show("<span style=\"color:red\">problem exporting database</span><br />");
  $zip->addEmptyDir('export');
}
$zip->close();
show( "<b>Backup created successfully with $numFiles files.</b><br /><br />" );
print('Click <a href="/'.$archive.'">here</a> to download the backup file for the entire site <b>including</b> the database.<br /><br />');
print('Click <a href="/'.$fullname.'">here</a> to download the backup file for the database alone.<br /><br />');
print('<span style="color:red"><b>Protect the files you download.</b></span>  A person with evil intent could use them to log in to the live website.<br /><br />');
print('The files will be removed from the server after about 3 minutes');
print('<p>Total memory used: '.formatBytes(memory_get_peak_usage()).'</p>');
logview($page, 0, 0, 0, 0, '');
} // end of checking for POST


print('</td></tr></table></div>');





//
//
//
function show($str){
   echo $str;
   //flush();
   //ob_flush();
   //sleep(1);   // I wish I could get the live site to flush these....
}

function backup_database( $directory, $outname , $dbhost, $dbusr, $dbpss ,$dbnam ) {
  global $fullname;
  $mysqli = new mysqli($dbhost, $dbusr, $dbpss, $dbnam);
  $mysqli->set_charset('utf8');

  $dir = $directory;
  $res = true;

  $name = $outname;
  $fullname = $dir.'/'.$name.'.gz';
  if(file_exists($fullname)) {unlink($fullname);}

  $sql = "SHOW TABLES";
  $show = $mysqli->query($sql);
  while ($r = $show->fetch_array()) {
    $tables[] = $r[0];
  }

  //cycle through
  $retbegin =
"-- ---------------------------------------------------------
--
-- SIMPLE SQL Dump
--
-- Host Connection Info: ".$mysqli->host_info."
-- Generation Time: ".date('F d, Y \a\t H:i A ( e )')."
-- Server version: ".$mysqli->server_info."
-- PHP Version: ".PHP_VERSION."
--
-- ---------------------------------------------------------\n\n

SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
SET time_zone = \"+00:00\";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
";
  $ntable = 1;
  foreach($tables as $table){
    $insertstr = '';
    show("backing up table `$table`<br />");
    $sqlbase[]  = $ntable.'_'.$table.'.sql';
    $sqlname    = $dir.'/'.$ntable.'_'.$table.'.sql';
    $sqlnames[] = $sqlname;
    if(file_exists($sqlname)) {unlink($sqlname);}
    $result     = $mysqli->query('SELECT * FROM '.$table);
    $num_fields = $result->field_count;
    $row2       = $mysqli->query('SHOW CREATE TABLE '.$table );
    $row2       = $row2->fetch_row();
    $dropstr    = "DROP TABLE IF EXISTS `{$table}`;\n";
    $return     = $retbegin."\n
-- ---------------------------------------------------------
--
-- Table structure for table : `{$table}`
--
-- ---------------------------------------------------------

".$dropstr.$row2[1].";\n";

    for ($i=0; $i<$num_fields; $i++){
      $nlines = 0;
      while($row = $result->fetch_row()){
        if( $nlines == 0 ) { # set the first statements
          $return .= "

--
-- Dumping data for table `{$table}`
--

";
          $array_field = array(); #reset ! important to resetting when loop
          while($field = $result->fetch_field()){ # get field
            $array_field[] = '`'.$field->name.'`';
          }
          $array_f[$table] = $array_field;
          $array_field = implode(', ', $array_f[$table]);
          $insertstr = "INSERT INTO `{$table}` ({$array_field}) VALUES\n(";
          $return .= $insertstr;
        }else{
          if(($nlines % 30) == 0){
            $return .= $insertstr;
          }else{
            $return .= '(';
          }
        }
        for($j=0; $j<$num_fields; $j++){
          $row[$j] = str_replace('\'','\'\'', preg_replace("/\n/","\\n", $row[$j] ?? '' ) );
          // 202300224 added this to capture stuff in editlogs
          $row[$j] = str_replace('\\\'\'','\\\'', $row[$j] ?? '');
          if(isset($row[$j])){
            $return .= ((is_numeric($row[$j]))?$row[$j]:(($row[$j]=='')?'null':'\''.fixentities($row[$j]).'\''));
          } else {
            $return .= 'null';
          }
          if ($j<($num_fields-1)) {$return.= ', ';}
        }
        if((($nlines+1) % 30) == 0){
          $return .= ");\n";
        }else{
          $return .= "),\n";
        }
        $nlines++;
        if(($nlines % 700) == 0){
          // write the contents
          if(!file_put_contents($sqlname, $return, FILE_APPEND)) die("error writing to file ".$sqlname);
          $return = '';
        }
      }
      # what is this checking for?
      preg_match("/\),\n/", $return, $match, false, -3); # check match
      if(isset($match[0])){
        $return = substr_replace($return, ";\n", -2);
      }
    }
    $return.="\n
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
    if(!file_put_contents($sqlname, $return, FILE_APPEND)) die("error writing to file ".$sqlname);
    $ntable++;
  }

  $sqlzip = new ZipArchive();
  if ($sqlzip->open($fullname, ZipArchive::CREATE) !== TRUE) {
    $result = 'failure';
  }else{
    $result = 'success';
    for($i=0;$i<($ntable-1);$i++){
      $sqlzip->addFile($sqlnames[$i], $sqlbase[$i]) or die ("ERROR: Could not add file: $sqlbase[$i]");
    }
    $sqlzip->close();
  }

  if( $mysqli && !$mysqli->error ) {
    $mysqli->close();
  }
  return $result;
}

function fixentities($dat){
  $ret = $dat;
  $ret = str_replace("“", "&ldquo;", $ret);
  $ret = str_replace("”", "&rdquo;", $ret);
  $ret = str_replace("‘", "&lsquo;", $ret);
  $ret = str_replace("’", "&rsquo;", $ret);
  return $ret;
}

?>
