<?php
header("Content-Type: text/html; charset=UTF-8");

include_once('simple_html_dom.php');
include_once('config.php');

$last_day = date("t", mktime(0, 0, 1, date("m"), 1, date("Y")));
$y = date("Y");
$m = date("m");

$url = "https://stu.pen.go.kr/sts_sci_md00_001.do?schulCode=C100000624&schulCrseScCode=3&schulKndScCode=03&schYm={$y}{$m}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$html = str_get_html(curl_exec($ch));
curl_close($ch);

for($i=1;$i<=$last_day;$i++){
  $lunch = get_lunch($i);
	$query = "INSERT INTO lunch SELECT '".$y."', '".$m."', '".$i."', '".$lunch."' FROM dual WHERE NOT EXISTS (SELECT * FROM lunch WHERE Y='".$y."' and M='".$m."' and D='".$i."')";
  mysqli_query($link, $query);
  if(!mysqli_error($link))
    $error = "OK.";
  else
    $error = mysqli_error($link);
  echo $query." : ".$error."<br>";
}
mysqli_close($link);

function get_lunch($date){
	global $html;

	$yoil = array("일","월","화","수","목","금","토");

	$now_year = date("Y");
	$now_month = date("m");

	if($date>0 && $date<10)
		$sel_date="0{$date}";
	else
		$sel_date = $date;
	$day = date("w",mktime(0,0,0,$now_month,1,$now_year))-1;
	$output="{$now_month}월 {$sel_date}일 ({$yoil[date('w', mktime(0,0,0,$now_month,$sel_date,$now_year))]})\\\\n\\\\n";

	$patterns[0] = '/<?.div>/';
	$patterns[1] = '/★/';
	$patterns[2] = '/\(해연\)/';
	$patterns[3] = '/\(해연중\)/';
	$patterns[4] = '/<br[^>]*>/i';
	$patterns[5] = '/해연,/';
	$patterns[6] = '/,해연/';
	$patterns[7] = '/\/해연/';
	$patterns[8] = '/10\./';
	$patterns[9] = '/11\./';
	$patterns[10] = '/12\./';
	$patterns[11] = '/13\./';
	$patterns[12] = '/1\./';
	$patterns[13] = '/2\./';
	$patterns[14] = '/3\./';
	$patterns[15] = '/4\./';
	$patterns[16] = '/5\./';
	$patterns[17] = '/6\./';
	$patterns[18] = '/7\./';
	$patterns[19] = '/8\./';
	$patterns[20] = '/9\./';
	$replacements[20] = '';
	$replacements[19] = ' ';
	$replacements[18] = '';
	$replacements[17] = '';
	$replacements[16] = "\\\\\\n";
	$replacements[15] = '';
	$replacements[14] = '';
	$replacements[13] = '';
	$replacements[12] = '⑩';
	$replacements[11] = '⑪';
	$replacements[10] = '⑫';
	$replacements[9] = '⑬';
	$replacements[8] = '①';
	$replacements[7] = '②';
	$replacements[6] = '③';
	$replacements[5] = '④';
	$replacements[4] = '⑤';
	$replacements[3] = '⑥';
	$replacements[2] = '⑦';
	$replacements[1] = '⑧';
	$replacements[0] = '⑨';

	foreach($html->find('div div div table tbody tr td div',$date + $day) as $element){
		$output .= $element->innertext;
	}
	
	if(mb_strlen($output, 'utf-8') <= 30)
		$output =  "{$now_month}월 {$sel_date}일 ({$yoil[date('w', mktime(0,0,0,$now_month,$sel_date,$now_year))]})\\\\n\\\\n예정된 식단이 없습니다.";

	$output = preg_replace($patterns,$replacements,$output);
	$output = str_replace("$date\\\\n[중식]\\\\n","",$output);
	$output = str_replace("&amp;","&",$output);
	return $output;
}
?>