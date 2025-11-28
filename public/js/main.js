/*
 *  Project : my.ri.net.ua
 *  File    : main.js
 *  Path    : public/js/main.js
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 21 Sep 2025 00:36:58
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of main.js
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



/**
 * для виджета LangSelector
 * @returns {undefined}
 */
 $(function () {
     $('#lang_changer').change(function() {
        window.location = '/lang-switch/change?lang=' + $(this).val();
        // console.log($(this).val());
     });
 } );



/**
 * для виджета ThemeSelector
 * @returns {undefined}
 */
$(function () {
    $('#theme').change(function() {
        window.location = '/theme/change?theme=' + $(this).val();
        // console.log($(this).val());
    });
} );



/**
 * Для виджета меню с шаблоном menu_template_bootstrap
 * Сохранение статуса меню-аккордеона на bootstrap
 * @type type
 */
/*
document.addEventListener('DOMContentLoaded', function () {
  const storageKey = 'accordion-menu-state';

  // Получаем сохранённое состояние из localStorage
  let savedState = {};
  try {
    savedState = JSON.parse(localStorage.getItem(storageKey)) || {};
  } catch (e) {
    savedState = {};
  }

  // Применяем состояние только к аккордеонам меню
  document.querySelectorAll('.accordion-collapse[id^="menu_collapse_"]').forEach(function (el) {
    const id = el.id;
    if (savedState[id]) {
      el.classList.add('show');
    } else {
      el.classList.remove('show');
    }

    // Навешиваем события на открытие/закрытие
    el.addEventListener('shown.bs.collapse', function () {
      savedState[id] = true;
      localStorage.setItem(storageKey, JSON.stringify(savedState));
    });
    el.addEventListener('hidden.bs.collapse', function () {
      savedState[id] = false;
      localStorage.setItem(storageKey, JSON.stringify(savedState));
    });
  });
});
*/
/*
document.addEventListener('DOMContentLoaded', function () {
  const storageKey = 'accordion-state';

  // Получаем сохранённое состояние из localStorage
  let savedState = {};
  try {
    savedState = JSON.parse(localStorage.getItem(storageKey)) || {};
  } catch (e) {
    savedState = {};
  }

  // Применяем состояние
  document.querySelectorAll('.accordion-collapse').forEach(function (el) {
    const id = el.id;
    if (savedState[id]) {
      el.classList.add('show');
    } else {
      el.classList.remove('show');
    }
  });

  // Навешиваем события на открытие/закрытие
  document.querySelectorAll('.accordion-collapse').forEach(function (el) {
    el.addEventListener('shown.bs.collapse', function () {
      savedState[el.id] = true;
      localStorage.setItem(storageKey, JSON.stringify(savedState));
    });
    el.addEventListener('hidden.bs.collapse', function () {
      savedState[el.id] = false;
      localStorage.setItem(storageKey, JSON.stringify(savedState));
    });
  });
});
*/


/**
 * Для виджета Menu с шаблоном li-ul
 */
jQuery(document).ready(function($) {
    jQuery('#my-accordion').dcAccordion(
    {
        eventType: "click",
        autoClose: true,
    }
    );
});



/**
 * Блок Копирования текста в буфер обмена по клику на кнопку с классом .copy-btn
 * и показ тоста об успешном/неуспешном копировании
 * 
 * Использовать примерно так:
 *  <button class="btn btn-primary btn-sm p-1 copy-btn" data-text='<?= json_encode($text); ?>'>
 *       <img src="<?= Icons::SRC_ICON_CLIPBOARD; ?>" title="<?= __('Скопировать в clipboard') ?>" alt="[copy]" height="30rem">
 *  </button>
 *  data-text может содержать как простой текст, так и JSON-строку
 *  (в последнем случае будет выполнен JSON.parse перед копированием)
 * 
 */

/**
 * контейнер тостов не обязателен в разметке — создаётся скриптом при необходимости
 */
document.addEventListener('DOMContentLoaded', function() {    
  const toastContainerId = 'toastContainer';
  let container = document.getElementById(toastContainerId);
  if (!container) {
    container = document.createElement('div');
    container.id = toastContainerId;
    container.className = 'position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = 1080;
    document.body.appendChild(container);
  }

  function getTextFromBtn(btn){
    const v = btn.getAttribute('data-text');
    try { return JSON.parse(v); } catch { return v; }
  }

  async function copyText(text){
    // Требует HTTPS и современных браузеров
    await navigator.clipboard.writeText(text);
  }

  function showToast(title, body, delay = 3000){
    const id = 'toast-' + Date.now() + '-' + Math.random().toString(36).slice(2,8);
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
      <div id="${id}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">${title}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${body}</div>
      </div>
    `.trim();
    const toastEl = wrapper.firstElementChild;
    container.appendChild(toastEl);
    const bsToast = new bootstrap.Toast(toastEl, { delay, autohide: true });
    bsToast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.copy-btn');
    if (!btn) return;
    const text = getTextFromBtn(btn);
    try {
      await copyText(text);
      showToast('Успех', 'Текст скопирован в буфер обмена');
    } catch (err) {
      showToast('Ошибка', 'Не удалось скопировать текст', 5000);
    }
  });
});

/* Конец Блока Копирования текста в буфер обмена */
