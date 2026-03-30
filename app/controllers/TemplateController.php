<?php
/**
 *  Project : my.ri.net.ua
 *  File    : TemplateController.php
 *  Path    : app/controllers/TemplateController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 29 Mar 2026 01:29:25
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Контроллер для работы с шаблонами привязки текстовых фрагментов к абонентам
 * 
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



namespace app\controllers;



use app\models\AbonModel;
use billing\core\MsgQueue;
use billing\core\MsgType;
use config\tables\Abon;
use config\tables\Ppp;
use config\tables\TSAbonTmpl;

class TemplateController extends AppBaseController {


    /**
     * Метод для вставки шаблона в базу данных
     * 
     * @param int $ppp_id ID PPP-подключения
     * @param int $abon_id ID абонента
     * @param string $template Текст шаблона
     * @return bool Возвращает true при успешной вставке, иначе false
     */
    public static function insert(int $ppp_id, int $abon_id, string $template): bool { 

        /**
         * Добавляем шаблон
         */

        $model = new AbonModel();
        
        // Отправляем сообщение об начале процесса вставки шаблона
        MsgQueue::msg(MsgType::SUCCESS, __('Inserting a template | Внесение шаблона | Внесення шаблону'));
        
        // Проверяем существование PPP-подключения по ID
        if ($model->validate_id(Ppp::TABLE, $ppp_id, Ppp::F_ID)) {
            MsgQueue::msg(MsgType::SUCCESS, __('ppp_id -- ok'));
            
            // Проверяем существование абонента по ID
            if ($model->validate_id(Abon::TABLE, $abon_id, Abon::F_ID)) {
                MsgQueue::msg(MsgType::SUCCESS, __('abon_id -- ok'));
                
                // Проверяем, что шаблон не пустой и является строкой
                if (!is_empty($template) && is_string($template)) {
                    MsgQueue::msg(MsgType::SUCCESS, __('template -- текст'));
                    
                    // Проверяем уникальность шаблона в базе данных
                    $test = $model->get_rows_by_sql("SELECT * FROM `".TSAbonTmpl::TABLE."` WHERE `".TSAbonTmpl::F_PPP_ID."`={$ppp_id} AND `".TSAbonTmpl::F_TEMPLATE."` LIKE \"%".$template."%\"");
                    if (count($test) == 0) {
                        MsgQueue::msg(MsgType::SUCCESS, __('template -- уникален'));

                        // Подготовливаем данные для вставки
                        $row[TSAbonTmpl::F_PPP_ID] = $ppp_id;
                        $row[TSAbonTmpl::F_ABON_ID] = $abon_id;
                        $row[TSAbonTmpl::F_TEMPLATE] = $template;

                        // Вставляем запись в таблицу шаблонов
                        if ($model->insert_row(TSAbonTmpl::TABLE, $row)) {
                            MsgQueue::msg(MsgType::SUCCESS, __('Добавили'));
                            return true;
                        } else {
                            MsgQueue::msg(MsgType::ERROR, __('Добавление не удалось. Ошибка добавления строки в базу'));
                        }
                    } else {
                        // Если шаблон уже существует
                        MsgQueue::msg(MsgType::ERROR, __('Ошибка: Шаблон не добавлен: Шаблон не уникален. Есть совпадения'));
                        MsgQueue::msg(MsgType::ERROR, __(get_html_table($test)));
                    }
                } else {
                    // Ошибка, если шаблон пустой или не строка
                    MsgQueue::msg(MsgType::ERROR, __('Ошибка: Шаблон не добавлен: Текст шаблона должен быть не пустой строкой'));
                }
            } else {
                // Ошибка, если ID абонента недействителен
                MsgQueue::msg(MsgType::ERROR, __('Ошибка: Шаблон не добавлен: abon_id ('.$abon_id.') не верен'));
            }
        } else {
            // Ошибка, если ID PPP-подключения недействителен
            MsgQueue::msg(MsgType::ERROR, __('Ошибка: Шаблон не добавлен: ppp_id ('.$ppp_id.') не верен'));
        }
        // Возвращаем false при неудачном завершении
        return false;

    }



}