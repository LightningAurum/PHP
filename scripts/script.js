window.onload = function() {
	let images = document.querySelectorAll('.divMinP'), index, image;
	for (let image of images) {
		image.addEventListener('mouseover', function() {
			document.getElementById('shownImage').src = "../pictures/" + this.getAttribute('data') + ".png";
		});
		image.addEventListener('mouseout', function() {
			var num = document.querySelectorAll('.divMinP')[0].getAttribute('data');
			document.getElementById('shownImage').src = "../pictures/" + num + ".png";
		});
	}
	document.querySelectorAll('input[type=button]')[2].onclick = function() {
		if(document.querySelectorAll('input[type=text]')[0].value < 1) {
			
		}
		else {
			document.querySelectorAll('input[type=text]')[0].value = parseInt(document.querySelectorAll('input[type=text]')[0].value) - 1;
		}
	};
	document.querySelectorAll('input[type=button]')[3].onclick = function() {
		document.querySelectorAll('input[type=text]')[0].value = parseInt(document.querySelectorAll('input[type=text]')[0].value) + 1;
	};

	document.getElementById('firstB').onclick = function() {
		let X = document.querySelectorAll('input[type=text]')[0].value;
		let text;
		let title;
		if(X > 0) {
			title = "Покупка"
			text = "В корзину добавлено " + X;
			if(X == 1) {
				text = "В корзину добавлен " + X + " товар";
			}
			else if(X == 2 || X == 3 || X == 4) {
				text += " товара";
			}
			else {
				text += " товаров";
			}
			Notifier.success(text, title);
			document.querySelectorAll('input[type=text]')[0].value = 0;
		}
		else {
			text = "Невозможно добавить товары в корзину. Попробуйте указать количество товаров больше 0.";
			title = "Ошибка покупки"
			Notifier.error(text, title);
			document.querySelectorAll('input[type=text]')[0].value = 0;
		}
	}
	document.getElementById('prev').onclick = function() {
		var num = '<?php echo $mainSectionID;?>';
		if(window.location.href.indexOf("cat_id") > -1) {
			window.location.href = "products.php"; //почему-то не осуществляется переход
			//alert('Yes');
	 	}
		else {
			window.location.href = "products.php?cat_id=" + num + "&page_num=0";
			alert(num); //почему-то переменная не передаётся
		}
	};
}