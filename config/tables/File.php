<?php


namespace config\tables;
use billing\core\base\Lang;
use config\Icons;
require_once DIR_LIBS . '/functions.php';


class File {

    /**
     * Для приватных файлов, хранящихся на других устройствах
     * Доступ через контроллер
     */
    public const DIR_PRIVATE    = '/storage/files';

    /**
     * Для публичных файлов, передаваемых прямой ссылкой
     * Путь обязательно внутри папки DIR_WWW
     */
    public const DIR_PUBLIC     = '/uploads';

    public const TITLE_ICONS    = 'Icons';
    public const TITLE_IMAGES   = 'Images';
    public const TITLE_DOCS     = 'Documents';
    public const TITLE_MEDIA    = 'Media';
    public const TITLE_ARC      = 'Archives';

    public const SUB_DIR_ICONS  = '/icons';
    public const SUB_DIR_IMAGES = '/images';
    public const SUB_DIR_DOCS   = '/docs';
    public const SUB_DIR_MEDIA  = '/media';
    public const SUB_DIR_ARC    = '/archives';

    public const SUB_DIRS = [
        self::TITLE_ICONS  => self::SUB_DIR_ICONS,
        self::TITLE_IMAGES => self::SUB_DIR_IMAGES,
        self::TITLE_DOCS   => self::SUB_DIR_DOCS,
        self::TITLE_MEDIA  => self::SUB_DIR_MEDIA,
        self::TITLE_ARC    => self::SUB_DIR_ARC,
    ];

    /**
     * Дефолтный индекс из для массива SUB_DIRS
     */
    public const TITLE_DEFAULT = self::TITLE_IMAGES;

    public const URI_INDEX      = '/files';         // Список опубликованных файлов
    public const URI_GET        = '/files/get';     // Для формирования URL удалённого приватного файла
    public const URI_UPLOAD     = '/files/upload';  // Форма отправки файлов на сервер
    public const URI_DEL        = '/files/delete';  // Удаление файла с сервера и из базы



    /**
     * Имя поля с ID, передаваемого в _GET запросе
     */
    public const F_GET_ID = 'file_id';

    /**
     * Имя массива в POST-запросе, в котором приходят данные формы
     */
    public const POST_REC  = 'file';

    /**
     * Имя поля массива для отправленного файла
     */
    public const F_POST_FILE = 'up_file';

    /**
     * Имя таблицы
     */
    public const TABLE = 'files_list';

    // --- Системные поля ---
    public const F_ID               = 'id';                 // ID файла
    public const F_USER_ID          = 'user_id';            // ID пользователя, который загрузил файл
    public const F_ORIGINAL_NAME    = 'original_name';      // Имя файла при загрузке
    public const F_STORED_NAME      = 'local_pathname';     // Фактическое сгенерённое имя файла + оригинальное расширение, сохранённое на диске
    public const F_IS_PUBLIC        = 'is_public';          // 1 — публичный, 0 — приватный, через контроллер
    public const F_SUB_TITLE        = 'sub_title';          // Название типа подпапки для публичных файлов, характеризующая группу или тип файла (Icon, Image, Doc, Media)
    public const F_MIME             = 'mime';               // MIME-тип (image/png, application/pdf и т.п.)
    public const F_SIZE             = 'size';               // Размер в байтах
    public const F_READONLY         = 'readonly';           // атрибут "только чтение"
    public const F_CREATION_UID     = 'creation_uid';       // Кто создал заппись
    public const F_CREATION_DATE    = 'creation_date';      // Дата создания записи
    public const F_MODIFIED_UID     = 'modified_uid';       // Кто изменил запись
    public const F_MODIFIED_DATE    = 'modified_date';      // Дата изменения записи

    // --- Локализованные описания ---
    public const F_UK_DESCRIPTION   = 'uk_description';     // uk - Описе файлу
    public const F_RU_DESCRIPTION   = 'ru_description';     // ru - Описание файла
    public const F_EN_DESCRIPTION   = 'en_description';     // en - File Description

    /* =========================
       Группировка полей
       ========================= */

    /**
     * Список поддерживаемых языков
     */
    public const SUPPORTED_LANGS = ['ru', 'uk', 'en'];

