// guides-search.js section
document.addEventListener("DOMContentLoaded", function () {
	const searchInput = document.getElementById("guide-search");
	const guideCards = document.querySelectorAll(".g-search");
	const noResultsMessage = document.getElementById("no-results-message");

	if (!searchInput) return;

	function normalizeText(str) {
		return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
	}

	searchInput.addEventListener("keyup", function () {
		const query = normalizeText(searchInput.value.trim());
		let anyMatch = false;

		guideCards.forEach(card => {
			const href = card.getAttribute("href");
			const modalId = href.replace("#", "");
			const modal = document.getElementById(modalId);

			let nameText = '';
			const nameSpans = card.querySelectorAll(".guide-name span");
			nameSpans.forEach(span => nameText += span.textContent + ' ');

			const nicknameText = modal?.querySelector(".g-nick")?.textContent || '';

			const name = normalizeText(nameText.trim());
			const nickname = normalizeText(nicknameText.trim());
			const match = name.includes(query) || nickname.includes(query);

			card.style.display = match ? "flex" : "none";
			if (match) anyMatch = true;
		});

		noResultsMessage.style.display = anyMatch ? "none" : "block";
	});
});

// Search Guide with url param
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("guide-search");

    if (input && input.value.trim() !== '') {
        // Trigger keyup event to activate search
        const event = new Event('keyup');
        input.dispatchEvent(event);

        // Scroll to input with 100px offset from top
        const offset = 350;
        const inputTop = input.getBoundingClientRect().top + window.pageYOffset;
        window.scrollTo({
            top: inputTop - offset,
            behavior: 'smooth'
        });
    }
});



