<?php

declare(strict_types=1);

namespace Dev\ViewBundle\Serializer\Normalizer;

use Dev\ViewBundle\View\ViewInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ViewNormalizer extends ObjectNormalizer
{
    public const VIEW_KEY = '__type__';

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        $normalized[self::VIEW_KEY] = $object::$__id__ ?? self::fetchId($object);
        return \array_merge($normalized, \array_filter(parent::normalize($object, $format, $context), static fn(mixed $v) => null !== $v));
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof ViewInterface;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }

    public function getSupportedTypes(string|null $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return false;
    }

    private static function fetchId(object $object): string
    {
        $strings = \explode("\\", \get_class($object));
        $view = \end($strings);
        $namespace = \current(\array_filter($strings, static fn(string $v) => !\in_array($v, ['Api', 'View', $view]))) ?? null;
        return $namespace ? $namespace . '\\' . $view : $view;
    }
}