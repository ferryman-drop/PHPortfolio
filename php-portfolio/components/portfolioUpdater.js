document.addEventListener('DOMContentLoaded', function () {
  const updateBtn = document.createElement('button');
  updateBtn.className = 'btn btn-primary mb-3';
  updateBtn.textContent = 'Обновить цены';
  const container = document.querySelector('.container');
  if (container) container.insertBefore(updateBtn, container.firstChild.nextSibling);

  updateBtn.addEventListener('click', function () {
    const items = Array.from(document.querySelectorAll('#portfolio-list li'));
    const ids = items.map(li => li.textContent.toLowerCase().includes('bitcoin') ? 'bitcoin'
      : li.textContent.toLowerCase().includes('ethereum') ? 'ethereum'
      : li.textContent.toLowerCase().includes('usd coin') ? 'usd-coin'
      : '').filter(Boolean);
    if (!ids.length) return;
    fetch('api/token-prices.php?ids=' + ids.join(','))
      .then(res => res.json())
      .then(data => {
        let total = 0;
        items.forEach(li => {
          let id = li.textContent.toLowerCase().includes('bitcoin') ? 'bitcoin'
            : li.textContent.toLowerCase().includes('ethereum') ? 'ethereum'
            : li.textContent.toLowerCase().includes('usd coin') ? 'usd-coin'
            : '';
          if (!id || !data[id]) return;
          const amount = parseFloat(li.textContent.split('×')[0].split(')').pop().trim());
          const price = data[id].usd;
          const value = amount * price;
          li.querySelector('span:last-child').innerHTML = amount + ' × $' + price + ' = <strong>$' + value.toFixed(2) + '</strong>';
          total += value;
        });
        document.getElementById('portfolio-value').textContent = '$' + total.toFixed(2);
      });
  });
}); 