    /**
     * Поля описаний по языкам
     */
    public const F_DESCRIPTION = [
        'ru' => self::F_RU_DESCRIPTION,
        'uk' => self::F_UK_DESCRIPTION,
        'en' => self::F_EN_DESCRIPTION,
    ];

    /**
     * Поля формы создания/редактирования
     */
    public const POST_FIELDS = [

        self::F_IS_PUBLIC           => 1,
        self::F_USER_ID             => 0,
        self::F_ORIGINAL_NAME       => null,
        self::F_STORED_NAME         => null,
        self::F_SUB_TITLE           => null,
        self::F_MIME                => null,
        self::F_SIZE                => null,
        self::F_UK_DESCRIPTION      => null,
        self::F_RU_DESCRIPTION      => null,
        self::F_EN_DESCRIPTION      => null,

    ];



    /**
     * Возвращает абсолютный путь к папке в которой находится файл
     * @param array $file
     * @return string
     */
    public static function get_abs_dir(array $file): string {
        // Формируем путь
        return $file[self::F_IS_PUBLIC]
            ? DIR_WWW  . self::DIR_PUBLIC  . self::SUB_DIRS[$file[self::F_SUB_TITLE]]
            : DIR_ROOT . self::DIR_PRIVATE;
    }



    /**
     * Возвращает абслютный путь к файлу в файловой системе
     * @param array $file
     * @return string
     */
    public static function get_abs_pathname(array $file): string {
        return self::get_abs_dir($file) . '/' . $file[self::F_STORED_NAME];
    }



    public static function get_src(array $file): string
    {
        $site = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'];
        if ($file[self::F_IS_PUBLIC]) {
            // Публичный → сразу относительный путь
            return $site . self::DIR_PUBLIC . self::SUB_DIRS[$file[self::F_SUB_TITLE]] . '/' . $file[self::F_STORED_NAME];
        } else {
            // Приватный → виртуальный URL на контроллер
            return $site . self::URI_GET . '/' . (int)$file[self::F_ID];
        }
    }



    public static function get_description(array $file): string {
        $lang = Lang::code();
        return  (!is_empty($file[self::F_DESCRIPTION[$lang]]) ? "{$file[self::F_DESCRIPTION[$lang]]}" : "" );
    }



    public static function get_title(array $file): string {
        $description = self::get_description($file);
        return  ":: {$file[self::F_ORIGINAL_NAME]}"
                . ($description ? "&#10:: {$description}" : "" )
                . "&#10:: (" . self::get_src($file) . ")";
    }


    public static function get_img_tag(
            array $file,
            string $width = null,
            string $height = null,
            string $attributes = '' // дополнительные атрибуты (например "class=...", style="...")
            ): string
    {
        $src = self::get_src($file);
        $title = self::get_title($file);
        $alt = "{$file[self::F_ORIGINAL_NAME]}";
        return "<img {$attributes} src='{$src}' title='{$title}' alt='{$alt}' ".($width ? "width='{$width}'" : "")." ".($height ? "height='{$height}'" : "").">";
    }



    public static function get_a_tag(
            array       $file,
            string|null $target= TARGET_BLANK,
            string|null $img = null,
            string|null $text = null,
            int|string|null $width=Icons::ICON_WIDTH_DEF,
            int|string|null $height=Icons::ICON_HEIGHT_DEF,
            string|null $attributes = null,
            string|null $style=null
    ) {
        return  a(
                    href: self::get_src($file),
                    target: $target,
                    alt:    pathinfo($file[self::F_ORIGINAL_NAME])['basename'],
                    title:  $file[self::F_ORIGINAL_NAME] . "&#10"
                            . (!is_empty($file[self::F_DESCRIPTION[Lang::code()]]) ? $file[self::F_DESCRIPTION[Lang::code()]] : ""),
                    width:  $width,
                    height: $height,
                    text:   ($img ? $img : "") . ' ' . ($text ? $text : ""),
                    attributes:  $attributes,
                    style:  $style
                );
        /*
            <?= File::get_img_tag($file, height: '32px');?>
            <div>
                <a href="<?= File::get_src($file); ?>" target="_blank">
                    <?= h($file[File::F_ORIGINAL_NAME]) ?>
                </a>
            </div>
        */
    }





}
