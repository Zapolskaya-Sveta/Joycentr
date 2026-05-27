// joy.js

// --- ГЛОБАЛЬНЫЕ ФУНКЦИИ (Уведомления и Confirm) ---
// 1. Отключаем автоматическое восстановление прокрутки браузером
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

// 2. Принудительно скроллим вверх при каждой загрузке страницы
window.scrollTo(0, 0);

// 3. Дополнительная страховка для переходов "Назад/Вперед"
window.addEventListener('pageshow', (event) => {
    window.scrollTo(0, 0);
});
window.showToast = function(message, isError = false) {
    let t = document.getElementById('toast');
    if (!t) { t = document.createElement('div'); t.id = 'toast'; document.body.appendChild(t); }
    const icon = isError ? '<i class="fas fa-exclamation-circle text-danger toast-icon" style="font-size:1.5rem; margin-right:10px;"></i>' : '<i class="fas fa-check-circle toast-icon" style="color: #3D3935; font-size:1.5rem; margin-right:10px;"></i>';
    t.className = `toast-notification ${isError ? 'error' : ''}`;
    t.innerHTML = `${icon} <span style="font-family: 'Lato', sans-serif;">${message}</span>`;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 4000);
}

let confirmCallback = null;
window.joyConfirm = function(message, callback) { 
    document.getElementById('joyConfirmText').innerText = message; 
    document.getElementById('joyConfirmModal').style.display = 'flex'; 
    document.body.style.overflow = 'hidden'; 
    confirmCallback = callback; 
}
window.closeJoyConfirm = function() { 
    document.getElementById('joyConfirmModal').style.display = 'none'; 
    document.body.style.overflow = ''; 
    confirmCallback = null; 
}

// --- ИНИЦИАЛИЗАЦИЯ ПРИ ЗАГРУЗКЕ ---
document.addEventListener('DOMContentLoaded', function() {
    
    // Кнопка подтверждения
    const confBtn = document.getElementById('joyConfirmBtn');
    if(confBtn) confBtn.addEventListener('click', function() { if (confirmCallback) confirmCallback(); closeJoyConfirm(); });

    // Тосты после перезагрузки
    if (localStorage.getItem('flashToast')) {
        const flash = JSON.parse(localStorage.getItem('flashToast'));
        showToast(flash.msg, flash.isError);
        localStorage.removeItem('flashToast');
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('order_success')) { 
        localStorage.removeItem('joyCart'); 
        localStorage.setItem('activeCabinetTab', 'my-orders'); 
        const modal = document.getElementById('orderSuccessModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; 
        }
        window.history.replaceState({}, document.title, window.location.pathname); 
    }
    if (urlParams.has('appoint_success')) { showToast('Заявка отправлена! Ожидайте подтверждения.', false); window.history.replaceState({}, document.title, window.location.pathname); }

    // Гамбургер меню (Внешний сайт)
    const hamburger = document.getElementById('hamburger'); const mobileNav = document.getElementById('mobileNav');
    if (hamburger && mobileNav) {
        hamburger.addEventListener('click', function() { this.classList.toggle('active'); mobileNav.classList.toggle('active'); document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : ''; });
        mobileNav.querySelectorAll('a').forEach(link => { link.addEventListener('click', () => { hamburger.classList.remove('active'); mobileNav.classList.remove('active'); document.body.style.overflow = ''; }); });
    }

    // Плавный скролл
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) { const sectionId = this.getAttribute('href'); if (sectionId && sectionId.includes('#')) { const id = sectionId.split('#')[1]; const section = document.getElementById(id); if (section) { e.preventDefault(); section.scrollIntoView({ behavior: 'smooth' }); } } });
    });

    // Маска телефона
    const phoneInput = document.getElementById('phoneInput');
    if (phoneInput && typeof IMask !== 'undefined') {
        const countrySelector = document.getElementById('countrySelector');
        let phoneMask = IMask(phoneInput, { mask: '+{375} (00) 000-00-00' });
        if (countrySelector) {
            countrySelector.value = 'by'; phoneInput.placeholder = "+375 (29) 123-45-67";
            countrySelector.addEventListener('change', function() { phoneMask.value = ''; if (this.value === 'ru') { phoneMask.updateOptions({ mask: '+{7} (000) 000-00-00' }); phoneInput.placeholder = "+7 (985) 640-87-70"; } else { phoneMask.updateOptions({ mask: '+{375} (00) 000-00-00' }); phoneInput.placeholder = "+375 (29) 123-45-67"; } });
        }
    }

    // ЗАКРЫТИЕ МОДАЛОК КЛИКОМ ПО ТЕМНОМУ ФОНУ
    window.addEventListener('click', (e) => { 
        if (e.target.classList.contains('modal-form') || e.target.classList.contains('registration-form') || e.target.classList.contains('clients-modal-overlay') || e.target.classList.contains('custom-confirm-overlay')) { 
            e.target.style.display = 'none'; 
            document.body.style.overflow = ''; 
        } 
    });

    // Загрузка корзины
    updateCartCount();
    if (document.getElementById('cartItemsContainer')) renderCart();

    // Summernote
    if (typeof $ !== 'undefined' && $('#postContent').length) {
        $('#postContent').summernote({ lang: 'ru-RU', height: 300, placeholder: 'Текст статьи...', toolbar: [['style', ['style']], ['font', ['bold', 'italic', 'underline', 'clear']], ['color', ['color']], ['para', ['ul', 'ol', 'paragraph']], ['insert', ['link', 'picture', 'video']], ['view', ['fullscreen', 'codeview', 'help']]] });
    }
    
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) { var fileName = document.getElementById(this.id).files[0].name; var nextSibling = e.target.nextElementSibling; nextSibling.innerText = fileName; });
    });

    // Динамические слоты в форме записи
    const specSelect = document.getElementById('appointSpecialistId');
    const slotContainer = document.getElementById('slotDropdownContainer');
    const slotSelect = document.getElementById('appointSlotDropdown');
    const sTypeSelect = document.getElementById('appointServiceType');

    if (specSelect) {
        specSelect.addEventListener('change', function() {
            if (typeof availableGlobalSlots !== 'undefined') {
                const specId = parseInt(this.value);
                slotSelect.innerHTML = '<option value="" disabled selected>Выберите свободное время</option>';
                if (specId > 0) {
                    const specSlots = availableGlobalSlots.filter(s => s.specialist_id == specId);
                    if (specSlots.length > 0) {
                        slotContainer.style.display = 'block'; slotSelect.required = true;
                        specSlots.forEach(slot => {
                            const dateObj = new Date(slot.slot_datetime);
                            const niceStr = dateObj.toLocaleDateString('ru-RU') + ' в ' + dateObj.toLocaleTimeString('ru-RU', {hour: '2-digit', minute:'2-digit'});
                            slotSelect.innerHTML += `<option value="${slot.id}">${niceStr}</option>`;
                        });
                    } else { slotContainer.style.display = 'block'; slotSelect.innerHTML = '<option value="" disabled selected>У данного специалиста нет свободных окон</option>'; slotSelect.required = true; }
                } else { slotContainer.style.display = 'none'; slotSelect.required = false; }
            }

            if (sTypeSelect) {
                const selectedOpt = this.options[this.selectedIndex];
                if (!selectedOpt || selectedOpt.disabled) return;
                const searchStr = selectedOpt.getAttribute('data-search') || '';
                const supportsCouples = searchStr.includes('семейн') || searchStr.includes('парн') || searchStr.includes('отношен');
                const couplesOpt = Array.from(sTypeSelect.options).find(o => o.value === 'Парная терапия');
                if (couplesOpt) {
                    if (!supportsCouples) {
                        couplesOpt.style.display = 'none';
                        couplesOpt.disabled = true;
                        if (sTypeSelect.value === 'Парная терапия') sTypeSelect.value = 'Очная индивидуальная сессия';
                    } else {
                        couplesOpt.style.display = 'block';
                        couplesOpt.disabled = false;
                    }
                }
            }
        });
    }

    const savedTab = localStorage.getItem('activeCabinetTab');
    if (savedTab && document.getElementById('tab-' + savedTab)) {
        showTab(savedTab, document.querySelector(`.joy-nav-item[onclick*="showTab('${savedTab}'"]`));
    }
    
    window.addEventListener('beforeunload', function() { localStorage.setItem('cabinetScrollY', window.scrollY); });
    const savedScroll = localStorage.getItem('cabinetScrollY');
    if (savedScroll) { setTimeout(() => window.scrollTo(0, parseInt(savedScroll)), 10); localStorage.removeItem('cabinetScrollY'); }

    // === Запуск анимации текста и карусели слайдера ===
    initTypewriter();
    initHomeCarousel();
});

