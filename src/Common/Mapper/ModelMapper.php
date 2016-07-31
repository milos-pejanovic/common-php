<?php
/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 3/29/2016
 * Time: 9:27 AM
 */

namespace Common\Mapper;
use Common\Models\ModelClass;
use Common\Models\ModelPropertyType;
use Common\Util\Validation;
use Common\Util\Iteration;

class ModelMapper implements IModelMapper {

	/**
	 * @param object $source
	 * @param object $model
	 * @return object
	 * @throws \InvalidArgumentException
	 */
	public function map($source, $model) {
		if(!is_object($source) || Validation::isEmpty($source)) {
			throw new \InvalidArgumentException('Source must be an object with properties.');
		}
        if(!is_object($model) || Validation::isEmpty($model)) {
            throw new \InvalidArgumentException('Model must be an object with properties.');
        }
		$modelClass = new ModelClass($model);

        $rootName = $modelClass->getRootName();
		if(Validation::hasRoot($source, $rootName)) {
			$source = $source->{$modelClass->getRootName()};
		}

		foreach($modelClass->getProperties() as $property) {
			$sourceValue = Iteration::findValueByName($property->getName(), $source, $property->getPropertyValue());
			$mappedValue = $this->mapValueByType($property->getType(), $sourceValue);
			$property->setPropertyValue($mappedValue);
		}

		return $model;
	}

	/**
	 * @param ModelPropertyType $propertyType
	 * @param mixed $value
	 * @return mixed
	 */
	protected function mapValueByType(ModelPropertyType $propertyType, $value) {
		$mappedPropertyValue = $value;

		if($propertyType->isModel()) {
			if($propertyType->getActualType() == 'array' && is_array($value)) {
				$mappedPropertyValue = $this->mapModelArray($propertyType->getModelClassName(), $value);
			}

			elseif($propertyType->getActualType() == 'object' && is_object($value)) {
				$mappedPropertyValue = $this->mapModel($propertyType->getModelClassName(), $value);
			}
		}

		return $mappedPropertyValue;
	}

	/**
	 * @param string $modelClassName
	 * @param array $source
	 * @return array
	 */
	protected function mapModelArray(string $modelClassName, array $source) {
		$mappedModelArray = null;
		foreach($source as $key => $value) {
//			$mappedModelArray[$key] = $value;
			if(is_object($value)) {
				$mappedModelArray[$key] = $this->mapModel($modelClassName, $value);
			}
		}

		return $mappedModelArray;
	}

	/**
	 * @param string $modelClassName
	 * @param object $source
	 * @return object
	 */
    protected function mapModel(string $modelClassName, $source) {
		$model = new $modelClassName();
		$mappedModel = $this->map($source, $model);

		return $mappedModel;
	}

	/**
	 * @param object $model
	 * @return \stdClass
	 * @throws \InvalidArgumentException
	 */
	public function unmap($model) {
		if(!is_object($model) || Validation::isEmpty($model)) {
			throw new \InvalidArgumentException('Model must be an object with properties.');
		}

		$modelClass = new ModelClass($model);
		$unmappedObject = new \stdClass();
		foreach($modelClass->getProperties() as $property) {
			$propertyKey = $property->getName();
			$propertyValue = $property->getPropertyValue();
			if(Validation::isEmpty($propertyValue)) {
				continue;
			}
			$unmappedObject->$propertyKey = $this->unmapValueByType($property->getType(), $propertyValue);
		}

		if(!Validation::isEmpty($modelClass->getRootName())) {
			$unmappedObject = $this->addRootElement($unmappedObject, $modelClass->getRootName());
		}

		return $unmappedObject;
	}

	/**
	 * @param ModelPropertyType $propertyType
	 * @param mixed $value
	 * @return mixed
	 */
	protected function unmapValueByType(ModelPropertyType $propertyType, $value) {
		$unmappedPropertyValue = $value;

		if($propertyType->isModel()) {
			if($propertyType->getActualType() == 'array' && is_array($value)) {
				$unmappedPropertyValue = $this->unmapModelArray($value);
			}

			elseif($propertyType->getActualType() == 'object' && is_object($value)) {
				$unmappedPropertyValue = $this->unmapModel($value);
			}
		}

		return $unmappedPropertyValue;
	}

	/**
	 * @param array $modelArray
	 * @return array
	 */
	protected function unmapModelArray(array $modelArray) {
		$unmappedObjectArray = [];
		foreach($modelArray as $k => $v) {
			$unmappedObjectArray[$k] = $this->unmapModel($v);
		}

		return $unmappedObjectArray;
	}

	/**
	 * @param object $model
	 * @return object
	 */
	protected function unmapModel($model) {
		$unmappedObject = $this->unmap($model);

		return $unmappedObject;
	}

	/**
	 * @param $object
	 * @param string $rootName
	 * @return \stdClass
	 */
	protected function addRootElement($object, string $rootName) {
		$newObject = new \stdClass();
		$newObject->$rootName = $object;

		return $newObject;
	}
}