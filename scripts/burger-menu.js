document.addEventListener('DOMContentLoaded', function() 
{
    const burgerMenu = document.getElementById('burger-btn');
    const navigation = document.getElementById('navigation');

    const mainContent = document.querySelector('.main-content');


    function toggleMenu()
    {
        navigation.classList.toggle('active');
        burgerMenu.classList.toggle('active');
        console.info('Menu toggled');
        
        if (navigation.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
            mainContent.style.display = 'none';
        } else {
            document.body.style.overflow = '';
            mainContent.style.display = '';
        }
    }

    burgerMenu.addEventListener('click', toggleMenu);

    const navLinks = navigation.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768 && navigation.classList.contains('active')) {
                toggleMenu();
                burgerMenu.classList.remove('active');
            }
    });  
    });
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && navigation.classList.contains('active')) {
            burgerMenu.classList.remove('active');
            navigation.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});