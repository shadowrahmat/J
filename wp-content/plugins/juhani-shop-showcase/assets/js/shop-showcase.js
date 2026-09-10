(function () {
	document.addEventListener('submit', function (event) {
		if (!event.target.matches('.jss-toolbar')) {
			return;
		}

		var emptyFields = event.target.querySelectorAll('input[value=""], select:not([value])');
		emptyFields.forEach(function (field) {
			if (!field.value) {
				field.disabled = true;
			}
		});
	});
})();
