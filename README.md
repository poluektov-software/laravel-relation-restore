# Laravel Relation Restore

[Документация на русском](README-ru.md)

## Description

Laravel Relation Restore - Laravel package for deleting and restoring related models. 
This package uses the soft delete method. The package can be useful where you want to delete 
and restore models with complex chains of related models.

## Installation

Add the following to your require part within the composer.json: 

```json
"poluektov-software/laravel-relation-restore": "*"
```
```batch
$ php composer update
```

or

```
$ php composer require poluektov-software/laravel-relation-restore
```

## Usage

In the database, you must create an additional field for each model. 
In migrations, for example, you can add the following line:

```php
//...
$table->integer( 'remove_code' )->nullable();
//...
```

You need to use the **Relationship Restore** trait in your models:

```php
//...
use Poluektov\RelationRestore\RelationRestore;

/**
 * Class Model
 *
 * @package App\Models
 */
class Model
{
    use RelationRestore;
//...
```

Each model must have a unique code:

```php
//...
    protected $autoRemoveCode = 100;
//...
```

You must delete and restore related models through methods 
**autoRemove** and **autoRestore** respectively:

```php
    /**
     * Model events handlers.
     *
     */
    public static function boot()
    {
        parent::boot();
    
        static::deleting( function ( Model $model ) {
            $model->relatedModels->each( function ( $relatedModel ) {
                $relatedModel->autoRemove( $model->getAutoRemove() );
            } );
        } );
        
        static::restoring( function ( Model $model ) {
            $model->relatedModels()->onlyAutoRemoved( $model->getAutoRemove() )->each( function ( $relatedModel ) {
                $relatedModel->autoRestore();
            } );
        } );
    }
```

## License

This package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
