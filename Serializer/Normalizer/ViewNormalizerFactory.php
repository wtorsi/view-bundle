<?php

declare(strict_types=1);

namespace Dev\ViewBundle\Serializer\Normalizer;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

readonly class ViewNormalizerFactory
{
    private const CACHE_NAMESPACE = 'view_normalizer';
    private const CACHE_LIFETIME = 24 * 3600;

    public function __construct(
        private PropertyTypeExtractorInterface $propertyTypeExtractor,
        private string $buildId = '',
        #[Autowire('%env(bool:APP_DEBUG)%')]
        private bool $debug = false
    )
    {
    }

    public function create(): ObjectNormalizer
    {
        return new ViewNormalizer(
            null,
            null,
            $this->buildPropertyAccessor(),
            $this->propertyTypeExtractor
        );
    }

    private function buildPropertyAccessor(): PropertyAccessor
    {
        return new PropertyAccessor(
            ReflectionExtractor::DISALLOW_MAGIC_METHODS,
            PropertyAccessor::THROW_ON_INVALID_INDEX | PropertyAccessor::THROW_ON_INVALID_PROPERTY_PATH,
            PropertyAccessor::createCache(self::CACHE_NAMESPACE, $this->debug ? 0 : self::CACHE_LIFETIME, $this->buildId),
            new ReflectionExtractor([], null, ['-', '-'], false, ReflectionExtractor::ALLOW_PUBLIC, null, ReflectionExtractor::DISALLOW_MAGIC_METHODS),
            // writer is not used, disable all
            new ReflectionExtractor([], [], ['-', '-'], false, ReflectionExtractor::ALLOW_PUBLIC, null, ReflectionExtractor::DISALLOW_MAGIC_METHODS)
        );
    }
}