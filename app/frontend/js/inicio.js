/*----------------------------------ANIMATIONS SCROLL----------------------------------*/


function addAnimatedClassToElements() {
	const nodes = document.querySelectorAll('.scroll-content');
	const viewportHeight = window.innerHeight;
	const elements = Array.from(nodes);

	if (elements.every(element => element.classList.contains('animated'))) {
		window.removeEventListener('scroll', addAnimatedClassToElements);
		return;
	}

	elements.forEach(element => {
		if (element.getBoundingClientRect().top < viewportHeight) {
			element.classList.add('animated');
		}
	});
}

window.addEventListener('scroll', addAnimatedClassToElements);
