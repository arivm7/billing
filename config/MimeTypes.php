<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : MimeTypes.php
 *  Path    : config/MimeTypes.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace config;

/**
 * Список поддерживаемых Mime-типов
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class MimeTypes {



    const F_EXT       = 'ext';
    const F_ALT       = 'alt';
    const F_BROWSABLE = 'browsable';
    const F_ICON      = 'icon';

    /**
     * Поля
     *  'ext'       -- массив расширений
     *  'alt'       -- Поле alt пи вводе изображения
     *  'browsable' -- Список форматов файлов, которые поддерживаются браузерами для отображения в HTML-документах, вместе с их соответствующими MIME типами.
     *                 Эти форматы и их MIME типы позволяют браузерам правильно интерпретировать и отображать графические файлы в HTML-документах.
     *  'icon'      -- Иконка
     */
    const MIME_TYPES = [

        'application/7z'                                                            => [ self::F_EXT => ['7z'],                self::F_ALT => '7z',      self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark-zip fs-2"></i>' ],
        'application/gzip'                                                          => [ self::F_EXT => ['gz'],                self::F_ALT => 'gz',      self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark-zip fs-2"></i>' ],
        'application/javascript'                                                    => [ self::F_EXT => ['js'],                self::F_ALT => 'JS',      self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-js fs-2"></i>' ],
        'application/json'                                                          => [ self::F_EXT => ['json'],              self::F_ALT => 'JSON',    self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-json fs-2"></i>' ],
        'application/msword'                                                        => [ self::F_EXT => ['doc'],               self::F_ALT => 'doc',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-filetype-doc fs-2"></i>' ],
        'application/pdf'                                                           => [ self::F_EXT => ['pdf'],               self::F_ALT => 'PDF',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-filetype-pdf fs-2"></i>' ],
        'application/rar'                                                           => [ self::F_EXT => ['rar'],               self::F_ALT => 'rar',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark-zip fs-2"></i>' ],
        'application/rtf'                                                           => [ self::F_EXT => ['rtf'],               self::F_ALT => 'RTF',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-earmark-richtext fs-2"></i>' ],
        'application/vnd.adobe.pdf'                                                 => [ self::F_EXT => ['pdf'],               self::F_ALT => 'pdf',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-filetype-pdf fs-2"></i>' ],
        'application/vnd.corel-draw'                                                => [ self::F_EXT => ['cdr'],               self::F_ALT => 'cdr',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.google-apps.document'                                      => [ self::F_EXT => ['gdoc'],              self::F_ALT => 'gdoc',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.google-apps.presentation'                                  => [ self::F_EXT => ['gslides'],           self::F_ALT => 'gslides', self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.google-apps.spreadsheet'                                   => [ self::F_EXT => ['gsheet'],            self::F_ALT => 'gsheet',  self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.lotus-1-2-3'                                               => [ self::F_EXT => ['wk3', 'wk4', 'wk5'], self::F_ALT => 'wk3',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.ms-access'                                                 => [ self::F_EXT => ['mdb', 'accdb'],      self::F_ALT => 'mdb',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.ms-excel'                                                  => [ self::F_EXT => ['xls', 'xlt'],        self::F_ALT => 'xls',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-filetype-xls fs-2"></i>' ],
        'application/vnd.ms-excel.addin.macroEnabled.12'                            => [ self::F_EXT => ['xlam'],              self::F_ALT => 'xlam',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.ms-excel.sheet.binary.macroEnabled.12'                     => [ self::F_EXT => ['xlsb'],              self::F_ALT => 'xlsb',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.ms-excel.sheet.macroEnabled.12'                            => [ self::F_EXT => ['xlsm'],              self::F_ALT => 'xlsm',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.ms-powerpoint'                                             => [ self::F_EXT => ['ppt', 'pot'],        self::F_ALT => 'ppt',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-filetype-ppt fs-2"></i>' ],
        'application/vnd.ms-powerpoint.addin.macroEnabled.12'                       => [ self::F_EXT => ['ppam'],              self::F_ALT => 'ppam',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.ms-powerpoint.presentation.macroEnabled.12'                => [ self::F_EXT => ['ppsm'],              self::F_ALT => 'ppsm',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.ms-powerpoint.slide.macroEnabled.12'                       => [ self::F_EXT => ['sldm'],              self::F_ALT => 'sldm',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.ms-powerpoint.slideshow.macroEnabled.12'                   => [ self::F_EXT => ['ppsx'],              self::F_ALT => 'ppsx',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.ms-powerpoint.template.macroEnabled.12'                    => [ self::F_EXT => ['potm'],              self::F_ALT => 'potm',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.ms-word'                                                   => [ self::F_EXT => ['doc'],               self::F_ALT => 'doc',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-filetype-doc fs-2"></i>' ],
        'application/vnd.oasis.opendocument.graphics'                               => [ self::F_EXT => ['odg'],               self::F_ALT => 'odg',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.oasis.opendocument.presentation'                           => [ self::F_EXT => ['odp'],               self::F_ALT => 'odp',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.oasis.opendocument.spreadsheet'                            => [ self::F_EXT => ['ods'],               self::F_ALT => 'ods',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.oasis.opendocument.text'                                   => [ self::F_EXT => ['odt'],               self::F_ALT => 'odt',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => [ self::F_EXT => ['pptx'],              self::F_ALT => 'PPTX',    self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-pptx fs-2"></i>' ],
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow'    => [ self::F_EXT => ['ppsx'],              self::F_ALT => 'ppsx',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.openxmlformats-officedocument.presentationml.template'     => [ self::F_EXT => ['potx'],              self::F_ALT => 'potx',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => [ self::F_EXT => ['xlsx'],              self::F_ALT => 'XLSX',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-filetype-xlsx fs-2"></i>' ],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template'      => [ self::F_EXT => ['xltx'],              self::F_ALT => 'xltx',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => [ self::F_EXT => ['docx'],              self::F_ALT => 'DOCX',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-filetype-docx fs-2"></i>' ],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template'   => [ self::F_EXT => ['dotx'],              self::F_ALT => 'dotx',    self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/vnd.wordperfect'                                               => [ self::F_EXT => ['wpd', 'wp'],         self::F_ALT => 'wpd',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark fs-2"></i>' ],
        'application/xml'                                                           => [ self::F_EXT => ['xml'],               self::F_ALT => 'XML',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-xml fs-2"></i>' ],
        'application/zip'                                                           => [ self::F_EXT => ['zip'],               self::F_ALT => 'zip',     self::F_BROWSABLE => 0, self::F_ICON => '<i class="bi bi-file-earmark-zip fs-2"></i>' ],
        'audio/mpeg'                                                                => [ self::F_EXT => ['mp3'],               self::F_ALT => 'MP3',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-music fs-2"></i>' ],
        'audio/ogg'                                                                 => [ self::F_EXT => ['ogg'],               self::F_ALT => 'OGG',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-music fs-2"></i>' ],
        'audio/wav'                                                                 => [ self::F_EXT => ['wav'],               self::F_ALT => 'WAV',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-music fs-2"></i>' ],
        'image/apng'                                                                => [ self::F_EXT => ['apng'],              self::F_ALT => 'APNG',    self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-image fs-2"></i>' ],
        'image/avif'                                                                => [ self::F_EXT => ['avif'],              self::F_ALT => 'AVIF',    self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-image fs-2"></i>' ],
        'image/bmp'                                                                 => [ self::F_EXT => ['bmp'],               self::F_ALT => 'BMP',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-image fs-2"></i>' ],
        'image/gif'                                                                 => [ self::F_EXT => ['gif'],               self::F_ALT => 'GIF',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-gif fs-2"></i>' ],
        'image/jpeg'                                                                => [ self::F_EXT => ['jpg', 'jpeg'],       self::F_ALT => 'JPEG',    self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-jpg fs-2"></i>' ],
        'image/png'                                                                 => [ self::F_EXT => ['png'],               self::F_ALT => 'PNG',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-png fs-2"></i>' ],
        'image/svg+xml'                                                             => [ self::F_EXT => ['svg'],               self::F_ALT => 'SVG',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-svg fs-2"></i>' ],
        'image/tiff'                                                                => [ self::F_EXT => ['tiff', 'tif'],       self::F_ALT => 'TIFF',    self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-image fs-2"></i>' ],
        'image/webp'                                                                => [ self::F_EXT => ['webp'],              self::F_ALT => 'WebP',    self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-webp fs-2"></i>' ],
        'image/x-icon'                                                              => [ self::F_EXT => ['ico'],               self::F_ALT => 'ICO',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-ico fs-2"></i>' ],
        'text/css'                                                                  => [ self::F_EXT => ['css'],               self::F_ALT => 'CSS',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-css fs-2"></i>' ],
        'text/csv'                                                                  => [ self::F_EXT => ['csv'],               self::F_ALT => 'CSV',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-csv fs-2"></i>' ],
        'text/html'                                                                 => [ self::F_EXT => ['html', 'htm'],       self::F_ALT => 'HTML',    self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-html fs-2"></i>' ],
        'text/plain'                                                                => [ self::F_EXT => ['txt'],               self::F_ALT => 'TXT',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-txt fs-2"></i>' ],
        'video/3gpp'                                                                => [ self::F_EXT => ['3gp'],               self::F_ALT => '3GP',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-play fs-2"></i>' ],
        'video/mp4'                                                                 => [ self::F_EXT => ['mp4'],               self::F_ALT => 'MP4',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-mp4 fs-2"></i>' ],
        'video/mpeg'                                                                => [ self::F_EXT => ['mpeg', 'mpg'],       self::F_ALT => 'MPEG',    self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-play fs-2"></i>' ],
        'video/ogg'                                                                 => [ self::F_EXT => ['ogv'],               self::F_ALT => 'OGV',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-play fs-2"></i>' ],
        'video/webm'                                                                => [ self::F_EXT => ['webm'],              self::F_ALT => 'WebM',    self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-filetype-webm fs-2"></i>' ],
        'video/x-flv'                                                               => [ self::F_EXT => ['flv'],               self::F_ALT => 'FLV',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-play fs-2"></i>' ],
        'video/x-msvideo'                                                           => [ self::F_EXT => ['avi'],               self::F_ALT => 'AVI',     self::F_BROWSABLE => 1, self::F_ICON => '<i class="bi bi-file-play fs-2"></i>' ],
    ];



    /**
     * Возвращает отсортированный массив расширений без пустых и повторяющихся записей.
     * Если $browsable > 0, то выводятся только расширения с 'browser' > 0.
     * @param int|null $browsable
     * @return array
     */
    public static function get_ext_list(int|null $browsable = null): array {
        $extensions = [];

        foreach (self::MIME_TYPES as $type => $info) {
            if (is_null($browsable) || (!empty($info[self::F_BROWSABLE]) && $browsable > 0)) {
                $extensions = array_merge($extensions, $info[self::F_EXT]);
            }
        }

        $extensions = array_filter($extensions); // убираем пустые записи
        $extensions = array_unique($extensions); // убираем повторяющиеся записи
        sort($extensions, SORT_STRING | SORT_FLAG_CASE); // сортируем по алфавиту
        return $extensions;
    }



    /**
     * Возвращает список MIME_TYPE по указанному расширению
     * или полный список, если расширение не указано.
     * Если $browsable > 0, то фильтруются только браузерные MIME.
     *
     * @param string|null $ext
     * @param int|null $browsable
     * @return array
     */
    public static function get_mime_types(string|null $ext = null, int|null $browsable = null): array {
        $result = [];

        foreach (self::MIME_TYPES as $mimeType => $info) {
            if (!is_null($browsable) && (empty($info[self::F_BROWSABLE]) || $info[self::F_BROWSABLE] == $browsable)) {
                continue; // отбрасываем не браузерные
            }

            if (is_null($ext)) {
                $result[] = $mimeType;
            } else {
                $ext = strtolower($ext);
                if (in_array($ext, array_map('strtolower', $info[self::F_EXT]))) {
                    $result[] = $mimeType;
                }
            }
        }

        return $result;
    }



    /**
     * Возвращает первый MIME_TYPE по указанному расширению.
     * Если найдено несколько — берётся первый.
     * @param string $ext
     * @return string
     */
    public static function get_mime_type(string $ext): string {
        if (!empty($ext)) {
            $ext = strtolower($ext);
            foreach (self::MIME_TYPES as $mimeType => $info) {
                if (in_array($ext, array_map('strtolower', $info[self::F_EXT]))) {
                    return $mimeType;
                }
            }
        }
        return '';
    }



    /**
     * Возвращает иконку из таблицы MIME-типов
     * @param string|null $ext
     * @param string|null $mimetype
     * @return string
     */
    public static function get_icon(?string $ext = null, ?string $mimetype = null): string {
        if ($ext !== null) {
            $mimetype = self::get_mime_type(strtolower($ext ?? ''));
            $mimetype = self::get_mime_type($ext ?? '');

        }

        if ($mimetype !== null && isset(self::MIME_TYPES[$mimetype])) {
            return self::MIME_TYPES[$mimetype][self::F_ICON] ?? '';
        }
        return '';
    }


}