(function () {
    'use strict';

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatMoney(value) {
        const number = Number(value || 0);
        return number.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function parseDate(value) {
        return new Date(String(value).replace(' ', 'T')).getTime();
    }

    function updateCountdownElement(el) {
        const endTime = parseDate(el.dataset.end);
        const now = Date.now();
        const diff = endTime - now;

        if (!endTime || diff <= 0) {
            el.textContent = 'Ended';
            el.classList.add('ended');
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hrs = Math.floor((diff / (1000 * 60 * 60)) % 24);
        const mins = Math.floor((diff / (1000 * 60)) % 60);
        const secs = Math.floor((diff / 1000) % 60);

        el.textContent = `${days}d ${hrs}h ${mins}m ${secs}s`;
    }

    function initCountdowns() {
        const elements = document.querySelectorAll('.countdown');
        if (!elements.length) return;

        elements.forEach(updateCountdownElement);

        if (window.buyerCountdownStarted) return;
        window.buyerCountdownStarted = true;

        setInterval(function () {
            document.querySelectorAll('.countdown').forEach(updateCountdownElement);
        }, 1000);
    }

    function showMessage(el, message, type) {
        if (!el) return;
        el.textContent = message;
        el.className = 'ajax-message ' + (type || '');
        el.style.display = 'block';
    }

    function renderAuctionCard(auction, detailBaseUrl) {
        return `
            <div class="auction-card">
                <div class="auction-thumb placeholder-thumb">No Image</div>
                <div class="auction-card-body">
                    <span class="category-chip">${escapeHtml(auction.category_name || 'Uncategorised')}</span>
                    <h3>${escapeHtml(auction.title)}</h3>
                    <p class="seller-line">Seller: ${escapeHtml(auction.seller_name || 'Unknown')}</p>
                    <div class="card-meta">
                        <span>Current Bid</span>
                        <strong>${formatMoney(auction.current_price)}</strong>
                    </div>
                    <div class="card-meta small-meta">
                        <span>Bid Count</span>
                        <strong>${Number(auction.bid_count || 0)}</strong>
                    </div>
                    <div class="countdown" data-end="${escapeHtml(auction.end_time)}">Loading...</div>
                    <a class="btn-primary full-width" href="${detailBaseUrl}${encodeURIComponent(auction.auction_id)}">View &amp; Bid</a>
                </div>
            </div>
        `;
    }

    function initBrowseAjax() {
        const grid = document.getElementById('auctionGrid');
        if (!grid) return;

        const categoryFilter = document.getElementById('categoryFilter');
        const auctionSearch = document.getElementById('auctionSearch');
        const ajaxMessage = document.getElementById('ajaxMessage');
        const apiUrl = grid.dataset.apiUrl || '../api/listings.php';
        const detailBaseUrl = grid.dataset.detailUrl || 'BuyerDashboardController.php?action=detail&id=';
        let searchTimer = null;

        function loadAuctions() {
            const params = new URLSearchParams();
            if (categoryFilter && categoryFilter.value) params.set('category_id', categoryFilter.value);
            if (auctionSearch && auctionSearch.value.trim()) params.set('q', auctionSearch.value.trim());

            grid.classList.add('loading');
            fetch(apiUrl + '?' + params.toString(), {
                method: 'GET',
                headers: {'Accept': 'application/json'}
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    grid.classList.remove('loading');
                    if (!data.ok) {
                        showMessage(ajaxMessage, data.msg || 'Unable to load auctions.', 'error-message');
                        return;
                    }

                    if (!data.auctions.length) {
                        grid.innerHTML = '<div class="empty-state">No active auctions matched your filter.</div>';
                    } else {
                        grid.innerHTML = data.auctions.map(function (auction) {
                            return renderAuctionCard(auction, detailBaseUrl);
                        }).join('');
                    }

                    if (ajaxMessage) ajaxMessage.style.display = 'none';
                    initCountdowns();
                })
                .catch(function () {
                    grid.classList.remove('loading');
                    showMessage(ajaxMessage, 'Something went wrong while loading auctions.', 'error-message');
                });
        }

        if (categoryFilter) {
            categoryFilter.addEventListener('change', loadAuctions);
        }

        if (auctionSearch) {
            auctionSearch.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(loadAuctions, 350);
            });
        }
    }

    function initBidForm() {
        const bidForm = document.getElementById('bidForm');
        if (!bidForm) return;

        const bidMessage = document.getElementById('bidMessage');
        const bidAmount = document.getElementById('bidAmount');
        const currentBid = document.getElementById('currentBid');
        const bidCount = document.getElementById('bidCount');
        const bidHistoryBody = document.getElementById('bidHistoryBody');

        bidForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const auctionId = bidForm.dataset.auctionId;
            const amount = bidAmount.value;
            const submitButton = bidForm.querySelector('button[type="submit"]');

            submitButton.disabled = true;
            submitButton.textContent = 'Placing...';

            const body = new URLSearchParams();
            body.set('auction_id', auctionId);
            body.set('amount', amount);

            fetch('../api/bids.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: body.toString()
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Place Bid';

                    if (!data.ok) {
                        showMessage(bidMessage, data.msg || 'Bid failed.', 'error-message');
                        return;
                    }

                    showMessage(bidMessage, data.msg || 'Bid placed successfully.', 'success-message');

                    if (currentBid) currentBid.textContent = formatMoney(data.new_bid);
                    if (bidCount) bidCount.textContent = Number(data.bid_count || 0);

                    bidAmount.min = (Number(data.new_bid) + 0.01).toFixed(2);
                    bidAmount.value = '';

                    if (bidHistoryBody && data.bid) {
                        const emptyRow = bidHistoryBody.querySelector('.empty-row');
                        if (emptyRow) emptyRow.remove();

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${escapeHtml(data.bid.bidder_name || 'You')}</td>
                            <td>${formatMoney(data.bid.amount)}</td>
                            <td>${escapeHtml(data.bid.created_at)}</td>
                        `;
                        bidHistoryBody.prepend(row);

                        while (bidHistoryBody.children.length > 10) {
                            bidHistoryBody.removeChild(bidHistoryBody.lastElementChild);
                        }
                    }
                })
                .catch(function () {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Place Bid';
                    showMessage(bidMessage, 'Something went wrong while placing your bid.', 'error-message');
                });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initCountdowns();
        initBrowseAjax();
        initBidForm();
    });
})();
