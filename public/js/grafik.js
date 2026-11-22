/* Pembungkus Chart.js: gaya seragam untuk semua grafik (garis 2px, grid samar, tooltip crosshair). */
(function () {
  if (!window.Chart) return;
  const css = getComputedStyle(document.documentElement);
  const v = (n) => css.getPropertyValue(n).trim();
  Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
  Chart.defaults.font.size = 12;
  Chart.defaults.color = v('--ink-2');
  Chart.defaults.borderColor = v('--line');
  Chart.defaults.plugins.legend.display = false; // legenda HTML ditulis di halaman
  Chart.defaults.plugins.tooltip.backgroundColor = '#13243a';
  Chart.defaults.plugins.tooltip.padding = 10;
  Chart.defaults.plugins.tooltip.boxPadding = 4;
  Chart.defaults.maintainAspectRatio = false;

  const rp = (n) => n == null ? '-' : 'Rp' + Math.round(n).toLocaleString('id-ID');
  const pct = (n) => n == null ? '-' : (n > 0 ? '+' : '') + n.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
  const tglPendek = (s) => { const d = new Date(s + 'T00:00:00'); return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }); };
  const seri = (i) => v('--series-' + (i + 1)) || v('--series-1');

  // Garis vertikal saat hover, supaya pembacaan per tanggal mudah.
  const crosshair = {
    id: 'crosshair',
    afterDraw(chart) {
      const a = chart.tooltip && chart.tooltip.getActiveElements();
      if (!a || !a.length) return;
      const x = a[0].element.x, { top, bottom } = chart.chartArea, ctx = chart.ctx;
      ctx.save(); ctx.strokeStyle = v('--ink-3'); ctx.lineWidth = 1; ctx.setLineDash([3, 3]);
      ctx.beginPath(); ctx.moveTo(x, top); ctx.lineTo(x, bottom); ctx.stroke(); ctx.restore();
    },
  };

  window.Grafik = {
    rp, pct, seri,

    /** Grafik garis harga. series: [{nama, data, utama?}] */
    hargaGaris(el, label, series) {
      // Warna mengikuti urutan pasar (tanpa menghitung seri utama), sama dengan legenda HTML.
      let k = 0;
      series = series.map((s) => ({ ...s, warna: s.utama ? v('--ink') : seri(k++) }));
      return new Chart(el, {
        type: 'line',
        plugins: [crosshair],
        data: {
          labels: label,
          datasets: series.map((s, i) => ({
            label: s.nama, data: s.data, spanGaps: true,
            borderColor: s.warna, backgroundColor: s.warna,
            borderWidth: s.utama ? 2.5 : 2, pointRadius: 0, pointHoverRadius: 5,
            pointHoverBorderColor: v('--surface'), pointHoverBorderWidth: 2, tension: 0.25, order: s.utama ? 0 : 1,
          })),
        },
        options: {
          interaction: { mode: 'index', intersect: false },
          scales: {
            x: { grid: { display: false }, ticks: { maxTicksLimit: 8, callback(val) { return tglPendek(this.getLabelForValue(val)); } } },
            y: { grace: '5%', ticks: { callback: (n) => rp(n) } },
          },
          plugins: { tooltip: { callbacks: { title: (it) => new Date(it[0].label + 'T00:00:00').toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }), label: (c) => ' ' + c.dataset.label + ': ' + rp(c.parsed.y) } } },
        },
      });
    },

    /** Batang perubahan (%) berpolaritas: naik merah, turun biru, satu sumbu dengan garis nol. */
    batangPersen(el, label, data) {
      return new Chart(el, {
        type: 'bar',
        data: { labels: label, datasets: [{ data, backgroundColor: data.map((n) => n >= 0 ? v('--div-naik') : v('--div-turun')), borderRadius: 4, borderSkipped: false, maxBarThickness: 26, label: 'Perubahan' }] },
        options: {
          scales: {
            x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } },
            y: { ticks: { callback: (n) => n + '%' }, grid: { color: (c) => c.tick.value === 0 ? v('--ink-3') : v('--line') } },
          },
          plugins: { tooltip: { callbacks: { label: (c) => ' ' + pct(c.parsed.y) } } },
        },
      });
    },

    /** Garis persen (inflasi), beberapa ukuran dalam satuan yang sama (%). */
    persenGaris(el, label, series) {
      return new Chart(el, {
        type: 'line',
        plugins: [crosshair],
        data: { labels: label, datasets: series.map((s, i) => ({ label: s.nama, data: s.data, borderColor: seri(i), backgroundColor: seri(i), borderWidth: 2, pointRadius: 3, pointHoverRadius: 6, pointHoverBorderColor: v('--surface'), pointHoverBorderWidth: 2, tension: 0.2 })) },
        options: {
          interaction: { mode: 'index', intersect: false },
          scales: { x: { grid: { display: false } }, y: { ticks: { callback: (n) => n + '%' }, grid: { color: (c) => c.tick.value === 0 ? v('--ink-3') : v('--line') } } },
          plugins: { tooltip: { callbacks: { label: (c) => ' ' + c.dataset.label + ': ' + pct(c.parsed.y) } } },
        },
      });
    },

    /** Sparkline kecil tanpa sumbu. */
    spark(el, data, warna) {
      return new Chart(el, {
        type: 'line',
        data: { labels: data.map((_, i) => i), datasets: [{ data, borderColor: warna || v('--ink-2'), borderWidth: 2, pointRadius: 0, tension: 0.3 }] },
        options: { events: [], scales: { x: { display: false }, y: { display: false } }, plugins: { tooltip: { enabled: false } }, animation: false },
      });
    },
  };
})();
