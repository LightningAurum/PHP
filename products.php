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
				echo "<header id='breadcrumb' style='background-color: rgb(180, 229, 158)'>";
				$link = "products.php";
				//echo "<input type='button' id='prev' onclick='location.href = $link' value='Назад'>";
				echo "<input type='button' id='prev' value='Назад'>";
				echo "<ul class='breadcrumb' style='display: inline-block'>";
				echo "<li><a href='index.html'>Главная</a></li>";
				echo "<li><a href='products.php'>Категории</a></li>";
				$result = $conn->prepare($sqlGetSection);
				$result->execute();
				foreach($result as $row){
					echo "<li>" . $row["name"] . "</li>";
					break;
				}
				echo "</ul></header>";
				$result = $conn->prepare($sqlGetSection);
				$result->execute();
				foreach($result as $row){
					echo "<h1 id='header'>$row[name]</h1>";
					setcookie("category", $row["name"]);
					setcookie("category_id", $row["id"]);
					break;
				}
				echo "<body style='background-color: rgb(238, 238, 238)'>";
				$result = $conn->prepare($sqlGetSection);
				$result->execute();
				foreach($result as $row){
					$date = date('m/d/Y', time());
					echo "<div id='testCard'><p id='rightPos'>$date<br><br><b>" . $row["name"] . "</b><br><br>". $row["details"] . "</div><br><br>";
				}
				$result = $conn->prepare($getGoodsInfo);
				$result->execute();
				echo "<div class='box'>";
					foreach($result as $row){
						echo "<a href='products.php?id=" . $row["id"] . "'>";
						echo "<div class='picturePrev' style='display: inline-block; text-align: center;'>";
						echo "<img src='" . $row["path"] . "' alt='" . $row["alt"] . "' style='max-height: 60%; max-width: 60%;'>";
						echo "<p class='cardP'>" . $row["header"] . "</p>";
						echo "<div class='desc'>" . $row["main_section_name"] . "</div></div></a>";
					}
				echo "</div>";
				$i = 0;
				echo "<div class='pages'>";
				while ($i != $page){
					echo "<a class='pageNum' href='products.php?cat_id=" . $cat . "&page_num=" . $i . "' style='text-align: center;'>$i</a>";
					$i++;
				}
				echo "<a class='pageNum' style='text-align: center;'>$page</a>";
				for ($j = $i + 1; $j < (int)$count + 1; $j++) {
					echo "<a class='pageNum' href='products.php?cat_id=" . $cat . "&page_num=" . $j . "' style='text-align: center;'>" . $j . "</a>";
				}
				echo "</div>";
			}
			elseif (strpos($url,'id') == true) {
				echo "<body style='background-color: rgb(255, 255, 255)'>";
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

				}
				else {
					$result = $conn->prepare($sqlSections);
					$result->execute();
					foreach($result as $row){
						$mainSectionID = $row["main_section_id"];
						break;
					}
				}
				echo "<header id='breadcrumb' style='background-color: rgb(180, 229, 158)'>";
				echo "<input type='button' id='prev' value='Назад'>";
				//echo "<input type='button' id='prev' onclick='goToPage($mainSectionID);' value='Назад'>";
				echo "<ul class='breadcrumb' style='display: inline-block'>";
				echo "<li><a href='index.html'>Главная</a></li>";
				echo "<li><a href='products.php'>Категории</a></li>";
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
				echo "</ul></header>";
				echo "<div class='productBox' style='display: flex; justify-content: space-between; width: 1400px'>";
				echo "<div class='slider'>";
				$result = $conn->prepare($sqlImg);
				$result->execute();
				foreach($result as $row){
					echo "<div data='" . $row["id"] . "' class='divMinP' name='image' value='" . $row["id"] . "'><img class='minP' src='" . $row["path"] . "' alt='" . $row["alt"] . "'></div>";
				}
				echo "<input id='slide' type='button' value='&#709'>";
				echo "</div>";
				echo "<div>";
				$result = $conn->prepare($sqlImg);
				$result->execute();
				foreach($result as $row){
					echo "<img id='shownImage' src='" . $row["path"] . "'>";
					$mainImagePath = $row["path"];
					break;
				}
				echo "</div>";
				echo "<div class='info'>";
				$goods = $_GET['id'];
				$sqlHeader = "SELECT goods.header FROM goods WHERE goods.id='$goods'";
				$result = $conn->prepare($sqlHeader);
				$result->execute();
				foreach($result as $row){
					echo "<h1 id='productH1' style='font-family: 'Circe Regular';'>$row[header]</h1>";
				}
				echo "<div class='tegs'>";
				echo "<ul class='list'>";
				$result = $conn->prepare($sqlSections);
				$result->execute();
				foreach($result as $row){
					echo "<li><a class='productA'>" . $row["name"] . "</a></li>";
				}
				echo "</ul>";
				echo "</div>";
				echo "<div class='prices'>";
				$result = $conn->prepare($sqlgood);
				$result->execute();
				foreach($result as $row){
					echo "<p class='price' id='price-old'>" . $row["price_without_sale"] . "</p>";
					echo "<p class='price' id='price'>" . $row["price"] . "</p>";
					echo "<p class='price' id='price-promo'>" . $row["price_with_promo"] . "</p>";
					echo "<p class='price' id='promo'>- с промокодом</p>";
				}
				echo "</div>";
				echo "<div class='shops'>";
				echo "<p><img src='../pictures/gg.png'> В наличии в магазине <a class='productA'>Lamoda</a></p>";
				echo "<p><img src='../pictures/ggg.png'> Бесплатная доставка</p>";
				echo "</div>";
				echo "<div class='eding'>";
				echo "<input type='button' value='-'><input type='text' value='0' disabled><input type='button' value='+'>";
				echo "</div>";
				echo "<div class='productButtons' style='margin-top: 30px; padding-right: 10px;'>";
				echo "<input class='productButton' id='firstB' type='submit' value='купить' name='shop' style='width: 170px; height: 40px; text-transform: uppercase; cursor: pointer;'>";
				echo "<input class='productButton' id='secondB' type='submit' value='В избранное' name='fav' style='width: 170px; height: 40px; text-transform: uppercase; cursor: pointer;'>";
				echo "</div>";
				echo "<div class='details'>";
				echo "<p>" . $row["description"] . "</p>";
				echo "</div>";
				echo "<div class='social-icons'>";
				echo "<div>ПОДЕЛИТЬСЯ:</div>";
				echo "<div><a class='productA link'><img src='../pictures/vk.png'></a></div>";
				echo "<div><a class='productA link'><img src='../pictures/gm.png'></a></div>";
				echo "<div><a class='productA link'><img src='../pictures/f.png'></a></div>";
				echo "<div><a class='productA link'><img src='../pictures/twi.png'></a></div>";
				echo "<div id='count'>123</div>";
				echo "</div>";
				echo "</div>";
				echo "</div>";
			}
			else {
				setcookie ("category", "", time() - 3600);
				setcookie ("category_id", "", time() - 3600);
				$sqlGetAllSections = "SELECT section.id, section.name, section.details, COUNT(*) AS goods_amount FROM goods LEFT JOIN section ON goods.main_section_id = section.id UNION SELECT section.id, section.name, section.details, COUNT(*) AS good_amount FROM goods INNER JOIN sections_list ON sections_list.product_id = goods.sections_id LEFT JOIN section ON goods.main_section_id = section.id GROUP BY section.id ORDER BY goods_amount DESC;";
				echo "<header id='breadcrumb' style='background-color: rgb(180, 229, 158)'>";
				echo "<ul class='breadcrumb' style='display: inline-block'>";
				echo "<li><a href='index.html'>Главная</a></li>";
				echo "<li>Категории</li>";
				echo "</ul></header>";
				echo "<h1 id='header'>Категории товаров</h1>";
				$result = $conn->prepare($sqlGetAllSections);
				$result->execute();
				echo "<div class='box'>";
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