<?php

namespace beckeross\jsonstyler\Repositories;

class JsonStylerRepository
{
    protected array $themes = [];
    protected string $currentTheme = 'default';

    public function loadThemes(array $themes = null): void
    {
        $this->themes = $themes ?? config('jsonstyler', []);
    }

    public function setTheme(string $theme): void
    {
        if (isset($this->themes[$theme])) {
            $this->currentTheme = $theme;
        }
    }

    public function getTheme(): array
    {
        return $this->themes[$this->currentTheme] ?? $this->themes['default'] ?? [];
    }

    public function formatJson(array|string $data): string
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $theme = $this->getTheme();

        $json = preg_replace('/([{}])/', "<span style='color: {$theme['braces']};'>$1</span>", $json);
        $json = preg_replace('/([\[\]])/', "<span style='color: {$theme['brackets']};'>$1</span>", $json);
        $json = preg_replace('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"(?=\s*:)/', "<span style='color: {$theme['keys']};'>\"$1\"</span>", $json);
        $json = preg_replace('/(:\s*)(-?\d+(\.\d+)?|true|false|null|"(?:[^"\\\\]|\\\\.)*?")/', '$1<span style="color: ' . $theme['values'] . ';">$2</span>', $json);

        if (!empty($theme['custom_keywords'])) {
            foreach ($theme['custom_keywords'] as $word => $color) {
                $json = preg_replace("/\b" . preg_quote($word, '/') . "\b/", "<span style='color: $color;'>$word</span>", $json);
            }
        }

        return "<pre><code class='json'>{$json}</code></pre>";
    }
}
