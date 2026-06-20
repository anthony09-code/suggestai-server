document.addEventListener("DOMContentLoaded", () => {
    const textarea = document.getElementById("raw_text");
    const counter = document.getElementById("char-count");
    const btn = document.getElementById("submit-btn");
    const form = document.querySelector("form");

    if (textarea && counter) {
        const max = 250;
        textarea.setAttribute("maxlength", max);
        function updateCount() {
            const count = textarea.value.length;
            counter.textContent = `${count} / ${max}`;
            counter.style.color =
                count >= max * 0.9 ? "var(--office-color)" : "";
        }
        textarea.addEventListener("input", updateCount);
        updateCount();
    }

    if (textarea && btn) {
        function updateBtn() {
            const isEmpty = textarea.value.trim() === "";
            btn.disabled = isEmpty;
            btn.style.opacity = isEmpty ? "0.5" : "1";
            btn.style.cursor = isEmpty ? "not-allowed" : "pointer";
        }
        textarea.addEventListener("input", updateBtn);
        updateBtn();
    }

    if (form && btn) {
        form.addEventListener("submit", function () {
            btn.disabled = true;
            btn.style.opacity = "0.6";
            btn.style.cursor = "not-allowed";
            // btn.textContent = "Submitting...";
        });
    }
});
