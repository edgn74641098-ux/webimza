<?php

namespace App\Services;

class PlaceholderService
{
    public function render(string $template, array $data): string
    {
        $template = preg_replace_callback('/\{\{#if\s+([A-Za-z0-9_]+)\}\}(.*?)\{\{\/if\}\}/s', function ($matches) use ($data) {
            $key = $matches[1];
            return filled($data[$key] ?? null) ? $matches[2] : '';
        }, $template) ?? $template;

        foreach ($data as $key => $value) {
            $template = str_replace('{{'.$key.'}}', (string) ($value ?? ''), $template);
        }

        return preg_replace('/\{\{[A-Za-z0-9_]+\}\}/', '', $template) ?? $template;
    }
}