// --- ОТКРЫТИЕ ФОРМ (БЛОКИРУЕМ СКРОЛЛ) ---
window.openAppointmentForSpec = function(specId, specName, slotId = null, slotTimeStr = null, e) {
    if(e) e.preventDefault();
    if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) { showToast("Для записи к специалисту необходимо авторизоваться.", true); openAuthModal(); return; } 
    let specSelect = document.getElementById('appointSpecialistId');
    if(specSelect) { specSelect.value = specId; specSelect.dispatchEvent(new Event('change')); }
    setTimeout(() => { let slotSelect = document.getElementById('appointSlotDropdown'); if(slotSelect && slotId) { slotSelect.value = slotId; } }, 150);
    let titleEl = document.querySelector('#appointmentModal .form-title'); if(titleEl) titleEl.innerHTML = `ЗАПИСЬ К ПСИХОЛОГУ`;
    document.getElementById('appointmentModal').style.display = 'flex';
    document.body.style.overflow = 'hidden'; 
}

window.openAppointment = function(e) { 
    if(e) e.preventDefault(); 
    if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) { showToast("Для записи на сессию необходимо войти в аккаунт.", true); openAuthModal(); return; }
    document.getElementById('appointmentModal').style.display = 'flex'; 
    document.body.style.overflow = 'hidden'; 
    if(typeof filterSpecialistsByService === 'function') filterSpecialistsByService();
}

window.openAuthModal = function(e) { 
    if(e) e.preventDefault(); 
    const m = document.getElementById('registrationForm'); 
    if(m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; } 
}

window.closeAuthModal = function() { 
    const m = document.getElementById('registrationForm'); 
    if(m) { m.style.display = 'none'; document.body.style.overflow = ''; } 
}

window.closeMeditationModal = function() { const m = document.getElementById('meditationModal'); if(m) { m.style.display = 'none'; document.body.style.overflow = ''; } }

// --- ЗАКРЫТИЕ ФОРМЫ ---
window.closeAnyModal = function(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = '';
}

window.toggleAuth = function(type) {
    document.getElementById('loginForm').style.display = type === 'login' ? 'block' : 'none';
    document.getElementById('registerForm').style.display = type === 'register' ? 'block' : 'none';
    document.getElementById('tabLogin').style.borderBottom = type === 'login' ? '2px solid #E0C6AD' : 'none';
    document.getElementById('tabRegister').style.borderBottom = type === 'register' ? '2px solid #E0C6AD' : 'none';
}

