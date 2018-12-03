<html>
<head>
    <meta charset="UTF-8">
    <title>初版検索</title></head>
<body>
<?php 
	if(empty($_POST["source"])) {
	}
	else {
		$source = $_POST["source"];
		$maxResult = $_POST["maxResult"];
		$searchKey = $_POST["searchKey"];
		$sortType = $_POST["sortType"];
		
		$books = google_books($source, $maxResult);
		$infolink = make_html($books, $searchKey, $sortType);
	}



function google_books($source, $maxResult){
       	$q = urlencode($source);
//       	$startIndex = 0;
       	$url = "https://www.googleapis.com/books/v1/volumes?q=".$q."&maxResults=".$maxResult."&langRestrict=ja";

		$json = file_get_contents($url);
		$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
		$books = book_elements($json, $maxResult);
		
		return $books;
}

function book_elements($json, $itemCount){
	$arr = json_decode($json,true);
	$books = array();
	for($i = 0; $i < $itemCount; $i++) {
		$books[$i] = array(
			"bs_totalItems" => $arr["totalItems"],
			"bs_title" => $arr["items"][$i]["volumeInfo"]["title"],
			"bs_authors" => $arr["items"][$i]["volumeInfo"]["authors"][0],
			"bs_publishedDate" => $arr["items"][$i]["volumeInfo"]["publishedDate"],
			"bs_description" => $arr["items"][$i]["volumeInfo"]["description"],
			"bs_pageCount" => $arr["items"][$i]["volumeInfo"]["pageCount"],
			"bs_categories" => $arr["items"][$i]["volumeInfo"]["categories"][0],
			"bs_thumbnail" => $arr["items"][$i]["volumeInfo"]["imageLinks"]["thumbnail"],
			"bs_language" => $arr["items"][$i]["volumeInfo"]["language"],
			"bs_previewLink" => $arr["items"][$i]["volumeInfo"]["previewLink"],
			"bs_salecountry" => $arr["items"][$i]["saleInfo"]["country"]);
	}
	return $books;
}

function make_html($books, $searchKey, $sortType){
	$html = "";
	$html .= '<div>総件数'.$books["0"]["bs_totalItems"].'</div>';
	$html .= '<div><table border = "1"><tr><th>No.</th><th>サムネイルリンク</th><th>タイトル</th><th>出版日</th><th>ページ数</th><th>著者</th><th>概要</th></tr>';
	$elements = array();

	if($searchKey === "publishedDate") {
		for($k = 0; $k< count($books); $k++){
			$elements[$k] = $books[$k]["bs_publishedDate"];
		}
	} else {
		for($k = 0; $k< count($books); $k++){
			$elements[$k] = $books[$k]["bs_pageCount"];
		}
	}
	
	if($sortType === "ASC"){
		array_multisort($elements, SORT_ASC, $books);
	} else {
		array_multisort($elements, SORT_DESC, $books);
	}
	


	for($j = 0; $j< count($books); $j++){
		$html .= '<tr><td>'.$j.'</td><td><a href="'.$books[$j]["bs_previewLink"].'"><img alt="GoogleBookリンク" src="'.$books[$j]["bs_thumbnail"].'"></a></td><td>'.$books[$j]["bs_title"].'</td><td>'.$books[$j]["bs_publishedDate"].'</td><td>'.$books[$j]["bs_pageCount"].'</td><td>'.$books[$j]["bs_authors"].'</td><td>'.$books[$j]["bs_description"].'</td></tr>';
	}
	$html .= "</table></div>";
	return $html;
}
?>

検索フォーム
	<form action = "book.php" method="post">
		<table border = "1">
		<tr>
			<td>検索窓</td>
			<td><input type="text" name="source"></td>
			<td><input type="radio" name="maxResult" value="40" checked>最大40件</td>
		</tr>
		<tr>
			<td>キー</td>
			<td><input type="radio" name="searchKey" value="publishedDate"checked>出版日
			    <input type="radio" name="searchKey" value="pageCount">ページ数</td>
		</tr>
		<tr>
			<td>ソート</td>
			<td><input type="radio" name="sortType" value="DESC" checked>降順
			    <input type="radio" name="sortType" value="ASC">昇順</td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input type="submit" value="入力"></td>
		</tr>
		</table>
	</form>
	<div><?php  echo "$infolink";?></div>
<div>
	<a href="/index.php">戻る</a>
</div>
</body>
</html>
