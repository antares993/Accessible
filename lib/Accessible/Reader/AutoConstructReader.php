<?php

namespace Accessible\Reader;

use \Accessible\Configuration;

class AutoConstructReader extends Reader
{
    /**
     * The name of the annotation class that define the construct arguments.
     *
     * @var string
     */
    private static $constructAnnotationClass = "Accessible\\Annotation\\Construct";

    /**
     * The name of the annotation class that define a property's default value.
     *
     * @var string
     */
    private static $initializeAnnotationClass = "Accessible\\Annotation\\Initialize";

    /**
     * The name of the annotation class that define the initial value of an object property.
     *
     * @var string
     */
    private static $initializeObjectAnnotationClass = "Accessible\\Annotation\\InitializeObject";

    /**
     * Get the list of needed arguments for given object's constructor.
     *
     * @param object $object The object to analyze.
     *
     * @return array The list of arguments.
     */
    public static function getConstructArguments($object)
    {
        $reflectionObject = new \ReflectionObject($object);
        $cacheId = md5("constructArguments:" . $reflectionObject->getName());

        $arrayCache = Configuration::getArrayCache();
        if ($arrayCache->contains($cacheId)) {
            return $arrayCache->fetch($cacheId);
        }

        $cacheDriver = Configuration::getCacheDriver();
        if ($cacheDriver !== null) {
            $constructArguments = $cacheDriver->fetch($cacheId);
            if ($constructArguments !== false) {
                $arrayCache->save($cacheId, $constructArguments);
                return $constructArguments;
            }
        }

        $constructArguments = null;
        $annotationReader = Configuration::getAnnotationReader();
        $objectClasses = self::getClassesToRead($reflectionObject);
        array_reverse($objectClasses);

        foreach ($objectClasses as $class) {
            $annotation = $annotationReader->getClassAnnotation($class, self::$constructAnnotationClass);
            if ($annotation !== null) {
                $constructArguments = $annotation->getArguments();
                break;
            }
        }

        $arrayCache->save($cacheId, $constructArguments);
        if ($cacheDriver !== null) {
            $cacheDriver->save($cacheId, $constructArguments);
        }

        return $constructArguments;
    }

    /**
     * Get the list of properties that have to be initialized automatically
     * during the object construction, plus their value.
     *
     * @param object $object The object to analyze.
     *
     * @return array The list of properties and values,
     *               in the form ["property" => "value"].
     */
    public static function getPropertiesToInitialize($object)
    {
        $reflectionObject = new \ReflectionObject($object);
        $cacheId = md5("propertiesToInitialize:" . $reflectionObject->getName());

        $arrayCache = Configuration::getArrayCache();
        if ($arrayCache->contains($cacheId)) {
            return $arrayCache->fetch($cacheId);
        }

        $cacheDriver = Configuration::getCacheDriver();
        if ($cacheDriver !== null) {
            $propertiesValues = $cacheDriver->fetch($cacheId);
            if ($propertiesValues !== false) {
                $arrayCache->save($cacheId, $propertiesValues);
                return $propertiesValues;
            }
        }

        $annotationReader = Configuration::getAnnotationReader();
        $objectClasses = self::getClassesToRead($reflectionObject);
        array_reverse($objectClasses);

        $propertiesValues = array();

        foreach ($objectClasses as $class) {
            foreach ($class->getProperties() as $property) {
                $propertyName = $property->getName();
                $initializeAnnotation = $annotationReader->getPropertyAnnotation($property, self::$initializeAnnotationClass);
                $initializeObjectAnnotation = $annotationReader->getPropertyAnnotation($property, self::$initializeObjectAnnotationClass);

                if ($initializeAnnotation !== null && $initializeObjectAnnotation !== null) {
                    throw new \LogicException("Two initial values are given for property $propertyName.");
                }

                if (empty($propertiesValues[$propertyName])) {
                    if ($initializeAnnotation !== null) {
                        $propertiesValues[$propertyName] = $initializeAnnotation->getValue();
                    } else if ($initializeObjectAnnotation !== null) {
                        $className = $initializeObjectAnnotation->getClassName();
                        $propertiesValues[$propertyName] = new $className();
                    }
                }
            }
        }

        $arrayCache->save($cacheId, $propertiesValues);
        if ($cacheDriver !== null) {
            $cacheDriver->save($cacheId, $propertiesValues);
        }

        return $propertiesValues;
    }
}
