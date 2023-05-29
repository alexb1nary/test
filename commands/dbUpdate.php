<?php

$start = time();

function addQuotes($var) {
    global $pdoDb;
    return ($var ? $pdoDb->quote($var) : 'null');
}

require_once '/var/www/html/mailer/sendmail.php';

try {
    $pdoFish = new PDO(
        'firebird:dbname=192.168.123.33:fishap;charset=utf-8',
        '',
        ''
    );
    $pdoFish->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Не удалось подключиться к базе Firebird: ' . $e->getMessage());
    exit;
}

try {
    $pdoDb = new PDO(
        'mysql:host=localhost;dbname=fmp;charset=utf8mb4',
        '',
        ''
    );
    $pdoDb->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
    $pdoDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Не удалось подключиться к базе db: ' . $e->getMessage());
    exit;
}

//tracks
echo 'dropping table tracks_new: ';

try {
    $res = $pdoDb->query('drop table if exists tracks_new');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\r";
echo 'dropping table tracks_new: ok';
echo "\n";
echo 'creating table tracks_new: ';


$sql = "CREATE TABLE `tracks_new` (
  `track_id` int NOT NULL,
  `track_song_id` int NOT NULL,
  `track_artist_id` int DEFAULT NULL,
  `track_trktype` int DEFAULT NULL,
  `track_stars` int NOT NULL DEFAULT '0',
  `track_views` int NOT NULL DEFAULT '0',
  `track_category` int NOT NULL DEFAULT '0',
  `track_code` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `track_isrc` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `track_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `track_version` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `track_playtime` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `track_release` date DEFAULT NULL,
  `track_cache` smallint NOT NULL DEFAULT '0',
  `track_comment` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `track_name_en` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`track_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

try {
    $res = $pdoDb->exec($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\r";
echo 'creating table tracks_new: ok';
echo "\n";
echo 'selecting tracks from unifish: ';

$sql = "SELECT 
            track_id,
            track_song_id,
            track_artist_id,
            track_trktype,
            track_stars,
            track_views,
            track_category,
            track_code,
            track_isrc,
            track_name,
            track_version,
            track_playtime,
            track_release,
            track_cache,
            track_comment,
            track_name_en
            FROM 
        TRACKS t 
            INNER JOIN ACTIVE_OBJS ON (TRACK_ID=AOBJ_OBJ_ID) 
                                    AND (CURRENT_DATE BETWEEN AOBJ_START AND AOBJ_STOP)
                                    and aobj_firm_id = 7
            GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16";

try {
    $stmtFish = $pdoFish->query($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой Firebird: ' . $e->getMessage());
    exit;
}

echo"\r";
echo 'selecting tracks from unifish: ok';
echo "\n";
echo 'inserting tracks from unifish:';
echo "\n";

$sqlQuery = "insert into tracks_new (
                    track_id,
                    track_song_id,
                    track_artist_id,
                    track_trktype,
                    track_stars,
                    track_views,
                    track_category,
                    track_code,
                    track_isrc,
                    track_name,
                    track_version,
                    track_playtime,
                    track_release,
                    track_cache,
                    track_comment,
                    track_name_en
                    ) values  ";

$rowNum = 0;
$fullQuery = '';
while ($trackRow = $stmtFish->fetch(PDO::FETCH_ASSOC)) {
    $rowNum++;
    $values = "(
        " . $trackRow['track_id'] . ",
        " . $trackRow['track_song_id'] . ",
        " . ($trackRow['track_artist_id'] ?: 'null') . ",
        " . ($trackRow['track_trktype'] ?: 'null') . ",
        " . $trackRow['track_stars'] . ",
        " . $trackRow['track_views'] . ",
        " . $trackRow['track_category'] . ",
        " . addQuotes($trackRow['track_code']) . ",
        " . addQuotes($trackRow['track_isrc']) . ",
        " . $pdoDb->quote($trackRow['track_name']) . ",
        " . addQuotes($trackRow['track_version']) . ",
        " . addQuotes($trackRow['track_playtime']) . ",
        " . addQuotes($trackRow['track_release']) . ",
        " . $trackRow['track_cache'] . ",
        " . addQuotes($trackRow['track_comment']) . ",
        " . addQuotes($trackRow['track_name_en']) . "
        )";

    //собираем запрос
    if (!$fullQuery) {
        $fullQuery = $sqlQuery . $values;
    } else {
        $fullQuery .= ", \n" . $values;
    }

    //отправляем
    if ($rowNum % 10000 == 0) {
        try {
            $res = $pdoDb->query($fullQuery);
            $fullQuery = '';
            echo $rowNum == 10000 ? "rowNum: $rowNum" : "\rrowNum: $rowNum";
        } catch (Exception $e) {
            sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
            exit;
        }
    }
}

//если что-то осталось
if ($fullQuery) {
    try {
        $res = $pdoDb->query($fullQuery);
        $fullQuery = '';
        echo "\rrowNum: $rowNum";
    } catch (Exception $e) {
        sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
        exit;
    }
}

echo "\ninserting tracks from unifish: ok";
echo "\ncreating track_isrc_index, result:";

try {
    $pdoDb->exec('CREATE INDEX track_isrc_index ON tracks_new (track_isrc)');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating track_isrc_index, result: ok";
echo "\ncreating track_name_index, result:";

try {
    $pdoDb->exec("CREATE INDEX track_name_index ON tracks_new (track_name)");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating track_name_index, result: ok";
echo "\ncreating track_artist_id_index:";

try {
    $pdoDb->exec("CREATE INDEX track_artist_id_index ON tracks_new (track_artist_id)");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating track_artist_id_index: ok";
echo "\ndropping table tracks:";

try {
    $res = $pdoDb->exec("drop table if exists tracks");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping table tracks: ok";
echo "\nrenaming tracks_new to tracks:";

try {
    $res = $pdoDb->exec("ALTER TABLE tracks_new RENAME tracks;");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rrenaming tracks_new to tracks: ok";


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//videos
echo "\ndropping table video_new:";

try {
    $res = $pdoDb->query('drop table if exists videos_new');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping table video_new: ok";
echo "\ncreating table videos_new:";

$sql = "CREATE TABLE if not exists `videos_new` (
  `video_id` int NOT NULL,
  `video_song_id` int NOT NULL,
  `video_artist_id` int DEFAULT NULL,
  `video_vidtype` int DEFAULT NULL,
  `video_stars` int NOT NULL DEFAULT '0',
  `video_views` int NOT NULL DEFAULT '0',
  `video_category` int NOT NULL DEFAULT '0',
  `video_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `video_version` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `video_director` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `video_playtime` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `video_release` date DEFAULT NULL,
  `video_isrc` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `video_code` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `video_cache` smallint NOT NULL DEFAULT '0',
  `video_comment` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `video_name_en` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

try {
    $res = $pdoDb->exec($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating table videos_new: ok";
echo "\nselecting videos from unifish:";

$sql = "select
            video_id,
            video_song_id,
            video_artist_id,
            video_vidtype,
            video_stars,
            video_views,
            video_category,
            video_name,
            video_version,
            video_director,
            video_playtime,
            video_release,
            video_isrc,
            video_code,
            video_cache,
            video_comment,
            video_name_en
        FROM VIDEO v 
            INNER JOIN ACTIVE_OBJS ON (VIDEO_ID=AOBJ_OBJ_ID)
                                    AND (CURRENT_DATE BETWEEN AOBJ_START AND AOBJ_STOP)
                                    and aobj_firm_id = 7
            GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17";

try {
    $stmtFish = $pdoFish->query($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой Firebird: ' . $e->getMessage());
    exit;
}

echo "\rselecting videos from unifish: ok";
echo "\ninserting videos in videos_new:";
echo "\n";

$sqlQuery = "insert into videos_new (
                    video_id,
                    video_song_id,
                    video_artist_id,
                    video_vidtype,
                    video_stars,
                    video_views,
                    video_category,
                    video_name,
                    video_version,
                    video_director,
                    video_playtime,
                    video_release,
                    video_isrc,
                    video_code,
                    video_cache,
                    video_comment,
                    video_name_en
                    ) values  ";

$rowNum = 0;
$fullQuery = '';
while ($videoRow = $stmtFish->fetch(PDO::FETCH_ASSOC)) {
    $rowNum++;
    $values = "(
        " . $videoRow['video_id'] . ",
        " . $videoRow['video_song_id'] . ",
        " . ($videoRow['video_artist_id'] ?: 'null') . ",
        " . ($videoRow['video_vidtype'] ?: 'null') . ",
        " . ($videoRow['video_stars'] ?: 0) . ",
        " . $videoRow['video_views'] . ",
        " . $videoRow['video_category'] . ",
        " . $pdoDb->quote($videoRow['video_name']) . ",
        " . addQuotes($videoRow['video_version']) . ",
        " . addQuotes($videoRow['video_director']) . ",
        " . addQuotes($videoRow['video_playtime']) . ",
        " . addQuotes($videoRow['video_release']) . ",
        " . addQuotes($videoRow['video_isrc']) . ",
        " . addQuotes($videoRow['video_code']) . ",
        " . ($videoRow['video_cache'] ?: 0) . ",
        " . addQuotes($videoRow['video_comment']) . ",
        " . addQuotes($videoRow['video_name_en']) . "
        )";

    //собираем запрос
    if (!$fullQuery) {
        $fullQuery = $sqlQuery . $values;
    } else {
        $fullQuery .= ", \n" . $values;
    }

    //отправляем
    if ($rowNum % 10000 == 0) {
        try {
            $res = $pdoDb->query($fullQuery);
            $fullQuery = '';

            echo $rowNum == 10000 ? "rowNum: $rowNum" : "\rrowNum: $rowNum";
        } catch (Exception $e) {
            sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
            exit;
        }
    }
}

//если что-то осталось
if ($fullQuery) {
    try {
        $res = $pdoDb->query($fullQuery);
        $fullQuery = '';
        echo "\rrowNum: $rowNum";
    } catch (Exception $e) {
        sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
        exit;
    }
}

echo "\ninserting videos in videos_new: ok";
echo "\ncreating video_name_index:";

try {
    $pdoDb->exec('CREATE INDEX video_name_index ON videos_new (video_name)');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating video_name_index: ok";
echo "\ncreating video_director_index:";

try {
    $pdoDb->exec("CREATE INDEX video_director_index ON videos_new (video_director)");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating video_director_index: ok";
echo "\ncreating video_release_index:";

try {
    $pdoDb->exec("CREATE INDEX video_release_index ON videos_new (video_release)");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating video_release_index: ok";
echo "\ncreating video_isrc_index:";

try {
    $pdoDb->exec("CREATE INDEX video_isrc_index ON videos_new (video_isrc)");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating video_isrc_index: ok";
echo "\ndropping table videos:";

try {
    $res = $pdoDb->exec("drop table if exists videos");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping table videos: ok";
echo "\nrenaming videos_new to videos:";

try {
    $res = $pdoDb->exec("ALTER TABLE videos_new RENAME videos;");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rrenaming videos_new to videos: ok";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//songs
echo "\ndropping table songs_new:";

try {
    $res = $pdoDb->query('drop table if exists songs_new');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdroping table songs_new: ok";
echo "\ncreating table songs_new:";

$sql = "CREATE TABLE `songs_new` (
  `songs_id` int NOT NULL,
  `songs_sngtype` int DEFAULT NULL,
  `songs_lang` int DEFAULT NULL,
  `songs_stars` int NOT NULL DEFAULT '0',
  `songs_views` int NOT NULL DEFAULT '0',
  `songs_category` int NOT NULL DEFAULT '0',
  `songs_pub_text` smallint DEFAULT '0',
  `songs_pub_music` smallint DEFAULT '0',
  `songs_complex` smallint DEFAULT '0',
  `songs_rework` smallint DEFAULT '0',
  `songs_cache` smallint NOT NULL DEFAULT '0',
  `songs_code` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `songs_iswc` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `songs_release` date DEFAULT NULL,
  `songs_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `songs_authors` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `songs_artist` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `songs_album` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `songs_playtime` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `songs_raodt` date DEFAULT NULL,
  `songs_comment` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `songs_name_en` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `songs_authors_en` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `songs_artist_en` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `songs_album_en` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`songs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

try {
    $res = $pdoDb->exec($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating table songs_new: ok";
echo "\nselecting songs from unifish:";


$sql = "SELECT 
            SONG_ID,
            SONG_SNGTYPE,
            SONG_LANG,
            SONG_STARS,
            SONG_VIEWS,
            SONG_CATEGORY,
            SONG_PUB_TEXT,
            SONG_PUB_MUSIC,
            SONG_COMPLEX,
            SONG_REWORK,
            SONG_CACHE,
            SONG_CODE,
            SONG_ISWC,
            SONG_RELEASE,
            SONG_NAME,
            SONG_AUTHORS,
            SONG_ARTIST,
            SONG_ALBUM,
            SONG_PLAYTIME,
            SONG_RAODT,
            SONG_COMMENT,
            SONG_NAME_EN,
            SONG_AUTHORS_EN,
            SONG_ARTIST_EN,
            SONG_ALBUM_EN
        FROM 
            SONGS 
            INNER JOIN ACTIVE_OBJS ON (SONG_ID=AOBJ_OBJ_ID) 
                                    AND (CURRENT_DATE BETWEEN AOBJ_START AND AOBJ_STOP)
                                    and aobj_firm_id = 7
            GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25";

try {
    $stmtFish = $pdoFish->query($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой Firebird: ' . $e->getMessage());
    exit;
}

echo "\rselecting songs from unifish: ok";
echo "\ninserting songs to songs_new:";

$sqlQuery = "insert into songs_new (
                    SONGS_ID,
                    SONGS_SNGTYPE,
                    SONGS_LANG,
                    SONGS_STARS,
                    SONGS_VIEWS,
                    SONGS_CATEGORY,
                    SONGS_PUB_TEXT,
                    SONGS_PUB_MUSIC,
                    SONGS_COMPLEX,
                    SONGS_REWORK,
                    SONGS_CACHE,
                    SONGS_CODE,
                    SONGS_ISWC,
                    SONGS_RELEASE,
                    SONGS_NAME,
                    SONGS_AUTHORS,
                    SONGS_ARTIST,
                    SONGS_ALBUM,
                    SONGS_PLAYTIME,
                    SONGS_RAODT,
                    SONGS_COMMENT,
                    SONGS_NAME_EN,
                    SONGS_AUTHORS_EN,
                    SONGS_ARTIST_EN,
                    SONGS_ALBUM_EN
                    ) values  ";

$rowNum = 0;
$fullQuery = '';
while ($songsRow = $stmtFish->fetch(PDO::FETCH_ASSOC)) {
    $rowNum++;
    $values = "(
        " . $songsRow['song_id'] . ",
        " . ($songsRow['song_sngtype'] ?: 'null') . ",
        " . ($songsRow['song_lang'] ?: 'null') . ",
        " . $songsRow['song_stars'] . ",
        " . $songsRow['song_views'] . ",
        " . $songsRow['song_category'] . ",
        " . $songsRow['song_pub_text'] . ",
        " . $songsRow['song_pub_music'] . ",
        " . $songsRow['song_complex'] . ",
        " . $songsRow['song_rework'] . ",
        " . $songsRow['song_cache'] . ",
        " . addQuotes($songsRow['song_code']) . ",
        " . addQuotes($songsRow['song_iswc']) . ",
        " . addQuotes($songsRow['song_release']) . ",
        " . $pdoDb->quote($songsRow['song_name']) . ",
        " . $pdoDb->quote($songsRow['song_authors']) . ",
        " . $pdoDb->quote($songsRow['song_artist']) . ",
        " . $pdoDb->quote($songsRow['song_album']) . ",
        " . $pdoDb->quote($songsRow['song_playtime']) . ",
        " . addQuotes($songsRow['song_raodt']) . ",
        " . addQuotes($songsRow['song_comment']) . ",
        " . addQuotes($songsRow['song_name_en']) . ",
        " . addQuotes($songsRow['song_authors_en']) . ",
        " . addQuotes($songsRow['song_artist_en']) . ",
        " . addQuotes($songsRow['song_album_en']) . "
        )";

    //собираем запрос
    if (!$fullQuery) {
        $fullQuery = $sqlQuery . $values;
    } else {
        $fullQuery .= ", \n" . $values;
    }

    //отправляем
    if ($rowNum % 10000 == 0) {
        try {
            $res = $pdoDb->query($fullQuery);
            $fullQuery = '';

            echo $rowNum == 10000 ? "\nrowNum: $rowNum" : "\rrowNum: $rowNum";
        } catch (Exception $e) {
            sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
            exit;
        }
    }
}

//если что-то осталось
if ($fullQuery) {
    try {
        $res = $pdoDb->query($fullQuery);
        $fullQuery = '';
        echo "\rrowNum: " . $rowNum;
    } catch (Exception $e) {
        sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
        exit;
    }
}

echo "\ncreating song_name_index:";

try {
    $pdoDb->exec('CREATE INDEX songs_name_index ON songs_new (songs_name)');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating song_name_index: ok";
echo "\ncreating song_authors_index:";

try {
    $pdoDb->exec("CREATE INDEX songs_authors_index ON songs_new (songs_authors)");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}
echo "\rcreating song_authors_index: ok";
echo "\ncreating song_artist_index:";

try {
    $pdoDb->exec("CREATE INDEX songs_artist_index ON songs_new (songs_artist)");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating song_artist_index: ok";
echo "\ndropping songs table:";

try {
    $res = $pdoDb->exec("drop table if exists songs");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping songs table: ok";
echo "\nrenaming songs_new to songs table:";

try {
    $res = $pdoDb->exec("ALTER TABLE songs_new RENAME songs;");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rrenaming songs_new to songs table: ok";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//artists
echo "\ndropping table artists_new:";

try {
    $res = $pdoDb->query('drop table if exists artists_new');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping table artists_new: ok";
echo "\ncreating table artists_new:";

$sql = "CREATE TABLE `artists_new` (
  `artist_id` int NOT NULL,
  `artist_arttype` int DEFAULT NULL,
  `artist_stars` int NOT NULL DEFAULT '0',
  `artist_views` int NOT NULL DEFAULT '0',
  `artist_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `artist_code` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `artist_isni` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `artist_photo` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `artist_comment` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `artist_cache` smallint NOT NULL DEFAULT '0',
  `artist_members` int NOT NULL DEFAULT '0',
  `artist_definer` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `artist_name_en` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`artist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

try {
    $res = $pdoDb->exec($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating table artists_new: ok";
echo "\nselecting artists from unifish:";

$sql = "select * from artists";

try {
    $stmtFish = $pdoFish->query($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой Firebird: ' . $e->getMessage());
    exit;
}

echo "\rselecting artists from unifish: ok";
echo "\ninserting into artists_new:";

$sqlQuery = "insert into artists_new (
                    artist_id,
                    artist_arttype,
                    artist_stars,
                    artist_views,
                    artist_name,
                    artist_code,
                    artist_isni,
                    artist_photo,
                    artist_comment,
                    artist_cache,
                    artist_members,
                    artist_definer,
                    artist_name_en
                    ) values  ";

$rowNum = 0;
$fullQuery = '';
while ($artistsRow = $stmtFish->fetch(PDO::FETCH_ASSOC)) {
    $rowNum++;
    $values = "(
        " . $artistsRow['artist_id'] . ",
        " . ($artistsRow['artist_arttype'] ?: 'null') . ",
        " . $artistsRow['artist_stars']. ",
        " . $artistsRow['artist_views'] . ",
        " . $pdoDb->quote($artistsRow['artist_name']) . ",
        " . addQuotes($artistsRow['artist_code']) . ",
        " . addQuotes($artistsRow['artist_isni']) . ",
        " . addQuotes($artistsRow['artist_photo']) . ",
        " . addQuotes($artistsRow['artist_comment']) . ",
        " . $artistsRow['artist_cache'] . ",
        " . $artistsRow['artist_members'] . ",
        " . addQuotes($artistsRow['artist_definer']) . ",
        " . addQuotes($artistsRow['artist_name_en']) . "
        )";

    //собираем запрос
    if (!$fullQuery) {
        $fullQuery = $sqlQuery . $values;
    } else {
        $fullQuery .= ", \n" . $values;
    }

    //отправляем
    if ($rowNum % 10000 == 0) {
        try {
            $res = $pdoDb->query($fullQuery);
            $fullQuery = '';
            echo $rowNum == 10000 ? "\nrowNum: $rowNum" : "\rrowNum: $rowNum";
        } catch (Exception $e) {
            sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
            exit;
        }
    }
}

//если что-то осталось
if ($fullQuery) {
    try {
        $res = $pdoDb->query($fullQuery);
        $fullQuery = '';
        echo "\rrowNum: $rowNum";
    } catch (Exception $e) {
        sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
        exit;
    }
}

echo "\ncreating index artist_name_index:";

try {
    $pdoDb->exec('CREATE INDEX artist_name_index ON artists_new (artist_name)');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating index artist_name_index: ok";
echo "\ndropping artists:";

try {
    $res = $pdoDb->exec("drop table if exists artists");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping artists: ok";
echo "\nrenaming artists_new to artists:";

try {
    $res = $pdoDb->exec("ALTER TABLE artists_new RENAME artists;");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rrenaming artists_new to artists: ok";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//albums
echo "\ndropping albums_new:";

try {
    $res = $pdoDb->query('drop table if exists albums_new');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping albums_new: ok";
echo "\ncreating albums_new:";

$sql = "CREATE TABLE `albums_new` (
  `album_id` int NOT NULL,
  `album_artist_id` int DEFAULT NULL,
  `album_alblab_id` int DEFAULT NULL,
  `album_albtype` int DEFAULT NULL,
  `album_stars` int NOT NULL,
  `album_views` int NOT NULL,
  `album_category` int NOT NULL,
  `album_upc` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `album_name` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `album_version` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `album_release` date DEFAULT NULL,
  `album_code` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `album_cache` smallint NOT NULL,
  `album_count` int DEFAULT NULL,
  `album_cover` int DEFAULT NULL,
  `album_comment` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

try {
    $res = $pdoDb->exec($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating albums_new: ok";
echo "\nselecting albums from unifish:";

$sql = "SELECT 
            *
        FROM ALBUMS a2 
        WHERE album_id IN (
            SELECT 
                album_id
            FROM ALBUMS a 
                inner JOIN ALBUM_TRACK at2 ON ALBUM_ID = at_album_id
                INNER JOIN TRACKS t ON at_track_id = track_id
                INNER JOIN ACTIVE_OBJS ao ON t.TRACK_ID = ao.AOBJ_OBJ_ID 
                    AND (CURRENT_DATE BETWEEN AOBJ_START AND AOBJ_STOP)
                    and aobj_firm_id = 7
            GROUP BY 1)";

try {
    $stmtFish = $pdoFish->query($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой Firebird: ' . $e->getMessage());
    exit;
}

echo "\rselecting albums from unifish: ok";
echo "\ninserting into albums_new:";

$sqlQuery = "insert into albums_new (
                    album_id,
                    album_artist_id,
                    album_alblab_id,
                    album_albtype,
                    album_stars,
                    album_views,
                    album_category,
                    album_upc,
                    album_name,
                    album_version,
                    album_release,
                    album_code,
                    album_cache,
                    album_count,
                    album_cover,
                    album_comment
                    ) values  ";

$rowNum = 0;
$fullQuery = '';
while ($albumsRow = $stmtFish->fetch(PDO::FETCH_ASSOC)) {
    $rowNum++;
    $values = "(
        " . $albumsRow['album_id'] . ",
        " . ($albumsRow['album_artist_id'] ?: 'null') . ",
        " . ($albumsRow['album_alblab_id'] ?: 'null') . ",
        " . ($albumsRow['album_albtype'] ?: 'null') . ",
        " . ($albumsRow['album_stars'] ?: 0) . ",
        " . ($albumsRow['album_views'] ?: 0) . ",
        " . ($albumsRow['album_category'] ?: 0) . ",
        " . addQuotes($albumsRow['album_upc']) . ",
        " . addQuotes($albumsRow['album_name']) . ",
        " . addQuotes($albumsRow['album_version']) . ",
        " . addQuotes($albumsRow['album_release']) . ",
        " . addQuotes($albumsRow['album_code']) . ",
        " . $albumsRow['album_cache'] . ",
        " . ($albumsRow['album_count'] ?: 'null') . ",
        " . ($albumsRow['album_cover'] ?: 'null') . ",
        " . addQuotes($albumsRow['album_comment']) . "
        )";

    //собираем запрос
    if (!$fullQuery) {
        $fullQuery = $sqlQuery . $values;
    } else {
        $fullQuery .= ", \n" . $values;
    }

    //отправляем
    if ($rowNum % 10000 == 0) {
        try {
            $res = $pdoDb->query($fullQuery);
            $fullQuery = '';
            echo $rowNum == 10000 ? "\nrowNum: $rowNum" : "\rrowNum: $rowNum";
        } catch (Exception $e) {
            sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
            exit;
        }
    }
}

//если что-то осталось
if ($fullQuery) {
    try {
        $res = $pdoDb->query($fullQuery);
        $fullQuery = '';
        echo "\rrowNum: $rowNum";
    } catch (Exception $e) {
        sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
        exit;
    }
}

echo "\ninserting into albums_new: ok";
echo "\ncreating album_name_index:";

try {
    $pdoDb->exec('CREATE INDEX album_name_index ON albums_new (album_name)');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating album_name_index: ok";
echo "\ncreating album_release_index:";

try {
    $pdoDb->exec('CREATE INDEX album_release_index ON albums_new (album_release)');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating album_release_index: ok";
echo "\ndropping albums:";

try {
    $res = $pdoDb->exec("drop table if exists albums");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping albums: ok";
echo "\nrenaming albums_new to albums:";

try {
    $res = $pdoDb->exec("ALTER TABLE albums_new RENAME albums;");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rrenaming albums_new to albums: ok";


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//album_track
echo "\ndropping table album_track_new:";
try {
    $res = $pdoDb->query('drop table if exists album_track_new');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropped table album_track_new: ok";
echo "\ncreating table album_track_new:";

$sql = "CREATE TABLE album_track_new (
            at_id integer not null auto_increment,
            at_album_id INTEGER NOT NULL,
            at_track_id INTEGER NOT NULL,
            at_position SMALLINT DEFAULT 0 NOT NULL,
            primary key (at_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

try {
    $res = $pdoDb->exec($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating table album_track_new: ok";
echo "\nselecting album_track from unifish:";

$sql = "select * from album_track";

try {
    $stmtFish = $pdoFish->query($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой Firebird: ' . $e->getMessage());
    exit;
}

echo "\rselecting album_track from unifish: ok";
echo "\ninserting into album_track_new:";

$sqlQuery = "insert into album_track_new (
                    at_album_id,
                    at_track_id,
                    at_position
                    ) values  ";

$rowNum = 0;
$fullQuery = '';
while ($albumTrackRow = $stmtFish->fetch(PDO::FETCH_ASSOC)) {
    $rowNum++;
    $values = "(" .
        $albumTrackRow['at_album_id'] . "," .
        $albumTrackRow['at_track_id'] . "," .
        $albumTrackRow['at_position'] . ")";

    //собираем запрос
    if (!$fullQuery) {
        $fullQuery = $sqlQuery . $values;
    } else {
        $fullQuery .= ", \n" . $values;
    }

    //отправляем
    if ($rowNum % 10000 == 0) {
        try {
            $res = $pdoDb->query($fullQuery);
            $fullQuery = '';
            echo $rowNum == 10000 ? "\nrowNum: $rowNum" : "\rrowNum: $rowNum";
        } catch (Exception $e) {
            sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
            exit;
        }
    }
}

//если что-то осталось
if ($fullQuery) {
    try {
        $res = $pdoDb->query($fullQuery);
        $fullQuery = '';
        echo "\rrowNum: $rowNum";
    } catch (Exception $e) {
        sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
        exit;
    }
}

echo "\ninserting into album_track_new: ok";
echo "\ncreating index at_album_id_index:";

try {
    $pdoDb->exec('CREATE INDEX at_album_id_index ON album_track_new (at_album_id)');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating index at_album_id_index: ok";
echo "\ncreating index_at_track_id:";

try {
    $pdoDb->exec('CREATE INDEX at_track_id_index ON album_track_new (at_track_id)');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating index_at_track_id: ok";
echo "\ndropping table album_track:";

try {
    $res = $pdoDb->exec("drop table if exists album_track");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping table album_track: ok";
echo "\nrenaming table album_track_new to album_track:";

try {
    $res = $pdoDb->exec("ALTER TABLE album_track_new RENAME album_track;");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rrenaming table album_track_new to album_track: ok";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//albtypes
echo "\ndropping table albtypes_new:";

try {
    $res = $pdoDb->query('drop table if exists albtypes_new');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdroppin table albtypes_new: ok";
echo "\ncreating table albtypes_ne:";

$sql = "CREATE TABLE albtypes_new (
            albtype_id INTEGER NOT NULL,
            albtype_code VARCHAR(3) NOT NULL,
            albtype_name VARCHAR(50) NOT NULL,
            CONSTRAINT pk_albtypes PRIMARY KEY (albtype_id)
        );";

try {
    $res = $pdoDb->exec($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating table albtypes_new: ok";
echo "\nselecting from albtypes:";

$sql = "select * from albtypes";

try {
    $stmtFish = $pdoFish->query($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой Firebird: ' . $e->getMessage());
    exit;
}

echo "\rselecting from albtypes: ok";
echo "\ninserting into albtypes_new:";

$sqlQuery = "insert into albtypes_new (
                    albtype_id,
                    albtype_code,
                    albtype_name
                    ) values  ";

$rowNum = 0;
$fullQuery = '';
while ($albtypesRow = $stmtFish->fetch(PDO::FETCH_ASSOC)) {
    $rowNum++;
    $values = "(" .
        $albtypesRow['albtype_id'] . "," .
        ($pdoDb->quote($albtypesRow['albtype_code']) ?: '') . "," .
        ($pdoDb->quote($albtypesRow['albtype_name']) ?: '') . ")";

    //собираем запрос
    if (!$fullQuery) {
        $fullQuery = $sqlQuery . $values;
    } else {
        $fullQuery .= ", \n" . $values;
    }

    //отправляем
    if ($rowNum % 10000 == 0) {
        try {
            $res = $pdoDb->query($fullQuery);
            $fullQuery = '';
            echo $rowNum == 10000 ? "\nrowNum: $rowNum" : "\rrowNum: $rowNum";
        } catch (Exception $e) {
            sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
            exit;
        }
    }
}

//если что-то осталось
if ($fullQuery) {
    try {
        $res = $pdoDb->query($fullQuery);
        $fullQuery = '';
        echo "\rrowNum: $rowNum";
    } catch (Exception $e) {
        sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
        exit;
    }
}

echo "\rinserting into albtypes_new: ok";
echo "\ndroppng table albtypes:";

try {
    $res = $pdoDb->exec("drop table if exists albtypes");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping table albtypes: ok";
echo "\nrenaming table albtypes_new:";

try {
    $res = $pdoDb->exec("ALTER TABLE albtypes_new RENAME albtypes;");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rrenaming table albtypes_new: ok";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//alblabels
echo "\ndroping table alblabels_new:";

try {
    $res = $pdoDb->query('drop table if exists alblabels_new');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping table alblabels_new: ok";
echo "\ncreating table alblabels_new:";

$sql = "CREATE TABLE alblabels_new (
                alblab_id INTEGER NOT NULL,
                alblab_name VARCHAR(300) NOT NULL,
                alblab_code VARCHAR(25) DEFAULT NULL,
                CONSTRAINT pk_alblabels PRIMARY KEY (alblab_id)
            );";

try {
    $res = $pdoDb->exec($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating table alblabels_new: ok";
echo "\nselecting from alblabels:";

$sql = "select * from alblabels";

try {
    $stmtFish = $pdoFish->query($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой Firebird: ' . $e->getMessage());
    exit;
}

echo "\rselecting from alblabels: ok";
echo "\ninserting into alblabels_new:";

$sqlQuery = "insert into alblabels_new (
                    alblab_id,
                    alblab_name,
                    alblab_code
                    ) values  ";

$rowNum = 0;
$fullQuery = '';
while ($alblabelsRow = $stmtFish->fetch(PDO::FETCH_ASSOC)) {
    $rowNum++;
    $values = "(" .
        $alblabelsRow['alblab_id'] . "," .
        ($pdoDb->quote($alblabelsRow['alblab_name']) ?: '') . "," .
        addQuotes($alblabelsRow['alblab_code']) . ")";

    //собираем запрос
    if (!$fullQuery) {
        $fullQuery = $sqlQuery . $values;
    } else {
        $fullQuery .= ", \n" . $values;
    }

    //отправляем
    if ($rowNum % 10000 == 0) {
        try {
            $res = $pdoDb->query($fullQuery);
            $fullQuery = '';
            echo $rowNum == 10000 ? "\nrowNum: $rowNum" : "\rrowNum: $rowNum";
        } catch (Exception $e) {
            sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
            exit;
        }
    }
}

//если что-то осталось
if ($fullQuery) {
    try {
        $res = $pdoDb->query($fullQuery);
        $fullQuery = '';
        echo "\rrowNum: $rowNum";
    } catch (Exception $e) {
        sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
        exit;
    }
}

echo "\rinserting into alblabels_new: ok";
echo "\ndropping table alblabels:";

try {
    $res = $pdoDb->exec("drop table if exists alblabels");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping table alblabels_new: ok";
echo "\nrenaming table alblabels_new:";

try {
    $res = $pdoDb->exec("ALTER TABLE alblabels_new RENAME alblabels;");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rrenaming table alblabels_new: ok";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//clients

//todo Понять нужна ли новая таблица Клиентов из базы Юнифиш или оставить таблицу из старого проекта.
echo "\ndroping table clients_new:";

try {
    $res = $pdoDb->query('drop table if exists clients_new');
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping table clients_new: ok";
echo "\ncreating table clients_new:";

$sql = "CREATE TABLE `clients_new` (
          `client_id` int NOT NULL,
          `client_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
          `client_fio` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
          `client_alias` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
          `client_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
          PRIMARY KEY (`client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

try {
    $res = $pdoDb->exec($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rcreating table clients_new: ok";
echo "\nselecting from clients:";

$sql = "select * from clients LEFT JOIN CLITYPES ON CLIENT_CLITYPE = CLITYPE_ID ";

try {
    $stmtFish = $pdoFish->query($sql);
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой Firebird: ' . $e->getMessage());
    exit;
}

echo "\rselecting from clients: ok";
echo "\ninserting into clients_new:";

$sqlQuery = "insert into clients_new (
                    client_id,
                    client_name,
                    client_fio,
                    client_alias,
                    client_type
                    ) values  ";

$rowNum = 0;
$fullQuery = '';
while ($clientsRow = $stmtFish->fetch(PDO::FETCH_ASSOC)) {
    $rowNum++;
    $values = "(" .
        $clientsRow['client_id'] . "," .
        ($pdoDb->quote($clientsRow['client_name']) ?: '') . "," .
        ($pdoDb->quote($clientsRow['client_nick']) ?: '') . "," .
        ($pdoDb->quote($clientsRow['client_alias']) ?: '') . "," .
        ($pdoDb->quote($clientsRow['clitype_name']) ?: '')  . ")";

    //собираем запрос
    if (!$fullQuery) {
        $fullQuery = $sqlQuery . $values;
    } else {
        $fullQuery .= ", \n" . $values;
    }

    //отправляем
    if ($rowNum % 10000 == 0) {
        try {
            $res = $pdoDb->query($fullQuery);
            $fullQuery = '';
            echo $rowNum == 10000 ? "\nrowNum: $rowNum" : "\rrowNum: $rowNum";
        } catch (Exception $e) {
            sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
            exit;
        }
    }
}

//если что-то осталось
if ($fullQuery) {
    try {
        $res = $pdoDb->query($fullQuery);
        $fullQuery = '';
        echo "\rrowNum: $rowNum";
    } catch (Exception $e) {
        sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
        exit;
    }
}

echo "\rinserting into clients_new: ok";
echo "\ndropping table clients:";

try {
    $res = $pdoDb->exec("drop table if exists clients");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rdropping table clients: ok";
echo "\nrenaming table clients_new:";

try {
    $res = $pdoDb->exec("ALTER TABLE clients_new RENAME clients;");
} catch (Exception $e) {
    sendEmailToAdmin([], [], '', 'Ошибка при работе с базой db: ' . $e->getMessage());
    exit;
}

echo "\rrenaming table clients_new: ok";



























$difference = time() - $start;

if ($difference <= 60) {
    $time = '0:' . $difference;
} else if ($difference <= 3600) {
    $time = '0:' . intval($difference / 60) . ':' . $difference % 60;
} else {
    $time = intval($difference / 3600) . ':' . intval($difference / 3600) . ':' . $difference % 60;
}

echo 'time: ' . $time;
echo "\n";











