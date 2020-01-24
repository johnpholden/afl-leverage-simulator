<?php

function estimate_win_pct($second=0,$homegoalarg=0,$homebehindarg=0,$awaygoalarg=0,$awaybehindarg=0,$hometeam=0,$awayteam=0) {
	$sims = 0;
	$homewins = 0;
	$awaywins = 0;
	$ties = 0;
	
	if($hometeam < 83) {
		$hometeam = $hometeam * 0.0024565154;
		$hometeam = $hometeam + 0.3789139361;
	} else {
		$hometeam = ((0.000009516841) * (pow($hometeam,4))) - (0.0034101129 * (pow($hometeam,3))) + (0.4578676037 * (pow($hometeam,2))) - (27.296729515 * $hometeam) + 610.1681530544;
	}
	
	$hometeam = $hometeam * 1000;
	$awayteam = 1000 - $hometeam;
	
	
	for($j=0;$j<10000;$j++) {
		$homegoal = $homegoalarg;
		$awaygoal = $awaygoalarg;
		$homebehind = $homebehindarg;
		$awaybehind = $awaybehindarg;
		$homescore = 0;	/* these get set after the game */
		$awayscore = 0;
		for($minutes=$second;$minutes<80;$minutes++) {
			$scoreshot = rand(0, 1000);
			if($scoreshot <= 524) {
				$isagoal = rand(0, 39);
				$whichteam = rand(0, $hometeam+$awayteam);
				if($isagoal < 23) {
					if($whichteam <= $hometeam) {
						$homegoal = $homegoal + 1;
					} else {
						$awaygoal = $awaygoal + 1;
					}
				} else {
					if($whichteam <= $hometeam) {
						$homebehind = $homebehind + 1;
					} else {
						$awaybehind = $awaybehind + 1;
					}
				}
				
				$scoreshot = rand(0, 1000);
				if($scoreshot <= 68) {
					$isagoal = rand(0, 39);
					$whichteam = rand(0, $hometeam+$awayteam);
					if($isagoal < 23) {
						if($whichteam <= $hometeam) {
							$homegoal = $homegoal + 1;
						} else {
							$awaygoal = $awaygoal + 1;
						}
					} else {
						if($whichteam <= $hometeam) {
							$homebehind = $homebehind + 1;
						} else {
							$awaybehind = $awaybehind + 1;
						}
					}

				}
			}
		}
		$homescore = ($homegoal * 6) + $homebehind;
		$awayscore = ($awaygoal * 6) + $awaybehind;
		if($homescore > $awayscore) {
			$homewins = $homewins + 1;
		} elseif ($awayscore > $homescore) {
			$awaywins = $awaywins + 1;
		} else {
			$ties = $ties + 1;
		}
	}
	$output['homewins'] = $homewins;
	$output['awaywins'] = $awaywins;
	$output['ties'] = $ties;
	
	return $output;
} 


echo date("h:i:sa")."\n";
$outputfile = fopen("final_table_variance_check.txt","a");
#fwrite($outputfile,"winpct|minute|margin|homewins|awaywins|ties");
//for($winpct=83;$winpct<100;$winpct++) {
//	for($minute=0;$minute<80;$minute++) {
for($ii=0;$ii<1000;$ii++) {
for($winpct=90;$winpct<91;$winpct++) {
	for($minute=0;$minute<1;$minute++) {
//		echo date("h:i:sa")."\n";
		for($goals=0;$goals<1;$goals++) {
			$homegoals = 0;
			$awaygoals = 0;
			$homebehinds = 0;
			$awaybehinds = 0;
			if($goals > 0) {
				$homegoals = (int)($goals / 6);
				$homebehinds = $goals % 6;
			} else {
				$awaygoals = (int)(abs($goals) / 6);
				$awaybehinds = abs($goals) % 6;
			}
						
			$output = estimate_win_pct($minute,$homegoals,$homebehinds,$awaygoals,$awaybehinds,$winpct,100-$winpct);
			echo $ii." ".$winpct." ".$minute." ".$goals." ".($output['homewins'])."\n";
			$write_str = ($winpct);
			$write_str = $write_str."|".($minute);
			$write_str = $write_str."|".($goals);
			$write_str = $write_str."|".($output['homewins']);
			$write_str = $write_str."|".($output['awaywins']);
			$write_str = $write_str."|".($output['ties']);
			$write_str = $write_str."\n";
			fwrite($outputfile,$write_str);
		}
	}
}
}
fclose($outputfile);
?>
