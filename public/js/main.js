/*
 *  Project : s1.ri.net.ua
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
    $('#lang').change(function() {
        window.location = '/language/change?lang=' + $(this).val();
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



  /**
   * Для виджета меню с шаблоном menu_template_bootstrap
   * Навешиваем события на открытие/закрытие
   */
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



