<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>The Rapid Investors — Landing</title>

  <!-- Tailwind CDN (static) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Small-font focused theme */
    html, body { height: 100%; }
    body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }

    /* subtle background gradient */
    .tri-gradient { background: linear-gradient(180deg, #f8fbff 0%, #eef6fb 45%, #f3f7fb 100%); }

    /* tiny helper for subtle card shadows */
    .card-shadow { box-shadow: 0 6px 18px rgba(16,24,40,0.06); }
  </style>
</head>
<body class="tri-gradient text-slate-700 text-sm leading-relaxed">

  <!-- Full page container -->
  <div class="min-h-screen flex flex-col">

    <!-- NAVBAR / HEADER -->
    <header class="w-full bg-white/60 backdrop-blur-sm shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <img src="{{ asset('public/assets/images/rapid-investors-logo2.png') }}" alt="TRI" class="h-16 w-auto object-contain" />
          <div class="flex flex-col -ml-1">
            <span class="font-semibold text-xs">The Rapid Investors</span>
            <span class="text-[11px] text-slate-500">Market insights • fast decisions</span>
          </div>
        </div>

        <nav class="hidden md:flex items-center gap-4 text-slate-600">
          <a href="#markets" class="hover:text-slate-900">Markets</a>
          <a href="#features" class="hover:text-slate-900">Features</a>
          <a href="#insights" class="hover:text-slate-900">Insights</a>
          <a href="#contact" class="hover:text-slate-900">Contact</a>
        </nav>

        <div class="flex items-center gap-3">
          <a href="admin/dashboard" class="px-3 py-1 rounded-md bg-[#00afff] text-white text-xs font-medium">Get Started</a>
        </div>
      </div>
    </header>

    <!-- HERO -->
    <section class="flex-1 flex items-center">
      <div class="max-w-7xl mx-auto px-4 py-12 w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">

          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 leading-tight">Real-time market snapshots & curated insights — for fast investors</h1>
            <p class="mt-3 text-xs text-slate-600 max-w-xl">Access concise stock data, watchlists, and short technical signals designed to help you make quicker, smarter decisions. Fully static demo with small, readable typography.</p>

            <div class="mt-6 flex gap-3 items-center">
              <a href="#markets" class="px-3 py-2 rounded-md bg-[#00afff] text-white text-xs font-semibold">See Market Overview</a>
              <a href="#features" class="px-3 py-2 rounded-md border border-slate-200 text-xs text-slate-700">Why TRI?</a>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-2 text-[11px] text-slate-600">
              <div class="flex items-start gap-2">
                <div class="h-8 w-8 rounded-md bg-white card-shadow flex items-center justify-center text-xs">A</div>
                <div>
                  <div class="font-semibold text-xs">Low latency</div>
                  <div class="text-[11px] text-slate-500">Fast feed previews</div>
                </div>
              </div>

              <div class="flex items-start gap-2">
                <div class="h-8 w-8 rounded-md bg-white card-shadow flex items-center justify-center text-xs">R</div>
                <div>
                  <div class="font-semibold text-xs">Reliable</div>
                  <div class="text-[11px] text-slate-500">Curated signals</div>
                </div>
              </div>

              <div class="flex items-start gap-2">
                <div class="h-8 w-8 rounded-md bg-white card-shadow flex items-center justify-center text-xs">I</div>
                <div>
                  <div class="font-semibold text-xs">Insights</div>
                  <div class="text-[11px] text-slate-500">Short actionable tips</div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-4 card-shadow">
            <div class="flex items-center justify-between">
              <div class="text-xs font-semibold">Portfolio preview</div>
              <div class="text-[11px] text-slate-500">Updated just now</div>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-3">
              <!-- example stock rows -->
              <div class="flex items-center justify-between p-3 rounded-md border border-slate-100">
                <div class="flex items-center gap-3">
                  <div class="w-10 text-xs">
                    <div class="font-semibold">AAPL</div>
                    <div class="text-[11px] text-slate-500">Apple Inc.</div>
                  </div>
                  <div class="text-xs text-slate-600">$172.90</div>
                </div>
                <div class="text-[11px] text-emerald-600 font-semibold">+1.24%</div>
              </div>

              <div class="flex items-center justify-between p-3 rounded-md border border-slate-100">
                <div class="flex items-center gap-3">
                  <div class="w-10 text-xs">
                    <div class="font-semibold">TSLA</div>
                    <div class="text-[11px] text-slate-500">Tesla</div>
                  </div>
                  <div class="text-xs text-slate-600">$254.10</div>
                </div>
                <div class="text-[11px] text-red-500 font-semibold">-0.87%</div>
              </div>

              <div class="flex items-center justify-between p-3 rounded-md border border-slate-100">
                <div class="flex items-center gap-3">
                  <div class="w-10 text-xs">
                    <div class="font-semibold">NSE</div>
                    <div class="text-[11px] text-slate-500">NIFTY 50</div>
                  </div>
                  <div class="text-xs text-slate-600">18,120.40</div>
                </div>
                <div class="text-[11px] text-emerald-600 font-semibold">+0.48%</div>
              </div>

            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- MARKETS SECTION -->
    <section id="markets" class="w-full bg-white/80">
      <div class="max-w-7xl mx-auto px-4 py-10">
        <h2 class="text-sm font-semibold text-slate-900">Market Overview</h2>
        <p class="text-xs text-slate-500 mt-1">Snapshot of popular tickers and short trend notes.</p>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">

          <div class="bg-white rounded-md p-4 card-shadow">
            <div class="flex items-center justify-between">
              <div class="text-xs font-semibold">Equities</div>
              <div class="text-[11px] text-slate-500">Global</div>
            </div>
            <div class="mt-3 text-[11px] text-slate-600">
              <div class="flex items-center justify-between"><div>Apple (AAPL)</div><div class="font-semibold">$172.90</div></div>
              <div class="flex items-center justify-between mt-2 text-[11px]"><div>Tesla (TSLA)</div><div class="text-red-500 font-semibold">-0.87%</div></div>
            </div>
          </div>

          <div class="bg-white rounded-md p-4 card-shadow">
            <div class="flex items-center justify-between">
              <div class="text-xs font-semibold">Indices</div>
              <div class="text-[11px] text-slate-500">Local / Global</div>
            </div>
            <div class="mt-3 text-[11px] text-slate-600">
              <div class="flex items-center justify-between"><div>NIFTY 50</div><div class="font-semibold">18,120.40</div></div>
              <div class="flex items-center justify-between mt-2 text-[11px]"><div>S&P 500</div><div class="text-emerald-600 font-semibold">+0.32%</div></div>
            </div>
          </div>

          <div class="bg-white rounded-md p-4 card-shadow">
            <div class="flex items-center justify-between">
              <div class="text-xs font-semibold">Commodities</div>
              <div class="text-[11px] text-slate-500">Today</div>
            </div>
            <div class="mt-3 text-[11px] text-slate-600">
              <div class="flex items-center justify-between"><div>Gold</div><div class="font-semibold">$2,020</div></div>
              <div class="flex items-center justify-between mt-2 text-[11px]"><div>Crude Oil</div><div class="text-red-500 font-semibold">-0.64%</div></div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- FEATURES / WHY TRI -->
    <section id="features" class="w-full py-10">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-sm font-semibold text-slate-900">Why TRI</h2>
        <p class="text-xs text-slate-500 mt-1">A few highlights that matter to active investors.</p>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">

          <div class="p-4 bg-white rounded-md card-shadow">
            <div class="text-xs font-semibold">Concise Signals</div>
            <div class="text-[11px] text-slate-600 mt-2">Short, actionable signals derived from price-action and simple momentum filters — formatted for quick decisions.</div>
          </div>

          <div class="p-4 bg-white rounded-md card-shadow">
            <div class="text-xs font-semibold">Watchlists</div>
            <div class="text-[11px] text-slate-600 mt-2">Organize tickers into compact watchlists and view small snapshots with change percentages.</div>
          </div>

          <div class="p-4 bg-white rounded-md card-shadow">
            <div class="text-xs font-semibold">Minimalist UI</div>
            <div class="text-[11px] text-slate-600 mt-2">A clutter-free interface that keeps your focus on data, not noise.</div>
          </div>

        </div>

      </div>
    </section>

    <!-- INSIGHTS / LATEST NOTES -->
    <section id="insights" class="w-full bg-white/80 py-10">
      <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-sm font-semibold text-slate-900">Quick Insights</h2>
        <p class="text-xs text-slate-500 mt-1">Short notes that help you decide — static examples below.</p>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-white rounded-md p-4 card-shadow text-[11px]">
            <div class="font-semibold text-xs">Earnings watch — AAPL</div>
            <div class="text-slate-600 mt-2">Analysts expect soft guidance; watch option-implied vols for short-term trades.</div>
            <div class="mt-3 text-[11px] text-slate-500">Nov 12, 2025</div>
          </div>

          <div class="bg-white rounded-md p-4 card-shadow text-[11px]">
            <div class="font-semibold text-xs">NIFTY momentum</div>
            <div class="text-slate-600 mt-2">Local breadth improving — consider scaling into top-performing sectors with strict stop-loss.</div>
            <div class="mt-3 text-[11px] text-slate-500">Nov 11, 2025</div>
          </div>

          <div class="bg-white rounded-md p-4 card-shadow text-[11px]">
            <div class="font-semibold text-xs">Commodities</div>
            <div class="text-slate-600 mt-2">Oil remains choppy; prefer pairs trades or short-term mean-reversion setups.</div>
            <div class="mt-3 text-[11px] text-slate-500">Nov 10, 2025</div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA + FOOTER -->
    <footer id="contact" class="w-full bg-white py-8">
      <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center gap-3">
            <img src="{{ asset('public/assets/images/rapid-investors-logo2.png') }}" alt="TRI" class="h-16 object-contain" />
            <div class="flex flex-col -ml-1">
              <span class="font-semibold text-xs">The Rapid Investors</span>
              <span class="text-[11px] text-slate-500">Trade better. Faster.</span>
            </div>
          </div>

          <p class="text-[11px] text-slate-500 mt-4 max-w-md">This is a static demo landing page—replace with dynamic data from your platform or APIs as needed.</p>
        </div>

        <div class="flex flex-col md:items-end text-[11px] text-slate-600">
          <div class="mb-2">Contact</div>
          <div>hello@rapidinvestors.example</div>
          <div class="mt-3 text-[11px] text-slate-500">© The Rapid Investors</div>
        </div>
      </div>
    </footer>

  </div>

</body>
</html>
