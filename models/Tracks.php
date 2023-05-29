<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tracks".
 *
 * @property int $track_id
 * @property string $track_code
 * @property int $track_song_id
 * @property int|null $track_artist_id
 * @property int|null $track_trktype
 * @property int $TRACK_STARS
 * @property int $TRACK_VIEWS
 * @property int $TRACK_CATEGORY
 * @property string|null $TRACK_CODE
 * @property string|null $track_isrc
 * @property string $track_name
 * @property string|null $TRACK_VERSION
 * @property string|null $TRACK_PLAYTIME
 * @property string|null $TRACK_RELEASE
 * @property int $TRACK_CACHE
 * @property string|null $TRACK_COMMENT
 * @property string|null $TRACK_NAME_EN
 */
class Tracks extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'tracks';
    }

    public function attributeLabels()
    {
        return [
            'track_id' => 'Track ID',
            'track_song_id' => 'Track Song ID',
            'track_artist_id' => 'Track Artist ID',
            'track_trktype' => 'Track Trktype',
            'TRACK_STARS' => 'Track Stars',
            'TRACK_VIEWS' => 'Track Views',
            'TRACK_CATEGORY' => 'Track Category',
            'TRACK_CODE' => 'Track Code',
            'track_isrc' => 'ISRC',
            'track_name' => 'Track Name',
            'TRACK_VERSION' => 'Track Version',
            'TRACK_PLAYTIME' => 'Track Playtime',
            'TRACK_RELEASE' => 'Track Release',
            'TRACK_CACHE' => 'Track Cache',
            'TRACK_COMMENT' => 'Track Comment',
            'TRACK_NAME_EN' => 'Track Name En',
        ];
    }

    public static function getTrackParams($id) {
        return Tracks::find()
                ->select('*')
                ->leftJoin('songs', 'tracks.track_song_id = songs.songs_id')
                ->leftJoin('artists', 'tracks.track_artist_id = artists.artist_id')
                ->leftJoin('album_track', 'tracks.track_id = album_track.at_track_id')
                ->leftJoin('albums', 'albums.album_id = album_track.at_album_id')
                ->leftJoin('alblabels', 'albums.album_alblab_id = alblabels.alblab_id')
                ->where(['track_id' => (int) $id])->asArray()->one();
        }

    public static function getTrackParts($id) {
        return Yii::$app->db2->createCommand("
                WITH trk AS (
                    SELECT
                        track_id,
                        AOBJ_ACOND_ID
                    FROM TRACKS
                        INNER JOIN ACTIVE_OBJS ON (TRACK_ID=AOBJ_OBJ_ID) and aobj_firm_id = 7 AND current_date BETWEEN aobj_start and aobj_stop
                        WHERE track_id = :id
                    ), active_c AS (
                        SELECT
                        track_id AS t_id,
                        AOBJ_ACOND_ID,
                        ACOND_ID,
                        ACOND_CLIENT_ID,
                        ACOND_MULT,
                        ACOND_START,
                        ACOND_STOP,
                        acond_firm_id,
                        ACOND_EXPROP,
                        CAST( (SELECT LIST(TER_CODE2,';') FROM TERS INNER JOIN CONDTERS ON CONDTER_TER_ID=TER_ID WHERE CONDTER_COND_ID=ACOND_ID) AS VARCHAR(500) ) AS TER_INC,
                        CAST( (SELECT LIST(TER_CODE2,';') FROM TERS INNER JOIN CONDTERS ON CONDTER_TER_XD=TER_ID WHERE CONDTER_COND_ID=ACOND_ID) AS VARCHAR(500) ) AS TER_EXC,
                        CAST( (SELECT LIST(IIF(REGION_NAME<>'',REGION_NAME,REGION_CODE),';') FROM REGIONS INNER JOIN CONDTERS ON CONDTER_REG_ID=REGION_ID WHERE CONDTER_COND_ID=ACOND_ID) AS VARCHAR(500) ) AS REG_INC,
                        CAST( (SELECT LIST(IIF(REGION_NAME<>'',REGION_NAME,REGION_CODE),';') FROM REGIONS INNER JOIN CONDTERS ON CONDTER_REG_XD=REGION_ID WHERE CONDTER_COND_ID=ACOND_ID) AS VARCHAR(500) ) AS REG_EXC
                    FROM
                        ACTIVE_CONDS
                        INNER JOIN trk ON ACOND_ID=AOBJ_ACOND_ID
                    ) SELECT
                        T_ID,
                        TRACK_SONG_ID,
                        TRACK_CODE AS CODE_1C,
                        TRACK_ISRC AS ISRC,
                        TRACK_NAME AS TITLE,
                        SONG_AUTHORS AS AUTHORS,
                        CLIENT_NICK AS PART_HOLDER,
                        AVT_NAME AS HOLDER_ROLE,
                        MAX(ACOND_MULT * PART_VALUE) AS PART_VALUE,
                        ACOND_START AS PERIOD_START,
                        ACOND_STOP AS PERIOD_STOP,
                        USAGE_ID,
                        USAGE_CODE,
                        USAGE_NAME,
                        CLIENT_NAME,
                        CLIENT_ID,
                        FIRM_NICK,
                        PART_EXTENDED,
                        IIF(ACOND_EXPROP=1, 'ALL',
                        CASE
                        WHEN REG_INC IS NOT NULL AND TER_INC IS NOT NULL THEN REG_INC||';'||TER_INC
                        WHEN REG_INC IS NOT NULL AND TER_INC IS NULL THEN REG_INC
                        WHEN REG_INC IS NULL AND TER_INC IS NOT NULL THEN TER_INC
                        END) AS TER_INC,
                        CASE
                        WHEN REG_EXC IS NOT NULL AND TER_EXC IS NOT NULL THEN REG_EXC||';'||TER_EXC
                        WHEN REG_EXC IS NOT NULL AND TER_EXC IS NULL THEN REG_EXC
                        WHEN REG_EXC IS NULL AND TER_EXC IS NOT NULL THEN TER_EXC
                        END AS TER_EXC
                    FROM active_c
                        INNER JOIN tracks ON t_id = track_id
                        INNER JOIN ARTISTS ON ARTIST_ID=TRACK_ARTIST_ID
                        INNER JOIN SONGS ON SONG_ID=TRACK_SONG_ID
                        INNER JOIN ACTIVE_OBJS ON (TRACK_ID=AOBJ_OBJ_ID) and aobj_firm_id = 7 AND current_date BETWEEN aobj_start and aobj_stop
                        INNER JOIN FIRMS f ON aobj_firm_id = firm_id
                        INNER JOIN CONDUSGS ON (CONDUSG_COND_ID=ACOND_ID)
                        INNER JOIN PARTS ON (AOBJ_PART_ID=PART_ID)
                        INNER JOIN AVTS ON (AVT_ID=PART_AVT)
                        INNER JOIN CPRHOLDERS ON (CHOLDER_ID=PART_CHOLDER_ID)
                        INNER JOIN usages ON (condusg_usg_id = usage_id)
                        INNER JOIN clients ON (ACOND_CLIENT_ID = client_id)
                    GROUP BY 1,2,3,4,5,6,7,8,10,11,12,13,14,15,16,17,18,19,20
                    ORDER BY usage_code", [":id" => (int)$id])->queryAll();
    }

    public static function getTrackAuthors($id) {
        return Yii::$app->db2->createCommand("
                SELECT
                    cholder_name,
                    avt_name
                    FROM
                    TRACKS t 
                    INNER JOIN SONGS ON SONG_ID=TRACK_SONG_ID
                    INNER JOIN ACTIVE_OBJS ON (song_ID=AOBJ_OBJ_ID) and aobj_firm_id = 7
                    INNER JOIN PARTS ON (AOBJ_PART_ID=PART_ID)
                    INNER JOIN CPRHOLDERS ON (CHOLDER_ID=PART_CHOLDER_ID)
                    INNER JOIN AVTS ON (AVT_ID=PART_AVT)
                    WHERE track_id = :id
                    GROUP BY 1,2
                    ORDER BY avt_name
            ", [":id" => (int)$id])->queryAll();
    }

    public function getArtists()
    {
        return $this->hasOne(Artists::class, ['artist_id' => 'track_artist_id']);
    }

    public function getSongs()
    {
        return $this->hasOne(Songs::class, ['songs_id' => 'track_song_id']);
    }

    public function getAlbumTracks()
    {
        return $this->hasOne(AlbumTrack::class, ['at_track_id' => 'track_id']);
    }
}
