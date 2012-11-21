<?
require("../dbconn.php");
require('lib.php');

mysql_query("set character_set_connection=utf8;");
mysql_query("set character_set_server=utf8;");
mysql_query("set character_set_client=utf8;");
mysql_query("set character_set_results=utf8;");
mysql_query("set character_set_database=utf8;");

$id = $_GET['id'];
$list = getGroupById($id);
$articles = makeArticles($list);


?>
<html>
<head>
<title> torrent </title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="js/jquery-1.8.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="naver.css" />
<link rel="stylesheet" type="text/css" href="css.css" />
</head>
<body>
<?
echo UI_main_list($title, $articles);
?>
</body>
</html>