window.flipResult = function(circle) { if(circle) circle.classList.toggle('flipped'); }
window.toggleHiddenText = function() { const t = document.getElementById('hiddenText'); t.style.display = t.style.display === 'block' ? 'none' : 'block'; }

// --- ВЫБОР МЕДИТАЦИЙ В МОДАЛКЕ ---
let selectedMeditation = null;
window.selectOption = function(el) {
    document.querySelectorAll('.meditation-section .option').forEach(o => o.style.border = 'none');
    el.style.border = '3px solid #E0C6AD';
    el.style.borderRadius = '15px';
    selectedMeditation = { id: el.getAttribute('data-id'), price: el.getAttribute('data-price'), title: el.nextElementSibling.innerText.replace('\n', ' '), image: el.querySelector('img').src };
}
window.addSelectedMeditationToCart = function() {
    if(!selectedMeditation) { showToast('Пожалуйста, выберите медитацию (нажмите на картинку)', true); return; }
    addToCart(selectedMeditation.id, selectedMeditation.title, selectedMeditation.price, selectedMeditation.image);
    closeMeditationModal();
}

// --- КОРЗИНА ---
window.addToCart = function(id, title, price, image) {
    if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) { showToast("Пожалуйста, авторизуйтесь.", true); openAuthModal(); return; }
    let cart = JSON.parse(localStorage.getItem('joyCart')) || [];
    if (cart.find(item => item.id === id)) { showToast('Уже в корзине!', true); return; }
    cart.push({ id, title, price, image }); localStorage.setItem('joyCart', JSON.stringify(cart));
    updateCartCount(); showToast('Добавлено в корзину!', false);
}
window.updateCartCount = function() { const cart = JSON.parse(localStorage.getItem('joyCart')) || []; const el = document.getElementById('cartCount'); if(el) { el.innerText = cart.length; el.style.display = cart.length > 0 ? 'inline-block' : 'none'; } }
window.renderCart = function() {
    const c = document.getElementById('cartItemsContainer'); const t = document.getElementById('cartTotal'); const f = document.getElementById('cartFooter');
    if (!c) return;
    const cart = JSON.parse(localStorage.getItem('joyCart')) || []; c.innerHTML = ''; let total = 0;
    if (cart.length === 0) {
        c.innerHTML = `<div class="empty-state" style="border: none;"><i class="fas fa-shopping-basket empty-icon"></i><h4 class="empty-text">Ваша корзина пуста</h4><a href="catalog.php" class="main-button small-btn mt-3" style="display: inline-block; text-decoration: none;">Перейти в каталог</a></div>`;
        if (f) f.style.display = 'none';
    } else {
        if (f) f.style.display = 'block';
        cart.forEach((item, index) => { let p = parseInt(item.price); if(isNaN(p)) p = 0; total += p;
            c.innerHTML += `<div class="cart-item-row d-flex align-items-center border-bottom py-3"><img src="${item.image}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px; margin-right: 15px;"><div class="flex-grow-1"><h6 class="mb-0 font-weight-bold">${item.title}</h6><span class="text-muted">${p} BYN</span></div><button class="action-btn btn-delete" onclick="removeFromCart(${index})"><i class="fas fa-trash"></i></button></div>`;
        });
    }
    if(t) t.innerText = total;
}
window.removeFromCart = function(index) { let cart = JSON.parse(localStorage.getItem('joyCart')) || []; cart.splice(index, 1); localStorage.setItem('joyCart', JSON.stringify(cart)); renderCart(); updateCartCount(); }
window.submitOrder = function() {
    const cart = localStorage.getItem('joyCart');
    if (!cart || JSON.parse(cart).length === 0) { showToast('Ваша корзина пуста.', true); return; }
    document.getElementById('hiddenCartInput').value = cart; document.getElementById('orderForm').submit();
}

// --- ФУНКЦИИ КАБИНЕТА (ВКЛАДКИ) ---
window.showTab = function(id, el) {
    document.querySelectorAll('.content-tab').forEach(d => d.style.display = 'none');
    document.getElementById('tab-' + id).style.display = 'block';
    document.querySelectorAll('.joy-nav-item').forEach(n => n.classList.remove('active'));
    
    if(el) { el.classList.add('active'); } 
    else { const navLink = document.querySelector(`.joy-nav-item[onclick*="showTab('${id}'"]`); if (navLink) navLink.classList.add('active'); }
    
    localStorage.setItem('activeCabinetTab', id);
    
    const innerContainers = document.getElementById('tab-' + id).querySelectorAll('.inner-tabs-container');
    innerContainers.forEach(container => { let savedInner = localStorage.getItem('innerTab_' + container.id) || 0; switchInnerTab(container.id, parseInt(savedInner)); });
    if(id === 'cart') renderCart();
}

window.switchInnerTab = function(containerId, index) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const buttons = container.querySelectorAll('.inner-tab-btn');
    const slider = container.querySelector('.inner-tab-slider');
    const targetPrefix = container.getAttribute('data-target-prefix');
    
    buttons.forEach((btn, i) => {
        const contentBlock = document.getElementById(`${targetPrefix}-${i}`);
        if (i === index) {
            btn.classList.add('active');
            if(slider) { slider.style.width = `${btn.offsetWidth}px`; slider.style.left = `${btn.offsetLeft}px`; }
            if (contentBlock) contentBlock.classList.add('active');
        } else {
            btn.classList.remove('active');
            if (contentBlock) contentBlock.classList.remove('active');
        }
    });
    localStorage.setItem('innerTab_' + containerId, index);
    if (containerId === 'psychAppTabs' && index === 1) { setTimeout(() => { renderPsychCalendar(); if(selectedCalDate) selectCalDay(selectedCalDate); }, 50); }
}

