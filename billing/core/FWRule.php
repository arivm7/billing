<?php
/**
 *  Project : my.ri.net.ua
 *  File    : FWRule.php
 *  Path    : billing/core/FWRule.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 May 2026 22:32:46
 *  License : GPL v3
 *
 *  Copyright (C) 2026 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of FWRule.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */




namespace billing\core;

/**
 * Description of FWRule
 *
 * @author ar
 */
class FWRule {

    public function __construct(
        public string $table,
        public string $chain,
        public string $action,
        public array $params = [],
    ) {}

    public function get(string $key): mixed
    {
        return $this->params[$key] ?? null;
    }

    public function set(string $key, mixed $value): static
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function in(string $key): bool
    {
        return array_key_exists($key, $this->params);
    }

    public function equals(FWRule $rule): bool
    {
        return
            $this->table === $rule->table
            && $this->chain === $rule->chain
            && $this->action === $rule->action
            && $this->params == $rule->params;
    }

    public function comment(): ?string
    {
        return $this->get('comment');
    }


}