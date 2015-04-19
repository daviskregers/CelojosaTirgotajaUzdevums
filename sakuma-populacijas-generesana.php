<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Dāvis Krēgers &mdash; Vairāku mērķu optimizācijas uzdevums</title>
</head>
<body>

<?php

$used = array();
$options = [1, 2, 3, 4, 5, 6, 7, 8];

?>

<table>

	<?php for($i = 0; $i < count($options); $i++): ?>

		<tr>
			
			<?php 
			$used = array();
			$temp = $options;
			shuffle($temp);

				for($j = 0; $j < count($options); $j++) {
					
					$rand = rand(0, count($temp) - count($used) - 1);
					if($rand < 0) $rand = 0;
					$used[] = $temp[$rand];
					unset($temp[$rand]);
					

					// reset keys
					$temp2 = array();
					shuffle($temp);
					foreach($temp as $value) {
						$temp2[] = $value;
					}
					$temp = $temp2;
					unset($temp2);

				}

			?>
			<td><?php echo implode(' ', $used); ?></td>
			<?php unset($temp); unset($used); ?>
		</tr>

	<?php endfor; ?>

</table>

</body>
</html>
