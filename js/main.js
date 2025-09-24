/* js/main.js – Contacts dashboard (production)
   - Search / Sort / Refresh
   - Add / Edit / Delete (single dialog)
   - Optional demo mode via ?demo=1 or <body data-demo="1">
*/
(() => {
    'use strict';

    // ---------- helpers ----------
    const $  = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const on = (el, ev, fn) => el && el.addEventListener(ev, fn);
    const esc = (s='') => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const deb = (fn, ms=250) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a),ms); }; };
    const rid = () => 'c_' + Math.random().toString(36).slice(2,10);
    const cmp = (a='', b='') => String(a).toLowerCase().localeCompare(String(b).toLowerCase());

    // ---------- DOM ----------
    const grid         = $('#cardsGrid');
    const addBtn       = $('#addBtn');
    const refreshBtn   = $('#refreshBtn');
    const searchInput  = $('#searchInput');
    const sortSelect   = $('#sortSelect');

    const dlg          = $('#contactDialog');
    const form         = $('#contactForm');
    const f_id         = $('#f_id');
    const f_first      = $('#f_firstName');
    const f_last       = $('#f_lastName');
    const f_email      = $('#f_email');
    const f_phone      = $('#f_phone');
    const f_notes      = $('#f_notes');
    const dialogTitle  = $('#dialogTitle');
    const cancelDialog = $('#cancelDialog');

    // toast
    function toast(msg, type='info'){
        const t = $('#toast'); if(!t) return;
        t.textContent = msg; t.className = `toast ${type} show`;
        clearTimeout(window.__tt); window.__tt = setTimeout(()=> t.classList.remove('show'), 1600);
    }

    // ---------- API (prod) ----------
    const API = {
        search: 'api/searchContacts.php',
        add:    'api/addContact.php',
        update: 'api/updateContact.php',
        del:    'api/deleteContact.php'
    };

    // Optional demo (only if explicitly requested)
    const params      = new URLSearchParams(location.search);
    const USE_DEMO    =
        document.body?.dataset.demo === '1' ||
        params.has('demo') || params.get('mock') === '1' ||
        (location.protocol === 'file:'); // allow local preview

    let demoData = [
        { id:'1', firstName:'Gary',     lastName:'Powell',   email:'gary@acme.co',   phoneNumber:'222-555-3333', notes:'Marketing' },
        { id:'2', firstName:'John',     lastName:'Kelly',    email:'john@acme.co',   phoneNumber:'222-555-4444', notes:'Sales' },
        { id:'3', firstName:'Linda',    lastName:'Cook',     email:'linda@acme.co',  phoneNumber:'777-333-9900', notes:'Finance' },
        { id:'4', firstName:'Margaret', lastName:'Walker',   email:'mw@acme.co',     phoneNumber:'777-888-1212', notes:'Ops' }
    ];

    const normalize = (list=[]) => list.map(c => ({
        id:          c.id || c.contactId || c.contactID || c._id || c.email || rid(),
        firstName:   c.firstName || c.first_name || c.fname || '',
        lastName:    c.lastName  || c.last_name  || c.lname || '',
        email:       c.email || '',
        phoneNumber: c.phoneNumber || c.phone || c.phone_number || '',
        notes:       c.notes || ''
    }));

    async function apiSearch(q=''){
        if (USE_DEMO){
            let list = demoData;
            if(q){
                const s = q.toLowerCase();
                list = list.filter(c => [c.firstName,c.lastName,c.email,c.phoneNumber,c.notes].join(' ').toLowerCase().includes(s));
            }
            return normalize(list);
        }
        try{
            const r = await fetch(`${API.search}?q=${encodeURIComponent(q)}`, { credentials:'same-origin' });
            if(!r.ok) throw new Error();
            const data = await r.json().catch(() => []);
            const list = Array.isArray(data) ? data : (data.results || []);
            return normalize(list);
        }catch{
            toast('Search failed', 'error');
            return [];
        }
    }

    async function apiAdd(obj){
        if (USE_DEMO){ obj.id = rid(); demoData.unshift(obj); return { ok:true, id: obj.id }; }
        const r  = await fetch(API.add, { method:'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(obj),credentials: 'same-origin'});
        if(!r.ok) throw new Error('add failed');
        return r.json().catch(()=>({}));
    }

    async function apiUpdate(obj){
        if (USE_DEMO){
            const i = demoData.findIndex(c => c.id === obj.id);
            if (i > -1) demoData[i] = { ...demoData[i], ...obj };
            return { ok:true };
        }
        const fd = new FormData(); Object.entries(obj).forEach(([k,v]) => v!=null && fd.append(k,v));
        const r  = await fetch(API.update, { method:'POST', body: fd, credentials:'same-origin' });
        if(!r.ok) throw new Error('update failed');
        return r.json().catch(()=>({}));
    }

    async function apiDelete(id){
        if (USE_DEMO){ demoData = demoData.filter(c => c.id !== id); return true; }
        const fd = new FormData(); fd.append('id', id);
        const r  = await fetch(API.del, { method:'POST', body: fd, credentials:'same-origin' });
        if(!r.ok) throw new Error('delete failed');
        return true;
    }

    // ---------- render ----------
    const initials = c => (c.firstName?.[0]||'?') + (c.lastName?.[0]||'');
    const fullName = c => [c.firstName, c.lastName].filter(Boolean).join(' ') || 'Unnamed';

    function renderGrid(list){
        if(!grid) return;
        grid.innerHTML = '';
        if(!list.length){
            grid.innerHTML = `
        <div class="empty">
          <h3>No contacts</h3>
          <div>Use <strong>+ New Contact</strong> to add one, or try a different search.</div>
        </div>`;
            return;
        }
        const frag = document.createDocumentFragment();
        list.forEach(c => {
            const card = document.createElement('article');
            card.className = 'card';
            card.dataset.id = c.id;
            card.innerHTML = `
        <div class="card-head">
          <div class="avatar">${esc(initials(c))}</div>
          <div>
            <div class="card-name">${esc(fullName(c))}</div>
            <div class="card-meta">${esc(c.email || c.phoneNumber || '')}</div>
          </div>
          <div class="card-actions">
            <button class="btn small edit">Edit</button>
            <button class="btn small danger del">Delete</button>
          </div>
        </div>
        ${c.notes ? `<div class="card-rows"><div class="row">📝 ${esc(c.notes)}</div></div>` : ''}`;
            frag.appendChild(card);
        });
        grid.appendChild(frag);
    }

    // ---------- dialog helpers ----------
    function openDialog(c = null){
        if (c){
            dialogTitle.textContent = 'Edit Contact';
            f_id.value    = c.id || '';
            f_first.value = c.firstName || '';
            f_last.value  = c.lastName || '';
            f_email.value = c.email || '';
            f_phone.value = c.phoneNumber || '';
            f_notes.value = c.notes || '';
        } else {
            dialogTitle.textContent = 'New Contact';
            form.reset();
            f_id.value = '';
        }
        if (dlg.showModal) dlg.showModal();
        else { dlg.setAttribute('open',''); document.body.classList.add('modal-open'); }
    }
    function closeDialog(){
        form.reset(); f_id.value = '';
        if (dlg.close) dlg.close();
        else { dlg.removeAttribute('open'); document.body.classList.remove('modal-open'); }
    }

    // ---------- interactions ----------
    let CURRENT = [];

    async function refresh(){
        const q = searchInput?.value?.trim() || '';
        CURRENT = await apiSearch(q);
        const sortBy = sortSelect?.value || '';
        if (sortBy){ CURRENT.sort((a,b) => cmp(a[sortBy], b[sortBy])); }
        renderGrid(CURRENT);
    }

    on(addBtn, 'click', () => openDialog());
    on(cancelDialog, 'click', closeDialog);
    on(refreshBtn, 'click', refresh);
    on(sortSelect, 'change', refresh);
    on(searchInput, 'input', deb(refresh, 250));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && dlg?.open) closeDialog(); });

    on(form, 'submit', async (e) => {

        const userId = localStorage.getItem('userId');
        e.preventDefault();
        const obj = {
            id:          f_id.value || undefined,
            first_name:   f_first.value.trim(),
            last_name:    f_last.value.trim(),
            email:       f_email.value.trim(),
            phone_number: f_phone.value.trim(),
            user_id:    userId
        };
        try{
            if (obj.id) await apiUpdate(obj);
            else        await apiAdd(obj);
            toast(obj.id ? 'Saved' : 'Added', 'success');
            closeDialog();
            await refresh();
        }catch{
            toast('Save failed','error');
        }
    });

    grid?.addEventListener('click', async (e) => {
        const card = e.target.closest('.card'); if(!card) return;
        const id = card.dataset.id;
        if (e.target.closest('.edit')){
            const c = CURRENT.find(x => x.id === id);
            if (c) openDialog(c);
        } else if (e.target.closest('.del')){
            if (!confirm('Delete this contact?')) return;
            try{
                await apiDelete(id);
                toast('Deleted','success');
                await refresh();
            }catch{
                toast('Delete failed','error');
            }
        }
    });

    // ---------- boot ----------
    refresh();
})();
