<?php
/**
 *  Project : my.ri.net.ua
 *  File    : FWAbonValidator.php
 *  Path    : billing/core/FWAbonValidator.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:32:46
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of FWAbonValidator.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




namespace billing\core;

use config\Mik;

/**
 * Основной валидатор
 *
 * @author ar
 */
class FWAbonValidator {

    private const RAW_RULE_META_KEYS = [
        '.id',
        'chain',
        'action',
        'bytes',
        'packets',
        'invalid',
        'dynamic',
        'disabled',
    ];

    private array $actualFilter = [];
    private array $actualNat = [];

    public function loadFilter(array $rules): void
    {
        $this->actualFilter = $this->normalizeRules($rules, 'filter');
    }

    public function loadNat(array $rules): void
    {
        $this->actualNat = $this->normalizeRules($rules, 'nat');
    }

    public function validate(): array
    {
        $errors = [];

        $errors = array_merge(
            $errors,
            $this->validateFilter()
        );

        $errors = array_merge(
            $errors,
            $this->validateNat()
        );

        return $errors;
    }

    private function validateFilter(): array
    {
        $expected = $this->buildFilterExpected();

        return $this->compareStrictOrder(
            $expected,
            $this->findAbonFilterBlock()
        );
    }

    private function validateNat(): array
    {
        $expected = $this->buildNatExpected();

        return $this->compareStrictOrder(
            $expected,
            $this->findAbonNatBlock()
        );
    }
    
    
    
    /*
     * Поиск блока
     */
    
    private function findAbonFilterBlock(): array
    {
        return array_values(array_filter(
            $this->actualFilter,
            fn(FWRule $r) =>
                str_starts_with(
                    (string)$r->comment(),
                    'ABON '
                )
        ));
    }

    private function findAbonNatBlock(): array
    {
        return array_values(array_filter(
            $this->actualNat,
            fn(FWRule $r) =>
                str_starts_with(
                    (string)$r->comment(),
                    'ABON '
                )
        ));
    }


    /**
     * Приводит список правил к FWRule[].
     * Поддерживает как уже собранные FWRule, так и сырые массивы RouterOS print.
     */
    private function normalizeRules(array $rules, string $table): array
    {
        $normalized = [];

        foreach ($rules as $rule) {
            if ($rule instanceof FWRule) {
                $normalized[] = $rule;
                continue;
            }

            if (!is_array($rule)) {
                continue;
            }

            $chain = trim((string) ($rule['chain'] ?? ''));
            $action = trim((string) ($rule['action'] ?? ''));

            if ($chain === '' || $action === '') {
                continue;
            }

            $params = [];
            foreach ($rule as $key => $value) {
                if (in_array($key, self::RAW_RULE_META_KEYS, true)) {
                    continue;
                }

                if ($value === '' || $value === null) {
                    continue;
                }

                if (is_string($value) && trim($value) === '') {
                    continue;
                }

                $params[$key] = $value;
            }

            $normalized[] = new FWRule(
                table: $table,
                chain: $chain,
                action: $action,
                params: $params
            );
        }

        return $normalized;
    }

    

    /*
     * Эталон filter
     * Строгий порядок.
     */
    
    private function buildFilterExpected(): array
    {
        return [

            new FWRule('filter','forward','accept',[
                'comment'=>FWAbon::COMMENT_ACCEPT,
                'in-interface-list'=>FWAbon::LAN_INTERFACE_LIST,
                'src-address-list'=>FWAbon::ABON_LIST
            ]),

            new FWRule('filter','forward','accept',[
                'comment'=>FWAbon::COMMENT_ACCEPT,
                'in-interface-list'=>FWAbon::WAN_INTERFACE_LIST,
                'dst-address-list'=>FWAbon::ABON_LIST
            ]),

            new FWRule('filter','forward','add-src-to-address-list',[
                'comment'=>FWAbon::COMMENT_LOG_LAN,
                'address-list'=>'_NO_ABON_LAN',
                'address-list-timeout'=>'1d',
                'in-interface-list'=>FWAbon::LAN_INTERFACE_LIST,
                'src-address-list'=>'!ABON'
            ]),

            new FWRule('filter','forward','drop',[
                'comment'=>FWAbon::COMMENT_DROP,
                'in-interface-list'=>FWAbon::LAN_INTERFACE_LIST,
                'src-address-list'=>'!ABON'
            ]),

            new FWRule('filter','forward','add-dst-to-address-list',[
                'comment'=>FWAbon::COMMENT_LOG_WAN,
                'address-list'=>'_NO_ABON_WAN',
                'address-list-timeout'=>'1d',
                'in-interface-list'=>FWAbon::WAN_INTERFACE_LIST,
                'dst-address-list'=>'!ABON'
            ]),

            new FWRule('filter','forward','drop',[
                'comment'=>FWAbon::COMMENT_DROP,
                'in-interface-list'=>FWAbon::WAN_INTERFACE_LIST,
                'dst-address-list'=>'!ABON'
            ]),

            new FWRule('filter','forward','add-src-to-address-list',[
                'comment'=>FWAbon::COMMENT_LOG_ALL,
                'address-list'=>'_NO_ABON_ALL',
                'address-list-timeout'=>'1d'
            ]),

            new FWRule('filter','forward','drop',[
                'comment'=>FWAbon::COMMENT_DROP
            ]),
        ];
    }

    

    /*
     * Эталон NAT
     */
    
    private function buildNatExpected(): array
    {
        return [

            new FWRule('nat','dstnat','redirect',[
                'comment'=>FWAbon::COMMENT_PROXY,
                'in-interface'=>'bridge_LAN',
                'protocol'=>'tcp',
                'src-address-list'=>'!ABON',
                'to-ports'=>FWAbon::PROXY_PORT
            ])
        ];
    }

    

    /*
     * Сравнение
     */
    
    private function compareStrictOrder(
        array $expected,
        array $actual
    ): array
    {
        $errors = [];

        if (count($expected) !== count($actual)) {
            $errors[] =
                'Rule count mismatch';
            return $errors;
        }

        foreach ($expected as $i => $rule) {

            if (!$rule->equals($actual[$i])) {
                $errors[] =
                    "Rule #{$i} mismatch";
            }
        }

        return $errors;
    }

    
    
    /*
     * Генерация repair script
     */

    public function repairScript(): array
    {
        $cmd = [];

        foreach ($this->buildFilterExpected() as $r) {
            $cmd[] = $this->toCLI($r);
        }

        foreach ($this->buildNatExpected() as $r) {
            $cmd[] = $this->toCLI($r);
        }

        return $cmd;
    }

    private function toCLI(FWRule $r): string
    {
        $p=[];

        foreach($r->params as $k=>$v){
            $p[]="$k=$v";
        }

        return sprintf(
            '/ip firewall %s add chain=%s action=%s %s',
            $r->table,
            $r->chain,
            $r->action,
            implode(' ', $p)
        );
    }
    
    
    
    
}