(function () {
    'use strict';

    function formatMoney(value) {
        const number = Number(value || 0);
        return number.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function showMessage(message, type) {
        const el = document.getElementById('adminStatsMessage');
        if (!el) return;
        el.textContent = message;
        el.className = 'ajax-message ' + (type || '');
        el.style.display = 'block';
    }

    function renderChart(topCategories) {
        const canvas = document.getElementById('topCategoryChart');
        if (!canvas || typeof Chart === 'undefined') return;

        const labels = topCategories.map(function (row) { return row.category_name; });
        const values = topCategories.map(function (row) { return Number(row.completed_count || 0); });

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['No completed auctions'],
                datasets: [{
                    label: 'Completed Auctions',
                    data: values.length ? values : [0]
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        fetch('../api/admin_stats.php', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.ok) {
                    showMessage(data.msg || 'Unable to load admin analytics.', 'error-message');
                    return;
                }

                const stats = data.stats || {};
                const highestSale = stats.highest_sale || {};

                setText('totalActive', Number(stats.total_active || 0));
                setText('totalEnded', Number(stats.total_ended || 0));
                setText('totalBids', Number(stats.total_bids || 0));
                setText('highestSaleAmount', formatMoney(highestSale.amount || 0));

                if (highestSale.title) {
                    setText(
                        'highestSaleText',
                        highestSale.title + (highestSale.category_name ? ' • ' + highestSale.category_name : '')
                    );
                }

                renderChart(stats.top_categories || []);
            })
            .catch(function () {
                showMessage('Something went wrong while loading admin analytics.', 'error-message');
            });
    });
})();
