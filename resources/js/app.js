import { Collapse, Modal } from 'bootstrap';
import $ from 'jquery';
import select2 from 'select2';
import '../css/app.css';

select2(window, $);

const root = document.documentElement;
const savedTheme = localStorage.getItem('admin-theme') || 'light';
root.dataset.adminTheme = savedTheme;

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const sidebarBackdrop = document.querySelector('.sidebar-backdrop');
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const setSidebarOpen = (open) => {
        sidebar?.classList.toggle('show', open);
        sidebarBackdrop?.classList.toggle('show', open);
        sidebarToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    sidebarToggle?.addEventListener('click', () => setSidebarOpen(!sidebar?.classList.contains('show')));
    document.querySelector('[data-sidebar-close]')?.addEventListener('click', () => setSidebarOpen(false));

    document.querySelectorAll('[data-admin-collapse]').forEach((toggle) => {
        const target = document.querySelector(toggle.dataset.adminCollapse);
        if (!target) return;
        const collapse = Collapse.getOrCreateInstance(target, { toggle: false });
        toggle.addEventListener('click', () => collapse.toggle());
        target.addEventListener('shown.bs.collapse', () => toggle.setAttribute('aria-expanded', 'true'));
        target.addEventListener('hidden.bs.collapse', () => toggle.setAttribute('aria-expanded', 'false'));
    });

    sidebar?.querySelectorAll('a.side-link').forEach((link) => link.addEventListener('click', () => {
        if (window.innerWidth < 992) setSidebarOpen(false);
    }));

    document.querySelectorAll('[data-admin-theme]').forEach((option) => {
        option.classList.toggle('active', option.dataset.adminTheme === savedTheme);
        option.addEventListener('click', () => {
            const theme = option.dataset.adminTheme;
            root.dataset.adminTheme = theme;
            localStorage.setItem('admin-theme', theme);
            document.querySelectorAll('[data-admin-theme]').forEach((item) => item.classList.toggle('active', item === option));
        });
    });

    $('.js-category-select').select2({ placeholder: 'Select one or more categories', width: '100%' });
    $('.js-tag-select').select2({
        tags: true,
        tokenSeparators: [','],
        placeholder: 'Type a tag and press Enter',
        width: '100%',
    });
    $('.js-product-select').select2({ placeholder: 'Select products for this deal', width: '100%' });

    const pickerModal = document.querySelector('#media-picker-modal');
    const pickerContent = document.querySelector('#media-picker-content');
    let activeMediaTarget = null;
    let activeMediaMultiple = false;

    const markSelectedMedia = () => {
        if (!activeMediaMultiple || !activeMediaTarget) return;
        const selected = new Set([...document.querySelectorAll(`[data-media-gallery="${activeMediaTarget}"] [data-gallery-item]`)].map((item) => item.dataset.galleryItem));
        pickerContent.querySelectorAll('[data-select-media]').forEach((choice) => choice.classList.toggle('selected', selected.has(choice.dataset.selectMedia)));
    };

    const loadMediaPage = async (url) => {
        pickerContent.innerHTML = '<div class="text-center py-5" style="color:var(--muted)"><div class="spinner-border spinner-border-sm me-2"></div>Loading media...</div>';
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        pickerContent.innerHTML = await response.text();
        markSelectedMedia();
    };

    document.querySelectorAll('[data-media-picker-target]').forEach((button) => {
        button.addEventListener('click', async () => {
            activeMediaTarget = button.dataset.mediaPickerTarget;
            activeMediaMultiple = button.hasAttribute('data-media-picker-multiple');
            await loadMediaPage('/admin/media/picker');
            Modal.getOrCreateInstance(pickerModal).show();
        });
    });

    pickerContent?.addEventListener('click', async (event) => {
        const page = event.target.closest('[data-media-page]');
        if (page) { event.preventDefault(); await loadMediaPage(page.href); return; }
        const choice = event.target.closest('[data-select-media]');
        if (!choice || !activeMediaTarget) return;
        if (activeMediaMultiple) {
            const gallery = document.querySelector(`[data-media-gallery="${activeMediaTarget}"]`);
            if (!gallery || gallery.querySelector(`[data-gallery-item="${choice.dataset.selectMedia}"]`)) return;
            if (gallery.querySelectorAll('[data-gallery-item]').length >= 20) { window.alert('A product can have up to 20 gallery images.'); return; }
            const item = document.createElement('div'); item.className = 'product-gallery-item'; item.dataset.galleryItem = choice.dataset.selectMedia;
            const image = document.createElement('img'); image.src = choice.dataset.mediaUrl; image.alt = choice.dataset.mediaName;
            const input = document.createElement('input'); input.type = 'hidden'; input.name = 'gallery_media_ids[]'; input.value = choice.dataset.selectMedia;
            const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'gallery-remove'; remove.dataset.removeGallery = ''; remove.setAttribute('aria-label','Remove image'); remove.innerHTML = '<i class="bi bi-x"></i>';
            item.append(image,input,remove); gallery.append(item); choice.classList.add('selected'); return;
        }
        const mediaInput = document.querySelector(`#${activeMediaTarget}`);
        if (mediaInput) {
            mediaInput.value = choice.dataset.selectMedia;
            mediaInput.setAttribute('value', choice.dataset.selectMedia);
        }
        const preview = document.querySelector(`[data-media-preview="${activeMediaTarget}"]`);
        if (preview) {
            const image = document.createElement('img');
            image.src = choice.dataset.mediaUrl;
            image.alt = choice.dataset.mediaName;
            preview.replaceChildren(image);
        }
        const label = document.querySelector(`[data-media-label="${activeMediaTarget}"]`);
        if (label) label.textContent = choice.dataset.mediaName;
        Modal.getOrCreateInstance(pickerModal).hide();
    });

    document.addEventListener('click', (event) => event.target.closest('[data-remove-gallery]')?.closest('[data-gallery-item]')?.remove());
    document.querySelector('#product-gallery-upload')?.addEventListener('change', (event) => {
        const preview = document.querySelector('#product-upload-preview'); if (!preview) return; preview.replaceChildren();
        [...event.target.files].slice(0,10).forEach((file) => { const item=document.createElement('div');item.className='product-gallery-item pending-upload';const image=document.createElement('img');image.src=URL.createObjectURL(file);image.alt=file.name;const badge=document.createElement('span');badge.className='upload-badge';badge.textContent='New';item.append(image,badge);preview.append(item); });
    });

    document.querySelectorAll('[data-color-picker]').forEach((picker) => {
        const code = document.querySelector(picker.dataset.colorPicker);
        picker.addEventListener('input', () => { if (code) code.value = picker.value.toUpperCase(); });
        code?.addEventListener('input', () => { if (/^#[0-9A-Fa-f]{6}$/.test(code.value)) picker.value = code.value; });
    });

    document.querySelectorAll('input[type="checkbox"][name="is_active"]').forEach((checkbox) => {
        checkbox.form?.addEventListener('submit', () => {
            if (!checkbox.checked && !checkbox.form.querySelector('input[data-status-fallback]')) {
                const fallback = document.createElement('input');
                fallback.type = 'hidden';
                fallback.name = 'is_active';
                fallback.value = '0';
                fallback.dataset.statusFallback = 'true';
                checkbox.form.append(fallback);
            }
        });
    });

    const purchaseLines = document.querySelector('#purchase-lines tbody');
    const refreshPurchaseTotals = () => {
        let subtotal = 0;
        purchaseLines?.querySelectorAll('tr').forEach((row) => {
            const total = (parseFloat(row.querySelector('.line-qty')?.value) || 0) * (parseFloat(row.querySelector('.line-cost')?.value) || 0);
            subtotal += total;
            row.querySelector('.line-total').textContent = `$${total.toFixed(2)}`;
        });
        const discount = parseFloat(document.querySelector('[name="discount"]')?.value) || 0;
        const tax = parseFloat(document.querySelector('[name="tax"]')?.value) || 0;
        document.querySelector('#purchase-grand-total')?.replaceChildren(`$${(subtotal - discount + tax).toFixed(2)}`);
    };
    const namePurchaseFields = () => purchaseLines?.querySelectorAll('tr').forEach((row, index) => {
        row.querySelector('.product-select').name = `items[${index}][product_id]`;
        row.querySelector('.product-name').name = `items[${index}][product_name]`;
        row.querySelector('.line-qty').name = `items[${index}][quantity]`;
        row.querySelector('.line-cost').name = `items[${index}][unit_cost]`;
    });
    document.querySelector('#add-purchase-line')?.addEventListener('click', () => { purchaseLines.append(document.querySelector('#purchase-line-template').content.cloneNode(true)); namePurchaseFields(); refreshPurchaseTotals(); });
    purchaseLines?.addEventListener('input', refreshPurchaseTotals);
    purchaseLines?.addEventListener('change', (event) => { if (event.target.matches('.product-select')) { const option = event.target.selectedOptions[0]; const name = event.target.closest('td').querySelector('.product-name'); if (option?.dataset.name) name.value = option.dataset.name; } refreshPurchaseTotals(); });
    purchaseLines?.addEventListener('click', (event) => { if (event.target.closest('.remove-line') && purchaseLines.rows.length > 1) { event.target.closest('tr').remove(); namePurchaseFields(); refreshPurchaseTotals(); } });
    namePurchaseFields();
    refreshPurchaseTotals();

    const posCart = new Map();
    const escapeHtml = (value) => String(value).replace(/[&<>'"]/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);
    const renderPosCart = () => {
        const cart = document.querySelector('#pos-cart'); if (!cart) return;
        const rows = [...posCart.values()]; const total = rows.reduce((sum,item)=>sum+item.price*item.quantity,0);
        cart.innerHTML = rows.length ? rows.map(item => `<div class="d-flex align-items-center gap-2"><div class="flex-grow-1"><div class="fw-semibold small">${escapeHtml(item.name)}</div><div class="small" style="color:var(--brand)">$${item.price.toFixed(2)}</div></div><div class="input-group input-group-sm" style="width:95px"><button class="btn btn-outline-secondary pos-quantity" data-id="${item.id}" data-change="-1">−</button><span class="input-group-text bg-white">${item.quantity}</span><button class="btn btn-outline-secondary pos-quantity" data-id="${item.id}" data-change="1">+</button></div></div>`).join('') : '<div class="text-center py-5 text-muted"><i class="bi bi-basket fs-2 d-block mb-2"></i>Cart is empty</div>';
        document.querySelector('#pos-subtotal')?.replaceChildren(`$${total.toFixed(2)}`); document.querySelector('#pos-total')?.replaceChildren(`$${total.toFixed(2)}`);
    };
    document.querySelector('#pos-products')?.addEventListener('click', (event) => { const button=event.target.closest('.pos-add'); if(!button || button.disabled)return; const item=posCart.get(button.dataset.id)||{id:button.dataset.id,name:button.dataset.name,price:parseFloat(button.dataset.price),quantity:0,available:parseFloat(button.dataset.available)}; if(item.quantity >= item.available) { window.alert(`Only ${item.available} item(s) are available in this warehouse.`); return; } item.quantity++; posCart.set(item.id,item); renderPosCart(); });
    document.querySelector('#pos-cart')?.addEventListener('click',(event)=>{const button=event.target.closest('.pos-quantity');if(!button)return;const item=posCart.get(button.dataset.id);const change=parseInt(button.dataset.change);if(change > 0 && item.quantity >= item.available){window.alert(`Only ${item.available} item(s) are available in this warehouse.`);return;}item.quantity+=change;if(item.quantity<1)posCart.delete(item.id);renderPosCart();});
    document.querySelector('#pos-clear')?.addEventListener('click',()=>{posCart.clear();renderPosCart();});
    const applyPosFilters = () => { const search=document.querySelector('#pos-search')?.value.toLowerCase() || ''; const brand=document.querySelector('#pos-brand')?.value || ''; document.querySelectorAll('.pos-product').forEach(card=>card.classList.toggle('d-none',!card.dataset.name.includes(search) || (brand && card.dataset.brand !== brand))); };
    document.querySelector('#pos-search')?.addEventListener('input',applyPosFilters);
    document.querySelector('#pos-brand')?.addEventListener('change',applyPosFilters);
    document.querySelector('#pos-warehouse')?.addEventListener('change',(event)=>{ const warehouseId=event.target.value; if(posCart.size){posCart.clear();renderPosCart();} document.querySelector('#pos-warehouse-notice')?.classList.toggle('d-none',Boolean(warehouseId)); document.querySelectorAll('.pos-product').forEach(card=>{const stocks=JSON.parse(card.dataset.stocks || '{}');const available=Number(stocks[warehouseId] || 0);const button=card.querySelector('.pos-add');button.dataset.available=available;button.disabled=!warehouseId || available<=0;const label=card.querySelector('.pos-stock');label.textContent=warehouseId ? `${available} in stock` : 'Select warehouse';label.classList.toggle('text-danger',Boolean(warehouseId) && available<=0);label.classList.toggle('text-success',available>0);}); });
    const savedDraft = document.querySelector('#pos-draft-data');
    if (savedDraft) {
        const warehouse = document.querySelector('#pos-warehouse');
        warehouse?.dispatchEvent(new Event('change'));
        const payment = document.querySelector('#pos-payment-method');
        if (payment && savedDraft.dataset.payment) payment.value = savedDraft.dataset.payment;
        JSON.parse(savedDraft.dataset.items || '[]').forEach((savedItem) => {
            const productButton = document.querySelector(`.pos-add[data-id="${savedItem.product_id}"]`);
            const available = Number(productButton?.dataset.available || 0);
            posCart.set(String(savedItem.product_id), { id: String(savedItem.product_id), name: savedItem.product_name, price: Number(savedItem.unit_price), quantity: Number(savedItem.quantity), available });
        });
        renderPosCart();
    }
    const submitPosOrder = (status) => {
        if (!document.querySelector('#pos-warehouse')?.value) { window.alert('Select the warehouse for this sale first.'); return; }
        if (!posCart.size) { window.alert('Add at least one product to the cart.'); return; }
        const form = document.querySelector('#pos-order-form'); if (!form) return;
        form.querySelectorAll('input[data-pos-order-field]').forEach((field) => field.remove());
        const add = (name, value) => { const input = document.createElement('input'); input.type = 'hidden'; input.name = name; input.value = value; input.dataset.posOrderField = 'true'; form.append(input); };
        add('warehouse_id', document.querySelector('#pos-warehouse')?.value || '');
        add('customer_name', document.querySelector('#pos-customer-name')?.value || '');
        add('customer_phone', document.querySelector('#pos-customer-phone')?.value || '');
        add('payment_method', document.querySelector('#pos-payment-method')?.value || 'Cash');
        add('status', status);
        [...posCart.values()].forEach((item, index) => { add(`items[${index}][product_id]`, item.id); add(`items[${index}][product_name]`, item.name); add(`items[${index}][quantity]`, item.quantity); add(`items[${index}][unit_price]`, item.price); });
        form.submit();
    };
    document.querySelector('#pos-checkout')?.addEventListener('click', () => submitPosOrder('completed'));
    document.querySelector('#pos-save-draft')?.addEventListener('click', () => submitPosOrder('draft'));
});
