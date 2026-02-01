document.addEventListener("DOMContentLoaded", () => {
    const words = ["FRESH", "COOL", "CLEAN", "FAST", "MINTY"];
    let currentIndex = 0;
    const target = document.getElementById('dynamic-text');

    function rotateText() {
        target.style.opacity = 0;

        setTimeout(() => {
            currentIndex = (currentIndex + 1) % words.length;
            target.textContent = words[currentIndex];
            target.style.opacity = 1;
        }, 300);
    }

    setInterval(rotateText, 2500);
});
