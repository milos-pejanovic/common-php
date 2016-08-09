<?php

/**
 * Created by PhpStorm.
 * User: milos.pejanovic
 * Date: 8/5/2016
 * Time: 9:13 AM
 */

use Common\Models\ModelClass;
use Common\Models\ModelProperty;
use Common\Models\ModelPropertyType;

class ModelPropertyTypeTest extends PHPUnit_Framework_TestCase {

    /**
     * @var ModelProperty[]
     */
    public $modelProperties;

    public function setUp() {
        $object = new stdClass();
        $object->a = 1;

        $model = new TestModel();
        $model->noType = null;
        $model->boolTrue = true;
        $model->boolFalse = false;
        $model->string = 'a';
        $model->namedString = 'named';
        $model->integer = 5;
        $model->array = [1,'a',3];
        $model->stringArray = ['a','b','c'];
        $model->integerArray = [1,2,3];
        $model->booleanArray = [true,true,false];
        $model->objectArray = [$object,$object,$object];
        $model->object = $object;
        $model->requiredString = 'requiredString';
        $model->alwaysRequiredBoolean = false;
        $model->multipleRequiredInteger = 5;
        $nestedModel = clone $model;
        $model->model = $nestedModel;
        $model->modelArray = [$nestedModel,$nestedModel];

        $modelClass = new ModelClass($model);
        $this->modelProperties = $modelClass->getProperties();
        parent::setUp();
    }

    /**
     * @param $index
     * @param $isModel
     * @dataProvider validValues
     */
    public function testIsModel($index, $isModel) {
        $expected = $this->modelProperties[$index]->getType()->isModel();
        $this->assertEquals($expected, $isModel);
    }

    /**
     * @param $index
     * @param $isModel
     * @param $propertyType
     * @dataProvider validValues
     */
    public function testGetPropertyType($index, $isModel, $propertyType) {
        $expected = $this->modelProperties[$index]->getType()->getPropertyType();
        $this->assertEquals($expected, $propertyType);
    }

    /**
     * @param $index
     * @param $isModel
     * @param $propertyType
     * @param $annotatedType
     * @dataProvider validValues
     */
    public function testGetAnnotatedType($index, $isModel, $propertyType, $annotatedType) {
        $expected = $this->modelProperties[$index]->getType()->getAnnotatedType();
        $this->assertEquals($expected, $annotatedType);
    }

    /**
     * @param $index
     * @param $isModel
     * @param $propertyType
     * @param $annotatedType
     * @param $actualType
     * @dataProvider validValues
     */
    public function testGetActualType($index, $isModel, $propertyType, $annotatedType, $actualType) {
        $expected = $this->modelProperties[$index]->getType()->getActualType();
        $this->assertEquals($expected, $actualType);
    }

    /**
     * @param $index
     * @param $isModel
     * @param $propertyType
     * @param $annotatedType
     * @param $actualType
     * @param $modelClassName
     * @dataProvider validValues
     */
    public function testGetModelClassName($index, $isModel, $propertyType, $annotatedType, $actualType, $modelClassName) {
        if($isModel) {
            $expected = $this->modelProperties[$index]->getType()->getModelClassName();
            $this->assertEquals($expected, $modelClassName);
        }
    }

    public function validValues() {
        return [
            [0, false, 'NULL', 'NULL', 'NULL', ''],
            [1, false, 'boolean', 'boolean', 'boolean', ''],
            [2, false, 'boolean', 'boolean', 'boolean', ''],
            [3, false, 'string', 'string', 'string', ''],
            [4, false, 'string', 'string', 'string', ''],
            [5, false, 'integer', 'integer', 'integer', ''],
            [6, false, 'array', 'array', 'array', ''],
            [7, false, 'array', 'string[]', 'array', ''],
            [8, false, 'array', 'integer[]', 'array', ''],
            [9, false, 'array', 'boolean[]', 'array', ''],
            [10, false, 'array', 'object[]', 'array', ''],
            [11, false, 'object', 'object', 'object', ''],
            [12, true, 'object', 'TestModel', 'object', '\TestModel'],
            [13, true, 'array', 'TestModel[]', 'array', '\TestModel'],
            [14, false, 'string', 'string', 'string', ''],
            [15, false, 'boolean', 'boolean', 'boolean', ''],
            [16, false, 'integer', 'integer', 'integer', '']
        ];
    }
}