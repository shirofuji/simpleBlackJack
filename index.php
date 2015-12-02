<h1>BlackJack</h1>
<?php 
session_start();
define('BL','0.0.1');
define('DECKCOUNT',5);

$suits = ['&#9824;','&#9827;','&#9829;','&#9830;'];
$cards = ['A',2,3,4,5,6,7,8,9,10,'J','Q','K'];

$deck = [];
for($i = 0;$i<DECKCOUNT;$i++){
	foreach($suits as $suit){
		foreach($cards as $card){
			$deck[] = $card.$suit;
		}
	}
}
shuffle($deck);
if(isset($_SESSION['started']) && $_SESSION['started']){
}else{
	$_SESSION['deck'] = serialize($deck);
	$_SESSION['dealer_hand'] = serialize([]);
	$_SESSION['my_hand'] = serialize([]);
}
$do = isset($_GET['do'])?$_GET['do']:'';
function get_value($hand){
	$value = 0;
	foreach($hand as $card){
		$card = str_replace('K','10',$card);
		$card = str_replace('Q','10',$card);
		$card = str_replace('J','10',$card);
		$value += intval($card);
	}
	foreach($hand as $card){
		if(strpos($card,'A') !== FALSE){
			if($value <= 10){
				$card = str_replace('A','11',$card);
			}else{
				$card = str_replace('A','1',$card);
			}
			$value += intval($card);
		}
	}
	return $value;
}
switch($do){
	case 'start':
		if(isset($_SESSION['started']) && $_SESSION['started']){
			die('game has already started. <a href="index.php?do=continue">click here to continue</a>');
		}else{
			$_SESSION['started'] = true;
			$deck = unserialize($_SESSION['deck']);
			$dealer_hand = [
				$deck[1],
				$deck[3]
			];
			$my_hand = [
				$deck[0],
				$deck[2]
			];
			$_SESSION['dealer_hand'] = serialize($dealer_hand);
			$_SESSION['my_hand'] = serialize($my_hand);
			for($i = 0; $i < 4; $i++){
				unset($deck[$i]);
			}
			$deck = array_values($deck);
			$_SESSION['deck'] = serialize($deck);
			echo "Dealer: ";
			foreach($dealer_hand as $dealer_card){
				if(get_value($dealer_hand) == 21){
					echo $dealer_card." ";
				}else{
					echo "&#9632; ";
				}
			}
			echo "<hr/>";
			echo "Me: ";
			foreach($my_hand as $my_card){
				echo $my_card." ";
			}
			if(get_value($my_hand) == 21){
				echo "<script>alert('Win')</script>";
				echo "<hr/><a href='index.php?do=end'>End</a>";
			}else if(get_value($dealer_hand) == 21){
				echo "<script>alert('Lose')</script>";
				echo "<hr/><a href='index.php?do=end'>End</a>";
			}else{
				echo "<hr/><a href='index.php?do=hit'>Hit</a> <a href='index.php?do=good'>Good</a>";
			}
		}
		break;
	case 'continue':
		if(isset($_SESSION['started']) && $_SESSION['started']){
			$my_hand = unserialize($_SESSION['my_hand']);
			$dealer_hand = unserialize($_SESSION['dealer_hand']);
			echo "Dealer: ";
			foreach($dealer_hand as $dealer_card){
				echo "&#9632; ";
			}
			echo "<hr/>";
			echo "Me: ";
			foreach($my_hand as $my_card){
				echo $my_card." ";
			}
			if(get_value($my_hand) > 21){
				echo "<script>alert('Busted');</script>";
				echo "<hr/><a href='index.php?do=end'>End</a>";
			}else if(get_value($my_hand) == 21){ 
				echo "<script>alert('Win');</script>";
				echo "<hr/><a href='index.php?do=end'>End</a>";
			}else{
				echo "<hr/><a href='index.php?do=hit'>Hit</a> <a href='index.php?do=good'>Good</a>";
			}
		}else{
			die('game has already ended. <a href="index.php?do=end">click here to continue</a>');
		}
		break;
	case 'hit':
		if(isset($_SESSION['started']) && $_SESSION['started']){
			$deck = unserialize($_SESSION['deck']);
			$my_hand = unserialize($_SESSION['my_hand']);
			$dealer_hand = unserialize($_SESSION['dealer_hand']);
			if(count($my_hand) < 5){
				$my_hand[] = $deck[0];
				unset($deck[0]);
				$deck = array_values($deck);
			}
			$_SESSION['deck'] = serialize($deck);
			$_SESSION['my_hand'] = serialize($my_hand);
			echo "Dealer: ";
			foreach($dealer_hand as $dealer_card){
				echo "&#9632; ";
			}
			echo "<hr/>";
			echo "Me: ";
			foreach($my_hand as $my_card){
				echo $my_card." ";
			}
			if(get_value($my_hand) > 21){
				echo "<script>alert('Busted');</script>";
				echo "<hr/><a href='index.php?do=end'>End</a>";
			}else if(get_value($my_hand) == 21){ 
				echo "<script>alert('Win');</script>";
				echo "<hr/><a href='index.php?do=end'>End</a>";
			}else{
				echo "<hr/><a href='index.php?do=hit'>Hit</a> <a href='index.php?do=good'>Good</a>";
			}
		}else{
			die('game has already ended. <a href="index.php?do=end">click here to continue</a>');
		}
		break;
		break;
	case 'good':
		if(isset($_SESSION['started']) && $_SESSION['started']){
			$my_hand = unserialize($_SESSION['my_hand']);
			$dealer_hand = unserialize($_SESSION['dealer_hand']);
			$my_value = get_value($my_hand);
			$dealer_value = get_value($dealer_hand);
			while(($dealer_value < $my_value && $dealer_value < 21) || ($my_value < 17 && $dealer_hand <= $my_hand)){
				$dealer_hand[] = $deck[0];
				unset($deck[0]);
				$deck = array_values($deck);
				$dealer_value = get_value($dealer_hand);
			}
			echo "Dealer: ";
			foreach($dealer_hand as $dealer_card){
				echo $dealer_card." ";
			}
			echo "<hr/>";
			echo "Me: ";
			foreach($my_hand as $my_card){
				echo $my_card." ";
			}
			if(($my_value > $dealer_value && $my_value <= 21) || ($my_value < 21 && $dealer_value > 21)){
				echo "<script>alert('Win')</script>";
			}else if($my_value == $dealer_value){
				echo "<script>alert('Draw')</script>";
			}else if($dealer_value <= 21){
				echo "<script>alert('Lose')</script>";
			}
			echo "<hr/><a href='index.php?do=end'>End</a>";
		}else{
			die('game has already ended. <a href="index.php?do=end">click here to continue</a>');
		}
		break;
	case 'end':
		echo "<hr/><a href='index.php?do=start'>start</a>";
		session_destroy();
		break;
}