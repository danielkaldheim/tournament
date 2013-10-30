<?php
session_start();
ob_start();
ini_set('display_errors', 'On');
$players = array(
	"Stian Lauknes",
	"Kenneth Johanson",
	"Dag Rønnevik",
	"Henrik Ormåsen",
	"Lasse Skogland",
	"Nils Reidar Hovden",
	"Yngve Solberg",
	"Torbjørn Lærlingson",
	"Sindre Seim Johansen",
	"Daniel Rufus Kaldheim",
	"Magnus Hauge Bakke",
	);

// global $first; $first= array('Per', 'Pål', 'Ole', 'Lise', 'Mette', 'Nina', 'Geir', 'Janne');
// global $last; $last = array('Olsen', 'Jonson', 'Norman', "Eriksen");

// function genName() {
// 	$first = $GLOBALS['first'];
// 	$last = $GLOBALS['last'];
// 	return $first[rand(0, count($first) - 1)]." ".$last[rand(0, count($last) - 1)];
// }
// $try = 0;
// for ($i = 0; $i <= 9; $i++) {
// 	$name = genName();
// 	if (in_array($name, $players)) {
// 		$i--;
// 		$try++;
// 	}
// 	else {
// 		$try = 0;
// 		$players[] = $name;
// 	}
// 	if ($try == ((count($first) * count($last))) * 2) {
// 		break;
// 	}
// }

$teamNames = array(
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
	"Lag {nummer}" => 'number',
	"Team {nummer}" => 'number'
	);
function shuffle_assoc(&$array) {
	$keys = array_keys($array);
	shuffle($keys);
	foreach($keys as $key) {
		$new[$key] = $array[$key];
	}
	$array = $new;
	return true;
}

