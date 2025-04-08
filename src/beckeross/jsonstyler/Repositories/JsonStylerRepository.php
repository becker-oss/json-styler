<?php

namespace beckeross\jsonstyler\Repositories;

class JsonStylerRepository
{
    protected array $themes = [];
    protected string $currentTheme = 'default';

    public function loadThemes(array|null $themes = null): void
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
        $fallback = [
            'braces' => 'darkorange',
            'brackets' => 'darkgreen',
            'keys' => 'purple',
            'values' => 'darkgreen',
            'background'=> 'bg-white',
            'custom_keywords' => [
                'ERROR' => 'red',
                'issues' => 'red',
            ]
        ];

        $theme = $this->themes[$this->currentTheme] ?? $this->themes['default'] ?? [];

        $cleanUp = function ($value) {
            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        $recursiveCleanUp = function ($array) use (&$recursiveCleanUp, $cleanUp) {
            $cleaned = [];
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $cleaned[$key] = $recursiveCleanUp($value);
                } else {
                    $cleaned[$key] = is_string($value) ? $cleanUp($value) : $value;
                }
            }
            return $cleaned;
        };

        $merged = array_merge($fallback, $recursiveCleanUp($theme));

        if (isset($theme['custom_keywords']) && is_array($theme['custom_keywords'])) {
            foreach ($theme['custom_keywords'] as $word => $color) {
                $merged['custom_keywords'][$word] = $cleanUp($color);
            }
        }

        return $merged;
    }

    public function formatJson(array|string $data): string
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                return "<pre><code class='json'>Invalid JSON: " . htmlspecialchars(json_last_error_msg()) . "</code></pre>";
            }
            $data = $decoded;
        }

        if ($data === null) {
            return "<pre><code class='json'>No data available.</code></pre>";
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $json = preg_replace_callback('/"([^"]+)":\s*"([^"]+)"/', function($matches) {
            return '"' . $matches[1] . '": "' . htmlspecialchars($matches[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
        }, $json);
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
