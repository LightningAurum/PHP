function showForm() {
	//document.getElementById("forForm").style.display = "none";
	//document.getElementById("testPage").style.display = "none";
	//document.getElementById("howToUse").style.display = "none";
	document.body.insertAdjacentHTML("afterbegin", '<form action="" method="GET"><fieldset style="width:250px; background-color: rgba(255, 255, 255, 0.7);"><legend>Форма обратной связи</legend><label for="name">Имя</label><br><input id="name" type="text" name="username"><br><br><label for="e_mail">E-mail</label><br><input id="e_mail" type="text" name="e_mail"><br><br><label for="birthday">Год рождения</label><br><input id="birthday" type="date" name="birthday"><br><div><label>Пол</label><br><input id="male" type="radio" name="maleOrFemale" value="male" checked><label for="male"> Мужской</label><input id="female" type="radio" name="maleOrFemale" value="female"><label for="female"> Женский</label></div><br><label for="topic">Тема обращения</label><br><input id="topic" type="text" name="topic"><br><br><label for="question">Суть вопроса</label><br><textarea id="question" rows="4" cols="23" name="question" style="resize: vertical;"></textarea><br><br><input type="checkbox" id="yes" name="rules" value="yes"><label for="yes">С контрактом ознакомлен</label><br><br><input name="submit" type="submit" name="submit"></fieldset></form>');
}

function closeForm() {
	var el = document.getElementById("divForm");
	el.remove();
	document.getElementById("forForm").style.display = "block";
	document.getElementById("testPage").style.display = "block";
	document.getElementById("howToUse").style.display = "block";
}