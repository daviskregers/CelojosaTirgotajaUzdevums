<?php

/**
 * Vairāku mērķu optimizācijas uzdevuma algoritms
 *
 * PHP version 5
 *
 * @author     Dāvis Krēgers <davis@image.lv>
 * @copyright  2015 Dāvis Krēgers
 * @license    https://creativecommons.org/publicdomain/zero/1.0/ CC0 1.0 Universal (CC0 1.0) 
 * @version    SVN: $Id$
 * @link       http://faili.deiveris.lv/genetiskais-algoritms1/
 */

require_once 'rand.class.php';

Class Algoritms {
	private $sakuma_populacija,
			$populacija, $berni,
			$mutacijas_varbutiba, 
			$intervals, $precizitate, $max_vertiba, $max_vertiba_binary, $dalijuma_skaitlis, $populacijas_info,
			$selekcija, $generacijas, $individiem_intervali, $rand, $krustosanas_intervali, $krustosanas_pari,
			$mutacijas_elementi, $mutacijas_genu_intervali, $selekcijas_elementi, $selekcijas_intervali, $selekcijas_elementi_rezultats,
			$distancu_matrica, $pilsetas, $p_keys;

	public function __construct($options) {
		foreach($options as $key => $option) {
			$this->$key = $option;
		}
		$this->populacijas_info = array('sum' => 0,'max' => array('key' => 0, 'val' => 0),'avg' => 0);
		$this->krustosanas_intervali = array();

		$this->selekcija = 'turnirs';

		$this->rand = new RND_Skaitlis();
		$this->loop();

	}

	function piemerotiba( $individs ) {
		$previous = 0; $sum = 0;
		foreach($individs as $pilseta) {
			if($previous == 0) {
				$i_sum = $this->distancu_matrica[count($this->distancu_matrica)-1][$pilseta-1];
				// echo "d(X, ".$pilseta.") = ".$i_sum."; <br />";

			} else {
				$i_sum = $this->distancu_matrica[$previous-1][$pilseta-1];
				// echo "d(".$previous.", ".$pilseta.") = ".$i_sum."; <br />";
			}
			$previous = $pilseta; 
			$sum += $i_sum;

		}
		echo "<br />";
		return $sum;
	}

	protected function loop() {

		for($i = 0; $i < $this->generacijas; $i++) {
			
			if($i != 0) echo "<h1 id=\"generacija-".$i."\">".($i+1).". Ģenerācija</h1>";
			else {
				?>
				<h1>Sākums</h1>
				<!-- <p>Intervāls: [<?php echo $this->intervals[0]; ?>; <?php echo $this->intervals[1]; ?>]</p>
				<p>Precizitāte: <?php echo $this->precizitate; ?></p>
				<p>MAX vērtība: <?php echo $this->max_vertiba; ?> => <?php echo $this->max_vertiba_binary; ?></p>
				<p>Dalījumu skaits: <?php echo $this->dalijuma_skaitlis; ?></p>
				<p>Sākuma vērtības populācijai: <?php echo count($this->sakuma_populacija); ?></p> -->

				<?php
				echo $this->print_distancu_matrica();
				?>


				<h1 id="generacija-0">Sākuma populācija</h1>
				<?php
			}

			if($i == 0) $this->populacija = $this->sakuma_populacija;

			$this->populacijas_piemerotiba();
			$this->populacijas_izvade();

			$this->individiem_aprekinatie_intervali();
			$this->individiem_aprekinatie_intervali_izvade();

			// if($this->selekcija == 'turnirs') $this->turnirs_paru_veidosana();
			// else $this->rulete_paru_veidosana();

			$this->turnirs_paru_veidosana();

			$this->krustosanas_intervali();
			$this->individu_krustosana();

			$this->individu_parbaude_mutacijai();
			$this->intervali_mutejosiem_geniem();

			$this->mutacija();

			$this->jaunas_paaudzes_selekcija();
			$this->jaunas_paaudzes_selekcija_intervali();

			if($this->selekcija == 'turnirs') $this->turnirs_jaunas_paaudzes_selekcija();
			else $this->rulete_jaunas_paaudzes_selekcija();

			$this->jauna_paaudze();



		}

		echo "<h1 id=\"generacija-".($i)."\">".($i+1).". Ģenerācija</h1>";
		$this->populacijas_piemerotiba();
		$this->populacijas_izvade();

	}

	protected function print_distancu_matrica() {
		?>

			<h1>Distanču matrica</h1>
			<table border=1>
				<tr>
					<td>&nbsp;</td>
					<?php for($i = 0; $i < count($this->p_keys); $i++): ?>	
						<td><?php echo $this->p_keys[$i]; ?></td>
					<?php endfor; ?>
				</tr>
				<?php
				for($i = 0; $i < count($this->distancu_matrica); $i++):
					?>
					<tr>
						<td><?php echo $this->p_keys[$i]; ?></td>
						<?php
						for($j = 0; $j < count($this->distancu_matrica[$i]); $j++): ?>
							<td><?php echo $this->distancu_matrica[$i][$j]; ?></td>
						<?php endfor;
						?>
					</tr>
					<?php
				endfor;
				?>
			</table>

		<?php
	}

	protected function jauna_paaudze() {
		$paaudze = array();
		foreach($this->selekcijas_elementi_rezultats as $key => $val) {
			$paaudze[] = $this->selekcijas_elementi[$val[1]];
		}
		$this->berni = array();
		$this->populacija = $paaudze;
	}

	protected function turnirs_jaunas_paaudzes_selekcija() {
		?>

			<h5>Turnīra selekcija jaunajai ģenerācijai</h5>
			<table border="1">
				<tr>
					<th>Gadījuma skaitlis no tabulas</th>
					<th>Izvēlētie indivīdi un piemērotība</th>
					<th>Uzvarētājs</th>
				</tr>
				
				<?php 
				$c = 0; $ParuVeidosana = array();
				for($i = 1; $i <= count($this->selekcijas_elementi); $i++) {
					$randVal = floatval($this->rand->generate()); $randKeys = array_keys($this->selekcijas_intervali); $randEl = 0;
					for($j = 0; $j < count($this->selekcijas_intervali); $j++) {
						if($randVal < floatval($randKeys[$j])) {
							$randKeys[$j];
							if($j > 0) $randEl = $this->selekcijas_intervali[$randKeys[$j-1]];
							else $randEl = $this->selekcijas_intervali[$randKeys[0]];
							break;
						}
					}
					$ParuVeidosana[] = array($randVal, $randEl);
				}

				$KrustosanasPari = $ParuVeidosana;
				for($i = 0; $i < count($ParuVeidosana); $i++):  ?>
				<tr>
					<td><?php echo $ParuVeidosana[$i][0]; ?></td>
					<td><?php echo ($ParuVeidosana[$i][1]+1). " (".skaitlis($this->selekcijas_elementi[$ParuVeidosana[$i][1]]['piemerotiba']).")"; ?></td>

					<?php if($i % 2 == 0): ?>
						<?php if($this->selekcijas_elementi[$ParuVeidosana[$i][1]]['piemerotiba'] < $this->selekcijas_elementi[$ParuVeidosana[$i+1][1]]['piemerotiba']): ?>
							<td rowspan=2><?php echo $ParuVeidosana[$i][1]+1; ?></td>
							<?php unset($KrustosanasPari[$i+1]); ?>
						<?php else: ?>
							<td rowspan=2><?php echo $ParuVeidosana[$i+1][1]+1; ?></td>
							<?php unset($KrustosanasPari[$i]); ?>
						<?php endif; ?>
					<?php endif; ?>

				</tr>
				<?php endfor; ?>


			</table>
		<?php
		$krustosana = array();
		foreach($KrustosanasPari as $val) { // atslegas neiet viena pec otras, reset
			$krustosana[] = $val;
		}
		$this->selekcijas_elementi_rezultats = $krustosana;
	}

	protected function jaunas_paaudzes_selekcija_intervali() {
		if($this->selekcija == 'turnirs'):
		?>

		<h5>Indivīdiem aprēķinātie intervāli</h5>

		<table border=1>
			<tr>
				<th>Indivīds</th>
				<th>Varbūtība tikt izvēlētam</th>
				<th>Kumulatīvā varbūtība</th>
				<th>Intervāls</th>
			</tr>
			<?php 
			$kumul = 0;
			for($i = 1; $i <= count($this->selekcijas_elementi); $i++):
				

				
					$probability = 1 / count($this->selekcijas_elementi);
					$prev = $kumul;
					$kumul += $probability;
					$slekc_int[''.$kumul] = $i;
					?>
					<tr>
						<td><?php echo $i; ?></td>
						<td><?php echo prbsk($probability); ?></td>
						<td><?php echo prbsk($kumul); ?></td>
						<td>
							<?php 
								echo ($i == 0) ? 
								"[".prbsk($prev).";".prbsk($kumul)."]" : 
								"(".prbsk($prev).";".prbsk($kumul)."]"; 
							?>
						</td>
					</tr>
					<?php

			endfor; ?>
		</table>
		<?php

		else:
			
			$piem_summa = 0;
			for ($i=0; $i < count($this->selekcijas_elementi); $i++) { 
				$piem_summa += $this->selekcijas_elementi[$i]['piemerotiba'];
			}

			$probability = $this->selekcijas_elementi[$i-1]['piemerotiba'] / $piem_summa;
			$kumul = 0;

			for($i = 1; $i <= count($this->selekcijas_elementi); $i++):
			
				$probability = 1 / count($this->selekcijas_elementi);
				$prev = $kumul;
				$kumul += $probability;
				$slekc_int[''.$kumul] = $i;

			endfor; 
		endif;

		$this->selekcijas_intervali = $slekc_int;
	}

	protected function jaunas_paaudzes_selekcija() {
		?>
		<h4>Tekošā populācija</h4>
		<table border=1>
			<tr>
				<th>Nr</th>
				<th>Indivīds</th>
				<th>Ceļa garums</th>
			</tr>
			<?php $i = 0; $selekcija = array();
			foreach($this->populacija as $key => $val): $i++; $selekcija[] = $val; ?>
			<tr>
				<td><?php echo $i; ?></td>
				<td><?php echo implode("",$val[0]); ?></td>
				<td><?php echo skaitlis($val['piemerotiba']); ?></td>
			</tr>
			<?php endforeach;
			foreach($this->berni as $key => $val): $i++; ?>
			<tr>
				<?php
					$piemerotiba = $this->piemerotiba($val);
				?>
				<td><?php echo $i; ?></td>
				<td><?php echo implode("",$val); ?></td>
				<td><?php echo skaitlis($piemerotiba); ?></td>
			</tr>
			<?php 
			 $selekcija[] = array($val, 'piemerotiba' => $piemerotiba);
			// $selekcija[] = array('x' => $x, 'y' => $y, 'xd' => $xd, 'yd' => $yd, 'BIN' => $val, 'piemerotiba' => $piemerotiba);
			endforeach; ?>
		</table>
		<?php
		$this->selekcijas_elementi = $selekcija;
	}

	protected function rnd_int_piemeklesana($rnd, $not_allowed = -1) {
		$randKeys = array_keys($this->mutacijas_genu_intervali);
		for($j = 0; $j < count($this->mutacijas_genu_intervali); $j++) {
			if($rnd < floatval($randKeys[$j])) {

				$randKeys[$j];
				if($j > 0) $gens = $this->mutacijas_genu_intervali[$randKeys[$j-1]];
				else $gens = $this->mutacijas_genu_intervali[$randKeys[0]];

				if($gens == $not_allowed && $not_allowed != -1) {
					return $this->rnd_int_piemeklesana($this->rand->generate(), $rnd);
				}

				return $gens;
			}
		}
	}

	protected function mutacija() {
		?>
		<h4>Mutācija</h4>
		<table border=1>
			<tr>
				<th>Pirms</th>
				<th>1. RND</th>
				<th>2. RND</th>
				<th>1. Gēns</th>
				<th>2. Gēns</th>
				<th>Pēc</th>
			</tr>
			<?php foreach($this->mutacijas_elementi as $key => $el): ?>
			<tr>
				<?php if($el[1] == true): ?>
					<td><?php echo implode("",$this->berni[$el[0]]); ?></td>
						<?php
							$rnd = $this->rand->generate();
							$rnd2 = $this->rand->generate();

							$gens = $this->rnd_int_piemeklesana($rnd);
							$gens2 = $this->rnd_int_piemeklesana($rnd2, $gens);

							$tmp = $this->berni[$el[0]];
							$p = $tmp[$gens-1];
							$tmp[$gens-1] = $tmp[$gens2-1];
							$tmp[$gens2-1] = $p;
							$this->berni[$el[0]] = $tmp;

						?>
					<td><?php echo $rnd; ?></td>
					<td><?php echo $rnd2; ?></td>
					<td><?php echo $gens+1; ?></td>
					<td><?php echo $gens2+1; ?></td>
					<td><?php echo implode("",$this->berni[$el[1]]); ?></td>
				<?php else: ?>
					<td><?php echo implode("",$this->populacija[$el[0]][0]); ?></td>
					<?php
							$rnd = $this->rand->generate();
							$rnd2 = $this->rand->generate();

							$gens = $this->rnd_int_piemeklesana($rnd);
							$gens2 = $this->rnd_int_piemeklesana($rnd2, $gens);

							$tmp = $this->populacija[$el[0]][0];
							$p = $tmp[$gens-1];
							$tmp[$gens-1] = $tmp[$gens2-1];
							$tmp[$gens2-1] = $p;
							$this->populacija[$el[0]][0] = $tmp;

						?>
					<td><?php echo $rnd; ?></td>
					<td><?php echo $rnd2; ?></td>
					<td><?php echo $gens; ?></td>
					<td><?php echo $gens2; ?></td>
					<td><?php echo implode("",$this->populacija[$el[0]][0]); ?></td>
				<?php endif; ?>
			</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}

	protected function intervali_mutejosiem_geniem() {
		?>
		<h4>Intervāli mutējošiem gēniem</h4>
		<?php $mutacijas_int = array();?>

		<table border=1>
			<tr>
				<th>Gēns</th>
				<th>Varbūtība tikt izvēlētam</th>
				<th>Kumulatīva varbūtība</th>
				<th>Intervāls</th>
			</tr>
			<?php $kumul = 0;
			for($i = 1; $i <= $this->dalijuma_skaitlis; $i++): 
				$prev = $kumul; $kumul += 1/($this->dalijuma_skaitlis); ?>
				<tr>
					<td><?php echo $i; ?></td>
					<td><?php echo prbsk(1/(2*$this->dalijuma_skaitlis)); ?></td>
					<td><?php echo prbsk($kumul); ?></td>
					<?php if($i==0): ?><td><?php echo "[".prbsk($prev).";".$kumul."]"; ?></td>
					<?php else: ?><td><?php echo "(".prbsk($prev).";".prbsk($kumul)."]"; ?></td>
					<?php endif;?>
				</tr>
			<?php 				
			$mutacijas_int[''.$kumul] = $i; 
			endfor; ?>
		</table>
		<?php
		$this->mutacijas_genu_intervali = $mutacijas_int;
	}

	protected function individu_parbaude_mutacijai() {
		$mutacijas_el = array(); ?>
		<h4>Indivīdu pārbaude mutācijai</h4>

		<table border=1>
			<tr>
				<th>Indivīds</th>
				<th>Gadījuma skaitlis</th>
				<th>Mutēs?</th>
			</tr>

			<?php foreach($this->populacija as $key => $val): $randZ = $this->rand->generate(); ?>
				<tr>
					<td><?php echo implode("",$val[0]); ?></td>
					<?php 
						$mutes = false;
						if(floatval($randZ) <= $this->mutacijas_varbutiba) {
							$mutacijas_el[] = array($key, false); // false - ir populacija, ne berns
							$mutes = true;
						}
					?>
					<td>
						<?php echo $randZ; ?> 
						<?php echo ($mutes == true) ? '&le;' : '>'; ?> 
						<?php echo $this->mutacijas_varbutiba; ?>
					</td>

					<td>
						<?php echo ($mutes == true) ? 'Jā' : 'Nē'; ?> 
					</td>

				</tr>
			<?php endforeach; ?>

			<?php foreach($this->berni as $key => $val): $randZ = $this->rand->generate(); ?>
				<tr>
					<td><?php echo implode("",$val); ?></td>
					<?php 
						$mutes = false;
						if(floatval($randZ) <= $this->mutacijas_varbutiba) {
							$mutacijas_el[] = array($key, true); // false - ir populacija, ne berns
							$mutes = true;
						}
					?>
					<td>
						<?php echo $randZ; ?> 
						<?php echo ($mutes == true) ? '&le;' : '>'; ?> 
						<?php echo $this->mutacijas_varbutiba; ?>
					</td>

					<td>
						<?php echo ($mutes == true) ? 'Jā' : 'Nē'; ?> 
					</td>

				</tr>
			<?php endforeach; ?>
		</table>
		<?php
		$this->mutacijas_elementi = $mutacijas_el;
	}

	protected function individu_krustosana() {
		// Dabujam gadijuma skaitlus un nosakam punktus

		$krustosana = $this->krustosanas_pari; $paris = array();


		$pari_krusosanai = (count($krustosana) % 2 > 0) ? (count($krustosana) - 1) / 2 : (count($krustosana)) / 2;
		for($i = 0; $i < $pari_krusosanai; $i++) {
			
			if($i > 0 && $krustosana[2*$i][1] == $krustosana[2*$i+1][1] || $i == 0 && $krustosana[0][1] == $krustosana[1][1]) {
				$paris[$i] = array('-', '-', '-', '-');
			}
			else {
				$paris[$i] = array($this->rand->generate(), $this->rand->generate());
				$randVal = floatval($paris[$i][0]); 

				$randKeys = array_keys($this->krustosanas_intervali[0]);

					for($j = 0; $j < count($this->krustosanas_intervali[0]); $j++) {
						if($randVal < floatval($randKeys[$j])) {
							if($j > 0) $paris[$i][] = $this->krustosanas_intervali[0][$randKeys[$j]];
							else $paris[$i][] = $this->krustosanas_intervali[0][$randKeys[0]];
							break;
						}
					}

					$randVal = floatval($paris[$i][1]); $randKeys = array_keys($this->krustosanas_intervali[1]);
					for($j = 0; $j < count($this->krustosanas_intervali[1]); $j++) {
						if($randVal < floatval($randKeys[$j])) {
							if($j > 0) $paris[$i][] = $this->krustosanas_intervali[1][$randKeys[$j]];
							else $paris[$i][] = $this->krustosanas_intervali[1][$randKeys[0]];
							break;
						}
					}
			}

			if($i > 0) $paris[$i][] = array($krustosana[2*$i], $krustosana[2*$i+1]);
			else $paris[$i][] = array($krustosana[0], $krustosana[1]);
		}


		// Krustojam
		$berni = array(); $iter_test = 0;
		foreach($paris as $key => $p) {
			$iter_test++;
			
			$start = $p[2]; $end = $p[3];

			if($start == '-' || $end == '-') {
				$this->berni[] = $this->populacija[$p[4][0][1]][0];
				$this->berni[] = $this->populacija[$p[4][1][1]][0];
			}
			else {
				
				$pirmais = $this->populacija[$p[4][0][1]][0];
				$otrais = $this->populacija[$p[4][1][1]][0];

				$pirmais_split = array('sakums' => array(), 'vidus' => array(), 'beigas' => array());
				$otrais_split = array('sakums' => array(), 'vidus' => array(), 'beigas' => array());


				// 1. solis 

				$j = 0;

				for($i = count($pirmais) - 1; $i >= $end; $i--) {
					$pirmais_split['beigas'][] = $pirmais[$i];
					$otrais_split['beigas'][] = $otrais[$i];
					unset($pirmais[$i]);
					unset($otrais[$i]);
				}

				$pirmais_split['beigas'] = str_split(strrev(implode("",$pirmais_split['beigas'])));
				$otrais_split['beigas'] = str_split(strrev(implode("",$otrais_split['beigas'])));

				for($i = 0; $i < $start; $i++) {
					$pirmais_split['sakums'][] = $pirmais[$i];
					$otrais_split['sakums'][] = $otrais[$i];
					unset($pirmais[$i]);
					unset($otrais[$i]);
				}

				foreach($pirmais as $ind) $pirmais_split['vidus'][] = $ind;
				foreach($otrais as $ind) $otrais_split['vidus'][] = $ind;


				unset($pirmais);
				unset($otrais);

				$pirmais_starpa = array();
				$otrais_starpa = array();

				// 2. solis

				$pirmais_starpa = str_split(implode("", $pirmais_split['beigas']).implode("", $pirmais_split['sakums']).implode("", $pirmais_split['vidus']));
				$otrais_starpa = str_split(implode("", $otrais_split['beigas']).implode("", $otrais_split['sakums']).implode("", $otrais_split['vidus']));

				// 3. solis

				for($i = 0; $i < count($pirmais_starpa); $i++) {
					if(in_array($pirmais_starpa[$i], $otrais_split['vidus'])) {
						unset($pirmais_starpa[$i]);
					}
					if(in_array($otrais_starpa[$i], $pirmais_split['vidus'])) {
						unset($otrais_starpa[$i]);
					}
				}

				$pirmais_starpa = str_split(implode("", $pirmais_starpa)); // reset keys
				$otrais_starpa = str_split(implode("", $otrais_starpa));

				// 4. solis
				$c = 0; $pirmais_gala = array(); $otrais_gala = array();

				for($i = 0; $i < count($pirmais_split['sakums']); $i++) {
					$pirmais_gala[] = $otrais_starpa[$i]; unset($otrais_starpa[$i]);
					$otrais_gala[] = $pirmais_starpa[$i]; unset($pirmais_starpa[$i]);
				}

				for($i = 0; $i < count($pirmais_split['vidus']); $i++) {
					$pirmais_gala[] = $pirmais_split['vidus'][$i];
					$otrais_gala[] = $otrais_split['vidus'][$i];
				}

				$pirmais_starpa = str_split(implode("", $pirmais_starpa)); // reset keys
				$otrais_starpa = str_split(implode("", $otrais_starpa));

				for($i = 0; $i < count($pirmais_split['beigas']); $i++) {
					$pirmais_gala[] = $otrais_starpa[$i]; unset($otrais_starpa[$i]);
					$otrais_gala[] = $pirmais_starpa[$i]; unset($pirmais_starpa[$i]);
				}

				$this->berni[] = $pirmais_gala;
				$this->berni[] = $otrais_gala;


			}


		}
		?>
		<h4>Indivīdu krustošana</h4>
		<table border=1>
			<tr>
				<th rowspan=2>Pāris</th>
				<th rowspan=2>Indivīds</th>
				<th colspan=4>Krustošanās punkti</th>
				<th rowspan=2>Bērni</th>
			</tr>
			<tr>
				<th>G.sk.</th>
				<th>1. punkts</th>
				<th>G.sk.</th>
				<th>2. punkts</th>
			</tr>
			<?php 
			$c = 0;
			for($i = 0; $i < $pari_krusosanai * 2; $i++): ?>
				<tr>
					<?php if($i % 2 == 0): $c++;  ?>
						<td rowspan=2><?php echo $c; ?></td>
					<?php endif; ?>

					<td>
						<?php echo implode("",$this->populacija[$krustosana[$i][1]][0]); ?>
					</td>

					<?php if($i % 2 == 0): ?>

						<td rowspan=2><?php echo $paris[$c-1][0]; ?></td>
						<?php if($paris[$c-1][2] != '-'): ?><td rowspan=2>Aiz <?php echo $paris[$c-1][2]; ?>. gēna</td>
						<?php else: ?><td rowspan=2>-</td><?php endif; ?>

						<td rowspan=2><?php echo $paris[$c-1][1]; ?></td>
						<?php if($paris[$c-1][3] != '-'): ?><td rowspan=2>Aiz <?php echo $paris[$c-1][3]; ?>. gēna</td>
						<?php else: ?><td rowspan=2>-</td><?php endif; ?>

					<?php endif; ?>

					<td>
						<?php echo implode("",$this->berni[$i]); ?>
					</td>

				</tr>
			<?php endfor; ?>
		</table>
		<?php
	}

	protected function krustosanas_intervali() {
		?>
		<h4>Krustošanas pozīciju intervāli</h4>
		<table border=1>
			<tr>
				<th colspan=2>Pirmais krustošanās punkts</th>
			</tr>
			<tr>
				<th>Pozīcija</th>
				<th>Intervāls</th>
				<?php 

					$kumul = 0; $count = $this->dalijuma_skaitlis / 2;
					$varb = 1 / $count;


					for($i = 1; $i <= $count; $i++ ): 
						$prev = $kumul; $kumul += $varb; 
						$this->krustosanas_intervali[0][''.$kumul] = $i;
					?>

						<tr>
							<td>Aiz <?php echo $i; ?>. gēna</td>
							<td>
								<?php echo prbsk($prev); ?> 
								<?php if($i == 1): ?>&le;<?php else: ?>&lt;<?php endif; ?>
								gad.sk. &le; 
								<?php echo prbsk($kumul); ?>
							</td>
						</tr>
				<?php endfor; ?>
			</tr>
		</table>

		<br />

		<table border=1>
			<tr>
				<th colspan=2>Otrais krustošanās punkts</th>
			</tr>
			<tr>
				<th>Pozīcija</th>
				<th>Intervāls</th>
				<?php 
					$kumul = 0; $count = $count - 1;
					$varb = 1 / $count;
					for($i = 1; $i <= $count; $i++ ): 
						$prev = $kumul; $kumul += $varb; 
						$this->krustosanas_intervali[1][''.$kumul] = $count+$i+1;
					?>
						<tr>
							<td>Aiz <?php echo $count+$i+1; ?>. gēna</td>
							<td>
								<?php echo prbsk($prev); ?> 
								<?php if($i == 1): ?>&le;<?php else: ?>&lt;<?php endif; ?>
								gad.sk. &le; 
								<?php echo prbsk($kumul); ?>
							</td>
						</tr>
				<?php endfor; ?>
			</tr>
		</table>
		<?php
	}

	protected function turnirs_paru_veidosana() {
		?>
		<h4>Pāru Veidošana</h4>
		<table border="1">
			<tr>
				<th>Gadījuma skaitlis no tabulas</th>
				<th>Izvēlētie indivīdi un piemērotība</th>
				<th>Uzvarētājs</th>
				<th>Pāris</th>
			</tr>
			
			<?php 
			$c = 0; $ParuVeidosana = array();
			for($i = 1; $i <= 2*count($this->populacija); $i++) {
				$randVal = floatval($this->rand->generate()); $randKeys = array_keys($this->individiem_intervali); $randEl = 0;
				for($j = 0; $j < count($this->individiem_intervali); $j++) {
					if($randVal < floatval($randKeys[$j])) {
						$randKeys[$j];
						if($j > 0) $randEl = $this->individiem_intervali[$randKeys[$j-1]];
						else $randEl = $this->individiem_intervali[$randKeys[0]];
						break;
					}
				}
				$ParuVeidosana[] = array($randVal, $randEl);
			}

			$KrustosanasPari = $ParuVeidosana;
			for($i = 0; $i < count($ParuVeidosana); $i++):  ?>
			<tr>
				<td><?php echo $ParuVeidosana[$i][0]; ?></td>
				<td><?php echo ($ParuVeidosana[$i][1]+1). " (".skaitlis($this->populacija[$ParuVeidosana[$i][1]]['piemerotiba']).")"; ?></td>

				<?php if($i % 2 == 0): ?>
					<?php if($this->populacija[$ParuVeidosana[$i][1]]['piemerotiba'] < $this->populacija[$ParuVeidosana[$i+1][1]]['piemerotiba']): ?>
						<td rowspan=2><?php echo $ParuVeidosana[$i][1]+1; ?></td>
						<?php unset($KrustosanasPari[$i+1]); ?>
					<?php else: ?>
						<td rowspan=2><?php echo $ParuVeidosana[$i+1][1]+1; ?></td>
						<?php unset($KrustosanasPari[$i]); ?>
					<?php endif; ?>
				<?php endif; ?>

				<?php if($i % 4 == 0 && $i != count($ParuVeidosana)): $c++;?>
					<td rowspan=4><?php echo $c; ?></td>
				<?php endif; ?>
			</tr>
			<?php endfor; ?>
		</table>
		<?php
		$krustosana = array();
		foreach($KrustosanasPari as $val) { // atslegas neiet viena pec otras, reset
			$krustosana[] = $val;
		}
		$this->krustosanas_pari = $krustosana;
	}

	protected function individiem_aprekinatie_intervali() {
		$kumul = 0;
		for($i = 1; $i <= count($this->populacija); $i++) {
			if($this->selekcija == 'turnirs'):
				$probability = 1 / count($this->populacija);
				$prev = $kumul;
				$kumul += $probability;
				$this->individiem_intervali[''.$kumul] = $i;
			else:
				$probability = $this->populacija[$i-1]['piemerotiba'] / $this->populacijas_info['sum'];
				$prev = $kumul;
				$kumul += $probability;
				$this->individiem_intervali[''.$kumul] = $i;
			endif;
		}
	}

	protected function individiem_aprekinatie_intervali_izvade() {
		?>
		<h4>Indivīdiem aprēķinātie intervāli</h4>

		<table border=1>
			<tr>
				<th>Indivīds</th>
				<th>Varbūtība tikt izvēlētam</th>
				<th>Kumulatīvā varbūtība</th>
				<th>Intervāls</th>
			</tr>
			<?php 
			$kumul = 0;
			for($i = 1; $i <= count($this->populacija); $i++):
				
				if($this->selekcija == 'turnirs'):
					$probability = 1 / count($this->populacija);
					$prev = $kumul;
					$kumul += $probability;
					?>
					<tr>
						<td><?php echo $i; ?></td>
						<td><?php echo prbsk($probability); ?></td>
						<td><?php echo prbsk($kumul); ?></td>
						<td>
							<?php 
								echo ($i == 0) ? 
								"[".prbsk($prev).";".prbsk($kumul)."]" : 
								"(".prbsk($prev).";".prbsk($kumul)."]"; 
							?>
						</td>
					</tr>
					<?php
				else:

					$probability = $this->populacija[$i-1]['piemerotiba'] / $this->populacijas_info['sum'];
					$prev = $kumul;
					$kumul += $probability;
					?>
					<tr>
						<td><?php echo $i; ?></td>
						<td><?php echo prbsk($probability); ?></td>
						<td><?php echo prbsk($kumul); ?></td>
						<td>
							<?php 
								echo ($i == 0) ? 
								"[".prbsk($prev).";".prbsk($kumul)."]" : 
								"(".prbsk($prev).";".prbsk($kumul)."]"; 
							?>
						</td>
					</tr>
					<?php



				endif;

			endfor; ?>
		</table>
		<?php
	}

	protected function populacijas_piemerotiba() {
		
		$this->populacijas_info = array('sum' => 0,'max' => array('key' => 0, 'val' => 0),'avg' => 0);
		foreach($this->populacija as $key => $individs) {
			
			$sum = 0; $previous = 0;
			$pilsetas = $individs[0];
			foreach($pilsetas as $pilseta) {
				if($previous == 0) {
					$i_sum = $this->distancu_matrica[count($this->distancu_matrica)-1][$pilseta-1];
					// echo "d(X, ".$pilseta.") = ".$i_sum."; <br />";

				} else {
					$i_sum = $this->distancu_matrica[$previous-1][$pilseta-1];
					// echo "d(".$previous.", ".$pilseta.") = ".$i_sum."; <br />";
				}
				$previous = $pilseta; 
				$sum += $i_sum;
			}
			// echo "Summa: ". $sum. "<Br /><Br />";
			$this->populacija[$key]['piemerotiba'] = $sum; 

			/* populacijas info */
			$this->populacijas_info['sum'] += $this->populacija[$key]['piemerotiba']; // Piemērotības summa
			if($this->populacija[$key]['piemerotiba'] > $this->populacijas_info['max']['val']) { // meklējam maksimālo vērtību
				$this->populacijas_info['max']['val'] = $this->populacija[$key]['piemerotiba'];
				$this->populacijas_info['max']['key'] = $key;
			}
		}
		$this->populacijas_info['avg'] = $this->populacijas_info['sum'] / count($this->populacija);
		$this->populacijas_info = $this->populacijas_info;

	}

	protected function populacijas_izvade() {
		?>
		<table border=1>
			<tr>
				<th>Nr</th>
				<th>Indivīds</th>
				<th>Ceļa garums</th>
			</tr>	
			
			<?php 
			foreach($this->populacija as $key => $individs): ?>
				<tr>
					<td><?php echo $key + 1; ?></td>
					<td><?php echo implode("",$individs[0]); ?></td>
					<td><?php echo skaitlis($individs['piemerotiba']); ?></td>
				</tr>

			<?php endforeach; ?>
		</table>
		<p>Kopējā piemērotība: <?php echo skaitlis($this->populacijas_info['sum']); ?></p>
		<p>MAX piemērotība: <?php echo skaitlis($this->populacijas_info['max']['val']); ?></p>
		<p>Vidējā piemērotība: <?php echo skaitlis($this->populacijas_info['avg']); ?></p>
		<?php
	}

}