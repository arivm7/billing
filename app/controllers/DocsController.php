<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : DocsController.php
 *  Path    : app/controllers/DocsController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;


use billing\core\App;
use billing\core\base\Lang;
use billing\core\base\View;
use config\SessionFields;
use config\tables\Abon;
use config\tables\Docs;
use config\tables\User;
use app\models\DocsModel;
use billing\core\Pagination;
use Valitron\Validator;
use DateTime;



/**
 * Description of DocsController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class DocsController extends AppBaseController {

    function indexAction() {
        $pager = new Pagination(
                sql: "SELECT * FROM `". Docs::TABLE."` WHERE 1 OR `".Docs::F_IS_VISIBLE."`=1 ORDER BY `".Docs::F_DATE_PUBLICATION."` DESC",
                per_page: 5
        );
        $docs = $pager->get_rows();

        $this->setVariables([
            'pager' => $pager,
            'docs'  => $docs,
        ]);

        View::setMeta(
            title: __("Документы"),
        );
    }



    /**
     * Подстановка значений в шаблон
     *
     * @param string $template Шаблон
     * @param array $data Данные для подстановки
     * @return string
     */
    function render_template(string $template, array $data): string {
        // Обработка блоков FOREACH
        $template = preg_replace_callback(
            '/\{FOREACH\.([A-Z0-9_]+)\}(.*?)\{FOREACH\.END\}/s',
            function($matches) use ($data) {
                $arrayKey = $matches[1];
                $block = $matches[2];

                // если нет данных или массив пустой → рендерим один раз с пустым массивом
                if (!isset($data[$arrayKey]) || !is_array($data[$arrayKey]) || empty($data[$arrayKey])) {
                    $newData = $data;
                    $newData[$arrayKey] = []; // пустой элемент, чтобы дефолты подставились
                    return $this->render_template($block, $newData);
                }

                $result = '';
                foreach ($data[$arrayKey] as $row) {
                    $newData = $data;
                    $newData[$arrayKey] = $row;
                    $result .= $this->render_template($block, $newData);
                }
                return $result;
            },
            $template
        );

        // Подстановка одиночных плейсхолдеров
        $template = preg_replace_callback(
            '/\{([A-Z0-9_.]+)(?:\|(.*?))?\}/',
            function($matches) use ($data) {
                $path = explode('.', $matches[1]);
                $value = $data;
                foreach ($path as $key) {
                    if (is_array($value) && array_key_exists($key, $value)) {
                        $value = $value[$key];
                    } else {
                        $value = null;
                        break;
                    }
                }
                return ($value === null || $value === '') ? ($matches[2] ?? '') : $value;
            },
            $template
        );

        return $template;
    }



    function render_template_(string $template, array $data): string {
        // Обработка блоков FOREACH
        $template = preg_replace_callback(
            '/\{FOREACH\.([A-Z0-9_]+)\}(.*?)\{FOREACH\.END\}/s',
            function($matches) use ($data) {
                $arrayKey = $matches[1];
                $block = $matches[2];

                if (!isset($data[$arrayKey]) || !is_array($data[$arrayKey])) {
                    return '';
                }

                $result = '';
                foreach ($data[$arrayKey] as $row) {
                    $newData = $data;
                    $newData[$arrayKey] = $row;  // теперь {ABON.PORT} найдёт $row['PORT']
                    $result .= $this->render_template($block, $newData);
                }
                return $result;
            },
            $template
        );

        // Подстановка одиночных плейсхолдеров
        $template = preg_replace_callback(
            '/\{([A-Z0-9_.]+)(?:\|(.*?))?\}/',
            function($matches) use ($data) {
                $path = explode('.', $matches[1]);
                $value = $data;
                foreach ($path as $key) {
                    if (is_array($value) && array_key_exists($key, $value)) {
                        $value = $value[$key];
                    } else {
                        $value = null;
                        break;
                    }
                }
                return ($value === null || $value === '') ? ($matches[2] ?? '') : $value;
            },
            $template
        );

        return $template;
    }





    function untemplate(string $template): string {
        $model = new DocsModel();
        if (App::$auth->isAuth) {
            $my = $_SESSION[User::SESSION_USER_REC];
            $my[Abon::TABLE] = $model->get_abons_by_uid($my[User::F_ID]);

            $abon_data = [];
            foreach ($my[Abon::TABLE] as $abon) {
                $abon_data[] = [
                    'PORT'    => num_len($abon[Abon::F_ID], 6),
                    'DATE'    => date('d.m.Y', $abon[Abon::F_DATE_JOIN]),
                    'ADDRESS' => $abon[Abon::F_ADDRESS],
                ];
            }

            $data =
            [
                'USER' =>
                [
                    'NAME' => $my[User::F_NAME_FULL],
                    'DATE' => date('d.m.Y', $my[User::F_CREATION_DATE]),
                ],
                'ABON' => $abon_data,
            ];

            $template = $this->render_template($template, $data);

        } else {
            $template = $this->render_template($template, []);
        }
        return $template;
    }


    function viewAction() {
        $model = new DocsModel();
        if (isset($_GET[Docs::F_GET_ID]) &&
                is_numeric($_GET[Docs::F_GET_ID]) &&
                $model->validate_id(table_name: Docs::TABLE, field_id: Docs::F_ID, id_value: (int)$_GET[Docs::F_GET_ID]))
        {
            $doc = $model->get_row_by_id(
                    table_name: Docs::TABLE,
                    field_id: Docs::F_ID,
                    id_value: (int)$_GET[Docs::F_GET_ID]
            );

            /**
             * Заполнить шаблонные элементы
             */
            foreach (Docs::TEMPLATE_FIELDS as $field) {
                $doc[$field] = $this->untemplate($doc[$field]);
            }

            $this->setVariables([
                'doc' => $doc,
            ]);

            View::setMeta(
                title: __('Rilan') . " :: " . __("Документы") . " :: " . $doc[Docs::F_TITLES[Lang::code()]],
                descr: "",
                keywords: ""
            );

        } else {
            redirect();
        }
    }


    function deleteAction() {
        $model = new DocsModel();
        if (isset($_GET[Docs::F_GET_ID]) && $model->validate_id(table_name: Docs::TABLE, field_id: Docs::F_ID, id_value: (int)$_GET[Docs::F_GET_ID])) {
            if ($model->delete_rows_by_field(table: Docs::TABLE, field_id: Docs::F_ID, value_id: (int)$_GET[Docs::F_GET_ID])) {
                $_SESSION[SessionFields::SUCCESS] = __('Документ удалён');
            } else {
                $_SESSION[SessionFields::ERROR] = __('Ошибка удаления');
            }
            redirect(Docs::URI_LIST);
        }
        redirect();
    }

    function editAction() {
        $model = new DocsModel();

        /**
         * Если есть данные формы, то обработать и сохранить
         */
        if (isset($_POST[Docs::POST_REC])) {
            $this->save();
        }


        if (isset($_GET[Docs::F_GET_ID]) &&
                is_numeric($_GET[Docs::F_GET_ID]) &&
                $model->validate_id(table_name: Docs::TABLE, field_id: Docs::F_ID, id_value: (int)$_GET[Docs::F_GET_ID]))
        {
            $document = $model->get_row_by_id(
                    table_name: Docs::TABLE,
                    field_id: Docs::F_ID,
                    id_value: (int)$_GET[Docs::F_GET_ID]
            );

            $this->setVariables([
                'doc' => $document,
            ]);

            View::setMeta(
                title: __('Rilan') . " :: " . __("Редактирование документа") . " :: " . cleaner_html($document[Docs::F_TITLES[Lang::code()]])
            );
        } else {
            $_SESSION[SessionFields::INFO] = __("Создание нового документа");
            View::setMeta(
                title: __('Rilan') . " :: " . __("Создание нового документа")
            );
        }
    }



    function validate(array $rec): bool {

        Validator::lang(Lang::code());
        $validator = new Validator($rec);

        $rules = [
            'required' => [
                Docs::F_AUTHOR_ID,
                Docs::F_RU_TITLE,
                Docs::F_UK_TITLE,
                Docs::F_EN_TITLE,
            ],
            'date' => [
                Docs::F_DATE_CREATION_STR,
                Docs::F_DATE_EXPIRATION_STR,
                Docs::F_DATE_PUBLICATION_STR,
            ],
            'lengthMax' => [
                [Docs::F_RU_TITLE, 200],
                [Docs::F_UK_TITLE, 200],
                [Docs::F_EN_TITLE, 200],
            ],
        ];
        $validator->rules($rules);

        // хотя бы в одном языке должен быть текст новости
        $hasText = false;
        foreach (Docs::SUPPORTED_LANGS as $lang) {
            if (!empty($rec[Docs::F_TEXTS[$lang]])) {
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
            return false;
        }
        return true;
    }



    function parce_post_rec(array $post_rec): array {

        $model = new DocsModel();

        $docs_rec = [];

        if  (
                isset($post_rec[Docs::F_ID]) &&
                $model->validate_id(table_name: Docs::TABLE, field_id: Docs::F_ID, id_value: $post_rec[Docs::F_ID])
            )
        {
            $docs_rec[Docs::F_ID] = $post_rec[Docs::F_ID];
        }

        $docs_rec[Docs::F_AUTHOR_ID] = $_SESSION[User::SESSION_USER_REC][User::F_ID];

        if (isset($post_rec[Docs::F_DATE_CREATION_STR])) {
            if (empty($post_rec[Docs::F_DATE_CREATION_STR]) || $post_rec[Docs::F_DATE_CREATION_STR] == "") {
                $docs_rec[Docs::F_DATE_CREATION] = null;
            } else {
                $dt = DateTime::createFromFormat(FORM_DATE_TIME, $post_rec[Docs::F_DATE_CREATION_STR]);
                $docs_rec[Docs::F_DATE_CREATION] = ($dt == false ? null : $dt->getTimestamp());
            }
        }

        if (isset($post_rec[Docs::F_DATE_PUBLICATION_STR])) {
            if (empty($post_rec[Docs::F_DATE_PUBLICATION_STR]) || $post_rec[Docs::F_DATE_PUBLICATION_STR] == "") {
                $docs_rec[Docs::F_DATE_PUBLICATION] = null;
            } else {
                $dt = DateTime::createFromFormat(FORM_DATE_TIME, $post_rec[Docs::F_DATE_PUBLICATION_STR]);
                $docs_rec[Docs::F_DATE_PUBLICATION] = ($dt == false ? null : $dt->getTimestamp());
            }
        }

        if (isset($post_rec[Docs::F_DATE_EXPIRATION_STR])) {
            if (empty($post_rec[Docs::F_DATE_EXPIRATION_STR]) || $post_rec[Docs::F_DATE_EXPIRATION_STR] == "") {
                $docs_rec[Docs::F_DATE_EXPIRATION] = null;
            } else {
                $dt = DateTime::createFromFormat(FORM_DATE_TIME, $post_rec[Docs::F_DATE_EXPIRATION_STR]);
                $docs_rec[Docs::F_DATE_EXPIRATION] = ($dt == false ? null : $dt->getTimestamp());
            }
        }

        $docs_rec[Docs::F_AUTO_VISIBLE] = $post_rec[Docs::F_AUTO_VISIBLE] ?? 0;
        $docs_rec[Docs::F_IS_VISIBLE]   = $post_rec[Docs::F_IS_VISIBLE]   ?? 0;
        $docs_rec[Docs::F_AUTO_DEL]     = $post_rec[Docs::F_AUTO_DEL]     ?? 0;
        $docs_rec[Docs::F_IS_DELETED]   = $post_rec[Docs::F_IS_DELETED]   ?? 0;

        $docs_rec[Docs::F_IN_VIEW_TITLE]        = $post_rec[Docs::F_IN_VIEW_TITLE]          ?? 0;
        $docs_rec[Docs::F_IN_VIEW_DESCRIPTION]  = $post_rec[Docs::F_IN_VIEW_DESCRIPTION]    ?? 0;
        $docs_rec[Docs::F_IN_VIEW_TEXT]         = $post_rec[Docs::F_IN_VIEW_TEXT]           ?? 0;

        /**
         * Поля по языкам
         */
        foreach (Docs::SUPPORTED_LANGS as $lang) {
            $docs_rec[Docs::F_TITLES[$lang]]        = cleaner_html($post_rec[Docs::F_TITLES[$lang]]);
            $docs_rec[Docs::F_DESCRIPTIONS[$lang]]  = cleaner_html($post_rec[Docs::F_DESCRIPTIONS[$lang]]);
            $docs_rec[Docs::F_TEXTS[$lang]]         = cleaner_html($post_rec[Docs::F_TEXTS[$lang]]);
        }

        return $docs_rec;
    }



    function save() {

        $model = new DocsModel();

        if (isset($_POST[Docs::POST_REC]) && is_array($_POST[Docs::POST_REC])) {

            $post_rec = $_POST[Docs::POST_REC];
//            debug($post_rec, '$post_rec', die: 0);

            /**
             * Проверяем
             */
            if (!$this->validate($post_rec)) {
                $_SESSION[SessionFields::FORM_DATA] = $post_rec;
                if (isset($post_rec[Docs::F_ID]) && $model->validate_id(table_name: Docs::TABLE, field_id: Docs::F_ID, id_value: $post_rec[Docs::F_ID])) {
                    redirect(Docs::URI_EDIT . "?".Docs::F_GET_ID.'='.$post_rec[Docs::F_ID]);
                } else {
                    redirect(Docs::URI_EDIT);
                }
            }

            $docs_rec = $this->parce_post_rec($post_rec);

            /**
             * Отправляем даные в базу
             */
            if (isset($docs_rec[Docs::F_ID])) {
                // обновление
                $model->update_row_by_id(table: Docs::TABLE, field_id: Docs::F_ID, row: $docs_rec);
                $model->add_success_info(__('Исправления успешно внесены'));
                $model->successToSession();
                redirect(Docs::URI_EDIT . "?".Docs::F_GET_ID.'='.$docs_rec[Docs::F_ID]);
            } else {
                // новая новость
                $model->insert_row(table: Docs::TABLE, row: $docs_rec);
                $docs_id = $model->lastInsertId();
                $model->add_success_info(__('Новая запись %s успешно добавлена', $docs_id));
                $model->successToSession();
                redirect(Docs::URI_EDIT . "?".Docs::F_GET_ID.'='.$docs_id);
            }

        } else {
            $model->add_error_info(__('Нет данных'));
            $model->errorsToSession();
        }
        redirect();
    }

}