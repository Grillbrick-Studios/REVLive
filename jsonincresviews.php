<?
$docroot = $_SERVER['DOCUMENT_ROOT'];
require_once $docroot."/includes/config.php";
require_once $docroot."/includes/functionsjson.php";

$resourceid = (int) preg_replace('/[^\d-]+/', '', ((isset($_REQUEST['id']))?$_REQUEST['id']:-1));

$ret = '';
$sql = 'update resource set resviews = resviews+1 where resourceid = '.$resourceid.' ';
$update = dbquery($sql);

mysqli_close($db);
?>

