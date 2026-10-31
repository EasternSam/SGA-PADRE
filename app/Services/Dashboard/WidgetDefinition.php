<?php

namespace App\Services\Dashboard;

use InvalidArgumentException;

/**
 * Describe un widget del dashboard: quién lo ve, qué feature del plan requiere,
 * qué tamaños admite y qué clase calcula sus datos.
 */
final class WidgetDefinition
{
    /**
     * @param  list<string>  $roles  Roles de Spatie que pueden ver el widget.
     * @param  list<string>  $sizes  Subconjunto de WidgetRegistry::SIZES.
     * @param  class-string<Contracts\WidgetDataProvider>|null  $provider
     */
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $description,
        public readonly string $category,
        public readonly array $roles,
        public readonly array $sizes,
        public readonly string $defaultSize,
        public readonly ?string $feature = null,
        public readonly ?string $provider = null,
    ) {
        if ($key === '' || ! preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
            throw new InvalidArgumentException("Clave de widget inválida: '{$key}'.");
        }

        $unknown = array_diff($sizes, WidgetRegistry::SIZES);
        if ($sizes === [] || $unknown !== []) {
            throw new InvalidArgumentException("El widget '{$key}' declara tamaños inválidos.");
        }

        if (! in_array($defaultSize, $sizes, true)) {
            throw new InvalidArgumentException("El tamaño por defecto de '{$key}' no está entre sus tamaños permitidos.");
        }
    }

    public function allowsSize(mixed $size): bool
    {
        return is_string($size) && in_array($size, $this->sizes, true);
    }

    /** @param  list<string>  $roles */
    public function visibleTo(array $roles): bool
    {
        return array_intersect($this->roles, $roles) !== [];
    }

    /**
     * Datos públicos para el cliente. No expone roles, feature ni provider.
     *
     * @return array{key: string, title: string, description: string, category: string, sizes: list<string>, defaultSize: string}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'sizes' => array_values($this->sizes),
            'defaultSize' => $this->defaultSize,
        ];
    }
}