// --- ФИЛЬТРЫ В КАБИНЕТЕ ---
window.applyAdminAppFilters = function() {
    const status = document.getElementById('filterStatus').value; const specId = document.getElementById('filterSpec').value; const serviceType = document.getElementById('filterAdminService') ? document.getElementById('filterAdminService').value : 'all'; const clientText = document.getElementById('filterClient').value.toLowerCase(); const dateVal = document.getElementById('filterDate').value; const container = document.getElementById('adminAppsContainer');
    if(!container) return;
    Array.from(container.querySelectorAll('.admin-app-item')).forEach(item => {
        let show = true;
        if (status !== 'all' && item.getAttribute('data-status') !== status) show = false;
        if (specId !== 'all' && item.getAttribute('data-spec') !== specId) show = false;
        if (serviceType !== 'all' && item.getAttribute('data-service') !== serviceType) show = false;
        if (clientText && !item.getAttribute('data-client').includes(clientText)) show = false;
        if (dateVal && item.getAttribute('data-appdate') !== dateVal) show = false;
        item.style.display = show ? 'block' : 'none';
    });
}
window.applyAdminSchedFilters = function() {
    const specId = document.getElementById('filterAdminSchedSpec').value; const dateVal = document.getElementById('filterAdminSchedDate').value; const container = document.getElementById('adminSchedContainer');
    if(!container) return;
    Array.from(container.querySelectorAll('.admin-sched-item')).forEach(item => { let show = true; if (specId !== 'all' && item.getAttribute('data-spec') !== specId) show = false; if (dateVal && item.getAttribute('data-date') !== dateVal) show = false; item.style.display = show ? 'block' : 'none'; });
}
window.applyPsychAppFilters = function() {
    const status = document.getElementById('filterPsychStatus').value; const serviceType = document.getElementById('filterPsychService') ? document.getElementById('filterPsychService').value : 'all'; const clientText = document.getElementById('filterPsychClient').value.toLowerCase(); const dateVal = document.getElementById('filterPsychDate').value; const container = document.getElementById('psychAppsContainer');
    if(!container) return;
    Array.from(container.querySelectorAll('.psych-app-item')).forEach(item => {
        let show = true;
        if (status !== 'all' && item.getAttribute('data-status') !== status) show = false;
        if (serviceType !== 'all' && item.getAttribute('data-service') !== serviceType) show = false;
        if (clientText && !item.getAttribute('data-client').includes(clientText)) show = false;
        if (dateVal && item.getAttribute('data-appdate') !== dateVal) show = false;
        item.style.display = show ? 'block' : 'none';
    });
}
window.sortAdminOrders = function() {
    const order = document.getElementById('sortOrderDate').value; const container = document.getElementById('adminOrdersContainer'); 
    if(!container) return; let items = Array.from(container.querySelectorAll('.admin-order-item'));
    items.sort((a, b) => { return order === 'desc' ? b.getAttribute('data-time') - a.getAttribute('data-time') : a.getAttribute('data-time') - b.getAttribute('data-time'); });
    items.forEach(item => container.appendChild(item));
}
window.applyUserFilters = function() {
    const role = document.getElementById('filterUserRole').value; const text = document.getElementById('filterUserText').value.toLowerCase(); const container = document.getElementById('crmUsersContainer');
    if(!container) return;
    Array.from(container.querySelectorAll('.crm-user-item')).forEach(item => { let show = true; if (role !== 'all' && item.getAttribute('data-role') !== role) show = false; if (text && !item.getAttribute('data-search').includes(text)) show = false; item.style.display = show ? 'table-row' : 'none'; });
}

