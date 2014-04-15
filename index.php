<?php
session_start();
ob_start();
require_once('config.php');
require('db.php');
include("class_knockout.php");


$players = array(
	"Stian Lauknes",
	"Kenneth Johanson",
	"Dag Rønnevik",
	"Henrik Ormåsen",
	"Lasse Skogland",
	"Nils Reidar Hovden",
	"Yngve Solberg",
	"Torbjørn Tollaksen",
	"Sindre Seim Johansen",
	"Daniel Rufus Kaldheim",
	"Magnus Hauge Bakke",
	"Kjetil Sande",
	"Erlend Aga"
	);
natsort($players);

$teamNames = array(
	'Automatisk' => 'auto',
	"Kattepusar" => array(
		"Tigers",
		"Lions",
		"Jaguars",
		"Cheetahs",
		"Cougar",
		"Leopards",
		"Panthers",
		),
	"Star Wars" => array(
		"Princess Leia",
		"Luke Skywalker",
		"Obi-Wan Kenobi",
		"Han Solo",
		"Chewbacca",
		"C-3PO",
		"R2-D2",
		"Darth Wader",
		"Yoda",
		"Palpatine",
		"Boba Fett",
		"Jabba The Hutt"
		),
	"Linux distros" => array(
		"Ubuntu",
		"Gentoo",
		"Arch Linux",
		"Mandriva",
		"Red Hat",
		"CentOS",
		"Fedora"
		),
	"Distriktskontor" => array(
		"Husnes",
		"Haugesund",
		"Stord",
		"Bømlo",
		"Os",
		"Jørpeland"
		),
	"Avdelinger" => array(
		"Drift",
		"Maskinvare",
		"Systemutvikling",
		"Telekom",
		"Rådgivning",
		"Webutviklng"
		),
	"Android" => array(
		"Cupcake",
		"Donut",
		"Eclair",
		"Froyo",
		"Gingerbread",
		"Honeycomb",
		"Ice Cream Sandwich",
		"Jelly Bean",
		"KitKat"
		),
	"Lag {nummer}" => 'number',
	"Team {nummer}" => 'number'
	);
ksort($teamNames);

$teamTypes = array(
	'Automatisk' => 'auto',
	'1 Per lag' => '1',
	'2 Per lag' => '2',
	'4 Per lag' => '4',
	'5 Per lag' => '5'
	);

$gameType = array(
	'fosball' => 'Fußball',
	'bordtennis' => 'Bordtennis'
	);

