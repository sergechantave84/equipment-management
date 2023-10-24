<?php

namespace App\Helper;

use Doctrine\Common\Annotations\AnnotationReader;
use Psr\Container\ContainerInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Utils
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Serializer
     */
    public static function getJsonSerializer()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $objectNormalizer = new ObjectNormalizer(
            $classMetadataFactory,
            $metadataAwareNameConverter,
            null,
            new ReflectionExtractor()
        );
        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            $objectNormalizer,
        ];
        $encoders = [new JsonEncoder()];

        return new Serializer($normalizers, $encoders);
    }

    /**
     * Undocumented function
     *
     * @param array $groups
     * @return array
     */
    public static function setContext(array $groups): array
    {
        $context = [];
        $context['circular_reference_handler'] = function ($object) {
            return $object->getId();
        };
        $context['groups'] = $groups;

        return $context;
    }

    public static function generateuuidv4(int $lengthData)
    {
        $data = openssl_random_pseudo_bytes($lengthData);
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
