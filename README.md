# Laravel Relation Restore

## Описание

Laravel Relation Restore - пакет Laravel для удаления и восстановления связанных моделей. 
Данный пакет использует метод мягкого удаления. Пакет может быть полезен там, где требуется удалять 
и восстанавливать модели со сложными цепочками связанных моделей.

## Использование

В базе данных для каждой модели необходимо создать дополнительное поле. 
В миграциях, например, можно добавлять следующую строку:

```php
//...
$table->integer( 'remove_code' )->nullable();
//...
```

Необходимо подключить трейт **RelationRestore** к вашим моделям.

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

В каждой модели необходимо прописать уникальный код:

```php
//...
    protected $autoRemoveCode = 100;
//...
```

Удалять и восстанавливать связанные модели необходимо через методы 
**autoRemove** и **autoRestore** соответственно.

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
