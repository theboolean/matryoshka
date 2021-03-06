# Overview

- [Objects](Overview.md#objects)
- [Models](Overview.md#models)
- [Criterias](Overview.md#criterias)

## Objects
Matryoshka does not impose you to use a specific kind of domain object nor it requires you to extend an abstract class provided by the library.

**Matryoshka let you choose how your object will be**.

You may encounter one of the following situations.

#### Array like objects
When you do not need structured objects you can simply use `ArrayObject` as base class for them. In this case, you have to use the [ArrayObjectResultSet](../library/ResultSet/ArrayObjectResultSet.php) class.

#### Objects with public properties
If you want to use something like:
```php
class MyDomainObject
{
   public $id;
   public $foo;
}
```
you have to use the [HydratingResultSet](../library/ResultSet/HydratingResultSet.php) class with the [ObjectProperty](https://github.com/zendframework/zend-stdlib/blob/master/src/Hydrator/ObjectProperty.php) hydrator.

#### Structured objects
When you want to use full structured objects with setter/getter methods, you have to use the [HydratingResultSet](../library/ResultSet/HydratingResultSet.php) class with the [ClassMethods](../library/Hydrator/ClassMethods.php) hydrator.
We highly recommend to use this solution, better if in combination with a well defined interface.

### Other features
Optionally you can add one or more of the following interfaces to your object classes and Matryoshka will use them to automatise some processes:
- [HydratingAwareInterface](https://github.com/zendframework/zend-stdlib/blob/master/src/Hydrator/HydratorAwareInterface.php) allows object to define its own hydrator, Matryoshka will use it when needed.
- [InputFilterAwareInterface](https://github.com/zendframework/zend-inputfilter/blob/master/src/InputFilterAwareInterface.php) allows object to define its own input filter, Matryoshka will use it when needed.
- [ModelAwareInterface](../library/ModelAwareInterface.php) allows object to use its own model class, Matryoshka will inject its instance.
- [ActiveRecordInterface](../library/Object/ActiveRecord/ActiveRecordInterface.php) adds the ability to save/delete object by using the object instance itself.

Furthermore, you can also use the [ObjectManager](../library/Object/ObjectManager.php) to get your object instances. It is a dedicated [service locator](https://github.com/zendframework/zend-servicemanager/blob/master/src/ServiceLocatorInterface.php) that allows you to register and to factory your objects. The default [abstract factory](../library/Object/Service/ObjectAbstractServiceFactory.php) will also inject dependencies of your objects. Configurations [here](Configuration.md#objects).

## Models

### How it works
The core of Matryoshka is the model service concept: a class implementing both the [ModelInterface](../library/ModelInterface.php) (the contract for any end user service that manages a collection of related data providing the same set of handful API to clients) and the [ModelStubInterface](../library/ModelStubInterface.php) (the contract for model stub objects that allow access to persistence related services such as the datagateway).

So, Matryoshka requires you implement concrete [criterias](Criterias) (those strictly related to your application business logic) which act on the layer of persistence through the use of the [ModelStubInterface](../library/ModelStubInterface.php). The model service (that implements  [ModelStubInterface](../library/ModelStubInterface.php)) passes its instance to the criteria objects when used with them.

On the other hand, when you use a model service in your application, you can pass [criterias](Criterias) objects to the model service (that implements [ModelInterface](../library/ModelInterface.php)) in order to perform an operation on the data.

### How to use a model service
From a consumer point of view, operations that can be performed on a model service are defined by [ModelInterface](../library/ModelInterface.php).

#### Creation

```php
$newObject = clone $myModel->getObjectPrototype();
```
Alternatively, you can create your object using the [ObjectManager](../library/Object/ObjectManager.php).

```php
$newObject = $objectManager->get('MyObject');
```

Then, assuming `MyObjectCriteria` is a class that implements [WritableCriteriaInterface.php](../library/Criteria/WritableCriteriaInterface.php) you can save your object:

```php
$myModel->save(new MyObjectCriteria, $newObject);
```

#### Reading
In order to fetch objects you need to use a [Criteria](Overview.md#Criterias). Assuming `MyCollectionCriteria` is a class extending [AbstractCriteria](../library/Criteria/AbstractCriteria.php) that also implements [ReadableCriteriaInterface.php](../library/Criteria/ReadableCriteriaInterface.php).


Reading multiple objects:

```php
$resultSet = $myModel->find((new MyCollectionCriteria)->setOffset(10)->setLimit(10));

foreach($resultSet as $object) {
    // your $object is here!
}
```


Reading a single object:

```php
$object = $myModel->find((new MyCollectionCriteria)->setId(1)->setLimit(1))->current();
```

##### Pagination

Furthermore if `MyCollectionCriteria` implements [PaginableCriteriaInterface.php](../library/Criteria/PaginableCriteriaInterface.php) you can get a paginator adapter that can be used with [Zend\Paginator](http://framework.zend.com/manual/current/en/modules/zend.paginator.introduction.html):

```php
$paginatorAdapter = $myModel->getPaginatorAdapter(new MyCollectionCriteria);
$paginator = new Zend\Paginator\Paginator($paginatorAdapter);
```

#### Updating

Assuming `$object` is an already fetched object and `MyObjectCriteria` is a class that implements [WritableCriteriaInterface.php](../library/Criteria/WritableCriteriaInterface.php) you can update your object:

```php
$myModel->save(new MyObjectCriteria, $object);
```

#### Deleting 

Assuming `MyObjectCriteria` is a class that implements [DeletableCriteriaInterface.php](../library/Criteria/DeletableCriteriaInterface.php) that allows you to delete objects by id:

```php
$myModel->delete((new MyObjectCriteria)->setId(11));
```


### Default model class

Matryoshka provides a ready-to-use implementation of model service: the [Model](../library/Model.php) class. It can be used just [configuring the model manager](Configuration.md#models). 

Furthermore, you can extend the [Model](../library/Model.php) class adding your own business logics and customisations. Also we suggest to extend it always with a simple placeholder class just for typing purpose:

```php
use Matryoshka\Model\Model;

final class MyModel extend Model 
{
}
```

### Event-driven Model class

Another way to extend the behaviour of a model service is by using the [ObservableModel](../library/ObservableModel.php): an event-driven extension of the [Model](../library/Model.php) class.

This class allow you to attach listeners in order to observe or change the model behaviour without having to extend the base [Model](../library/Model.php) class.

It is implemented by composing the [EventManager](http://framework.zend.com/manual/current/en/modules/zend.event-manager.event-manager.html). It defines a set of pre/post events for each actions performed on the model, as defined inside the specialised [ModelEvent](../library/ModelEvent.php) class that represents the event, encapsulates the target context and parameters passed, and provides some behaviour for interacting with the event manager.

The [ObservableModel](../library/ObservableModel.php) can be easily enabled and listeners can be attached by [configuring the model manager](Configuration.md#models).  

### Other features

- Object and resultset prototypes: each model service implements the [ModelPrototypeInterface](../library/ModelPrototypeInterface.php) that defines getter methods for prototypes used by the service.

- [HydratingAwareInterface](https://github.com/zendframework/zend-stdlib/blob/master/src/Hydrator/HydratorAwareInterface.php):  when an hydrator is provided, the model service uses it to hydrate/extract data to/from the object in order to work with the persistence layer. Note that Matryoshka allows you to define different hydrator for models and for objects: model service hydrators will be used for persistence tasks, object hydrators for any other purpose. That give you the ability to define different hydration strategy depending the context.

- [InputFilterAwareInterface](https://github.com/zendframework/zend-inputfilter/blob/master/src/InputFilterAwareInterface.php): allows model services to define its own input filter

### Classes and interfaces diagram
![Alt text](https://cdn.rawgit.com/matryoshka-model/matryoshka/develop/docs/assets/images/modelservice-classdiagram.svg)


## Criterias

A criteria is a class implementing at least a [criteria interface](../library/Criteria/) that performs an operation on the dataset through the datagateway. Matryoshka in unaware about the datagateway interface, so the model service just pass datagateway instance to the criteria. This simple mechanism allows you to have direct control and the maximum level of flexibility over the queries performed on the datagateway.

At same time, to facilitate the criterias implementation, Matryoshka provides simple interfaces for each kind of operation that can be performed:
![Alt text](https://cdn.rawgit.com/matryoshka-model/matryoshka/develop/docs/assets/images/criteria-classdiagram.svg)

Each interfaces require just the implementation of one method that applies the operation to the model. 

> Note that a criteria class can implement more than one criteria interfaces at same time. However we recommend to make criteria as much simple as possible.

### The criteria flow

- The consumer (i.e. a controller) passes a configured criteria object to a model service method (one of those that [ModelInterface](../library/ModelInterface.php) defines, depending the kind of criteria)
- The model service method executes common tasks (i.e. hydrating/extracting data)
- The model service method calls the criteria passing a [ModelStubInterface](../library/ModelStubInterface.php) instance (the model service itself because it implements `ModelStubInterface` too)
- The criteria does its job and returns a result
- The model service method process the result (i.e. preparing the resultset) and returns it to the consumer

### Implementing criteria

Each [criteria interface](../library/Criteria/) defines an **apply** method that you have to implement.
**[WIP]**





