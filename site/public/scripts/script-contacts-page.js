document.addEventListener('DOMContentLoaded', function() {
    initBackToTopButton();
    initContactMap();
    initContactForm();
    const contactForm = document.querySelector('.contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            const message = document.getElementById('message');
            const privacy = document.getElementById('privacy');
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            document.querySelectorAll('.form-group.error').forEach(el => el.classList.remove('error'));
            let isValid = true;
            if (name.value.trim() === '') {
                showError(name, 'Пожалуйста, введите ваше имя');
                isValid = false;
            }
            if (!validateEmail(email.value.trim())) {
                showError(email, 'Пожалуйста, введите корректный email');
                isValid = false;
            }
            if (phone.value.trim().length < 16) {
                showError(phone, 'Пожалуйста, введите корректный номер телефона');
                isValid = false;
            }
            if (message.value.trim() === '') {
                showError(message, 'Пожалуйста, введите сообщение');
                isValid = false;
            }
            if (!privacy.checked) {
                showError(privacy, 'Необходимо согласие на обработку данных');
                isValid = false;
            }
            if (isValid) {
                contactForm.innerHTML = '<div class="form-success">' +
                    '<i class="fa-solid fa-circle-check"></i>' +
                    '<h3>Спасибо за ваше сообщение!</h3>' +
                    '<p>Мы получили ваш запрос и свяжемся с вами в ближайшее время.</p>' +
                    '</div>';
                contactForm.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.contact-card, .map-container, .contact-form-container, .social-section');
        
        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 150;
            
            if (elementTop < window.innerHeight - elementVisible) {
                element.classList.add('animate');
            }
        });
    };
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll();
    
    // Helper functions
    function showError(input, message) {
        const formGroup = input.closest('.form-group') || input.parentElement;
        formGroup.classList.add('error');
        
        const errorMessage = document.createElement('div');
        errorMessage.className = 'error-message';
        errorMessage.innerText = message;
        
        formGroup.appendChild(errorMessage);
    }
    
    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }
});

// Функция инициализации Яндекс карты
function initContactMap() {
    const script = document.createElement('script');
    script.src = 'https://api-maps.yandex.ru/2.1/?apikey=ваш-api-ключ&lang=ru_RU';
    script.async = true;
    script.onload = function() {
        ymaps.ready(createContactMap);
    };
    document.head.appendChild(script);
}

// Функция создания карты с расположением клиники
function createContactMap() {
    const clinicCoordinates = [53.9298, 27.651];
    const myMap = new ymaps.Map('contact-map', {
        center: clinicCoordinates,
        zoom: 16,
        controls: ['zoomControl', 'geolocationControl', 'fullscreenControl']
    });
    const myPlacemark = new ymaps.Placemark(clinicCoordinates, {
        hintContent: 'Медцентр Маяк Здоровья',
        balloonContent: '<strong>Медцентр «Маяк Здоровья»</strong><br>' +
                        'г. Минск, ул. К. Туровского, 14<br>' +
                        'Тел.: <a href="tel:7289">7289</a>, <a href="tel:+375172150289">+375 17 215-02-89</a><br>' +
                        '<a href="#contact-form">Записаться на приём</a>'
    }, {
        preset: 'islands#redMedicalIcon', // Используем предопределенную иконку медицинского учреждения
        iconColor: '#4682b4',
        zIndex: 1000
    });
    myMap.geoObjects.add(myPlacemark);
    myMap.setCenter(clinicCoordinates);
    myMap.behaviors.disable('scrollZoom');
    myMap.behaviors.enable('multiTouch');
    const addressControl = new ymaps.control.Button({
        data: {
            content: 'Показать адрес',
            title: 'Нажмите, чтобы увидеть адрес клиники'
        },
        options: {
            selectOnClick: false,
            maxWidth: 150
        }
    });
    addressControl.events.add('click', function() {
        myPlacemark.balloon.open();
    });
    myMap.controls.add(addressControl, {float: 'right', floatIndex: 100});
}

// Функция инициализации формы обратной связи
function initContactForm() {
    const contactForm = document.querySelector('.contact-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                message: document.getElementById('message').value,
                privacy: document.getElementById('privacy').checked
            };
            if (!validateContactForm(formData)) return;

            const submitBtn = contactForm.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || contactForm.querySelector('input[name="_token"]')?.value;

            try {
                const response = await fetch(contactForm.action && contactForm.action !== '#' ? contactForm.action : '/contacts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                    body: JSON.stringify({
                        name: formData.name,
                        email: formData.email,
                        phone: formData.phone,
                        message: formData.message,
                    }),
                });

                if (response.ok) {
                    showContactFormSuccess();
                    contactForm.reset();
                } else {
                    const data = await response.json().catch(() => ({}));
                    console.error('Ошибка отправки:', data);
                    alert('Не удалось отправить сообщение. Попробуйте позже.');
                }
            } catch (err) {
                console.error('Ошибка сети:', err);
                alert('Не удалось отправить сообщение. Проверьте соединение.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            initPhoneMask(phoneInput, { countryCode: '375' });
        }
    }
}

// Функция валидации формы
function validateContactForm(formData) {
    let isValid = true;
    document.querySelectorAll('.form-group.error').forEach(group => {
        group.classList.remove('error');
    });
    if (!formData.name.trim()) {
        markFieldAsInvalid('name');
        isValid = false;
    }
    
    // Проверка email
    if (!validateEmail(formData.email)) {
        markFieldAsInvalid('email');
        isValid = false;
    }
    if (!validatePhone(formData.phone)) {
        markFieldAsInvalid('phone');
        isValid = false;
    }
    
    // Проверка сообщения
    if (!formData.message.trim()) {
        markFieldAsInvalid('message');
        isValid = false;
    }
    if (!formData.privacy) {
        document.querySelector('.form-privacy').classList.add('error');
        isValid = false;
    }
    
    return isValid;
}

// Функция для отметки поля как невалидного
function markFieldAsInvalid(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        const formGroup = field.closest('.form-group');
        if (formGroup) {
            formGroup.classList.add('error');
        }
    }
}

// Функция проверки email
function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

// Функция проверки телефона
function validatePhone(phone) {
    // Удаляем все нецифровые символы
    const digits = phone.replace(/\D/g, '');
    // Проверяем, что осталось достаточно цифр
    return digits.length >= 10;
}

// Функция отображения сообщения об успешной отправке формы
function showContactFormSuccess() {
    let successMessage = document.querySelector('.contact-success-message');
    
    if (!successMessage) {
        successMessage = document.createElement('div');
        successMessage.className = 'contact-success-message';
        successMessage.innerHTML = `
            <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
            <h3>Сообщение отправлено!</h3>
            <p>Благодарим за обращение. Мы свяжемся с вами в ближайшее время.</p>
            <button class="close-message"><i class="fa-solid fa-xmark"></i></button>
        `;
        const formContainer = document.querySelector('.contact-form-container');
        formContainer.appendChild(successMessage);
        const closeButton = successMessage.querySelector('.close-message');
        closeButton.addEventListener('click', function() {
            successMessage.remove();
        });
        setTimeout(() => {
            if (successMessage && successMessage.parentNode) {
                successMessage.remove();
            }
        }, 5000);
    }
}
