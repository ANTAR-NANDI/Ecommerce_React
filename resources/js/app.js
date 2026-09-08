import { Modal } from 'bootstrap';
import $ from 'jquery';
import select2 from 'select2';
import '../css/app.css';

select2(window, $);

const root = document.documentElement;
const savedTheme = localStorage.getItem('admin-theme') || 'light';
root.dataset.adminTheme = savedTheme;

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => sidebar?.classList.toggle('show'));

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

    const pickerModal = document.querySelector('#media-picker-modal');
    const pickerContent = document.querySelector('#media-picker-content');
    let activeMediaTarget = null;

    const loadMediaPage = async (url) => {
        pickerContent.innerHTML = '<div class="text-center py-5" style="color:var(--muted)"><div class="spinner-border spinner-border-sm me-2"></div>Loading media...</div>';
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        pickerContent.innerHTML = await response.text();
    };

    document.querySelectorAll('[data-media-picker-target]').forEach((button) => {
        button.addEventListener('click', async () => {
            activeMediaTarget = button.dataset.mediaPickerTarget;
            await loadMediaPage('/admin/media/picker');
            Modal.getOrCreateInstance(pickerModal).show();
        });
    });

    pickerContent?.addEventListener('click', async (event) => {
        const page = event.target.closest('[data-media-page]');
        if (page) { event.preventDefault(); await loadMediaPage(page.href); return; }
        const choice = event.target.closest('[data-select-media]');
        if (!choice || !activeMediaTarget) return;
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
    const renderPosCart = () => {
        const cart = document.querySelector('#pos-cart'); if (!cart) return;
        const rows = [...posCart.values()]; const total = rows.reduce((sum,item)=>sum+item.price*item.quantity,0);
        cart.innerHTML = rows.length ? rows.map(item => `<div class="d-flex align-items-center gap-2"><div class="flex-grow-1"><div class="fw-semibold small">${item.name}</div><div class="small" style="color:var(--brand)">$${item.price.toFixed(2)}</div></div><div class="input-group input-group-sm" style="width:95px"><button class="btn btn-outline-secondary pos-quantity" data-id="${item.id}" data-change="-1">−</button><span class="input-group-text bg-white">${item.quantity}</span><button class="btn btn-outline-secondary pos-quantity" data-id="${item.id}" data-change="1">+</button></div></div>`).join('') : '<div class="text-center py-5 text-muted"><i class="bi bi-basket fs-2 d-block mb-2"></i>Cart is empty</div>';
        document.querySelector('#pos-subtotal')?.replaceChildren(`$${total.toFixed(2)}`); document.querySelector('#pos-total')?.replaceChildren(`$${total.toFixed(2)}`);
    };
    document.querySelector('#pos-products')?.addEventListener('click', (event) => { const button=event.target.closest('.pos-add'); if(!button)return; const item=posCart.get(button.dataset.id)||{id:button.dataset.id,name:button.dataset.name,price:parseFloat(button.dataset.price),quantity:0}; item.quantity++; posCart.set(item.id,item); renderPosCart(); });
    document.querySelector('#pos-cart')?.addEventListener('click',(event)=>{const button=event.target.closest('.pos-quantity');if(!button)return;const item=posCart.get(button.dataset.id);item.quantity+=parseInt(button.dataset.change);if(item.quantity<1)posCart.delete(item.id);renderPosCart();});
    document.querySelector('#pos-clear')?.addEventListener('click',()=>{posCart.clear();renderPosCart();});
    document.querySelector('#pos-search')?.addEventListener('input',(event)=>document.querySelectorAll('.pos-product').forEach(card=>card.classList.toggle('d-none',!card.dataset.name.includes(event.target.value.toLowerCase()))));
});
