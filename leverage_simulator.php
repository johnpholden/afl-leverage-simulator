<?php

/**

Australian Rules win simulator
(C) 2019 John Holden
Free for personal use

**/


function estimate_win_pct($second=0,$homegoalarg=0,$homebehindarg=0,$awaygoalarg=0,$awaybehindarg=0,$hometeam=0,$awayteam=0) {
	#$sims = 0;			#no longer used
	$homewins = 0;		#number of times the home team has won in this instance
	$awaywins = 0;		#number of times the away team has won in this instance
	$ties = 0;			#number of ties
	
	#$hometeam in this instance represents the home team's winning percentage
	#it could be better named
	#
	#
	# this is the 
	# the model based on Squiggle data showed winning percentage had a linear best-fit until 83%
	# and a best-fit of a fourth power polynomial after that as it approached 100%
	# this was tested multpile times recursively to ensure a 90% winning percentage at a 0-0 game start would
	#   generate a 90% win rate
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
			if($scoreshot <= 524) {					#here, 524 represents the odds a scoring shot is generated in the given minute (52.4%)
				$isagoal = rand(0, 39);
				$whichteam = rand(0, $hometeam+$awayteam);
				if($isagoal < 23) {					#here, 23/40 represents the percent chance a scoring shot is a goal
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
				if($scoreshot <= 68) {				#here, 68 represents the odds a second scoring shot is generated in the given minute (6.8%)
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
		$homescore = ($homegoal * 6) + $homebehind;	#calculate final scores
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
#uncomment next line for a header row
#fwrite($outputfile,"winpct|minute|margin|homewins|awaywins|ties");
												#for($winpct=83;$winpct<100;$winpct++) {
												#	for($minute=0;$minute<80;$minute++) {
#how many simulations to run
#this was used to test variance at higher winning percentage points to test the sim
#each data point gets run 10,000 times in the estimate_win_pct function so only use this if you need to perform multiple tests of 10,000 for each data point!
#for($ii=0;$ii<1000;$ii++) {
	#run only for home initial win percentage 90
	for($winpct=90;$winpct<91;$winpct++) {
		#run only from the start of the game
		#minutes is defined as 80 above because this was written a month before 64 minute games were a thing :/
		for($minute=0;$minute<1;$minute++) {
#			echo date("h:i:sa")."\n";	#this was used to benchmark speed against Python

			#start the game at 0-0; $goals represents the range of the initial margin
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
				
				#run the simulation 10,000 times with the given parameters
				#the number 10,000 is specified in the estimate_win_pct function
				$output = estimate_win_pct($minute,$homegoals,$homebehinds,$awaygoals,$awaybehinds,$winpct,100-$winpct);

				#display the results so we know where we're up to - can be commented out for slightly faster performance
				echo $ii." ".$winpct." ".$minute." ".$goals." ".($output['homewins'])."\n";

				#and write it all out to file
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
#}
fclose($outputfile);
?>
