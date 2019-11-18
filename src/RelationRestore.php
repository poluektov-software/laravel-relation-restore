<?php

namespace Poluektov\RelationRestore;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait RelationRestore
 *
 * Управление автоматическим удалением / восстановлением связанных моделей (сущностей).
 *
 * @package Poluektov\RelationRestore
 */
trait RelationRestore
{
    use SoftDeletes;

    /**
     * Имя поля признака автоматического удаления.
     *
     * @var string
     */
    protected $autoRemoveName = 'auto_remove';

    /**
     * Код автоматического удаления.
     *
     * @var
     */
    protected $autoRemoveCode = null;



    /**
     * Условие для получения и автоматически удаленных моделей (сущностей).
     *
     * @param   \Illuminate\Database\Eloquent\Builder $query Запрос
     * @param   int|null $autoRemoveCode Код автоматического удаления
     * @return  mixed
     */
    public function scopeWithAutoRemoved( Builder $query, int $autoRemoveCode = null )
    {
        return $query->where( function ( $query ) use ( $autoRemoveCode ) {

            $query->whereNotNull( $this->table . '.deleted_at' );

            $autoRemoveCode
                ? $query->where( $this->table .  '.' . $this->autoRemoveName, $autoRemoveCode )
                : $query->whereNotNull( $this->table .  '.' . $this->autoRemoveName );
        } )
            ->withTrashed()
            ->orWhereNull( $this->table . '.deleted_at' );
    }

    /**
     * Условие для получения только автоматически удаленных моделей (сущностей).
     *
     * @param   \Illuminate\Database\Eloquent\Builder $query Запрос
     * @param   int|null $autoRemoveCode Код автоматического удаления
     * @return  mixed
     */
    public function scopeOnlyAutoRemoved( Builder $query, int $autoRemoveCode = null )
    {
        $query->onlyTrashed();

        return $autoRemoveCode
            ? $query->where( $this->autoRemoveName, $autoRemoveCode )
            : $query->whereNotNull( $this->autoRemoveName );
    }

    /**
     * Условие для получения не автоматически удаленных моделей (сущностей).
     *
     * @param   mixed $query Запрос
     * @return  mixed
     */
    public function scopeOnlyNotAutoRemoved( $query )
    {
        return $query->onlyTrashed()->whereNull( $this->autoRemoveName );
    }

    /**
     * Автоматическое удаление модели (сущности) с указанным признаком.
     *
     * @param   int $autoRemoveCode Код автоматического удаления
     * @return  mixed
     */
    public function autoRemove( int $autoRemoveCode )
    {
        $this->{ $this->autoRemoveName } = $autoRemoveCode;
        $this->save();

        return $this->delete();
    }

    /**
     * Автоматическое восстановление модели (сущности).
     *
     * @return  mixed
     */
    public function autoRestore()
    {
        $result = $this->restore();

        if ( $result ) {
            $this->{ $this->autoRemoveName } = null;
            $this->save();
        }

        return $result;
    }

    /**
     * Проверка, модели (сущности) на признак автоматического удаления.
     *
     * Если модель (сущность) автоматически удалена, возвращается true.
     * Иначе возвращается false.
     *
     * @return  bool
     */
    public function isAutoRemoved() : bool
    {
        return $this->trashed() && !is_null( $this->{ $this->autoRemoveName } );
    }

    /**
     * Получение признака автоматического удаления.
     *
     * @return  int|null
     */
    public function getAutoRemove() : ?int
    {
        $autoRemove = $this->{ $this->autoRemoveName };

        return $autoRemove ?? $this->autoRemoveCode;
    }
}
