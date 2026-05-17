document.querySelectorAll('.place-bid-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const listingId = this.dataset.listingId;
        const amount = this.querySelector('input[name="amount"]').value;

        fetch('/controllers/BidsController.php?action=placeBid', {
            method: 'POST',
            body: new URLSearchParams({listing_id: listingId, amount: amount})
        }).then(res => res.json()).then(data => {
            const msgDiv = this.querySelector('.bid-message');
            if (data.ok) {
                msgDiv.textContent = 'Bid placed successfully!';
                document.querySelector(`#current-bid-${listingId}`).textContent = data.new_bid;
                document.querySelector(`#bid-count-${listingId}`).textContent = data.bid_count;
            } else {
                msgDiv.textContent = data.error;
            }
        });
    });
});