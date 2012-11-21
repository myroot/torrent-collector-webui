<?
require("../dbconn.php");
mysql_query("set character_set_connection=utf8;");
mysql_query("set character_set_server=utf8;");
mysql_query("set character_set_client=utf8;");
mysql_query("set character_set_results=utf8;");
mysql_query("set character_set_database=utf8;");

$no = $_REQUEST['no'];

$sql = "select data, filename, filetype from torrent where no = '".$no."' limit 1";
$result = @mysql_query($sql);
$data = @mysql_result($result,0, "data");
$filename = @mysql_result($result,0, "filename");
$filetype = @mysql_result($result,0, "filetype");

if(strstr($filename, "%")){
	$filename = urldecode($filename);
	$filename = iconv("cp949", "utf-8", $filename);
}

$type = $filetype == 'torrent' ? "application/x-bittorrent" : "application/x-subrip";
$size = strlen($data);

header("Cache-control: private");
header("Content-type: $type");
header("Content-length: $size");
header("Content-Disposition: attachment; filename=$filename");
header("Content-Description: PHP Generated Data");
echo $data;
?>
