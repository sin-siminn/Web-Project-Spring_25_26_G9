<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Analytics Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="dashboard-container admin-analytics">
    <div class="dashboard-header">
        <div>
            <h2>Admin Analytics Dashboard</h2>
            <p>Platform-wide auction lifecycle summary and completed-auction category chart.</p>
        </div>
        <div class="dashboard-actions">
            <a href="../views/admin_panel.php" class="btn-secondary">Seller Requests</a>
            <a href="../views/home.php" class="btn-primary">Home</a>
        </div>
    </div>

    <div id="adminStatsMessage" class="ajax-message" style="display:none;"></div>

    <div class="summary-grid analytics-grid">
        <div class="summary-card">
            <span>Total Active Auctions</span>
            <strong id="totalActive">0</strong>
        </div>
        <div class="summary-card">
            <span>Total Ended Auctions</span>
            <strong id="totalEnded">0</strong>
        </div>
        <div class="summary-card">
            <span>Total Bids Placed</span>
            <strong id="totalBids">0</strong>
        </div>
        <div class="summary-card highest-sale-card">
            <span>Highest-Value Sale</span>
            <strong id="highestSaleAmount">0.00</strong>
            <small id="highestSaleText">No completed sale yet.</small>
        </div>
    </div>

    <div class="chart-card">
        <h3>Top 5 Categories by Completed Auctions</h3>
        <canvas id="topCategoryChart" height="140"></canvas>
    </div>
</div>

<script src="../assets/js/admin_analytics.js"></script>
</body>
</html>
