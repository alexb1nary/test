<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "clidocs".
 *
 * @property string $cdoc_id
 * @property int $cdoc_client_id
 * @property string|null $cdoc_name
 * @property string|null $cdoc_file
 * @property string|null $cdoc_ext
 * @property string|null $cdoc_loaded
 * @property string|null $cdoc_date
 * @property int $cdoc_onsite
 * @property int $cdoc_onsitersp
 * @property string $cdoc_pid
 * @property string $cdoc_realfile
 */
class Clidocs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'clidocs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cdoc_id', 'cdoc_client_id', 'cdoc_onsite', 'cdoc_onsitersp', 'cdoc_pid', 'cdoc_realfile'], 'required'],
            [['cdoc_client_id', 'cdoc_onsite', 'cdoc_onsitersp'], 'integer'],
            [['cdoc_id', 'cdoc_pid'], 'string', 'max' => 40],
            [['cdoc_name'], 'string', 'max' => 100],
            [['cdoc_file'], 'string', 'max' => 255],
            [['cdoc_ext'], 'string', 'max' => 10],
            [['cdoc_loaded', 'cdoc_date'], 'string', 'max' => 20],
            [['cdoc_realfile'], 'string', 'max' => 200],
            [['cdoc_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cdoc_id' => 'Cdoc ID',
            'cdoc_client_id' => 'Cdoc Client ID',
            'cdoc_name' => 'Cdoc Name',
            'cdoc_file' => 'Cdoc File',
            'cdoc_ext' => 'Cdoc Ext',
            'cdoc_loaded' => 'Cdoc Loaded',
            'cdoc_date' => 'Cdoc Date',
            'cdoc_onsite' => 'Cdoc Onsite',
            'cdoc_onsitersp' => 'Cdoc Onsitersp',
            'cdoc_pid' => 'Cdoc Pid',
            'cdoc_realfile' => 'Cdoc Realfile',
        ];
    }

    public function getClients()
    {
        return $this->hasOne(Clients::class, ['client_id' => 'cdoc_client_id']);
    }
}
