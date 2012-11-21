<?

class FileMetadata {
	var $name;
	var $type;
	var $id;
	var $raw;
	function FileMetadata( $d ){
		$this->raw = $d;
		$this->name = $d['filename'];
		if( strstr($this->name,"%")){
			$this->name = urldecode($d['filename']);
			$this->name = iconv("cp949", "utf-8", $this->name);
		}
		$this->type = $d['filetype'];
		$this->id = $d['no'];
	}
}

class Article {
	var $title;
	var $files;
	var $link;
	var $raw;
	var $group;
	function Article( $d ) {
		$this->raw = $d;
		$this->title = $d['title'];
		$this->link = $d['link'];
		$this->files = array();
		$file = new FileMetadata($d);
		array_push($this->files, $file);
		$this->group = $d['group'];
	}

	function addFile( $d ) {
		$file = new FileMetadata($d);
		array_push($this->files, $file);
	}
}

function getArticleCount($where="1"){
	$sql = "SELECT count(no) as count FROM `torrent` WHERE ".$where;
	$r = mysql_query($sql);
	$count = mysql_result($r, 0, "count");
	return $count;
}

function getList($where , $offset=0 , $limit=100 ){
	$sql = "select no,community,link,title,filename,filetype,`group` from torrent where ".$where." order by no desc limit ".$offset.",".$limit.";";
	$sql = "select a.no,community,link,a.title,filename,filetype,`group`,b.count from torrent as a join ( select count,no from torrent_group) as b on b.no = a.group where ".$where." order by no desc limit ".$offset.",".$limit.";";
	//echo $sql;
	$r = mysql_query($sql);
	$list = array();
	while($d=mysql_fetch_array($r)){
		array_push($list, $d);
	}
	@mysql_free_result($r);
	return $list;
}

function getReleatedArticles($array, $link){
        foreach( $array as $item ) {
                if($item->link == $link)
                        return $item;
        }
        return null;
}

function makeArticles($d){
	$articles = array();
	foreach($d as $item){
		$article = getReleatedArticles($articles,$item['link']);
		if( $article ){
			$article->addFile($item);
		}else{
			$article = new Article($item);
			array_push($articles, $article);
		}
	}
	return $articles;
}

function getGroupById($id){
	$sql = "select * from torrent where `group` = '".$id."' order by no desc;";
	$groups = array();
	$r = mysql_query($sql);
	while($d = mysql_fetch_array($r)){
		$groups[] = $d;
	}
	return $groups;
}

function getGroupList(){
	#$sql = "select * from torrent_group order by `update` desc";
	$sql = "select * from torrent_group join (select *,count(*) as cnt from torrent_group_map group by group_id order by cnt desc ) as b on torrent_group.no=b.group_id order by cnt desc";
	$r = mysql_query($sql);
	$groups = array();
	while($d = mysql_fetch_array($r)){
		$groups[] = $d;
	}
	return $groups;
}

function getGroups($keywords){
	$resultSet = array();
	foreach($keywords as $keyword){
		$sql = "select torrent_id from torrent_keyword_map as a join (select no from torrent_keyword where value like '".$keyword."') as b on a.keyword=b.no";
		$r = mysql_query($sql);
		$result = array();
		while($d = mysql_fetch_array($r)){
			array_push($result, $d['torrent_id']);
		}
		if( count($resultSet) > 0 )
			$resultSet = array_intersect($resultSet, $result);
		else
			$resultSet = $result;
	}
	$where = implode(',', $resultSet);
	$sql = "select no,community,link,title,filename,filetype,`group` from torrent where no in (".$where.") order by no desc";
	$r = mysql_query($sql);
	if(!$r)
		return array();

	$data = array();
	while($d=mysql_fetch_array($r)){
		array_push($data,$d);
	}
	return $data;
}


function get_recently_keywords($limit = 100){
	if( !$limit )
		$limit = 100;
	$sql = "select * from torrent_keyword join (select * from torrent_keyword_map group by keyword order by no desc) as b on torrent_keyword.no=b.keyword limit ".$limit;
	$r = mysql_query($sql);
	$keywords = array();
	while($d = mysql_fetch_array($r) ){
		array_push($keywords,$d['value']);
	}
	return $keywords;
}

function get_popular_keywords($limit = 100) {
	if( !$limit )
		$limit = 100;
	$sql = "select * from torrent_keyword join (select *, count(*) as cnt from torrent_keyword_map group by keyword order by cnt desc) as b on torrent_keyword.no=b.keyword limit ".$limit;
	$r = mysql_query($sql);
	$keywords = array();
	while($d = mysql_fetch_array($r) ){
		array_push($keywords,$d['value']);
	}
	return $keywords;
}

