document.addEventListener('DOMContentLoaded', () => {
    const loginModal = document.getElementById('loginModal');
    const closeLoginBtn = document.getElementById('close-login-btn');
    const loginForm = document.getElementById('login-form');
    const loginErrorMessage = document.getElementById('login-error-message');
    const modalOverlay = document.getElementById('modal-overlay'); 
    const loginButton = document.querySelector('.login-btn');

    // Функція для відкриття модального вікна логіну
    window.openLoginModal = function() {
        // Перевіряємо наявність елементів перед використанням
        if (loginModal && modalOverlay) {
            if (loginErrorMessage) {
                loginErrorMessage.style.display = 'none'; // Приховуємо помилки при відкритті
                loginErrorMessage.textContent = '';
            }
            loginModal.style.display = 'block';
            modalOverlay.style.display = 'block';
            // Додаємо класи для анімації появи
            setTimeout(() => {
                loginModal.classList.add('active');
                modalOverlay.classList.add('active');
            }, 10);
            document.body.style.overflow = 'hidden';
        } else {
            console.error("Модальне вікно логіну або оверлей не знайдено");
        }
    }

    // Функція для закриття модального вікна логіну
    window.closeLoginModal = function() {
        if (loginModal && modalOverlay) {
            loginModal.classList.remove('active');
            modalOverlay.classList.remove('active');
            // Чекаємо завершення анімації
            setTimeout(() => {
                loginModal.style.display = 'none';
                // Приховуємо оверлей тільки якщо немає інших активних модальних вікон
                if (!document.querySelector('.modal-content.active')) {
                    modalOverlay.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
                if (loginForm) loginForm.reset(); // Скидаємо поля форми
                if (loginErrorMessage) loginErrorMessage.style.display = 'none'; // Приховуємо повідомлення про помилку
            }, 300);
        }
    }

    // Додаємо обробник події для кнопки логіну
    if (loginButton) {
        loginButton.addEventListener('click', (e) => {
            e.preventDefault(); // Запобігаємо дефолтній дії
            openLoginModal();
        });
    }

    // Додаємо обробник для кнопки закриття
    if (closeLoginBtn) {
        closeLoginBtn.addEventListener('click', closeLoginModal);
    }

    // Обробник для кліку по оверлею
    if (modalOverlay) {
        modalOverlay.addEventListener('click', () => {
            // Закриваємо логін тільки якщо він активний
            if (loginModal && loginModal.classList.contains('active')) {
                closeLoginModal();
            }
        });
    }

    // Обробник для подання форми логіну
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault(); // Запобігаємо традиційному відправленню форми
            
            if (loginErrorMessage) loginErrorMessage.style.display = 'none';

            const formData = new FormData(loginForm);

            // Базова клієнтська перевірка
            if (!formData.get('identifier') || !formData.get('password')) {
                if (loginErrorMessage) {
                    loginErrorMessage.textContent = 'Логін/Email та пароль обов\'язкові.';
                    loginErrorMessage.style.display = 'block';
                }
                return;
            }

            fetch(`${URL_ROOT}/public/index.php?action=login`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => {
                        throw new Error(errData.message || `Помилка HTTP! статус: ${response.status}`);
                    }).catch(() => {
                        throw new Error(`Помилка HTTP! статус: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Логін успішний, перезавантажуємо сторінку
                    window.location.reload();
                } else {
                    // Логін не успішний, показуємо повідомлення про помилку
                    if (loginErrorMessage) {
                        loginErrorMessage.textContent = data.message || 'Невідома помилка.';
                        loginErrorMessage.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                console.error('Помилка при логіні:', error);
                if (loginErrorMessage) {
                    loginErrorMessage.textContent = error.message || 'Виникла помилка під час логіну. Спробуйте ще раз.';
                    loginErrorMessage.style.display = 'block';
                }
            });
        });
    }
});