// --- ФОРМЫ РЕДАКТИРОВАНИЯ ---
window.editProduct = function(data) {
    document.getElementById('productFormBlock').style.display = 'block';
    if(data) {
        document.getElementById('formTitle').innerText = 'Редактирование продукта';
        document.getElementById('prodId').value = data.id; document.getElementById('prodTitle').value = data.title;
        document.getElementById('prodPrice').value = data.price; document.getElementById('prodDesc').value = data.description;
        document.getElementById('prodImgOld').value = data.image; document.getElementById('prodCat').value = data.category || 'general';
        if(document.getElementById('prodAccessLink')) document.getElementById('prodAccessLink').value = data.access_link || '';
    } else {
        document.getElementById('formTitle').innerText = 'Добавление продукта';
        document.getElementById('productFormBlock').querySelector('form').reset(); document.getElementById('prodId').value = ''; document.getElementById('customFileProd').nextElementSibling.innerText = 'Выберите файл'; document.getElementById('prodImgOld').value = '';
    }
    document.getElementById('productFormBlock').scrollIntoView({behavior: 'smooth'});
}
window.editPost = function(data) {
    document.getElementById('postFormBlock').style.display = 'block';
    if(data) {
        document.getElementById('postFormTitle').innerText = 'Редактирование статьи';
        document.getElementById('postId').value = data.id; document.getElementById('postTitle').value = data.title;
        document.getElementById('postShortDesc').value = data.short_desc; $('#postContent').summernote('code', data.content); 
        if(document.getElementById('postAuthor')) document.getElementById('postAuthor').value = data.author_id || ''; 
        if(document.getElementById('postImgOld')) document.getElementById('postImgOld').value = data.image || '';
    } else {
        document.getElementById('postFormTitle').innerText = 'Новая статья'; document.getElementById('postFormBlock').querySelector('form').reset(); $('#postContent').summernote('code', ''); document.getElementById('postId').value = '';
        if(document.getElementById('postAuthor')) document.getElementById('postAuthor').value = ''; document.getElementById('customFilePost').nextElementSibling.innerText = 'Выберите файл';
        if(document.getElementById('postImgOld')) document.getElementById('postImgOld').value = '';
    }
    document.getElementById('postFormBlock').scrollIntoView({behavior: 'smooth'});
}
window.editSpecialist = function(data) {
    document.getElementById('specialistFormBlock').style.display = 'block';
    if(data) {
        document.getElementById('specFormTitle').innerText = 'Редактирование профиля: ' + data.first_name;
        document.getElementById('formSpecId').value = data.id;
        document.getElementById('formSpecFirstName').value = data.first_name; document.getElementById('formSpecLastName').value = data.last_name;
        document.getElementById('formSpecPatronymic').value = data.patronymic || '';
        document.getElementById('formSpecSpec').value = data.specialization; document.getElementById('formSpecExp').value = data.experience_years;
        document.getElementById('formSpecEdu').value = data.education; document.getElementById('formSpecDesc').value = data.description;
        if(document.getElementById('formSpecSched')) document.getElementById('formSpecSched').value = data.work_schedule || '';
        if(document.getElementById('formSpecImgOld')) document.getElementById('formSpecImgOld').value = data.photo || '';
        document.getElementById('newUserFields').style.display = 'none';
    } else {
        document.getElementById('specFormTitle').innerText = 'Добавление психолога'; document.getElementById('specialistFormBlock').querySelector('form').reset();
        document.getElementById('formSpecId').value = ''; document.getElementById('customFileSpec').nextElementSibling.innerText = 'Выберите файл...';
        if(document.getElementById('formSpecImgOld')) document.getElementById('formSpecImgOld').value = '';
        document.getElementById('newUserFields').style.display = 'flex';
    }
    document.getElementById('specialistFormBlock').scrollIntoView({behavior: 'smooth'});
}
window.editGroup = function(data) {
    document.getElementById('groupFormBlock').style.display = 'block';
    if(data) {
        document.getElementById('grpFormTitle').innerText = 'Редактирование группы';
        document.getElementById('formGrpId').value = data.id; document.getElementById('formGrpTitle').value = data.title;
        document.getElementById('formGrpDate').value = data.event_date ? data.event_date.replace(' ', 'T').substring(0, 16) : '';
        document.getElementById('formGrpSeats').value = data.max_seats;
        if (document.getElementById('formGrpSpec')) document.getElementById('formGrpSpec').value = data.spec_id;
        if (document.getElementById('formGrpRoom')) document.getElementById('formGrpRoom').value = data.room_id || '';
        document.getElementById('formGrpDesc').value = data.description;
    } else {
        document.getElementById('grpFormTitle').innerText = 'Новая группа'; document.getElementById('groupFormBlock').querySelector('form').reset(); document.getElementById('formGrpId').value = '';
    }
    document.getElementById('groupFormBlock').scrollIntoView({behavior: 'smooth'});
}
window.editFaq = function(id, q, a, s) {
    document.getElementById('faqFormId').value = id; document.getElementById('faqFormQ').value = q; document.getElementById('faqFormA').value = a; document.getElementById('faqFormStatus').value = s; document.getElementById('faqFormTitle').innerText = 'Редактировать вопрос';
}

// --- КАЛЕНДАРЬ ПСИХОЛОГА ---
let currentCalDate = new Date();
let selectedCalDate = null;

window.renderPsychCalendar = function() {
    const grid = document.getElementById('calGrid');
    const monthYearText = document.getElementById('calMonthYear');
    if (!grid) return;

    grid.innerHTML = '';
    const year = currentCalDate.getFullYear(); const month = currentCalDate.getMonth();
    const monthNames = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
    monthYearText.innerText = `${monthNames[month]} ${year}`;

    const days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
    days.forEach(d => { grid.innerHTML += `<div class="calendar-day-name">${d}</div>`; });

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    let startOffset = firstDay === 0 ? 6 : firstDay - 1;

    for (let i = 0; i < startOffset; i++) grid.innerHTML += `<div class="calendar-day empty"></div>`;

    let offDays = JSON.parse(localStorage.getItem('psychOffDays')) || [];

    for (let day = 1; day <= daysInMonth; day++) {
        const fullDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        let hasEvent = false;
        if (typeof globalPsychApps !== 'undefined') { hasEvent = globalPsychApps.some(app => app.date === fullDateStr); }
        
        let isOffDay = offDays.includes(fullDateStr);
        
        let cls = 'calendar-day';
        if (selectedCalDate === fullDateStr) { cls += ' selected'; } 
        else if (isOffDay) { cls += ' day-off'; } 
        else if (hasEvent) { cls += ' has-event'; }

        grid.innerHTML += `<div class="${cls}" onclick="selectCalDay('${fullDateStr}')" title="${isOffDay ? 'Выходной' : ''}">${day}</div>`;
    }
}

window.prevMonth = function() { currentCalDate.setMonth(currentCalDate.getMonth() - 1); renderPsychCalendar(); }
window.nextMonth = function() { currentCalDate.setMonth(currentCalDate.getMonth() + 1); renderPsychCalendar(); }

