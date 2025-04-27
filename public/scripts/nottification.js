document.addEventListener('DOMContentLoaded', () => {
    const bell = document.getElementById('bell');
    const notificationDot = document.getElementById('notificationDot');
    const notificationPopup = document.getElementById('notificationPopup');

    function addShakeEffect() {
        // Перевіряємо наявність елементів
        if (notificationDot && bell && notificationDot.style.display !== 'none') {
            bell.classList.add('shake');
        }
    }

    // Викликаємо функцію після завантаження DOM
    addShakeEffect();

    // Перевіряємо наявність bell перед додаванням слухачів
    if (bell) {
        bell.addEventListener('click', () => {
            window.open('messages.html', '_blank');
            if (notificationDot) {
                notificationDot.style.display = 'none';
            }
            bell.classList.remove('shake');
        });

        bell.addEventListener('mouseover', () => {
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                return;
            }
            if (notificationPopup) {
                notificationPopup.style.display = 'block';
            }
        });

        bell.addEventListener('mouseout', () => {
            if (notificationPopup) {
                notificationPopup.style.display = 'none';
            }
        });
    }
});