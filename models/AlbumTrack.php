<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "album_track".
 *
 * @property string $at_album_id
 * @property int $AT_TRACK_ID
 * @property int $AT_POSITION
 */
class AlbumTrack extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'album_track';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['at_album_id', 'AT_TRACK_ID', 'AT_POSITION'], 'required'],
            [['AT_TRACK_ID', 'AT_POSITION'], 'integer'],
            [['at_album_id'], 'string', 'max' => 100],
            [['at_album_id', 'AT_TRACK_ID'], 'unique', 'targetAttribute' => ['at_album_id', 'AT_TRACK_ID']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'at_album_id' => 'At Album ID',
            'AT_TRACK_ID' => 'At Track ID',
            'AT_POSITION' => 'At Position',
        ];
    }
}
