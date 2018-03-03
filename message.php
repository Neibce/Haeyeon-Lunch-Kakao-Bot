<?php
$data = file_get_contents('php://input'); 
$data = json_decode($data);
$now_year = date("Y");
$now_month = date("m");
$last_day = date("t", mktime(0, 0, 1, $now_month, 1, $now_year));

if($data->{'type'} == 'photo'){
	echo
<<< EOD
{
  "message":{
    "text" : "이미지 업로드 기능은 지원하지 않습니다."
  },
  "keyboard": {
		"type" : "buttons",
		"buttons" : ["급식확인", "알레르기 정보"]
  }
}
EOD;
	exit();
}
switch ($data->{'content'}) {
	case '급식확인':
		echo <<< EOD
{
	"message":{
    "text" : "아래 항목 중 식단을\\n알고 싶은 날짜가 포함된 기간을 선택해주세요."
  },
  "keyboard": {
		"type" : "buttons",
		"buttons" : ["{$now_month}월 01일 ~ 10일", "{$now_month}월 11일 ~ 20일", "{$now_month}월 21일 ~ {$last_day}일"]
	}
}
EOD;
	break; //급식확인 END

	case '뒤로가기':
		echo
<<< EOD
{
  "message":{
    "text" : "뒤로가기"
  },
  "keyboard": {
		"type" : "buttons",
		"buttons" : ["급식확인", "알레르기 정보"]
  }
}
EOD;
	break;

	case '알레르기 정보':	
	echo <<< EOD
{
  "message":{
    "text" : "[알레르기 정보]\\n①난류 ②우유 ③메밀 ④땅콩 ⑤대두 ⑥밀 ⑦고등어 ⑧게 ⑨새우 ⑩돼지고기 ⑪복숭아 ⑫토마토 ⑬아황산류"
  },
  "keyboard": {
		"type" : "buttons",
		"buttons" : ["급식확인", "알레르기 정보"]
  }
}
EOD;
	break;

	case "{$now_month}월 01일 ~ 10일":
		echo get_date(1, 10);
	break;

	case "{$now_month}월 11일 ~ 20일":
		echo get_date(11, 20);
	break;

	case "{$now_month}월 21일 ~ {$last_day}일":
		echo get_date(21, $last_day);
	break;

	default:
		if(preg_match('/(.*)월 (.*)일 \((.*)\)/', $data->{'content'})) {
			$data->{'content'} = preg_replace('/(.*)월 /','',$data->{'content'});
			$data->{'content'} = preg_replace('/일 \(.*\)/','',$data->{'content'});
			$data->{'content'} = preg_replace('/0(.)/','$1',$data->{'content'});
			$lunch = get_lunch($data->{'content'});
echo <<< EOD
{
  "message":{
    "text" : "{$lunch}"
  },
  "keyboard": {
	"type" : "buttons",
	"buttons" : ["급식확인", "알레르기 정보"]
  }
}
EOD;
			}
		break;
}
function get_lunch($date){
	include_once("config.php");

	$y = date("Y");
	$m = date("m");

	$query = "SELECT * FROM lunch WHERE Y='".$y."' and M='".$m."' and D='".$date."'";
	$menu = mysqli_fetch_assoc(mysqli_query($link, $query))['C'];

	$query = "SELECT * FROM kcal WHERE Y='".$y."' and M='".$m."' and D='".$date."'";
  	$kcal = mysqli_fetch_assoc(mysqli_query($link, $query))['C'];
	mysqli_close($link);

 	if($kcal)
    $result = $menu ."\\n\\n칼로리: {$kcal}kcal";
 	else
 		$result = $menu;
 	
 	return $result;
}

function get_date($sd, $ed){
	$now_year = date("Y");
	$now_month = date("m");
	$last_day = date("t", mktime(0, 0, 1, $now_month, 1, $now_year));

	$result = <<< EOD
{
  "message":{
    "text" : "아래 항목 중 식단을\\n알고 싶은 날짜를 선택해주세요."
  },
  "keyboard": {
    "type": "buttons",
    "buttons": [
EOD;
	for($i=$sd;$i<=$ed;$i++){
		$yoil = date("N",mktime(0,0,0,$now_month,$i,$now_year));
		if($yoil!=6&&$yoil!=7){
			if($i>0&&$i<10)
		  	$result .= "\"{$now_month}월 0{$i}일 ";
			else
				$result .= "\"{$now_month}월 {$i}일 ";
		  switch ($yoil) {
				case 1:
					$temp = '(월)';
					break;
				case 2:
					$temp = '(화)';
					break;
				case 3:
					$temp = '(수)';
					break;
				case 4:
					$temp = '(목)';
					break;
				case 5:
					$temp = '(금)';
					break;
			}
			$result .= $temp . "\",";
		}
	}
	$result .= "\"뒤로가기\"";
	$result .= <<< EOD
    ]
  }
}
EOD;
return $result;
}
?>