<?php



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
