<?
require("../dbconn.php");
require('lib.php');

mysql_query("set character_set_connection=utf8;");
mysql_query("set character_set_server=utf8;");
mysql_query("set character_set_client=utf8;");
mysql_query("set character_set_results=utf8;");
mysql_query("set character_set_database=utf8;");

$perpage = 100;

$mode = 'list';
if($_GET['group']){
	$g = explode(',',$_GET['group']);
	$mode = 'group';
}else
	$g=array();

$predefind = array();
$predefind[0] = array('무한도전','세바퀴','우리,결혼했어요');
$predefind[1] = array('개그콘서트','일밤','런닝맨');
$predefind[2] = array('놀러와','힐링캠프','안녕하세요','마의');
$predefind[3] = array('마의','강심장','승승장구');
$predefind[4] = array('황금어장','짝','한밤의,tv연예');
$predefind[5] = array('해피투게더','김국진의,현장박치기');
$predefind[6] = array('슈퍼스타K4','유희열의,스케치북','위대한');

$week = (int)date('w');
$today_keyword = array_merge(array(date('ymj',time()-60*60*24)),$predefind[$week]);


$search = $_GET['search'];

if($search){
	$where = "title like '%".$search."%'";
}else
	$where = "1";

if(!$_GET['page'])
	$_GET['page'] = 1;
$page = $_GET['page'];


if($mode == 'list'){
	$start = ($page-1)*$perpage;
	$totalCount = getArticleCount($where);
	$maxPage = ceil($totalCount/$perpage);
	$list = getList($where, $start,100);
}
else if($mode == 'group'){
	$list = getGroups($g);
}

$articles = makeArticles($list);

$pop_keyword = get_popular_keywords(50);
//$recently_keyword = get_recently_keywords(20);

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
<form method=get action=''>
<input type=text name=search title=search value=<?=$search?>>
</form>
<?
if( $mode == 'group') {
	echo "Group keyword : ";
	foreach($g as $value){
		$n = array();
		foreach($g as $k)
			if( $k != $value ) array_push($n,$k);
		$arg = implode(',',$n);
		echo "<a href=".$PHP_SELF."?group=".$arg."><span style='border:1px solid #ddd'>".$value." <img src=img/x_icon.png></span></a> ";
	}
}

echo UI_today_keyword($today_keyword);
echo UI_popular_keyword($pop_keyword,$g);
//echo UI_recently_keyword($recently_keyword);
if( $mode == 'list' ){
	//echo UI_fav_group_list();
	if( $search )
		$title = $search." 검색결과";
	else
		$title = "최근 파일들";
	echo UI_main_list($title, $articles,1);
	echo UI_paging($page,$maxPage, $_GET);
}else if($mode == 'group') {
	$title = '';
	foreach($g as $value){
		$title .= $value." ";
	}
	$title .= "groups";
	echo UI_main_list($title, $articles);
}
?>
</body>
</html>

