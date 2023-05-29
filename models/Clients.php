<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "clients".
 *
 * @property int $client_id
 * @property int|null $client_pid
 * @property int $client_grp
 * @property string|null $client_name
 * @property string|null $client_fio
 * @property string|null $client_alias
 * @property string|null $client_type
 */
class Clients extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'clients';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_id', 'client_grp'], 'required'],
            [['client_id', 'client_pid', 'client_grp'], 'integer'],
            [['client_name', 'client_fio', 'client_alias'], 'string', 'max' => 100],
            [['client_type'], 'string', 'max' => 20],
            [['client_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'client_id' => 'Client ID',
            'client_pid' => 'Client Pid',
            'client_grp' => 'Client Grp',
            'client_name' => 'Client Name',
            'client_fio' => 'Client Fio',
            'client_alias' => 'Client Alias',
            'client_type' => 'Client Type',
        ];
    }
}