$selectedTeamName = ((isset($_POST['teamName'])) ? $_POST['teamName'] : 'Automatisk');

	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Lag Generator</title>
		<link href="assets/styles/style.css" rel="stylesheet">

		<!-- Run in full-screen mode. -->
		<meta name="apple-mobile-web-app-capable" content="yes">

		<!-- Make the status bar black with white text. -->
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

		<!-- Customize home screen title. -->
		<meta name="apple-mobile-web-app-title" content="Lag Generator">

		<!-- Disable phone number detection. -->
		<meta name="format-detection" content="telephone=no">

		<!-- Set viewport. -->
		<meta name="viewport" content="initial-scale=1">

		<!-- Prevent text size adjustment on orientation change. -->
		<style>html { -webkit-text-size-adjust: 100%; }</style>

		<!-- Icons -->

		<!-- iOS 7 iPad (retina) -->
		<link href="/assets/images/AppIcon76@2x.png" sizes="152x152" rel="apple-touch-icon">

		<!-- iOS 6 iPad (retina) -->
		<link href="/assets/images/AppIcon72@2x.png" sizes="144x144" rel="apple-touch-icon">

		<!-- iOS 7 iPhone (retina) -->
		<link href="/assets/images/AppIcon60@2x.png" sizes="120x120" rel="apple-touch-icon">

		<!-- iOS 6 iPhone (retina) -->
		<link href="/assets/images/AppIcon57@2x.png" sizes="114x114" rel="apple-touch-icon">

		<!-- iOS 7 iPad -->
		<link href="/assets/images/AppIcon76x76.png" sizes="76x76" rel="apple-touch-icon">

		<!-- iOS 6 iPad -->
		<link href="/assets/images/AppIcon72x72.png" sizes="72x72" rel="apple-touch-icon">

		<!-- iOS 6 iPhone -->
		<link href="/assets/images/AppIcon57x57.png" sizes="57x57" rel="apple-touch-icon">

		<script src="/assets/vendor/jquery/jquery.js"></script>
		<script src="/assets/vendor/bootstrap/js/button.js"></script>
		<script src="/assets/vendor/bootstrap/js/dropdown.js"></script>
		<script src="/assets/vendor/bootstrap/js/tooltip.js"></script>
		<script src="/assets/vendor/bootstrap-select/bootstrap-select.js"></script>
		<script src="/assets/bracket/dist/jquery.bracket.min.js"></script>
		<script src="/assets/js/common.js"></script>
	</head>

	<body>
		<div class="container">
			<?php if (!isset($_GET['round']) && !isset($_GET['match'])) :
			$_SESSION['results'] = null;
			?>
			<div class="page-header">
				<h1>Lag Generator</h1>
			</div>
			<div class="flg_options <?php echo (($_GET['generateTeams']) ? 'hide_options' : ''); ?>">
				<form method="POST" action="<?php $_SERVER['REQUEST_URI']; ?>">
					<div class="row">
						<div class="col-md-3">
							<legend>Velg spillere</legend>
							<?php foreach ($players as $player) : ?>
								<div class="checkbox">
									<label>
										<input type="checkbox" name="players[]" value="<?php echo $player; ?>"<?php echo ((isset($_POST['players']) && in_array($player, $_POST['players'])) ? ' checked' : ''); ?>>
										<?php echo $player; ?>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
						<div class="col-md-3">
							<legend>Velg lagnavn</legend>
							<select name="teamName">
								<?php foreach ($teamNames as $teamName => $array) : ?>
									<option value="<?php echo $teamName; ?>"<?php echo ((is_array($array)) ? 'data-subtext="'.$array[rand(0, (count($array)-1))].'"' : (($array == 'number') ? 'data-subtext="'.str_replace('{nummer}', rand(1, round(count($players), 2, PHP_ROUND_HALF_DOWN)), $teamName).'"' : '')); ?> <?php echo (($selectedTeamName == $teamName) ? 'selected' : ''); ?>><?php echo $teamName; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-3">
							<legend>Velg Spill</legend>
							<select name="gameType">
								<?php foreach ($gameType as $key => $title) : ?>
									<option value="<?php echo $key; ?>"<?php echo ((isset($_POST['gameType']) && $_POST['gameType'] == $key) ? 'selected' : ''); ?>><?php echo $title; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-md-3">
							<legend>Velg oppsett</legend>
							<select name="teamType">
								<?php foreach ($teamTypes as $teamType => $value) : ?>
									<option value="<?php echo $value; ?>" <?php echo ((isset($_POST['teamType']) && $_POST['teamType'] == $value) ? 'selected' : ''); ?>><?php echo $teamType; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4 flg_action">
							<button class="btn btn-primary btn-large btn-block" type="submit" name="generateTeams" value="true">Generer lag</button>
						</div>
					</div>
				</form>
			</div>
			<?php if (isset($_POST['generateTeams']) && isset($_POST['players']) && count($_POST['players']) > 1) : ?>
				<br/>
				<div class="flg_team">
					<?php
					$teamName = $_POST['teamName'];
					if ($_POST['teamName'] == "Automatisk") {
						unset($teamNames['Automatisk']);
						$keys = array_keys($teamNames);
						shuffle($keys);
						$teamName = array_pop($keys);
					}
					$teamNameNo = 0;
					$teamRound = 0;
					$noPlayers = 0;
					switch ($_POST['gameType']) {
						case 'bordtennis':
						$defaultPlayersPrTeam = 1;
						break;
						case 'fosball':
						default:
						$defaultPlayersPrTeam = 2;
						break;
					}
					$playersPrTeam = ((!isset($_POST['teamType']) or $_POST['teamType'] == 'auto') ? $defaultPlayersPrTeam : $_POST['teamType']);
					$teams = array();
					$selectedTeam = $teamNames[$teamName];
					if (is_array($selectedTeam)) {
						shuffle($selectedTeam);
					}
					$players = $_POST['players'];
					shuffle_assoc($players);
					foreach ($players as $player) {
						if (is_array($selectedTeam)) {
							$teams["Team ".$selectedTeam[$teamNameNo].((!empty($teamRound) ? ' '.$teamRound : ''))][] = $player;
						}
						else {
							$teams[str_replace('{nummer}', ($teamNameNo + 1), $teamName)][] = $player;
						}
						$noPlayers++;
						if (($playersPrTeam == "auto" && ($noPlayers == 2 or (count($players) <= 4))) or ($noPlayers == $playersPrTeam)) {
							$noPlayers = 0;
							$teamNameNo++;
							if (is_array($selectedTeam) && $teamNameNo == count($selectedTeam)) {
								$teamNameNo = 0;
								$teamRound++;
							}
						}
					}

					$sessionTeam = array();
					?>
					<div class="row">
						<?php foreach ($teams as $team => $players) :
						$sessionTeam[] = array('name' => $team, 'players' => $players);
						?>
						<div class="col-md-4">
							<legend><?php echo $team; ?></legend>
							<ul>
								<?php foreach ($players as $player) : ?>
									<li><?php echo $player; ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach;
					$_SESSION['teams'] = $sessionTeam;
					?>
				</div>
			</div>
			<br/>
			<div class="row">
				<div class="col-md-4 flg_action">
					<a class="btn btn-success btn-large btn-block" href="?round=0&match=0">Start spill</a>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if (isset($_GET['round']) && isset($_GET['match'])) :
	$noTeams = count($_SESSION['teams']);
	$gamesFirstRound = round($noTeams / 2, 0, PHP_ROUND_HALF_DOWN);
	$gamesSpare = round($noTeams / 2, 0, PHP_ROUND_HALF_UP) - $gamesFirstRound;
	$compets = array();
	foreach ($_SESSION['teams'] as $comp) {
		$compets[] = $comp['name'];
	}

	$KO = new KnockoutGD($_SESSION['teams']);
	?>
	<div class="row flg_game">
		<?php
		$currentRound = $_GET['round'];
		$currentMatch = $_GET['match'];

		if ((isset($_GET['c1_goals']) && $_GET['c1_goals'] >= 10) or (isset($_GET['c2_goals']) && $_GET['c2_goals'] >= 10)) {
			$_SESSION['results'][$currentRound][$currentMatch] = array(
				'c1_goals' => $_GET['c1_goals'],
				'c2_goals' => $_GET['c2_goals']
				);
		}

		if (isset($_SESSION['results'])) {
			foreach ($_SESSION['results'] as $round => $match) {
				foreach($match as $match_no => $goals) {
					$KO->setResByMatch((int)$match_no, (int)$round, (int)$goals['c1_goals'], (int)$goals['c2_goals']);
				}
			}
		}

		$bracket = $KO->getBracket();
		$roundInfo = $KO->roundsInfo;
		$next_match = 0;
		$next_round = 0;
		foreach ($bracket as $round => $match) {
			$next_round = $round;
			foreach ($match as $match_no => $info) {
				$next_match = $match_no;
				if ($currentRound == $round && $match_no > $currentMatch) {
					break 2;
				}
				elseif (($currentRound + 1) == $round && $match_no == 0) {
					break 2;
				}
			}
		}
		if ($currentRound == 0 && $currentMatch == 0) {
			if (!isset($bracket[$currentRound][$currentMatch])) {
				$currentRound = $next_round;
				$currentMatch = $next_match;
			}
		}
		if (countMatches($bracket) == 0) {
			echo "<h4>Feil!<br/><small>Vennligst velg flere lag</small></h4>";
		}
		$flip = false;
		if ((isset($_GET['c1_goals']) && $_GET['c1_goals'] >= 5) or (isset($_GET['c2_goals']) && $_GET['c2_goals'] >= 5)) {
			$flip = true;
		}
		if ((isset($_GET['c1_goals']) && $_GET['c1_goals'] >= 10) or (isset($_GET['c2_goals']) && $_GET['c2_goals'] >= 10)) {
			if ($next_round == $currentRound && $next_match == $currentMatch) {
				$final = $bracket[$currentRound][$currentMatch];
				$winner = $final['c1'];
				if ($final['s1'] < $final['s2']) {
					$winner = $final['c2'];
				}
				$players = $winner['players'];
				if (count($players) > 1) {
					$last_pl = array_pop($players);
				}
				?>
				<center>
					<i class="fa fa-trophy"></i>
				</center>
				<h1 class="winner_title"><?php echo $winner['name']; ?></h1>
				<h3 class="winner_sub_title">Gratulerer <?php echo implode(', ', $players).((isset($last_pl)) ? ' og '.$last_pl : ''); ?>, <?php echo ((count($winner['players']) > 1) ? 'dere' : 'du'); ?> vant <?php echo ((countMatches($bracket) > 1) ? 'turneringen' : 'kampen'); ?>!</h3>
				<?php
			}
			else {
				$final = $bracket[$currentRound][$currentMatch];
				$winner = $final['c1'];
				if ($final['s1'] < $final['s2']) {
					$winner = $final['c2'];
				}
				?>
				<h1 class="winner_title"><?php echo $winner['name']; ?></h1>
				<h3 class="winner_sub_title">Gratulerer, dere er videre!</h3>
				<div class="row">
					<div class="col-md-4 flg_action">
						<a class="btn btn-success btn-large btn-block" href="?round=<?php echo $next_round; ?>&match=<?php echo $next_match; ?>">Neste spill</a>
					</div>
				</div>
				<?php
			}
		}
		else {
			foreach($bracket as $round => $match) :
				if ($currentRound == $round) : ?>
			<?php
			foreach ($match as $no => $info) :
				if ($currentMatch == $no) : ?>
			<div class="col-md-12">
				<h1><?php echo $roundInfo[$round][0]; ?></h1>
				<legend class="clearfix">
					<span style="float:left; display: block; width: 45%;">
						<?php echo $info['c1']['name']; ?>
					</span>
					<span style="float:left; display: block; width: 10%; text-align: center;">
						<small>vs</small>
					</span>
					<span style="float:left; display: block; width: 45%; text-align: right;">
						<?php echo $info['c2']['name']; ?>
					</span>
				</legend>
			</div>
			<div class="col-xs-6">
				<div class="goals" id="c1_goals">
					<?php echo ((isset($_GET['c1_goals'])) ? $_GET['c1_goals'] : '0'); ?>
				</div>
				<a href="?round=<?php echo $round; ?>&match=<?php echo $no; ?>&c1_goals=<?php echo (((isset($_GET['c1_goals'])) ? $_GET['c1_goals'] : '0') + 1); ?>&c2_goals=<?php echo ((isset($_GET['c2_goals'])) ? $_GET['c2_goals'] : '0'); ?>" class="btn btn-large <?php echo (($flip) ? 'btn-dark' : 'btn-default'); ?> btn-block new_goal" id="teamone_new_goal">Mål!</a>
				<br/>
				<label>Spillere:</label>
				<br />
				<ul class="list-unstyled">
					<?php foreach ($info['c1']['players'] as $player) : ?>
						<li><?php echo $player; ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="col-xs-6">
				<div class="goals" id="c2_goals">
					<?php echo ((isset($_GET['c2_goals'])) ? $_GET['c2_goals'] : '0'); ?>
				</div>
				<a href="?round=<?php echo $round; ?>&match=<?php echo $no; ?>&c1_goals=<?php echo ((isset($_GET['c1_goals'])) ? $_GET['c1_goals'] : '0'); ?>&c2_goals=<?php echo (((isset($_GET['c2_goals'])) ? $_GET['c2_goals'] : '0') + 1); ?>" class="btn btn-large <?php echo (($flip) ? 'btn-default' : 'btn-dark'); ?> btn-block new_goal" id="teamtwo_new_goal">Mål!</a>
				<br/>
				<label>Spillere:</label>
				<br />
				<ul class="list-unstyled">
					<?php foreach ($info['c2']['players'] as $player) : ?>
						<li><?php echo $player; ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
			endif;
			endforeach; ?>
			<?php
			endif;
			endforeach; ?>
		</div>
		<?php
	}
	?>
	<br /><br/><br />
	<a href="/" class="btn btn-danger">Tilbakestill</a>
<?php endif; ?>
</div>
</body>
</html>

<?php

function shuffle_assoc(&$array) {
	$keys = array_keys($array);
	shuffle($keys);
	foreach($keys as $key) {
		$new[$key] = $array[$key];
	}
	$array = $new;
	return true;
}

function countMatches($bracket) {
	$num = 0;
	foreach ($bracket as $key => $value) {
		$num += count($value);
	}
	return $num;
}
?>
