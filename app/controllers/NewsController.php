<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : NewsController.php
 *  Path    : app/controllers/NewsController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;

use billing\core\base\Lang;
use billing\core\base\View;
use config\SessionFields;
use config\tables\News;
use app\models\NewsModel;
use billing\core\Pagination;
use Valitron\Validator;
use DateTime;

/**
 * Description of NewsController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class NewsController extends AppBaseController {


    function indexAction() {
        $pager = new Pagination(
                sql: "SELECT * FROM `".News::TABLE."` WHERE 1 OR `".News::F_IS_VISIBLE."`=1 ORDER BY `".News::F_DATE_PUBLICATION."` DESC",
                per_page: 5
        );
        $news = $pager->get_rows();

        $this->setVariables([
            'pager' => $pager,
            'news'  => $news,
        ]);

        View::setMeta(
            title: __("Новости"),
        );
    }


    function viewAction() {
        $model = new NewsModel();
        if (isset($_GET[News::F_GET_ID]) &&
                is_numeric($_GET[News::F_GET_ID]) &&
                $model->validate_id(table_name: News::TABLE, field_id: News::F_ID, id_value: (int)$_GET[News::F_GET_ID]))
        {
            $news = $model->get_row_by_id(
                    table_name: News::TABLE,
                    field_id: News::F_ID,
                    id_value: (int)$_GET[News::F_GET_ID]
            );

            $this->setVariables([
                'news' => $news,
            ]);

            View::setMeta(
                title: __('Rilan') . " :: " . __("Новости") . " :: " . $news[News::F_TITLES[Lang::code()]],
                descr: "",
                keywords: ""
            );

        } else {
            redirect();
        }
    }



    function editAction() {
        $model = new NewsModel();

        /**
         * Если есть данные формы, то обработать и сохранить
         */
        if (isset($_POST[News::POST_REC])) {
            $this->save();
        }


        if (isset($_GET[News::F_GET_ID]) &&
                is_numeric($_GET[News::F_GET_ID]) &&
                $model->validate_id(table_name: News::TABLE, field_id: News::F_ID, id_value: (int)$_GET[News::F_GET_ID]))
        {
            $news = $model->get_row_by_id(
                    table_name: News::TABLE,
                    field_id: News::F_ID,
                    id_value: (int)$_GET[News::F_GET_ID]
            );

            $this->setVariables([
                'news' => $news,
            ]);

            View::setMeta(
                title: __('Rilan') . " :: " . __("Редактирование новости") . " :: " . $news[News::F_TITLES[Lang::code()]],
                descr: "",
                keywords: ""
            );
        } else {
            $_SESSION[SessionFields::ERROR] = __('WTF?');
            redirect();
        }
    }



    function validate(array $rec): bool {

        Validator::lang(Lang::code());
        $validator = new Validator($rec);

        $rules = [
            'required' => [
                News::F_AUTHOR_ID,
                News::F_RU_TITLE,
                News::F_UK_TITLE,
                News::F_EN_TITLE,
            ],
            'date' => [
                News::F_DATE_CREATION_STR,
                News::F_DATE_EXPIRATION_STR,
                News::F_DATE_PUBLICATION_STR,
            ],
            'lengthMax' => [
                [News::F_RU_TITLE, 200],
                [News::F_UK_TITLE, 200],
                [News::F_EN_TITLE, 200],
            ],
        ];
        $validator->rules($rules);

        // хотя бы в одном языке должен быть текст новости
        $hasText = false;
        foreach (News::SUPPORTED_LANGS as $lang) {
            if (!empty($rec[News::F_TEXTS[$lang]])) {
                $hasText = true;
                break;
            }
        }
        if (!$hasText) {
            $validator->error('texts', __('Необходимо заполнить текст хотя бы на одном языке'));
        }

        if(!$validator->validate() || !$hasText) {
            // сохраняем ошибки в сессию
            $_SESSION[SessionFields::ERROR] = $validator->errors();
            $_SESSION[SessionFields::FORM_DATA] = $_POST[News::POST_REC];
            return false;
        }
        return true;
    }



    function parce_post_rec(array $post_rec): array {

        $model = new NewsModel();

        $news_rec = [];

        if  (
                isset($post_rec[News::F_ID]) &&
                $model->validate_id(table_name: News::TABLE, field_id: News::F_ID, id_value: $post_rec[News::F_ID])
            )
        {
            $news_rec[News::F_ID] = $post_rec[News::F_ID];
        }

        $news_rec[News::F_AUTHOR_ID] = $post_rec[News::F_AUTHOR_ID];

        if (isset($post_rec[News::F_DATE_CREATION_STR])) {
            if (empty($post_rec[News::F_DATE_CREATION_STR]) || $post_rec[News::F_DATE_CREATION_STR] == "") {
                $news_rec[News::F_DATE_CREATION] = null;
            } else {
                $dt = DateTime::createFromFormat(FORM_DATE_TIME, $post_rec[News::F_DATE_CREATION_STR]);
                $news_rec[News::F_DATE_CREATION] = ($dt == false ? null : $dt->getTimestamp());
            }
        }

        if (isset($post_rec[News::F_DATE_PUBLICATION_STR])) {
            if (empty($post_rec[News::F_DATE_PUBLICATION_STR]) || $post_rec[News::F_DATE_PUBLICATION_STR] == "") {
                $news_rec[News::F_DATE_PUBLICATION] = null;
            } else {
                $dt = DateTime::createFromFormat(FORM_DATE_TIME, $post_rec[News::F_DATE_PUBLICATION_STR]);
                $news_rec[News::F_DATE_PUBLICATION] = ($dt == false ? null : $dt->getTimestamp());
            }
        }

        if (isset($post_rec[News::F_DATE_EXPIRATION_STR])) {
            if (empty($post_rec[News::F_DATE_EXPIRATION_STR]) || $post_rec[News::F_DATE_EXPIRATION_STR] == "") {
                $news_rec[News::F_DATE_EXPIRATION] = null;
            } else {
                $dt = DateTime::createFromFormat(FORM_DATE_TIME, $post_rec[News::F_DATE_EXPIRATION_STR]);
                $news_rec[News::F_DATE_EXPIRATION] = ($dt == false ? null : $dt->getTimestamp());
            }
        }

        $news_rec[News::F_AUTO_VISIBLE] = $post_rec[News::F_AUTO_VISIBLE] ?? 0;
        $news_rec[News::F_IS_VISIBLE]   = $post_rec[News::F_IS_VISIBLE]   ?? 0;
        $news_rec[News::F_AUTO_DEL]     = $post_rec[News::F_AUTO_DEL]     ?? 0;
        $news_rec[News::F_IS_DELETED]   = $post_rec[News::F_IS_DELETED]   ?? 0;

        /**
         * Поля по языкам
         */
        foreach (News::SUPPORTED_LANGS as $lang) {
            $news_rec[News::F_TITLES[$lang]]        = cleaner_html($post_rec[News::F_TITLES[$lang]]);
            $news_rec[News::F_DESCRIPTIONS[$lang]]  = cleaner_html($post_rec[News::F_DESCRIPTIONS[$lang]]);
            $news_rec[News::F_TEXTS[$lang]]         = cleaner_html($post_rec[News::F_TEXTS[$lang]]);
        }

        return $news_rec;
    }



    function save() {

        $model = new NewsModel();

        if (isset($_POST[News::POST_REC]) && is_array($_POST[News::POST_REC])) {

            $post_rec = $_POST[News::POST_REC];
//            debug($post_rec, '$post_rec', die: 0);

            /**
             * Проверяем
             */
            if (!$this->validate($post_rec)) {
                redirect('');
            }

            $news_rec = $this->parce_post_rec($post_rec);

            /**
             * Отправляем даные в базу
             */
            if (isset($news_rec[News::F_ID])) {
                // обновление
                $model->update_row_by_id(table: News::TABLE, field_id: News::F_ID, row: $news_rec);
                $model->add_success_info(__('Исправления успешно внесены'));
                $model->successToSession();
                redirect("/news/edit?".News::F_GET_ID.'='.$news_rec[News::F_ID]);
            } else {
                // новая новость
                $model->insert_row(table: News::TABLE, row: $news_rec);
                $news_id = $model->lastInsertId();
                $model->add_success_info(__('Новая запись %s успешно добавлена', $news_id));
                $model->successToSession();
                redirect("/news/edit?".News::F_GET_ID.'='.$news_id);
            }

        } else {
            $model->add_error_info(__('Нет данных'));
            $model->errorsToSession();
        }
        redirect();
    }

}