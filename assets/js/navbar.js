document.addEventListener("DOMContentLoaded", () => {
    const userMenu = document.getElementById('user-menu');
    const userMenuButton = document.getElementById('user-menu-button');

    userMenuButton.addEventListener('click', () => {
        userMenu.classList.toggle('hidden');
    });
})