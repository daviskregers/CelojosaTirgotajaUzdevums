<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Dāvis Krēgers &mdash; Vairāku mērķu optimizācijas uzdevums</title>
	<style>
		body {
			padding-top: 200px;
		}
		nav {
			width: 100%;
			background: #cdcdcd;
			position: fixed;
			height: 200px;
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


<?php 

require 'helpers.php';
require 'rand.class.php';
require 'algoritms.class.php';

$rand = new RND_Skaitlis();

// /* Sākuma mainīgie */

$pilsetas = array(
		'Pampāļi' => 'Pampáli, Pampāļu pagasts', 
		'Lielvārde' => 'Lielvārde, Lielvārdes pilsēta', 
		'Ēdole' => 'Édole, Ēdoles pagasts', 
		'Ogre' => 'Ogre, Ogres pilsēta',
		'Madona' => 'Madona, Madonas pilsēta',
		'Aglona' => 'Aglona, Aglona Parish',
		'Dricāni' => 'Dricāni, Dricānu pagasts',
		'Talsi' => 'Talsi, Talsu pilsēta',
		'Rīga' => 'Rīgas pilsēta');

$p_keys = array_keys($pilsetas);
$distancematrix = array(
	0 => array(
		0 => 0,
		1 => 195.015,
		2 => 78.635,
		3 => 182.114,
		4 => 313.736,
		5 => 366.193,
		6 => 386.165,
		7 => 116.327,
		8 => 145.07
		),
	1 => array(
		0 => 195.314,
		1 => 0,
		2 => 235.61,
		3 => 17.952,
		4 => 116.756,
		5 => 177.984,
		6 => 197.588,
		7 => 175.115,
		8 => 53.56
		),
	2 => array(
		0 => 79.773,
		1 => 235.141,
		2 => 0,
		3 => 222.241,
		4 => 353.863,
		5 => 406.32,
		6 => 426.292,
		7 => 78.661,
		8 => 178.65
		),
	3 => array(
		0 => 182.303,
		1 => 17.902,
		2 => 222.599,
		3 => 0,
		4 => 131.627,
		5 => 192.855,
		6 => 212.458,
		7 => 162.104,
		8 => 36.4
		),
	4 => array(
		0 => 314.577,
		1 => 116.763,
		2 => 354.873,
		3 => 132.807,
		4 => 0,
		5 => 131.326,
		6 => 106.484,
		7 => 283.239,
		8 => 166.5
		),
	5 => array(
		0 => 366.595,
		1 => 177.964,
		2 => 406.891,
		3 => 194.008,
		4 => 131.08,
		5 => 0,
		6 => 75.818,
		7 => 346.396,
		8 => 227.7
		),
	6 => array(
		0 => 386.303,
		1 => 197.301,
		2 => 426.599,
		3 => 213.345,
		4 => 105.822,
		5 => 75.811,
		6 => 0,
		7 => 366.105,
		8 => 247.04
		),
	7 => array(
		0 => 116.64,
		1 => 174.046,
		2 => 78.489,
		3 => 161.145,
		4 => 292.767,
		5 => 345.224,
		6 => 365.197,
		7 => 0,
		8 => 117.32
		),
	8 => array(
		0 => 146.018,
		1 => 53.938,
		2 => 174.756,
		3 => 37.04,
		4 => 167.284,
		5 => 228.512,
		6 => 252.471,
		7 => 117.58,
		8 => 0,
		),
	);

$sakuma_populacija = array(
		array('3 1 8 2 4 7 5 6'),
		array('6 2 7 8 1 4 3 5'),
		array('8 2 1 4 7 6 5 3'),
		array('7 1 8 2 4 3 5 6'),
		array('1 6 4 2 7 8 5 3'),
		array('3 8 4 5 1 6 2 7'),
		array('7 2 8 6 1 4 5 3'),
		array('7 2 5 8 4 6 3 1')
	);

for($i = 0; $i < count($sakuma_populacija); $i++) {
	$sakuma_populacija[$i][0] = explode(" ", $sakuma_populacija[$i][0]);
}

unset($pilsetas[8]);

$generacijas = 100;

$parametri = array(
	'precizitate' => 0.001,
	'dalijuma_skaitlis' => count($pilsetas) - 1 ,
	'selekcija' => 'turnīrs',
	'mutacijas_varbutiba' => 0.1,
	'sakuma_populacija' => $sakuma_populacija,
	'generacijas' => $generacijas,
	'distancu_matrica' => $distancematrix,
	'pilsetas' => $pilsetas,
	'p_keys' => $p_keys,
);
?>

<nav>
	<ul>
		<?php for($i = 0; $i <= $generacijas; $i++): ?>
			<li><a href="#generacija-<?php echo $i; ?>"><?php echo $i+1; ?>. ģenerācija</a></li>
		<?php endfor; ?>
	</ul>
</nav>

<?php $algoritms = new Algoritms($parametri); ?>

</body>
</html>