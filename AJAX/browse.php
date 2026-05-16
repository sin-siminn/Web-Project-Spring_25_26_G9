<?php
require '../config/db.php';
require '../controllers/AuthController.php';
session_start();
requireLogin();

$categories = $conn->query("SELECT id, name FROM categories")->fetch_all(MYSQLI_ASSOC);
?>

<select id="categoryFilter">
    <option value="">All Categories</option>
    <?php foreach($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
    <?php endforeach; ?>
</select>

<input type="text" id="searchInput" placeholder="Search auctions...">

<div id="auctionCards"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadListings(category='', keyword='') {
    $.getJSON('api/listings.php', {category_id: category, q: keyword}, function(data){
        let html = '';
        data.forEach(listing => {
            html += `
            <div class="card" data-end="${listing.end_datetime}">
                <img src="${listing.thumbnail}" />
                <h3>${listing.title}</h3>
                <p>Current Bid: $<span class="current_bid">${listing.current_bid}</span></p>
                <p>Bids: ${listing.bid_count}</p>
                <div class="countdown"></div>
                <a href="auction_detail.php?id=${listing.id}">View</a>
            </div>`;
        });
        $('#auctionCards').html(html);
        initCountdowns();
    });
}

$('#categoryFilter').change(function(){ loadListings(this.value, $('#searchInput').val()); });
$('#searchInput').keyup(function(){ loadListings($('#categoryFilter').val(), this.value); });

function initCountdowns() {
    $('.card').each(function(){
        const endTime = new Date($(this).data('end')).getTime();
        const countdownEl = $(this).find('.countdown');
        setInterval(() => {
            const now = new Date().getTime();
            const diff = endTime - now;
            if(diff>0){
                const h = Math.floor(diff/(1000*60*60));
                const m = Math.floor((diff%(1000*60*60))/(1000*60));
                const s = Math.floor((diff%(1000*60))/1000);
                countdownEl.text(`${h}h ${m}m ${s}s`);
            } else { countdownEl.text('Auction Ended'); }
        }, 1000);
    });
}

loadListings();
</script>