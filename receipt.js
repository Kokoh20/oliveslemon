(function(){
  const qs = new URLSearchParams(location.search);
  const id = qs.get('id') || '';

  const els = {
    id: document.getElementById('r_id'),
    date: document.getElementById('r_date'),
    status: document.getElementById('r_status'),
    name: document.getElementById('r_name'),
    phone: document.getElementById('r_phone'),
    address: document.getElementById('r_address'),
    type: document.getElementById('r_type'),
    items: document.getElementById('r_items'),
    sub: document.getElementById('r_sub'),
    disc: document.getElementById('r_disc'),
    total: document.getElementById('r_total'),
    paymethod: document.getElementById('r_paymethod'),
    paid: document.getElementById('r_paid'),
    ref: document.getElementById('r_ref'),
  };

  document.getElementById('printBtn')?.addEventListener('click', ()=> window.print());
  const trackBtn = document.getElementById('trackBtn');
  if(trackBtn && id){ trackBtn.href = 'track.html?id=' + encodeURIComponent(id); }

  function money(n){ return (n||0).toFixed(2); }

  function render(order){
    if(!order) return;
    els.id.textContent = order.id || '';
    els.date.textContent = order.createdAt || '';
    els.status.textContent = (order.status || '').toUpperCase();
    const c = order.customer || {};
    els.name.textContent = c.name || '';
    els.phone.textContent = c.phone || '';
    els.address.textContent = c.address || '';
    els.type.textContent = (c.type || '').toUpperCase();

    const items = order.items || [];
    els.items.innerHTML = items.map(it =>{
      const extras = (it.extras||[])
        .filter(e => (e.qty||0) > 0)
        .map(e => `${e.qty}× ${e.name}`)
        .join(', ');
      const line = (it.price + (it.extras||[]).reduce((s,e)=> s + e.price*e.qty, 0)) * (it.qty||1);
      return `
        <div class="d-flex justify-content-between">
          <div>
            <div>${it.qty || 1}× ${it.name}${it.variant ? ` — ${it.variant.name}` : ''}</div>
            ${extras ? `<div class="small muted">Extras: ${extras}</div>` : ''}
          </div>
          <div>₱ ${money(line)}</div>
        </div>`;
    }).join('');

    const t = order.totals || {};
    els.sub.textContent = money(t.subtotal || 0);
    els.disc.textContent = money(t.discount || 0);
    els.total.textContent = money(t.payable || 0);

    const p = order.payment || {};
    els.paymethod.textContent = (p.method || 'cash').toUpperCase();
    els.paid.textContent = p.paid ? '• PAID' : '• UNPAID';
    els.ref.textContent = p.ref || '-';
  }

  function load(){
    if(!id){
      els.items.innerHTML = '<div class="alert alert-warning">Missing order id.</div>';
      return;
    }
    fetch('orders.php?id=' + encodeURIComponent(id))
      .then(r => r.json())
      .then(res => {
        if(res && res.order){ render(res.order); }
        else { els.items.innerHTML = '<div class="alert alert-danger">Order not found.</div>'; }
      })
      .catch(() => {
        els.items.innerHTML = '<div class="alert alert-danger">Failed to load receipt.</div>';
      });
  }

  load();
})();