$teamTypes = array(
	'Automatisk' => 'auto',
	'1 Per lag' => '1',
	'2 Per lag' => '2',
	'4 Per lag' => '4',
	'5 Per lag' => '5'
	)

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>iDrift Fußball Lag Generator</title>
		<link href="assets/styles/style.css" rel="stylesheet">
	</head>

	<body>
		<div class="container">
			<div class="page-header">
				<h1>iDrift Fußball Lag Generator</h1>
			</div>
			<?php if (!isset($_GET['game'])) : ?>
				<div class="flg_options <?php echo (($_GET['generateTeams']) ? 'hide_options' : ''); ?>">
					<form method="POST" action="<?php $_SERVER['REQUEST_URI']; ?>">
						<div class="row">
							<div class="col-md-4">
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
							<div class="col-md-4">
								<legend>Velg lagnavn</legend>
									<select name="teamName">
										<?php foreach ($teamNames as $teamName => $array) : ?>
										<option value="<?php echo $teamName; ?>"<?php echo ((is_array($array)) ? 'data-subtext="'.$array[rand(0, (count($array)-1))].'"' : (($array == 'number') ? 'data-subtext="'.str_replace('{nummer}', rand(1, round(count($players), 2, PHP_ROUND_HALF_DOWN)), $teamName).'"' : '')); ?> <?php echo ((isset($_POST['teamName']) && $_POST['teamName'] == $teamName) ? 'selected' : ''); ?>><?php echo $teamName; ?></option>
										<?php endforeach; ?>
									</select>
							</div>
							<div class="col-md-4">
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
						$teamNameNo = 0;
						$teamRound = 0;
						$noPlayers = 0;
						$playersPrTeam = ((!isset($_POST['teamType']) or $_POST['teamType'] == 'auto') ? 2 : $_POST['teamType']);
						$teams = array();
						$selectedTeam = $teamNames[$_POST['teamName']];
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
								$teams[str_replace('{nummer}', ($teamNameNo + 1), $_POST['teamName'])][] = $player;
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
							<a class="btn btn-success btn-large btn-block" href="?game=1">Start spill</a>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			<?php if (isset($_GET['game'])) :
				$noTeams = count($_SESSION['teams']);
				$gamesFirstRound = round($noTeams / 2, 0, PHP_ROUND_HALF_DOWN);
				$gamesSpare = round($noTeams / 2, 0, PHP_ROUND_HALF_UP) - $gamesFirstRound;
			?>
			<div class="row flg_game">
			<?php
				$team = 0;
				$matches = array();
				for ($i = 0; $i < $gamesFirstRound; $i++) :
					$team1 = $_SESSION['teams'][$team];
					$team++;
					$team2 = $_SESSION['teams'][$team];
					$team++;
					$matches[] = array($team1, $team2);
					?>
				<?php endfor; ?>
				<?php if ($_GET['game'] <= count($matches)) : ?>

					<?php $match = $matches[$_GET['game'] - 1]; ?>
					<?php if ((isset($_GET['teamone_goals']) && $_GET['teamone_goals'] >= 10) or (isset($_GET['teamtwo_goals']) && $_GET['teamtwo_goals'] >= 10)) :
						$match[0]['goals'] = $_GET['teamone_goals'];
						$match[1]['goals'] = $_GET['teamtwo_goals'];
						if (isset($_GET['teamone_goals']) && $_GET['teamone_goals'] >= 10) {
							$winner = $match[0];
						}
						elseif (isset($_GET['teamtwo_goals']) && $_GET['teamtwo_goals'] >= 10) {
							$winner = $match[1];
						}
						$_SESSION['results'][] = $match;
					?>
					<h1 class="winner_title">Gratulerer, <?php echo $winner['name']; ?>!</h1>
					<h3 class="winner_sub_title">Dere vant</h3>
					<br />
					<?php if ($_GET['game'] < count($matches)) : ?>
						<div class="row">
							<div class="col-md-4 flg_action">
								<a class="btn btn-success btn-large btn-block" href="?game=<?php echo ($_GET['game'] + 1); ?>">Neste spill</a>
							</div>
						</div>
					<?php endif; ?>
					<?php else: ?>
					<div class="col-md-12">
						<legend class="clearfix">
							<span style="float:left; display: block; width: 45%;">
								<?php echo $match[0]['name']; ?>
							</span>
							<span style="float:left; display: block; width: 10%; text-align: center;">
								<small>vs</small>
							</span>
							<span style="float:left; display: block; width: 45%; text-align: right;">
								<?php echo $match[1]['name']; ?>
							</span>
						</legend>
					</div>
					<div class="col-xs-6">
						<div class="goals" id="teamone_goals">
							<?php echo ((isset($_GET['teamone_goals'])) ? $_GET['teamone_goals'] : '0'); ?>
						</div>
						<a href="?game=<?php echo $_GET['game']; ?>&teamone_goals=<?php echo (((isset($_GET['teamone_goals'])) ? $_GET['teamone_goals'] : '0') + 1); ?>&teamtwo_goals=<?php echo ((isset($_GET['teamtwo_goals'])) ? $_GET['teamtwo_goals'] : '0'); ?>" class="btn btn-large btn-primary btn-block new_goal" id="teamone_new_goal">&nbsp;<br />Mål<br/>&nbsp;</a>
						<br/>
						<label>Spillere:</label>
						<br />
						<ul class="list-unstyled">
						<?php foreach ($match[0]['players'] as $player) : ?>
							<li><?php echo $player; ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
					<div class="col-xs-6">
						<div class="goals" id="teamtwo_goals">
							<?php echo ((isset($_GET['teamtwo_goals'])) ? $_GET['teamtwo_goals'] : '0'); ?>
						</div>
						<a href="?game=<?php echo $_GET['game']; ?>&teamone_goals=<?php echo ((isset($_GET['teamone_goals'])) ? $_GET['teamone_goals'] : '0'); ?>&teamtwo_goals=<?php echo (((isset($_GET['teamtwo_goals'])) ? $_GET['teamtwo_goals'] : '0') + 1); ?>" class="btn btn-large btn-primary btn-block new_goal" id="teamtwo_new_goal">&nbsp;<br />Mål<br/>&nbsp;</a>
						<br/>
						<label>Spillere:</label>
						<br />
						<ul class="list-unstyled">
						<?php foreach ($match[1]['players'] as $player) : ?>
							<li><?php echo $player; ?></li>
						<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<script src="/assets/vendor/jquery/jquery.js"></script>
		<script src="/assets/vendor/bootstrap/js/button.js"></script>
		<script src="/assets/vendor/bootstrap/js/dropdown.js"></script>
		<script src="/assets/vendor/bootstrap/js/tooltip.js"></script>
		<script src="/assets/vendor/bootstrap-select/bootstrap-select.js"></script>
		<script src="/assets/js/common.js"></script>
	</body>
</html>
