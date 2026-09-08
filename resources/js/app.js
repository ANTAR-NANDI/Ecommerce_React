import 'bootstrap';
import '../css/app.css';

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
});
