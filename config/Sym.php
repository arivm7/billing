<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : Sym.php
 *  Path    : config/Sym.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Псевдографические символы, используемы для красивого оформления
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



namespace config;


class Sym {

    // треугольник
    // 🞀⏴◀◁◂◃◄
    const CH_TRIANGLE_LEFT =    "🞀";
    const CH_TRIANGLE_RIGT =     "🞂";
    // 🞂🢒⊳⏵▶▷▸▹►⧐⮞⮞
    const CH_TRIANGLE =          "►";
    // ▼▲▾▴
    const CH_TRIANGLE_UP =       "▲";
    const CH_TRIANGLE_DOWN =     "▼";

    // ‐ ‑ ‒ –
    // — U+2014 EM DASH &#8212;
    // ⸺ − 𑁒 𑁓 𑁔
    // &ndash;	&#8211;	–	тире
    // &mdash;	&#8212;	—	длинное тире
    const CH_DASH =           "&mdash;";

    const CH_YES =            "&#9745;"; // |&#9745;|&#8864;|
    const CH_NO =             "&#9746;"; // |&#9746;|&#128505;|
    // ⏻    &#9211; U+23FB POWER SYMBOL
    // ⏼    &#9212; U+23FC POWER ON-OFF SYMBOL
    const CH_OFF =            "⏻"; //
    const CH_ON =             "⏼"; //
    // ⌦    &#8998; U+2326 ERASE TO THE RIGHT
    // ⌫    &#9003; U+232B ERASE TO THE LEFT
    const CH_ERASE_LEFT =     "&#9003;"; //
    // ⌧    &#8999; U+2327 X IN A RECTANGLE BOX      • clear key
    // ☒    &#9746; U+2612 BALLOT BOX WITH X
    // ⮽    &#11197;    U+2BBD BALLOT BOX WITH LIGHT X
    // ⮾    &#11198;    U+2BBE CIRCLED X
    // ⮿    &#11199;    U+2BBF CIRCLED BOLD X
    const CH_DELETE =         "⮿"; //

    // ⎘    &#9112;    U+2398 NEXT PAGE
    // ⎗    &#9111;    U+2397 PREVIOUS PAGE
    // ⎙    &#9113;    U+2399 PRINT SCREEN SYMBOL
    //      &#128221;   U+1F4DD MEMO
    // 🗈🗎🗏🗐
    // &#128466;    U+1F5D2 SPIRAL NOTE PAD
    // 🗟 &#128479;  U+1F5DF PAGE WITH CIRCLED TEXT
    const CH_ACTIVE =     "&#128479;"; // U+1F5DF PAGE WITH CIRCLED TEXT
    const CH_PAUSE =      "&#9208"; // U+23F8 DOUBLE VERTICAL BAR &#9208; | ‖ U+2016 DOUBLE VERTICAL LINE
    const CH_AGENT =      "&#9874;"; //
    const CH_CLIENT =     "⛁"; //
    const CH_VISIBLE =    "☀"; //
    const CH_INVISIBLE =  "⛭"; //
    //☍ &#9741;
    //⚭ &#9901;
    //⚮ &#9902;
    //⚯ &#9903;
    //⯺
    const CH_LINK =       "&#9741;";
    const CH_LINKED =     "<font color=green>".CH_LINK."</font>";
    const CH_LINKING =    CH_LINK;

    const CH_UAH =        "₴"; // ₴ &#8372;
    const CH_NUMERO =     "№"; // № U+2116 NUMERO SIGN &#8470; |
    // 🏱 &#127985; U+1F3F1 WHITE PENNANT
    // [RED FLAG] &#128681; U+1F6A9 TRIANGULAR FLAG ON POST
    const CH_FLAG =     "&#127985;";
    // ⯃ &#11203; U+2BC3 HORIZONTAL BLACK OCTAGON
    // [RED stop sign]  &#128721; U+1F6D1 OCTAGONAL SIGN
    const CH_STOP =     "&#11203;";
    // ⇒⇐⇦⇨⇽⇾←→↢↣↤↦⟸⟹
    const CH_ARROW_LF =     "⟸";
    const CH_ARROW_RG =     "⟹";
    const CH_COPYRIGHT_SIGN =  "©";
    const CH_REGISTERED_SIGN = "®";

    const CH_TRASH = "&#128465;";


}