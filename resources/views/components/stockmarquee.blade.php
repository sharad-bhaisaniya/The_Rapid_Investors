{{-- resources/views/components/stockmarquee.blade.php --}}

<style>
    .stock-marquee-breakout {
        width: 100vw;
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw;
        margin-right: -50vw;
        max-width: 100vw;
        overflow: hidden; 
        z-index: 0;
        /* Light background and subtle bottom border like the screenshot */
        background: #f8fafc; 
        padding: 12px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .ticker-wrap {
        width: 100%;
        height: 45px; 
        display: flex;
        align-items: center;
    }

    .ticker-content {
        display: flex;
        width: max-content;
        animation: ticker-scroll 60s linear infinite;
    }

    .ticker-wrap:hover .ticker-content {
        animation-play-state: paused;
    }

    @keyframes ticker-scroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    /* Card Style: White box with rounded corners and the left colored bar */
    .ticker-item {
        display: inline-flex;
        align-items: center;
        background: #ffffff;
        margin: 0 10px;
        padding: 6px 16px;
        height: 38px;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        white-space: nowrap;
        /* Thick indicator bar on the left */
        border-left-width: 5px; 
    }

    .ticker-symbol {
        color: #000;
        font-weight: 700;
        font-size: 14px;
        margin-right: 15px;
        text-transform: uppercase;
    }

    .ticker-price {
        color: #1f2937;
        font-weight: 500;
        font-size: 14px;
        margin-right: 15px;
    }

    /* Color Logic for the left bar */
    .card-up { border-left-color: #15803d; }
    .card-down { border-left-color: #b91c1c; }
    .card-neutral { border-left-color: #94a3b8; }

    /* Text Colors */
    .trend-up { color: #15803d; }
    .trend-down { color: #b91c1c; }
    .trend-neutral { color: #64748b; }
    
    .change-text {
        font-weight: 500;
        font-size: 13px;
    }
</style>

<div class="stock-marquee-breakout">
    <div class="ticker-wrap">
        <div id="tickerContent" class="ticker-content">
            <div class="ticker-item card-neutral">
                <span class="text-gray-500 text-sm">Fetching Live Market Prices...</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('tickerContent');
    const API_URL = '/api/angel/nifty50-marquee';

    async function fetchMarketData() {
        try {
            const response = await fetch(API_URL, { cache: 'no-store' });
            if (!response.ok) throw new Error('API Error');
            const json = await response.json();

            if (json.status && Array.isArray(json.data)) {
                renderTicker(json.data);
            }
        } catch (error) {
            console.error('Ticker Error:', error);
        }
    }

    function renderTicker(data) {
        if (!data || !data.length) return;

        const itemsHtml = data.map(item => {
            const symbol = item.symbol;
            const ltpNum = parseFloat(item.ltp || 0);
            const changeNum = parseFloat(item.change || 0);
            
            // PERCENTAGE CALCULATION:
            // Check if API provides pChange/percentChange, else calculate manually
            let pChange = item.pChange || item.percentChange;
            
            if (pChange === undefined || pChange === null) {
                const prevClose = ltpNum - changeNum;
                pChange = prevClose !== 0 ? (changeNum / prevClose) * 100 : 0;
            }

            const ltpDisplay = ltpNum.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            
            const changeDisplay = Math.abs(changeNum).toFixed(2);
            const percentDisplay = Math.abs(parseFloat(pChange)).toFixed(2);
            
            let statusClass = 'card-neutral';
            let trendClass = 'trend-neutral';
            let sign = '';

            if (changeNum > 0) {
                statusClass = 'card-up';
                trendClass = 'trend-up';
                sign = '+';
            } else if (changeNum < 0) {
                statusClass = 'card-down';
                trendClass = 'trend-down';
                sign = '-'; 
            }

            return `
                <div class="ticker-item ${statusClass}">
                    <span class="ticker-symbol">${symbol}</span>
                    <span class="ticker-price">${ltpDisplay}</span>
                    <span class="change-text ${trendClass}">
                        ${sign}${changeDisplay} (${sign}${percentDisplay}%)
                    </span>
                </div>
            `;
        }).join('');

        // Duplicate the items for seamless looping
        container.innerHTML = itemsHtml + itemsHtml;
    }

    // Initial Load
    fetchMarketData();
    
    // Refresh every 60 seconds
    setInterval(fetchMarketData, 60000); 
});
</script>