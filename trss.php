<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

setlocale(LC_ALL, 'ru_RU');
date_default_timezone_set('Europe/Moscow');
header('Content-Type: text/xml; charset=utf-8');

$url = $_SERVER['SERVER_NAME']; // адрес вашего сайта

$feed = '<?xml version="1.0" encoding="UTF-8"?>
     <rss xmlns:yandex="http://news.yandex.ru"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:turbo="http://turbo.yandex.ru"
     version="2.0">
     <channel>
        <title>Название вашего сайта</title>
        <link>'.$url.'</link>
        <description>Описание вашего сайта</description>
        <language>ru</language>
        <turbo:analytics type="Yandex" id="ID счетчика Яндекс.Метрики"></turbo:analytics>
        <turbo:adNetwork type="Yandex" id="идентификатор блока" turbo-ad-id="first_ad_place"></turbo:adNetwork>
        <turbo:adNetwork type="AdFox" turbo-ad-id="second_ad_place">
        <yandex:related type="infinity">
        ';

        $sdd_db_host='localhost'; // хост базы данных (чаще всего localhost)
        $sdd_db_name='site'; // название ваше базы данных
        $sdd_db_user='admin'; // пользователь базы данных
        $sdd_db_pass='12345'; // пароль к базе данных

        $mysqli = new mysqli($sdd_db_host,$sdd_db_user,$sdd_db_pass); // подключение к серверу БД с заданными параметрами авторизации
        if ($mysqli->connect_errno) echo "Error - Failed to connect to MySQL: " . $mysqli->connect_error;

        $mysqli->query("SET NAMES utf8"); // используется, если ключи не в формате UTF-8
        $mysqli->select_db($sdd_db_name); // выбираем нужную базу данных
        $sql = "SELECT * FROM `site` WHERE `type` = 'articles'";
        // создаем запрос на выборку из базы данных
        // SELECT * FROM `site` WHERE `type` = 'articles' расшифровывается так:
        // выбрать из базы данных site все пары, где указан тип articles (статьи)
        $result = $mysqli->query($sql);
        
        // создадим бесконечную ленту статей (после дочитывания первой за ней сразу же пойдет следующая)
        while($row = mysqli_fetch_assoc($result)) {
        	$data[] = $row;
        	$feed .= '<link url="'.$site.'/articles/'.$row['id'].'">'.$row['name'].'</link>';
        }
        //  $row['id'] - уникальный идентификатор мастериалы из БД
        //  $row['name'] - название статьи с этим уникальным id
       
       mysqli_close();
       $feed .= '</yandex:related>';

       // теперь остается собрать сами статьи в ленту, немного заменив относительные ссылки
        foreach ($data as $row) {
            $text = $row['text'];

            $text = str_replace('src="/', 'src="' . $url . '/', $text); 
            $text = str_replace('href="/', 'href="' . $url . '/', $text); 
            
            //str_replace('что заменяем', 'на что заменяем', в нашей строке);

            $feed .= '
            <item turbo="true">        
                <link>' . $url . '/articles/'.$row['id'].'</link>
                <author>'.$row['author'].'</author>
                <category>'.$row['category'].'</category>
                <turbo:source>' . $url . '/articles/'.$row['id'].'</turbo:source>
                <turbo:topic>Turbo '.$row['name'].'</turbo:topic>
                <pubDate>'.date(DATE_RFC822, strtotime($row['date'])).'</pubDate>
                <turbo:content>
                    <![CDATA[
                        <header>
                            <h1>' . $row['name'] . '</h1>
                            <figure>
                           		<img src="'.$site.'/'.$row['photo'].'">
                        	</figure>
                        </header>
                        ' . $text . '
                </turbo:content>
            </item>';
        }
        // <link>собираем ссылку на статью</link>
        // <author>автор публикации</author>
        // <category>категория материала</category>
        // <turbo:source>URL страницы-источника, который можно передать в Метрику</turbo:source>
        // <turbo:topic>Заголовок страницы, который можно передать в Метрику</turbo:topic>
        // date(DATE_RFC822, strtotime($row['date'])) используется для того, чтобы привести дату к формату RFC-822 (Sun, 29 Sep 2002 19:59:01 +0300)

        $feed .= '
    </channel>
    </rss>';

echo $feed;
