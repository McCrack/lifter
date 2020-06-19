<?php

$brancher->auth() or die("Access denied!");

$time = time();
$log = "<small class='gold'>".USER_NAME." [".date("d M, H:i:s")."]</small>";

switch(SUBPAGE){
	case "comment":
		$log .= "<div class='green'>".file_get_contents('php://input')."</div>";
		$mySQL->query("UPDATE gb_orders SET log=CONCAT('".$mySQL->escape_string($log)."',log) WHERE OrderID=".PARAMETER." LIMIT 1");

		print showLog();
	break;
	case "paid":
		$body = $mySQL->single_row("SELECT body FROM gb_orders WHERE OrderID=".PARAMETER." LIMIT 1")['body'];
		$cart = JSON::parse($body);
		if(SUBPARAMETER){
			foreach($cart as $id=>$cnt){
				$writeoff = $mySQL->query("UPDATE gb_stock set remainder=remainder-".$cnt." WHERE ItemID=".$id." AND stock LIKE 'reserved' LIMIT 1");
				if($writeoff){
					$mySQL->query("INSERT INTO gb_stock SET ItemID=".$id.",stock='waste',remainder=".$cnt." ON DUPLICATE KEY UPDATE remainder=remainder+".$cnt);
				}
			}
		}else foreach($cart as $id=>$cnt){
			$writeoff = $mySQL->query("UPDATE gb_stock set remainder=remainder-".$cnt." WHERE ItemID=".$id." AND stock LIKE 'waste' LIMIT 1");
			if($writeoff){
				$mySQL->query("INSERT INTO gb_stock SET ItemID=".$id.",stock='reserved',remainder=".$cnt." ON DUPLICATE KEY UPDATE remainder=remainder+".$cnt);	
			}
		}

		$log .= "<div>".(SUBPARAMETER?"Set":"Drop")." a <span class='red'>payment</span> marker</div>";
		$mySQL->query("UPDATE gb_orders SET paid=".(INT)SUBPARAMETER.", modified=".$time.", log=CONCAT('".$mySQL->escape_string($log)."',log) WHERE OrderID=".PARAMETER." LIMIT 1");

		print showLog();
	break;
	case "status":
		$order = $mySQL->single_row("SELECT status,body,UserID FROM gb_orders WHERE OrderID=".PARAMETER." LIMIT 1");
		$userID = ($order['status']=="new") ? USER_ID : $order['UserID'];
		$cart = JSON::parse($order['body']);
		if(SUBPARAMETER=="accepted"){
			foreach($cart as $id=>$cnt){
				$writeoff = $mySQL->query("UPDATE gb_stock set remainder=remainder-".$cnt." WHERE ItemID=".$id." AND stock LIKE 'main' LIMIT 1");
				if($writeoff){
					$mySQL->query("INSERT INTO gb_stock SET ItemID=".$id.",stock='reserved',remainder=".$cnt." ON DUPLICATE KEY UPDATE remainder=remainder+".$cnt);
				}else die("<div class='red'>Unknow error.</div>");
			}
		}elseif(($order['status']=="accepted") && SUBPARAMETER=="canceled") foreach($cart as $id=>$cnt){
			$writeoff = $mySQL->query("UPDATE gb_stock set remainder=remainder-".$cnt." WHERE ItemID=".$id." AND stock LIKE 'reserved' LIMIT 1");
			if($writeoff){
				$mySQL->query("INSERT INTO gb_stock SET ItemID=".$id.",stock='main',remainder=".$cnt." ON DUPLICATE KEY UPDATE remainder=remainder+".$cnt);
			}else die("<div class='red'>Unknow error.</div>");
		}
		$log .= "<div>Change status <span class='red'>".$order['status']."</span> to <span class='red'>".SUBPARAMETER."</span></div>";
		$mySQL->query("UPDATE gb_orders SET UserID=".$userID.", status='".SUBPARAMETER."', modified=".$time.", log=CONCAT('".$mySQL->escape_string($log)."',log) WHERE OrderID=".PARAMETER." LIMIT 1");

		print showLog();
	break;
	case "delivery":
		$log .= "<div>Change <span class='red'>delivery</span> options</div>";
		$data = file_get_contents('php://input');
		$mySQL->query("UPDATE gb_orders SET delivery='".$mySQL->escape_string($data)."', modified=".$time.", log=CONCAT('".$mySQL->escape_string($log)."',log) WHERE OrderID=".PARAMETER." LIMIT 1");

		print showLog();
	break;
	case "discount":
		$newdiscount = (INT)SUBPARAMETER;
		$discount = $mySQL->single_row("SELECT discount FROM gb_orders WHERE OrderID=".PARAMETER." LIMIT 1")['discount'];
		$log .= "<div>Change discount: <span class='red'>".$discount."%</span> to <span class='red'>".$newdiscount."%</span></div>";
		$mySQL->query("UPDATE gb_orders SET discount='".$newdiscount."', modified=".$time.", log=CONCAT('".$mySQL->escape_string($log)."',log) WHERE OrderID=".PARAMETER." LIMIT 1");

		print showLog();
	break;
	case "cart":
		$cart = JSON::load("php://input");
		$IDs = [];
		foreach($cart as $id=>$cnt) $IDs[] = $id;
		$body = $mySQL->query("SELECT ItemID,selling,currency,DiscountID,discount FROM gb_items LEFT JOIN gb_discounts USING(DiscountID) WHERE ItemID IN (".implode(",", $IDs).")");
		$total = 0;
		$cng = new config("../".$config->{"base folder"}."/config.init");
		foreach($body as $row){
			$discount = 0;
			$row['selling'] *= $cng->{$row['currency']};
			if($row['DiscountID'] && $row['discount']){
				$discount = floor($row['selling'] * $row['discount'] / 100);
			}
			$total += ($row['selling'] - $discount) * $cart[$row['ItemID']];
		}

		$log .= "<div>Change amount</div>";

		$mySQL->query("UPDATE gb_orders SET price='".money_format("%i", $total)."', body='".$mySQL->escape_string(JSON::encode($cart))."', modified=".$time.", log=CONCAT('".$mySQL->escape_string($log)."',log) WHERE OrderID=".PARAMETER." LIMIT 1");

		print showLog();
	break;

	case "serchclient":
		$phone = file_get_contents('php://input');
		$citizen = $mySQL->single_row("
		SELECT 
			Name,phone,Email,CommunityID,reputation 
		FROM gb_community 
		WHERE
			Phone LIKE '".$phone."' 
		LIMIT 1");

		if(empty($citizen)){
			print '{"status":"0"}';
		}else print JSON::encode([
			"status"=>1,
			"citizen"=>$citizen
		]);
	break;
	case "serchproduct":
		$code = explode("-", PARAMETER);
		$item = $mySQL->single_row("
		SELECT
			PageID,gb_items.ItemID AS ItemID,name,label,brand,preview,selling,currency,DiscountID,discount,caption,remainder
		FROM gb_items
		CROSS JOIN gb_stock USING(ItemID)
		CROSS JOIN gb_models USING(PageID)
		LEFT JOIN gb_discounts USING(DiscountID)
		WHERE
			gb_items.ItemID=".end($code)."
		AND stock LIKE 'main'
		LIMIT 1");

		if(!empty($item)):
			$cng = new config("../".$config->{"base folder"}."/config.init");
			$item['price'] = $item['selling'] *= $cng->{$item['currency']};
			if($item['DiscountID'] && $item['discount']){
				$item['selling'] -= floor($item['selling'] * $item['discount'] / 100);
			}?>
			<div class="sticker">
				<img src="<?=$item['preview']?>" width="100%">
				<div class="name">
					<small class="gold"><?=$item['brand']?></small><br>
					<?=($item['name'].' - '.$item['label'])?>
				</div>
				<div class="price">
					<?if($item['price']>$item['selling']):?>
						<div class="discount"><?=$item['caption']?></div>
						<s><?=$item['price']?></s>
					<?endif?>
					<span><?=$item['selling']?></span> грн
				</div>
				<div class="remainder">
					<span data-translate="textContent">remainder</span>: <?=$item['remainder']?> - 
					<input type="number" name="amount" value="<?=(($item['remainder']>0)?1:0)?>" max="<?=$item['remainder']?>" min="<?=(($item['remainder']>0)?1:0)?>">
					<br>
					<label class="btn"><input type="radio" name="addItemToOrder" data-id="<?=$item['ItemID']?>" hidden autocomplete="off"><span data-translate="textContent">add to order</span></label>
				</div>
			</div>
		<?endif;
	break;
	case "add":
		$code = explode("-", PARAMETER);
		$itemID = reset($code);
		$amount = end($code);
		$item = $mySQL->single_row("
		SELECT
			PageID,gb_items.ItemID AS ItemID,name,label,brand,preview,selling,currency,DiscountID,discount,caption,remainder
		FROM gb_items
		CROSS JOIN gb_stock USING(ItemID)
		CROSS JOIN gb_models USING(PageID)
		LEFT JOIN gb_discounts USING(DiscountID)
		WHERE
			gb_items.ItemID=".$itemID."
		AND stock LIKE 'main'
		LIMIT 1");

		
		$cng = new config("../".$config->{"base folder"}."/config.init");
		$item['price'] = $item['selling'] *= $cng->{$item['currency']};
		if($item['DiscountID'] && $item['discount']){
			$item['selling'] -= floor($item['selling'] * $item['discount'] / 100);
		}
		$sum = $item['selling'] * $amount;
		?>
		<tr>
			<td><img src="<?=$item['preview']?>" width="68"></td>
			<td>
				<small><?=($item['PageID'].'-'.$item['ItemID'])?></small><br>
				<b><?=($item['name'].' - '.$item['label'])?></b>
			</td>
			<td>
				<?if($item['price']>$item['selling']):?>
				<label><input type="checkbox" data-price="<?=$item['price']?>" value="<?=$item['selling']?>" checked autocomplete="off"><?=$item['caption']?></label><br>
				<?else:?>
				<input type="checkbox" data-price="<?=$item['price']?>" value="<?=$item['selling']?>" autocomplete="off" hidden>
				<?endif?>
				<input type="number" value="<?=$amount?>" data-id="<?=$item['ItemID']?>" max="<?=$item['remainder']?>" min="1"> ✕ <span class="price"></span> = <b class="sum"></b> грн
			</td>
			<td class="drop-row" align="center" onclick="form.dropRow(this.parentNode)">&#xe9ac;</td>
		</tr>
	<?break;
	case "addtoorder":
		$cart = JSON::load('php://input');
		list($itemID, $amount) = each($cart);
		
		$order = $mySQL->single_row("SELECT status,body FROM gb_orders WHERE OrderID=".PARAMETER." LIMIT 1");
		$body = JSON::parse($order['body']);
		$body[$itemID] += $amount;
		$mySQL->query("UPDATE gb_orders SET body='".JSON::encode($body)."' WHERE OrderID=".PARAMETER." LIMIT 1");
		if($order['status']=="accepted"){
			$writeoff = $mySQL->query("UPDATE gb_stock set remainder=remainder-".$amount." WHERE ItemID=".$itemID." AND stock LIKE 'main' LIMIT 1");
			if($writeoff){
				$mySQL->query("INSERT INTO gb_stock SET ItemID=".$itemID.",stock='reserved',remainder=".$amount." ON DUPLICATE KEY UPDATE remainder=remainder+".$amount);
			}else die("<div class='red'>Unknow error.</div>");
		}

		$item = $mySQL->single_row("
		SELECT
			PageID,gb_items.ItemID AS ItemID,name,label,brand,preview,selling,currency,DiscountID,discount,caption,remainder
		FROM gb_items
		CROSS JOIN gb_stock USING(ItemID)
		CROSS JOIN gb_models USING(PageID)
		LEFT JOIN gb_discounts USING(DiscountID)
		WHERE
			gb_items.ItemID=".$itemID."
		AND stock LIKE 'main'
		LIMIT 1");

		$cng = new config("../".$config->{"base folder"}."/config.init");
		$item['selling'] *= $cng->{$item['currency']};
		$discount = 0;
		if($item['DiscountID'] && $item['discount']){
			$discount = floor($item['selling'] * $item['discount'] / 100);
		}
		$item['selling'] -= $discount;
		$sum = $item['selling'] * $amount;
		$remainder = ($item['remainder']>=$amount);

		?>
		<tr>
			<td bgcolor="white" align="center"><img src="<?=$item['preview']?>" width="64"></td>
			<td>
				<small>id: <?=($item['PageID'].'-'.$item['ItemID'])?></small><br>
				<b><?=($item['name'].' - '.$item['label'])?></b><br>
				<span class="green"><?=$item['brand']?></span>
			</td>
			<td>
				<?if($discount):?><div class="red"><?=$item['caption']?></div><?endif?>
				<b><?=$amount?></b>
				 ✕ <?=$item['selling']?><br>
				<b class="sum"><?=$sum?></b> грн
			</td>
			<?if($order['status']=="new"):?>
			<td align="center" class="<?=($remainder?'green':'red')?>">
				<span data-translate="textContent">remainder</span>: <b calss="remainder"><?=$item['remainder']?></b>
			</td>
			<?endif?>
		</tr>
	<?break;
	case "create":
		$p = JSON::load('php://input');
		$citizen = $mySQL->single_row("SELECT CommunityID,reputation FROM gb_community WHERE CommunityID=".$p['citizen']." LIMIT 1");
		
		if(empty($citizen)){
			$citizen = [
				"reputation"=>0,
				"CommunityID"=>$mySQL->insert("gb_community",[
					"CitizenID"=>$mySQL->single_row("SELECT MAX(CitizenID) AS CitizenID FROM gb_community LIMIT 1")['CitizenID']+1,
					"Name"=>$p['name'],
					"Phone"=>$p['phone'],
					"Email"=>$p['email']
				])
			];
		}else $mySQL->query("UPDATE gb_community SET reputation=reputation+1, Name='".$p['name']."',Email='".$p['email']."' WHERE CommunityID=".$p['citizen']." LIMIT 1");
		
		foreach($p['body'] as $id=>$cnt){
			$writeoff = $mySQL->query("UPDATE gb_stock SET remainder=remainder-".$cnt." WHERE ItemID=".$id." AND stock LIKE 'main' LIMIT 1");
			if($writeoff){
				$mySQL->query("INSERT INTO gb_stock SET ItemID=".$id.",stock='reserved',remainder=".$cnt." ON DUPLICATE KEY UPDATE remainder=remainder+".$cnt);
			}else die("Unknow Error.");
		}
		
		$log .= "<div>Created order<br>Buyer reputation: <span class='gold'>".$citizen['reputation']."</span><br>Pice: <span class='green'>".$p['price']." uah</span></div>";
		$OrderID = $mySQL->insert("gb_orders",[
			"CommunityID"=>$citizen['CommunityID'],
			"UserID"=>USER_ID,
			"created"=>$time,
			"modified"=>$time,
			"type"=>$p['type'],
			"status"=>"accepted",
			"payment"=>$p['payment'],
			"price"=>$p['price'],
			"delivery"=>JSON::encode($p['delivery']),
			"body"=>JSON::encode($p['body']),
			"log"=>$mySQL->escape_string($log)
		]);
		print $OrderID;
	break;
	default:break;
}


function showLog(){
	global $mySQL;
	return $mySQL->single_row("SELECT log FROM gb_orders WHERE OrderID=".PARAMETER." LIMIT 1")['log'];
}
?>