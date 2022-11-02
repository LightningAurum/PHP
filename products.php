<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/productStyle.css">
		<title>Категории товаров</title>
	</head>
	<body>
		<?php
			$dsn = 'mysql:dbname=sitedb;host=127.0.0.1';
			$user = 'root';
			$password = '';
			$conn = new PDO($dsn, $user, $password);
			
			$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			if (strpos($url,'cat_id') == true) {
				$cat = $_GET['cat_id'];
				$page = $_GET['page_num'];

				$getGoodsAmountForSection = "SELECT COUNT(*) FROM ((SELECT goods.id, goods.header, image.path, image.alt, goods.description FROM goods
				LEFT JOIN image ON goods.picture_id = image.id INNER JOIN section ON goods.main_section_id = section.id
				WHERE goods.main_section_id = '$cat' AND goods.product_activity = true)
				UNION (SELECT goods.id, goods.header, image.path, image.alt, goods.description FROM goods
				LEFT JOIN image ON goods.picture_id = image.id INNER JOIN sections_list ON goods.sections_id = sections_list.product_id
				WHERE sections_list.section_id = '$cat' AND goods.product_activity = true)) AS amount;";
				$res = $conn->prepare($getGoodsAmountForSection);
				$res->execute();
				$row = $res->fetch();
				$count = ($row[0] - 12) / 12;

				if($page > (int)$count){
					header('Location: error.html');
				}

				$lastProduct = (int)($page * 12);
				$sqlGetSection = "SELECT * FROM section WHERE section.id='$cat'";
				$getGoodsInfo = "SELECT goods.id, goods.header, image.path, image.alt, section.name AS main_section_name FROM goods
				LEFT JOIN image ON goods.picture_id = image.id LEFT JOIN section ON goods.main_section_id = section.id
				WHERE goods.main_section_id = '$cat' AND goods.product_activity = true
				UNION SELECT goods.id, goods.header, image.path, image.alt, (SELECT section.name FROM section WHERE goods.main_section_id = section.id) AS main_section_name FROM goods
				LEFT JOIN image ON goods.picture_id = image.id INNER JOIN sections_list ON goods.sections_id = sections_list.product_id
				LEFT JOIN section ON sections_list.section_id = section.id WHERE sections_list.section_id = '$cat' AND goods.product_activity = true
				LIMIT $lastProduct, 12;";
				
		?>
				<header id='breadcrumb' style='background-color: rgb(180, 229, 158)'>
				<input id='prev' type='button' value='Назад' name='back' style='cursor: pointer;'>
				<ul class='breadcrumb' style='display: inline-block'>
				<li><a href='index.html'>Главная</a></li>
				<li><a href='products.php'>Категории</a></li>
		<?php
				$result = $conn->prepare($sqlGetSection);
				$result->execute();
				foreach($result as $row){
					echo "<li>" . $row["name"] . "</li>";
					break;
				}
		?>
				</ul></header>
		<?php
				$result = $conn->prepare($sqlGetSection);
				$result->execute();
				foreach($result as $row){
					echo "<h1 id='header'>$row[name]</h1>";
					setcookie("category", $row["name"], time() + 3600, false, true);
					setcookie("category_id", $row["id"], time() + 3600, false, true);
					break;
				}
		?>
				<body style='background-color: rgb(238, 238, 238)'>
		<?php
				$result = $conn->prepare($sqlGetSection);
				$result->execute();
				foreach($result as $row){
					$date = date('m/d/Y', time());
					echo "<div id='testCard'><p id='rightPos'>$date<br><br><b>" . $row["name"] . "</b><br><br>". $row["details"] . "</div><br><br>";
				}
				$result = $conn->prepare($getGoodsInfo);
				$result->execute();
		?>
				<div class='box'>
		<?php
				foreach($result as $row){
					echo "<a href='products.php?id=" . $row["id"] . "'>";
					echo "<div class='picturePrev' style='display: inline-block; text-align: center;'>";
					echo "<img src='" . $row["path"] . "' alt='" . $row["alt"] . "' style='max-height: 60%; max-width: 60%;'>";
					echo "<p class='cardP'>" . $row["header"] . "</p>";
					echo "<div class='desc'>" . $row["main_section_name"] . "</div></div></a>";
				}
		?>
				</div><div class='pages'>
		<?php
				$i = 0;
				while ($i != $page){
					echo "<a class='pageNum' href='products.php?cat_id=" . $cat . "&page_num=" . $i . "' style='text-align: center;'>$i</a>";
					$i++;
				}
				echo "<a class='pageNum' style='text-align: center;'>$page</a>";
				for ($j = $i + 1; $j < (int)$count + 1; $j++) {
					echo "<a class='pageNum' href='products.php?cat_id=" . $cat . "&page_num=" . $j . "' style='text-align: center;'>" . $j . "</a>";
				}
		?>
				</div>
		<?php
			}
			elseif (strpos($url,'id') == true) {
		?>
				<body style='background-color: rgb(255, 255, 255)'>
		<?php
				$good_id = $_GET['id'];
				$sqlImg = "SELECT dop_picture_list.id, image.path, image.alt, (SELECT image.path FROM goods LEFT JOIN image ON goods.picture_id = image.id WHERE goods.id = '$good_id') AS main_picture FROM dop_picture_list LEFT JOIN image ON dop_picture_list.image_id = image.id WHERE dop_picture_list.product_id = '$good_id';";
				$sqlSections = "SELECT section.id, section.name, section.details,
				(SELECT section.name FROM goods LEFT JOIN section ON goods.main_section_id = section.id WHERE goods.id = '$good_id') AS main_section,
				(SELECT section.id FROM goods LEFT JOIN section ON goods.main_section_id = section.id WHERE goods.id = '$good_id') AS main_section_id
				FROM sections_list LEFT JOIN section ON sections_list.section_id = section.id WHERE sections_list.product_id = '$good_id';";
				$sqlgood = "SELECT * FROM goods WHERE goods.id='$good_id'";
				
				$res = $conn->query("SELECT count(*) FROM goods");
				$row = $res->fetch();
				$count = $row[0];
				if($good_id > (int)$count){
					header('Location: error.html');
				}
				if(isset($_COOKIE["category"]) and isset($_COOKIE["category_id"])) {
					$mainSectionID = $_COOKIE["category_id"];
					$mainSection = $_COOKIE["category"];
				}
				else {
					$result = $conn->prepare($sqlSections);
					$result->execute();
					foreach($result as $row){
						$mainSectionID = $row["main_section_id"];
						break;
					}
				}
		?>
				<header id='breadcrumb' style='background-color: rgb(180, 229, 158)'>
				<input id="prev" type="button" value="Назад" name="back" style="cursor: pointer;">
				<ul class='breadcrumb' style='display: inline-block'>
				<li><a href='index.html'>Главная</a></li>
				<li><a href='products.php'>Категории</a></li>
		<?php
				if(isset($_COOKIE["category"]) and isset($_COOKIE["category_id"])) {
					echo "<li><a href='products.php?cat_id=" . $_COOKIE["category_id"] . "&page_num=0'>" . $_COOKIE["category"] . "</a></li>";
				}
				else {
					$result = $conn->prepare($sqlSections);
					$result->execute();
					foreach($result as $row){
						echo "<li><a href='products.php?cat_id=" . $row["main_section_id"] . "&page_num=0'>" . $row["main_section"]  . "</a></li>";
						$mainSection = $row["main_section"];
						break;
					}
				}
				$result = $conn->prepare($sqlgood);
				$result->execute();
				foreach($result as $row){
					echo "<li>" . $row["header"] . "</li>";
					break;
				}
		?>
				</ul></header>
				<div class='productBox' style='display: flex; justify-content: space-between; width: 1400px'>
				<div class='slider'>
		<?php
				$result = $conn->prepare($sqlImg);
				$result->execute();
				foreach($result as $row){
					echo "<div data='" . $row["id"] . "' class='divMinP' name='image' value='" . $row["id"] . "'><img class='minP' src='" . $row["path"] . "' alt='" . $row["alt"] . "'></div>";
				}
		?>
				<input id='slide' type='button' value='&#709'></div><div>
		<?php
				$result = $conn->prepare($sqlImg);
				$result->execute();
				foreach($result as $row){
					echo "<img id='shownImage' src='" . $row["path"] . "'>";
					$mainImagePath = $row["path"];
					break;
				}
		?>
				</div><div class='info'>
		<?php
				$goods = $_GET['id'];
				$sqlHeader = "SELECT goods.header FROM goods WHERE goods.id='$goods'";
				$result = $conn->prepare($sqlHeader);
				$result->execute();
				foreach($result as $row){
					echo "<h1 id='productH1' style='font-family: 'Circe Regular';'>$row[header]</h1>";
				}
		?>
				<div class='tegs'><ul class='list'>
		<?php
				$result = $conn->prepare($sqlSections);
				$result->execute();
				foreach($result as $row){
					echo "<li><a class='productA'>" . $row["name"] . "</a></li>";
				}
		?>
				</ul></div><div class='prices'>
		<?php
				$result = $conn->prepare($sqlgood);
				$result->execute();
				foreach($result as $row){
					echo "<p class='price' id='price-old'>" . $row["price_without_sale"] . "</p>";
					echo "<p class='price' id='price'>" . $row["price"] . "</p>";
					echo "<p class='price' id='price-promo'>" . $row["price_with_promo"] . "</p>";
					echo "<p class='price' id='promo'>- с промокодом</p>";
				}
		?>
				</div><div class='shops'><p><img src='../pictures/gg.png'> В наличии в магазине <a class='productA'>Lamoda</a></p><p><img src='../pictures/ggg.png'> Бесплатная доставка</p>
				</div><div class='eding'>
				<input type='button' value='-'><input type='text' value='0' disabled><input type='button' value='+'></div>
				<div class='productButtons' style='margin-top: 30px; padding-right: 10px;'>
				<input class='productButton' id='firstB' type='submit' value='купить' name='shop' style='width: 170px; height: 40px; text-transform: uppercase; cursor: pointer;'>
				<input class='productButton' id='secondB' type='submit' value='В избранное' name='fav' style='width: 170px; height: 40px; text-transform: uppercase; cursor: pointer;'>
				</div><div class='details'>
		<?php
				echo "<p>" . $row["description"] . "</p>";
		?>
				</div><div class='social-icons'>
				<div>ПОДЕЛИТЬСЯ:</div>
				<div><a class='productA link'><img src='../pictures/vk.png'></a></div>
				<div><a class='productA link'><img src='../pictures/gm.png'></a></div>
				<div><a class='productA link'><img src='../pictures/f.png'></a></div>
				<div><a class='productA link'><img src='../pictures/twi.png'></a></div>
				<div id='count'>123</div></div></div></div>
		<?php
			}
			else {
				setcookie ("category", "", time() - 3600);
				setcookie ("category_id", "", time() - 3600);
				$sqlGetAllSections = "SELECT section.id, section.name, section.details, COUNT(*) AS goods_amount FROM goods LEFT JOIN section ON goods.main_section_id = section.id UNION SELECT section.id, section.name, section.details, COUNT(*) AS good_amount FROM goods INNER JOIN sections_list ON sections_list.product_id = goods.sections_id LEFT JOIN section ON goods.main_section_id = section.id GROUP BY section.id ORDER BY goods_amount DESC;";
				$result = $conn->prepare($sqlGetAllSections);
				$result->execute();
		?>
				<header id='breadcrumb' style='background-color: rgb(180, 229, 158)'>
				<ul class='breadcrumb' style='display: inline-block'>
				<li><a href='index.html'>Главная</a></li><li>Категории</li>
				</ul></header><h1 id='header'>Категории товаров</h1>
				<div class='box'>
		<?php
					foreach($result as $row){
						echo "<a href='products.php?cat_id=" . $row["id"] . "&page_num=0'>";
						echo "<div class='picturePrev' style='display: inline-block;'>";
						echo "<img src=''>";
						echo "<p class='cardP'>" . $row["name"] . "</p>";
						echo "<div style='background-color: #3366CC; color: rgb(255, 255, 255)'> Всего: " . $row["goods_amount"] . " шт. товара</div>";
						echo "</div>";
						echo "</a>";
					}
				echo "</div>";
			}
		?>
		<script src="scripts/script.js"></script>
		<script src="scripts/Notifier.js"></script>
		<script src="scripts/jquery-3.6.0.min.js"></script>
	</body>
</html>