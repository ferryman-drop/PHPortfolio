document.addEventListener('DOMContentLoaded', function () {
  const ctx = document.getElementById('portfolioChart');
  if (ctx) {
    // Собираем данные из DOM
    const items = Array.from(document.querySelectorAll('#portfolio-list li')).map(li => {
      const name = li.querySelector('span').textContent.split(' (')[0];
      const value = parseFloat(li.querySelector('strong').textContent.replace('$', '').replace(/ /g, ''));
      return { name, value };
    });
    const data = {
      labels: items.map(i => i.name),
      datasets: [{
        label: 'Стоимость',
        data: items.map(i => i.value),
        backgroundColor: [
          'rgba(255, 99, 132, 0.5)',
          'rgba(54, 162, 235, 0.5)',
          'rgba(255, 206, 86, 0.5)',
          'rgba(75, 192, 192, 0.5)',
          'rgba(153, 102, 255, 0.5)',
          'rgba(255, 159, 64, 0.5)'
        ],
        borderWidth: 1
      }]
    };
    new Chart(ctx, {
      type: 'doughnut',
      data: data,
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          title: { display: true, text: 'Распределение портфолио' }
        }
      },
    });
  }

  // ROI Bar Chart
  const roiCtx = document.getElementById('roiChart');
  if (roiCtx) {
    const rows = Array.from(document.querySelectorAll('#portfolio-list li'));
    const names = rows.map(li => li.querySelector('span').textContent.split(' (')[0]);
    const rois = rows.map(li => {
      const roiSpan = li.querySelector('.roi-pos, .roi-neg');
      if (roiSpan) return parseFloat(roiSpan.textContent.replace('%',''));
      return 0;
    });
    new Chart(roiCtx, {
      type: 'bar',
      data: {
        labels: names,
        datasets: [{
          label: 'ROI (%)',
          data: rois,
          backgroundColor: rois.map(r => r >= 0 ? 'rgba(30, 126, 52, 0.7)' : 'rgba(217, 4, 41, 0.7)'),
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'ROI по токенам' }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  }
}); 