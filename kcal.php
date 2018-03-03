<?php
include_once('simple_html_dom.php');
include_once('config.php');

$y = date("Y");
$m = date("m");
$firstDay = date("w", mktime(0,0,0,$m,1,$y)) ;
$lastDay = date("t", mktime(0,0,0, $m,1, $y));

$arrKcal = getKcalMerge();

for($i=1;$i<=count($arrKcal);$i++){
  $query = "INSERT INTO kcal SELECT '".$y."', '".$m."', '".$i."', '".$arrKcal[$i-1]."' FROM dual WHERE NOT EXISTS (SELECT * FROM kcal WHERE Y='".$y."' and M='".$m."' and D='".$i."')";
  mysqli_query($link, $query);
  if(!mysqli_error($link))
    $error = "OK.";
  else
    $error = mysqli_error($link);
  echo $query." : ".$error."<br>";
}
mysqli_close($link);

function getKcalMerge(){
    global $firstDay, $lastDay;
    for($week=0;$week<getWeek();$week++) {
        $kcalArray[$week] = (getKcal(getMonday($week)));
    }
   $result = array();
    for($week=0;$week<getWeek();$week++) {
        $result = array_merge($result,$kcalArray[$week]);
    }
    $result = array_splice($result, $firstDay, $lastDay);

    return $result;
}

function getWeek(){
    global $y, $m, $lastDay;
    $firstWeek = date("W",mktime(0,0,0, $m+1, 1, $y));
    $lastWeek = date("W",mktime(0,0,0, $m+1, $lastDay, $y));
    return $lastWeek-$firstWeek+1;
}

function getMonday($num){
    global $y, $m, $lastDay;
    $ar=0;
    $isFirstDay=0;
    $result[]=0;
    for($i=1;$i<=$lastDay;$i++){
        $day =  date("w", mktime(0,0,0, $m, $i, $y));
        if ($day==1){
            $result[$ar]=$i;
            $ar++;
        }
    }
    foreach ($result as $element){ //달 첫째 주에 월요일이 없을경우 Array에 1을 추가.
        if($element==1) {
            $isFirstDay = 1;
        }
    }
    if($isFirstDay==0){
        array_unshift($result, 1);
    }
    return $result[$num];
}

function getKcal($date,$output = ""){
    global $y, $m;
    $url = "https://stu.pen.go.kr/sts_sci_md01_001.do?schulCode=C100000624&schulCrseScCode=3&schulKndScCode=03&schYmd={$y}{$m}{$date}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $html = str_get_html(curl_exec($ch));
    curl_close($ch);

    foreach ($html->find('table') as $element)
        $output .= $element->outertext;
    $out = preg_replace('/(.*)\<th scope=\"row\">에너지\(kcal\)<\/th>(.*)<td class="textC last"><\/td>(.*)/', '$2', $output);
    $out = preg_replace('/\s+/', '', $out);
    $out = preg_replace('/class/', ' class', $out);
    $out = preg_replace('/<\/tr><tr><thscope="row">탄수화물\(g\)(.*)/', '', $out);
    $out = preg_replace('/<td class="textClast"><\/td>/', '', $out);
    $out = preg_replace('/<\/td>/', '|', $out);
    $out = preg_replace('/<td class="textC">/', '', $out);
    $result = explode("|", $out);
    return $result;
}