window.selectCalDay = function(dateStr) {
    selectedCalDate = dateStr;
    renderPsychCalendar(); 
    
    const dayView = document.getElementById('psychDayView'); const dayTitle = document.getElementById('dayViewTitle'); const timeline = document.getElementById('dailyTimeline');
    dayView.style.display = 'block'; const d = dateStr.split('-'); dayTitle.innerText = `Расписание на ${d[2]}.${d[1]}.${d[0]}`; timeline.innerHTML = '';
    
    let offDays = JSON.parse(localStorage.getItem('psychOffDays')) || [];
    let isOffDay = offDays.includes(dateStr);
    
    timeline.innerHTML += `<button class="btn ${isOffDay ? 'btn-success' : 'btn-outline-secondary'} btn-sm mb-3 w-100" onclick="toggleOffDay('${dateStr}')" style="border-radius:15px;"><i class="fas ${isOffDay ? 'fa-check' : 'fa-bed'}"></i> ${isOffDay ? 'Сделать рабочим днем' : 'Отметить как выходной'}</button>`;

    if(isOffDay) { timeline.innerHTML += `<div class="text-center text-muted p-4"><i class="fas fa-mug-hot fa-2x mb-2"></i><br>Это ваш выходной день.</div>`; return; }

    const now = new Date();

    for (let h = 9; h <= 21; h++) {
        const timeStr = `${String(h).padStart(2, '0')}:00`; const exactDateTime = `${dateStr} ${timeStr}:00`;
        let statusHtml = `<span class="text-muted">Свободно (Нажмите, чтобы открыть окно)</span>`;
        let blockClass = 'free'; let onClickAction = `onclick="quickAddSlot('${dateStr}T${timeStr}')"`; let inlineStyle = '';

        if (typeof globalPsychApps !== 'undefined') {
            const apps = globalPsychApps.filter(a => a.time.substring(0, 16) === exactDateTime.substring(0, 16));
            if (apps.length > 0) {
                statusHtml = ''; onClickAction = ''; 
                apps.forEach(app => {
                    if(app.isGroup) {
                        blockClass = 'booked'; inlineStyle = 'background-color: #fdfbf9; border-left: 4px solid #E0C6AD;';
                        statusHtml += `<span class="font-weight-bold d-block" style="color: #3D3935;"><i class="fas fa-users" style="color:#E0C6AD;"></i> Группа: ${app.topic}</span>`;
                    } else if (app.status === 'canceled') {
                        blockClass = 'booked'; inlineStyle = 'background-color: #fff5f5; border-left: 4px solid #dc3545;';
                        statusHtml += `<span class="text-danger font-weight-bold d-block"><i class="fas fa-times-circle"></i> ОТМЕНЕНО: ${app.client}</span>`;
                    } else {
                        blockClass = 'booked'; inlineStyle = 'background-color: #fdfbf9; border-left: 4px solid #E0C6AD;';
                        statusHtml += `<span class="font-weight-bold d-block" style="color: #3D3935;"><i class="fas fa-check-circle" style="color:#E0C6AD;"></i> Запись: ${app.client} (${app.topic})</span>`;
                    }
                });
            }
        }

        if (blockClass === 'free' && typeof globalPsychSlots !== 'undefined') {
            const slot = globalPsychSlots.find(s => s.time.substring(0, 16) === exactDateTime.substring(0, 16));
            if (slot) {
                if (new Date(exactDateTime) > now) {
                    blockClass = 'slot'; 
                    statusHtml = `<span class="text-success font-weight-bold"><i class="fas fa-door-open"></i> Окно открыто</span>`;
                    if(slot.notes) statusHtml += `<br><small class="text-info font-italic">${slot.notes}</small>`; 
                    onClickAction = ''; 
                }
            }
        }
        
        if (blockClass === 'free' && new Date(exactDateTime) < now) {
            statusHtml = `<span class="text-muted" style="opacity: 0.5;">Время прошло</span>`;
            onClickAction = ''; blockClass = '';
        }

        timeline.innerHTML += `<div class="hour-block ${blockClass}" style="${inlineStyle}" ${onClickAction}><div class="time">${timeStr}</div><div class="status">${statusHtml}</div></div>`;
    }
}
window.toggleOffDay = function(dateStr) { let offDays = JSON.parse(localStorage.getItem('psychOffDays')) || []; if(offDays.includes(dateStr)) { offDays = offDays.filter(d => d !== dateStr); } else { offDays.push(dateStr); } localStorage.setItem('psychOffDays', JSON.stringify(offDays)); selectCalDay(dateStr); }
window.quickAddSlot = function(dateTimeVal) { joyConfirm(`Вы хотите открыть свободное окно на ${dateTimeVal.replace('T', ' ')}?`, () => { const form = document.getElementById('quickSlotForm'); form.querySelector('input[name="slot_time"]').value = dateTimeVal; form.submit(); }); }

