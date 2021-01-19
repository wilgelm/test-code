<?php

namespace common\models\locations;

use common\models\BaseModel;
use common\models\drivers\DriverModel;
use common\models\trips\TripModel;
use Yii;
use yii\db\ActiveQuery;
use yii\web\HttpException;

/**
 * Класс для работы с сущностью locations
 *
 * @package common\models\locations
 *
 * @property int $id ID записи
 * @property string $point Метка расположения
 * @property int $driver_id ID водителя
 * @property string $created_at Время создания
 * @property int $trip_id ID поездки
 * @property float $speed Скорость км/ч
 * @property string $sent Время считывания этого местоположения устройством
 * @property boolean $archival Флаг архивности поездки
 * @property int $type Тип координат
 * @property int $route_id ID роута
 *
 * @property DriverModel $driver Релейшон связи водителя и локейшенов
 * @property TripModel $trip Релейшон связи локейшона и поездки
 */
class LocationModel extends BaseModel
{
    /** Тип "Координата в произвольный момент" */
    public const TYPE_ARBITRARY = 1;
    /** Тип "Координата в момент входа в точку геофенсинга" */
    public const TYPE_POINT_ENTRY = 2;
    /** Тип "Координата в момент выхода из точки геофенсинга" */
    public const TYPE_POINT_WAY_OUT = 3;

    /**
     * Получаем экземпляр подключения к базе.
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->driver2;
    }

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['point', 'driver_id', 'sent'], 'required'],
            [['driver_id', 'trip_id', 'type', 'route_id'], 'integer'],
            [['point', 'sent'], 'string'],
            [['type'], 'default', 'value' => self::TYPE_ARBITRARY],
            [['type'], 'in', 'range' => LocationModel::getTypeList()],
            [['speed'], 'number'],
        ];
    }

    /**
     * Имя таблицы в бд
     *
     * @return string
     */
    public static function tableName()
    {
        return 'locations.locations';
    }

    /**
     * Получить список типов
     */
    public static function getTypeList()
    {
        return [
            self::TYPE_ARBITRARY,
            self::TYPE_POINT_ENTRY,
            self::TYPE_POINT_WAY_OUT,
        ];
    }

    /**
     * relation сущности
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTrip()
    {
        return $this->hasOne(TripModel::class, ['id' => 'trip_id']);
    }

    /**
     * Метод формирует point
     *
     * @param $lat
     *
     * @param $lng
     */
    public function setPoint($lat, $lng)
    {
        $point = $lat . ',' . $lng;
        $this->point = $point;
    }

    /**
     * Метод формирует время отправления координат
     *
     * @param $date
     *
     * @throws HttpException
     */
    public function setSent($date)
    {
        $this->sent = $date;
    }

    /**
     * Связь с таблицей водителей.
     *
     * @return ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(DriverModel::className(), ['id' => 'driver_id']);
    }

    /**
     * Заархивировать точку
     *
     * @return bool
     */
    public function doArchived(): bool
    {
        $this->archival = true;

        return $this->save();
    }
}