function UI_today_keyword($keywords){
	$HEAD = <<<RAWDATA
<div class="section">
	<h2 class=hx> today keywords </h2>
        <div class="tx">
RAWDATA;
	$FOOT = <<<RAWDATA
	</div>
</div>
RAWDATA;
	$contents = array();
	foreach($keywords as $keyword){
		$line = "<a href=".$PHP_SELF."?group=".$keyword.">[".$keyword."]</a> ";
		$contents[] = $line;
	}
	$content = implode('', $contents);
	return $HEAD.$content.$FOOT;
}

function UI_popular_keyword($keywords, $g = array()){
	$HEAD = <<<RAWDATA
<div class="section">
	<h2 class=hx> Popular keywords </h2>
        <div class="tx">
RAWDATA;
	$FOOT = <<<RAWDATA
	</div>
</div>
RAWDATA;
	$contents = array();
	foreach($keywords as $keyword){
		$t = $g;
		array_push($t,$keyword);
		$key = implode(',',$t);
		$line = "<a href=".$PHP_SELF."?group=".$key.">[".$keyword."]</a> ";
		array_push($contents,$line);
	}
	$content = implode('', $contents);
	return $HEAD.$content.$FOOT;
}

function UI_recently_keyword($keywords){
	$HEAD = <<<RAWDATA
<div class="section">
	<h2 class=hx> Recently keywords </h2>
        <div class="tx">
RAWDATA;
	$FOOT = <<<RAWDATA
	</div>
</div>
RAWDATA;
	$contents = array();
	foreach($keywords as $keyword){
		$line = "<a href=".$PHP_SELF."?group=".$keyword.">[".$keyword."]</a>";
		array_push($contents,$line);
	}
	$content = implode('', $contents);
	return $HEAD.$content.$FOOT;
}

function UI_main_list($title, $articles, $showgroup = 0){
	$head = "<div class=\"list\">
        <div class=\"title\">
                <h3>$title</h3>
        </div>
        <ul>";
	$foot = '</ul></div>';
	$contents = array();
	foreach($articles as $article){
		$line = "<li>";
		foreach($article->files as $file){
			$iconpath = $file->type == "torrent" ? "img/torrent.gif" : "img/subtitle.png";
			$line .= " <a href=download.php?no=".$file->id." target=_blank><img src=".$iconpath." title='".$file->name."' align=absmiddle></a>";
		}
		$line .=" $article->title";
		if($showgroup && $article->raw['count'] > 1){
			$line .= " <a href=group.php?id=".$article->raw['group'].">...more[".$article->raw['count']."]</a>";
		}
		$line .=" <a href=http://www.google.co.kr/search?q=".urlencode($article->title)." target=_blank><img src=img/google-icon.png width=15></a>";
		$line .="</li>";
		array_push($contents,$line);
	}
	$content = implode('',$contents);
	return $head.$content.$foot;
}

function arrayToArg($array){
	$result = array();
	foreach($array as $key => $value){
		array_push($result, $key."=".$value);
	}
	return implode("&",$result);
}

function UI_paging($page,$max, $get){
	$pre = $page - 5;
	$next = $page +5;
	if($pre < 1){
		$next = $next-$pre;
		$pre = 1;
	}
	if( $next > $max ){
		$pre -= ($next-$max);
		$next = $max;
		if($pre<1)
			$pre = 1;		
	}
	
	$contents = array();
	array_push($contents, "<div class='paginate'>");
	if( $page != 1 ){
		$get['page'] = $page-1;
		array_push($contents, "<a href=".$PHP_SELF."?".arrayToArg($get)." class='pre'>이전</a>");
	}
	
	for($i=$pre; $i<=$next; $i++){
		$get['page'] = $i;
		if($i==$page)
			array_push($contents, "<strong>".$i."</strong>");
		else
			array_push($contents, "<a href=".$PHP_SELF."?".arrayToArg($get).">".$i."</a>");
	}
	$get['page'] = $page+1;
	if( $page < $max )
		array_push($contents, "<a href=".$PHP_SELF."?".arrayToArg($get)." class='next'>다음</a>");
	array_push($contents, "</div>");
	return implode('',$contents);
}

function UI_fav_group_list(){
	$contents = array();
	array_push($contents,"<div class='list'><div class='title'>관심 그룹</div>");
	array_push($contents,"<ul>");
	array_push($contents,"<li>황금어장</li>");
	array_push($contents,"<li>울랄라 부부</li>");
	array_push($contents,"</ul><div>");
	return implode('',$contents);
}
?>
