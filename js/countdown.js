function updateCountdowns() {
    document.querySelectorAll('.countdown').forEach(div => {
        const end = new Date(div.dataset.end);
        const now = new Date();
        const diff = end - now;
        if(diff <= 0) {
            div.textContent = 'Auction ended';
        } else {
            const d = Math.floor(diff/1000/60/60/24);
            const h = Math.floor(diff/1000/60/60) % 24;
            const m = Math.floor(diff/1000/60) % 60;
            const s = Math.floor(diff/1000) % 60;
            div.textContent = `${d}d ${h}h ${m}m ${s}s`;
        }
    });
}
setInterval(updateCountdowns, 1000);
updateCountdowns();