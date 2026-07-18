import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const openButton = document.getElementById('sidebar-open');
    const closeButton = document.getElementById('sidebar-close');
    const desktopToggle = document.getElementById('desktop-sidebar-toggle');

    const open = () => {
        sidebar?.classList.remove('-translate-x-full');
        backdrop?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };
    const close = () => {
        sidebar?.classList.add('-translate-x-full');
        backdrop?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    openButton?.addEventListener('click', open);
    closeButton?.addEventListener('click', close);
    backdrop?.addEventListener('click', close);
    desktopToggle?.addEventListener('click', () => {
        const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
        localStorage.setItem('simple-stock-sidebar', isCollapsed ? 'collapsed' : 'expanded');
    });
    sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
        if (window.innerWidth < 1024) close();
    }));
    document.addEventListener('keydown', event => event.key === 'Escape' && close());
});
