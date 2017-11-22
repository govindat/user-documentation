<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */
namespace HHVM\UserDocumentation;

final class APINavData {
  private APIIndex $index;
  private function __construct(
    APIProduct $product,
  ) {
    $this->index = APIIndex::get($product);
  }

  <<__Memoize>>
  public static function get(APIProduct $product): this {
    return new self($product);
  }

  public function getNavData(): array<string, NavDataNode> {
    return [
      'Classes' => $this->getNavDataForClasses(APIDefinitionType::CLASS_DEF),
      'Interfaces' => $this->getNavDataForClasses(APIDefinitionType::INTERFACE_DEF),
      'Traits' => $this->getNavDataForClasses(APIDefinitionType::TRAIT_DEF),
      'Functions' => shape(
        'name' => 'Functions',
        'urlPath' => '/hack/reference/function/',
        'children' => $this->getNavDataForFunctions(),
      ),
    ];
  }

  public function getRootNameForType(
    APIDefinitionType $class_type,
  ): string {
    switch ($class_type) {
      case APIDefinitionType::CLASS_DEF:
        return 'Classes';
      case APIDefinitionType::INTERFACE_DEF:
        return 'Interfaces';
      case APIDefinitionType::TRAIT_DEF:
        return 'Traits';
      case APIDefinitionType::FUNCTION_DEF:
        return 'Functions';
    }
  }

  private function getNavDataForClasses(
    APIDefinitionType $class_type,
  ): NavDataNode {
    $nav_data = [];
    $classes = $this->index->getClassIndex($class_type);

    foreach ($classes as $class) {
      $nav_data[$class['name']] = shape(
        'name' => $class['name'],
        'urlPath' => $class['urlPath'],
        'children' => $this->getNavDataForMethods($class['methods']),
      );
    }
    return shape(
      'name' => $this->getRootNameForType($class_type),
      'urlPath' => '/hack/reference/'.$class_type.'/',
      'children' => $nav_data,
    );
  }

  private function getNavDataForMethods(
    array<string, APIMethodIndexEntry> $methods,
  ): array<string, NavDataNode> {
    $nav_data = [];
    foreach ($methods as $method) {
      $nav_data[$method['name']] = shape(
        'name' => $method['name'],
        'urlPath' => $method['urlPath'],
        'children' => [],
      );
    }
    return $nav_data;
  }

  private function getNavDataForFunctions(
  ): array<string, NavDataNode> {
    $functions = $this->index->getFunctionIndex();

    $nav_data = [];
    foreach ($functions as $function) {
      $nav_data[$function['name']] = shape(
        'name' => $function['name'],
        'urlPath' => $function['urlPath'],
        'children' => [],
      );
    }
    return $nav_data;
  }
}
