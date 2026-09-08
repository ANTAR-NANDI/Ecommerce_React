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
});
