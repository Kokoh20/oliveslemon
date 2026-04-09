(function(){
  const container = document.getElementById('ordersContainer');
  const refreshBtn = document.getElementById('refreshBtn');
  const API = '../orders.php';

  function money(n){ return (n||0).toFixed(2); }
  function dayKey(iso){
    if(!iso) return 'Unknown Date';
    const d = new Date(iso);
    if(Number.isNaN(d.getTime())) return 'Unknown Date';
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const dd = String(d.getDate()).padStart(2,'0');
    return `${y}-${m}-${dd}`;
  }

  function render(list){
    // Only show placed orders (status = new)
    const placed = (list||[]).filter(o=> (o.status||'').toLowerCase()==='new');
    if(!placed.length){ container.innerHTML = '<div class="alert alert-info">No placed orders yet.</div>'; return; }

    // Group by day
    const groups = new Map();
    for(const o of placed){
      const key = dayKey(o.createdAt);
      if(!groups.has(key)) groups.set(key, []);
      groups.get(key).push(o);
    }

    // Build HTML per day section with subtotal and count
    const sections = [];
    const keys = Array.from(groups.keys()).sort((a,b)=> a<b?1:-1); // newest day first
    for(const key of keys){
      const orders = groups.get(key);
      const dayTotal = orders.reduce((s,o)=> s + (o.totals && o.totals.payable || 0), 0);
      const header = `
        <div class="d-flex justify-content-between align-items-end mb-2">
          <h5 class="mb-0">${key}</h5>
          <div class="text-muted small">${orders.length} order(s) • ₱ ${money(dayTotal)}</div>
        </div>`;

      const itemsHtml = orders.map(o=>{
      const items = (o.items||[]).map(i=> `${i.qty}× ${i.name}`).join(', ');
      const id = o.id || '';
      const status = 'PLACED';
      const created = o.createdAt || '';
      const total = o.totals && o.totals.payable || 0;
      const trackHref = `../track.html?id=${encodeURIComponent(id)}`;
      return `
        <div class="card">
          <div class="card-body d-flex gap-3 align-items-start">
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-light">${status}</span>
                <code class="small">${id}</code>
              </div>
              <div class="fw-bold mt-1">${o.customer?.name || ''} • ${o.customer?.phone || ''}</div>
              <div class="text-muted small">${o.customer?.address || ''}</div>
              <div class="small mt-1">${items}</div>
              <div class="small mt-1"><a href="${trackHref}" target="_blank">Track</a></div>
              <div class="mt-2 d-flex gap-2">
                <button class="btn btn-success btn-sm" data-action="complete" data-id="${id}">Complete</button>
                <button class="btn btn-outline-danger btn-sm" data-action="cancel" data-id="${id}">Cancel</button>
              </div>
            </div>
            <div class="text-end">
              <div class="fw-bold">₱ ${money(total)}</div>
              <div class="small text-muted">${created}</div>
            </div>
          </div>
        </div>`;
      }).join('');

      sections.push(`<section class="mb-4">${header}${itemsHtml}</section>`);
    }

    container.innerHTML = sections.join('');

    // Bind action buttons
    container.querySelectorAll('[data-action][data-id]').forEach(btn =>{
      btn.addEventListener('click', async () =>{
        const id = btn.getAttribute('data-id');
        const action = btn.getAttribute('data-action');
        const status = action === 'complete' ? 'completed' : 'cancelled';
        const prevText = btn.textContent;
        btn.disabled = true; btn.textContent = 'Please wait…';
        try {
          const res = await fetch(API, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status })
          });
          if(!res.ok){ throw new Error('Failed to update'); }
          // Reload list; updated order (no longer new) will disappear from this view
          load();
        } catch(e){
          alert('Update failed. Please try again.');
          btn.disabled = false; btn.textContent = prevText;
        }
      });
    });
  }

  function load(){
    fetch(API)
      .then(r=> r.json())
      .then(res => render(res.orders || []))
      .catch(()=> container.innerHTML = '<div class="alert alert-danger">Failed to load orders.</div>');
  }

  // Actions available: Complete (-> completed), Cancel (-> cancelled)

  refreshBtn.addEventListener('click', load);
  load();
})();