// --- МОДАЛКА УВЕДОМЛЕНИЙ ---
window.openClientsModal = function() { 
    document.getElementById('clientsModalOverlay').style.display = 'flex'; 
    document.body.style.overflow = 'hidden'; 
}
window.closeClientsModal = function() { 
    document.getElementById('clientsModalOverlay').style.display = 'none'; 
    document.body.style.overflow = ''; 
}
window.filterNotifClients = function() {
    const text = document.getElementById('notifClientSearch').value.toLowerCase();
    const groupFilter = document.getElementById('notifGroupFilter').value;
    const items = document.querySelectorAll('.notif-client-item');
    items.forEach(item => {
        const name = item.getAttribute('data-name'); const groupId = item.getAttribute('data-group'); const isTomorrow = item.getAttribute('data-tomorrow');
        let show = true;
        if (text && !name.includes(text)) show = false;
        if (groupFilter === 'tomorrow') { if (isTomorrow !== 'yes') show = false; } else if (groupFilter !== 'all' && groupId !== groupFilter) { show = false; }
        item.style.display = show ? 'block' : 'none';
    });
}
window.selectAllNotifClients = function() {
    const checkboxes = document.querySelectorAll('.notif-client-item input[type="checkbox"]');
    const visibleCheckboxes = Array.from(checkboxes).filter(cb => cb.closest('.notif-client-item').style.display !== 'none');
    if(visibleCheckboxes.length === 0) return;
    const allChecked = visibleCheckboxes.every(cb => cb.checked);
    visibleCheckboxes.forEach(cb => cb.checked = !allChecked);
    updateClientsBtnCount();
}
document.addEventListener('change', function(e) { if (e.target.matches('.notif-client-item input[type="checkbox"]')) { updateClientsBtnCount(); } });
function updateClientsBtnCount() { const checked = document.querySelectorAll('.notif-client-item input[type="checkbox"]:checked').length; const btn = document.getElementById('btnSelectClients'); if(btn) btn.innerText = `Отправить рассылку (${checked})`; }

window.openAppointmentForService = function(serviceTitle, e) {
    if(e) e.preventDefault();
    if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) { showToast("Для записи необходимо авторизоваться.", true); openAuthModal(); return; }
    document.getElementById('appointmentModal').style.display = 'flex';
    document.body.style.overflow = 'hidden'; 
    
    const sTypeSelect = document.getElementById('appointServiceType');
    if (sTypeSelect) {
        let titleLower = serviceTitle.toLowerCase();
        if (titleLower.includes('онлайн')) { sTypeSelect.value = 'Онлайн сессия'; } 
        else if (titleLower.includes('парн') || titleLower.includes('семейн')) { sTypeSelect.value = 'Парная терапия'; } 
        else { sTypeSelect.value = 'Очная индивидуальная сессия'; }
        filterSpecialistsByService();
    }
}

window.filterSpecialistsByService = function() {
    const serviceTypeSelect = document.getElementById('appointServiceType');
    const specSelect = document.getElementById('appointSpecialistId');
    if (!specSelect || !serviceTypeSelect) return;
    const serviceType = serviceTypeSelect.value;
    const options = specSelect.querySelectorAll('option:not([disabled])');
    
    specSelect.value = ""; 
    const slotContainer = document.getElementById('slotDropdownContainer');
    if(slotContainer) slotContainer.style.display = 'none';

    options.forEach(opt => {
        const searchStr = opt.getAttribute('data-search') || '';
        let show = true;
        if (serviceType === 'Парная терапия') {
            if (!searchStr.includes('семейн') && !searchStr.includes('парн') && !searchStr.includes('отношен')) { show = false; }
        }
        opt.style.display = show ? 'block' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const specCards = document.querySelectorAll('.spec-card-wrapper');
    if (filterBtns.length > 0 && specCards.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const filterVal = this.getAttribute('data-filter');
                specCards.forEach(card => {
                    if (filterVal === 'all') { card.style.display = 'block'; } 
                    else {
                        const specs = card.getAttribute('data-specs') || '';
                        if (specs.includes(filterVal)) { card.style.display = 'block'; } 
                        else { card.style.display = 'none'; }
                    }
                });
            });
        });
    }
});

// --- КВИЗ (ИНТЕЛЛЕКТУАЛЬНЫЙ ТЕСТ ПОДБОРА ПСИХОЛОГА) ---
let quizAnswers = { target: '', problem: '', format: '', style: '', gender: '' };
let quizResultSpec = null;

window.nextQuizStep = function(nextStepId, answerVal) {
    if (nextStepId === 2) quizAnswers.target = answerVal;
    if (nextStepId === 3) quizAnswers.problem = answerVal;
    if (nextStepId === 4) quizAnswers.format = answerVal;
    if (nextStepId === 5) quizAnswers.style = answerVal;
    
    document.querySelectorAll('.quiz-step').forEach(step => step.style.display = 'none');
    document.getElementById('q-step-' + nextStepId).style.display = 'block';
}

window.finishQuiz = function(answerVal) {
    quizAnswers.gender = answerVal;
    const formData = new FormData();
    formData.append('target', quizAnswers.target);
    formData.append('problem', quizAnswers.problem);
    formData.append('format', quizAnswers.format);
    formData.append('style', quizAnswers.style);
    formData.append('gender', quizAnswers.gender);
    
    fetch('quiz_handler.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        document.querySelectorAll('.quiz-step').forEach(step => step.style.display = 'none');
        const resultBlock = document.getElementById('q-step-result');
        resultBlock.style.display = 'block';
        if (data && data.success) {
            quizResultSpec = data;
            document.getElementById('quiz-result-card').innerHTML = `
                <img src="${data.img}" alt="${data.name}" style="width:130px; height:130px; object-fit:cover; border-radius:50%; margin-bottom:15px; border:3px solid #E0C6AD; box-shadow: 0 4px 10px rgba(224,198,173,0.3);">
                <h4 style="margin:0; font-family:'Tenor Sans', sans-serif; color:#3D3935; font-weight: bold;">${data.name}</h4>
                <p class="text-muted small m-0 mt-2">${data.role}</p>
                <div class="mt-3 text-muted small" style="line-height: 1.5; font-family: 'Lato', sans-serif;">
                    Этот специалист обладает высоким индексом соответствия по Вашему запросу и готов принять Вас как в офисе, так и онлайн.
                </div>
            `;
            document.getElementById('quiz-book-btn').style.display = 'inline-block';
        } else {
            document.getElementById('quiz-result-card').innerHTML = `
                <p class="m-0 py-3 text-muted">Не удалось подобрать узкопрофильного специалиста. Наш куратор готов связаться с Вами для индивидуального подбора.</p>
            `;
            document.getElementById('quiz-book-btn').style.display = 'none';
        }
    })
    .catch(err => { console.error(err); showToast("Ошибка при обработке результатов.", true); });
}

