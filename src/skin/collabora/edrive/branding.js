var brandProductName = 'EDrive Office';
var brandProductURL = 'https://www.ecclesiacrm.com';
var brandProductFAQURL = 'https://www.ecclesiacrm.com';
var menuItems;
window.onload = function() {
	// wait until the menu (and particularly the document-header) actually exists
	function setLogo() {
		var logoHeader = document.getElementById('document-header');
		var logo = logoHeader && document.querySelector('#document-header > a');
		if (!logo) {
			// the logo does not exist in the menu yet, re-try in 250ms
			setTimeout(setLogo, 250);
		} else {
			logo.setAttribute('data-cooltip', brandProductName);
			logo.setAttribute('href', brandProductURL);
			logo.addEventListener('click', function(e) {
				e.preventDefault();
				window.open(brandProductURL, '_blank');
			});

			menuItems = document.querySelectorAll('#main-menu > li > a');
		}
	}

	function setAboutImg() {
		var lk = document.getElementById('lokit-version');
		var aboutDialog = document.getElementById('about-dialog-info');
		if (!lk || !aboutDialog) {
			setTimeout(setAboutImg, 250);
		} else {
			var div = document.createElement('div');
			div.style.marginInlineEnd = 'auto';
			div.id = 'lokit-extra';

			let span = document.createElement('span');
			span.setAttribute('dir', 'ltr');
			span.textContent = 'built on\u00A0';

			let anchor = document.createElement('a');
			anchor.href = 'https://col.la/lot';
			anchor.setAttribute('target', '_blank');
			anchor.textContent = 'a great technology base';

			div.appendChild(span);
			div.appendChild(anchor);
			lk.parentNode.parentNode.insertBefore(div, lk.parentNode);
		}
	}

	setLogo();
	setAboutImg();
}

document.onkeyup = function(e) {
	if (e.altKey && e.shiftKey && menuItems) {
		menuItems.forEach(function(menuItem) {
		  menuItem.style.setProperty('text-decoration', 'underline', 'important');
		});
	}
};
