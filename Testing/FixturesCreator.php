<?php

namespace Modera\FoundationBundle\Testing;

/**
 * Create fixture with all non relations field filled.
 *
 * @author    Alex Plaksin <alex.plaksin@modera.net>
 * @copyright 2015 Modera Foundation
 */
class FixturesCreator
{
    /**
     * Create Class instance from given FQDN entity name.
     *
     * 1. Check if class is ORM\Entity
     * 2. Get non relation fields list if setter exists
     * 3. Fill them
     *
     * @param string $className
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public static function createFixture($className, $index = 1)
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('No such(%) class name ', $className));
        }

        $reflectionClass = new \ReflectionClass($className);
        $docComment = $reflectionClass->getDocComment();

        /*
         * if class docComment has
         * @ORM\Entity and @ORM\Table doc blocks all is ok
         */
        if (!preg_match('/@ORM\\\Entity/i', $docComment) || !preg_match('/@ORM\\\Table\(/i', $docComment)) {
            throw new \InvalidArgumentException(sprintf('Given class(%s) is not Doctrine Entity ', $className));
        }

        /* @var string[] */;
        $propertyNames = [];

        /*
         * Grabbing all class properties that have @ORM\Column in docComment
         */
        foreach ($reflectionClass->getProperties() as $property) {
            if (preg_match('/@ORM\\\Column/', $property->getDocComment())) {
                if ($reflectionClass->hasMethod(static::getSetterName($property->getName()))) {
                    $propertyNames[] = $property->getName();
                }
            }
        }

        $resultClass = new $className();
        foreach ($propertyNames as $propertyName) {
            $resultClass->{static::getSetterName($propertyName)}($propertyName.$index);
        }

        return $resultClass;
    }

    protected static function getSetterName($propertyName)
    {
        return 'set'.ucfirst($propertyName);
    }
}
