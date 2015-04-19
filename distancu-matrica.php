<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Dāvis Krēgers &mdash; Vairāku mērķu optimizācijas uzdevums</title>
	<style>
		body {
			padding-top: 50px;
		}
		nav {
			width: 100%;
			background: #cdcdcd;
			position: fixed;
			height: 50px;
			margin-top: -50px;
		}
		nav ul li {
			display: inline;
			margin-left: 20px;
		}
		nav ul li a {
			color: #000;
			font-weight: bold;
			text-decoration: none;
		}
	</style>
</head>
<body>

<nav>
	<ul>
		<?php for($i = 0; $i <= 2; $i++): ?>
			<li><a href="#generacija-<?php echo $i; ?>"><?php echo $i+1; ?>. ģenerācija</a></li>
		<?php endfor; ?>
	</ul>
</nav>

<h1>Distanču matrica</h1>

<?php 

$pilsetas = array(
		'Pampāļi' => 'Pampáli, Pampāļu pagasts', 
		'Lielvārde' => 'Lielvārde, Lielvārdes pilsēta', 
		'Ēdole' => 'Édole, Ēdoles pagasts', 
		'Ogre' => 'Ogre, Ogres pilsēta',
		'Madona' => 'Madona, Madonas pilsēta',
		'Aglona' => 'Aglona, Aglona Parish',
		'Dricāni' => 'Dricāni, Dricānu pagasts',
		'Talsi' => 'Talsi, Talsu pilsēta',
		'Rīga' => 'Rīgas pilsēta'
	);
$p_keys = array_keys($pilsetas);
$distancematrix = array();

for($i = 0; $i < count($pilsetas); $i++) {
	for($j = 0; $j < count($pilsetas); $j++) {

		$from = $pilsetas[$p_keys[$i]];
		$to = $pilsetas[$p_keys[$j]];
		
		$from = urlencode($from);
		$to = urlencode($to);

		$data = file_get_contents("http://maps.googleapis.com/maps/api/distancematrix/json?origins=$from&destinations=$to&language=en-EN&sensor=false");
		$data = json_decode($data);

		$distance = 0;

		foreach($data->rows[0]->elements as $road) {
		    $distance += $road->distance->value;
		}

		echo "To: ".$data->destination_addresses[0];
		echo "<br/>";
		echo "From: ".$data->origin_addresses[0];
		echo "<br/>";
		echo "Distance: ".$distance." meters";
		echo "<br/>";

		$distancematrix[$i][$j] = $distance / 1000;

	}
}

// array(9) { [0]=> array(9) { [0]=> int(0) [1]=> float(195.015) [2]=> float(78.635) [3]=> float(182.114) [4]=> float(313.736) [5]=> float(366.193) [6]=> float(386.165) [7]=> float(116.327) [8]=> float(145.076) } [1]=> array(9) { [0]=> float(195.314) [1]=> int(0) [2]=> float(235.61) [3]=> float(17.952) [4]=> float(116.756) [5]=> float(177.984) [6]=> float(197.588) [7]=> float(175.115) [8]=> float(53.561) } [2]=> array(9) { [0]=> float(79.773) [1]=> float(235.141) [2]=> int(0) [3]=> float(222.241) [4]=> float(353.863) [5]=> float(406.32) [6]=> float(426.292) [7]=> float(78.661) [8]=> float(178.657) } [3]=> array(9) { [0]=> float(182.303) [1]=> float(17.902) [2]=> float(222.599) [3]=> int(0) [4]=> float(131.627) [5]=> float(192.855) [6]=> float(212.458) [7]=> float(162.104) [8]=> float(36.49) } [4]=> array(9) { [0]=> float(314.577) [1]=> float(116.763) [2]=> float(354.873) [3]=> float(132.807) [4]=> int(0) [5]=> float(131.326) [6]=> float(106.484) [7]=> float(283.239) [8]=> float(166.51) } [5]=> array(9) { [0]=> float(366.595) [1]=> float(177.964) [2]=> float(406.891) [3]=> float(194.008) [4]=> float(131.08) [5]=> int(0) [6]=> float(75.818) [7]=> float(346.396) [8]=> float(227.71) } [6]=> array(9) { [0]=> float(386.303) [1]=> float(197.301) [2]=> float(426.599) [3]=> float(213.345) [4]=> float(105.822) [5]=> float(75.811) [6]=> int(0) [7]=> float(366.105) [8]=> float(247.048) } [7]=> array(9) { [0]=> float(116.64) [1]=> float(174.046) [2]=> float(78.489) [3]=> float(161.145) [4]=> float(292.767) [5]=> float(345.224) [6]=> float(365.197) [7]=> int(0) [8]=> float(117.321) } [8]=> array(9) { [0]=> float(146.018) [1]=> float(53.938) [2]=> float(174.756) [3]=> float(37.04) [4]=> float(167.284) [5]=> float(228.512) [6]=> float(252.471) [7]=> float(117.58) [8]=> int(0) } }

?>
<h1>Distanču matrica</h1>
<table border=1>
<tr>
	<td>&nbsp;</td>
	<?php for($i = 0; $i < count($p_keys); $i++): ?>	
		<td><?php echo $p_keys[$i]; ?></td>
	<?php endfor; ?>
</tr>
<?php
for($i = 0; $i < count($distancematrix); $i++):
	?>
	<tr>
		<td><?php echo $p_keys[$i]; ?></td>
		<?php
		for($j = 0; $j < count($distancematrix[$i]); $j++): ?>
			<td><?php echo $distancematrix[$i][$j]; ?></td>
		<?php endfor;
		?>
	</tr>
	<?php
endfor;
?>
</table>
</body>
</html>