window.resetQuiz = function() {
    quizAnswers = { target: '', problem: '', format: '', style: '', gender: '' };
    quizResultSpec = null;
    document.querySelectorAll('.quiz-step').forEach(step => step.style.display = 'none');
    document.getElementById('q-step-1').style.display = 'block';
}

window.bookFromQuiz = function() {
    if (quizResultSpec) {
        document.getElementById('quizModal').style.display = 'none';
        document.body.style.overflow = '';
        openAppointmentForSpec(quizResultSpec.id, quizResultSpec.name);
        resetQuiz();
    }
}

// --- АНИМАЦИЯ ПЕЧАТАЮЩЕГОСЯ ТЕКСТА (ЦИТАТЫ) ---
function initTypewriter() {
    const quotes = document.querySelectorAll('.dissolve-quote');
    if (quotes.length === 0) return;

    // Сохраняем исходный текст и очищаем элементы
    const originalTexts = Array.from(quotes).map(q => q.textContent.trim());
    quotes.forEach(q => q.textContent = '');

    let currentLine = 0;

    function typeLine() {
        if (currentLine >= quotes.length) return;

        const el = quotes[currentLine];
        const text = originalTexts[currentLine];
        el.classList.add('typing');
        let charIndex = 0;

        function typeChar() {
            if (charIndex < text.length) {
                el.textContent += text.charAt(charIndex);
                charIndex++;
                setTimeout(typeChar, 35); // Скорость печати (в мс)
            } else {
                el.classList.remove('typing');
                currentLine++;
                setTimeout(typeLine, 600); // Задержка перед началом следующей строки
            }
        }
        typeChar();
    }

    // Запуск анимации при прокрутке до блока с цитатами
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    typeLine();
                    obs.disconnect();
                }
            });
        }, { threshold: 0.1 });
        const target = document.querySelector('.quote-section');
        if (target) observer.observe(target);
        else typeLine();
    } else {
        typeLine();
    }
}

// --- АВТОМАТИЧЕСКАЯ КАРУСЕЛЬ НА ГЛАВНОЙ ---
function initHomeCarousel() {
    const carousel = document.querySelector('.carousel');
    if (!carousel) return;

    const items = carousel.querySelectorAll('.carousel-item');
    const dots = carousel.querySelectorAll('.carousel-dot');
    let currentIndex = 0;
    let slideInterval;

    function showSlide(index) {
        items.forEach((item, i) => {
            if (i === index) {
                item.classList.add('active');
                dots[i].classList.add('active');
            } else {
                item.classList.remove('active');
                dots[i].classList.remove('active');
            }
        });
        currentIndex = index;
    }

    function nextSlide() {
        let nextIndex = (currentIndex + 1) % items.length;
        showSlide(nextIndex);
    }

    function startSlideShow() {
        slideInterval = setInterval(nextSlide, 4000); // Интервал смены слайдов (4 секунды)
    }

    function stopSlideShow() {
        clearInterval(slideInterval);
    }

    // Ручное переключение при клике на точки под слайдером
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            stopSlideShow();
            showSlide(i);
            startSlideShow();
        });
    });

    startSlideShow();
}

// --- ГОРИЗОНТАЛЬНАЯ ПРОКРУТКА КАРТОЧЕК (СТРЕЛКИ) ---
let currentSpecOffset = 0;
let currentPubOffset = 0;

window.slideSpecLeft = function() {
    const container = document.getElementById('spec-slider');
    if (!container) return;
    const cardWidth = 335; // 300px ширина + 35px отступ
    const maxOffset = 0;
    currentSpecOffset += cardWidth;
    if (currentSpecOffset > maxOffset) currentSpecOffset = 0;
    container.style.transform = `translateX(${currentSpecOffset}px)`;
}

window.slideSpecRight = function() {
    const container = document.getElementById('spec-slider');
    if (!container) return;
    const cardWidth = 335;
    const visibleWidth = container.parentElement.offsetWidth;
    const totalWidth = container.scrollWidth;
    const minOffset = -(totalWidth - visibleWidth);
    
    currentSpecOffset -= cardWidth;
    if (currentSpecOffset < minOffset) {
        currentSpecOffset = minOffset;
    }
    container.style.transform = `translateX(${currentSpecOffset}px)`;
}

window.slideLeft = function() {
    const container = document.getElementById('slider');
    if (!container) return;
    const cardWidth = 335;
    const maxOffset = 0;
    currentPubOffset += cardWidth;
    if (currentPubOffset > maxOffset) currentPubOffset = 0;
    container.style.transform = `translateX(${currentPubOffset}px)`;
}

window.slideRight = function() {
    const container = document.getElementById('slider');
    if (!container) return;
    const cardWidth = 335;
    const visibleWidth = container.parentElement.offsetWidth;
    const totalWidth = container.scrollWidth;
    const minOffset = -(totalWidth - visibleWidth);
    
    currentPubOffset -= cardWidth;
    if (currentPubOffset < minOffset) {
        currentPubOffset = minOffset;
    }
    container.style.transform = `translateX(${currentPubOffset}px)`;
}