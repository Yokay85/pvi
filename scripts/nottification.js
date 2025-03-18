const bell = document.getElementById('bell');
const notificationDot = document.getElementById('notificationDot');
const notificationPopup = document.getElementById('notificationPopup');

function addShakeEffect() {
    if (notificationDot.style.display !== 'none') {
        bell.classList.add('shake');
    }
}

window.addEventListener('DOMContentLoaded', () => {
    addShakeEffect();
});

bell.addEventListener('click', () => {
    window.open('messages.html', '_blank');
    notificationDot.style.display = 'none';
    bell.classList.remove('shake');
});

bell.addEventListener('mouseover', () => {

    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        return;
    }
    notificationPopup.style.display = 'block';
});

bell.addEventListener('mouseout', () => {
    notificationPopup.style.display = 'none';}
);