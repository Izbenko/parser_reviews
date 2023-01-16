<?php

require_once 'simple_html_dom.php';

function numberMonthsDate($date) // меняет месяц на числовое представление
{
    $monthsList = array(
        "января" => "01",
        "февраля" => "02",
        "марта" => "03",
        "апреля" => "04",
        "мая" => "05",
        "июня" => "06",
        "июля" => "07",
        "августа" => "08",
        "сентября" => "09",
        "октября" => "10",
        "ноября" => "11",
        "декабря" => "12",
    );

    $dateArr = explode(' ', $date);
    $month = $dateArr[1];
    $date = implode('.', $dateArr);

    $date = str_replace($month, $monthsList[$month], $date);

    return $date;
}

function getPage($url) // получить html-страницу
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function getReviews($url) // получить отзывы
{
    $page = getPage($url);
    $html = str_get_html($page);

    $totalPages = $html->find('.js-list-more', 0);
    $totalPages = $totalPages->attr['data-nav-page-count']; // количество страниц с отзывами

    $result = [];

    for ($i = 1; $i <= $totalPages; $i++) {
        $newUrl = $url . "?PAGEN_1=$i"; // получаем url для каждой страницы с отзывами
        $page = getPage($newUrl);
        $html = str_get_html($page);

        foreach ($html->find('.review-wrap') as $review) {
            $name = $review->find('.user-name', 0);
            $name = $name->plaintext;
            $date = $review->find('.review-date', 0);
            $date = $date->plaintext;
            $grade = $review->find('.review-rating', 0);
            $grade = $grade->plaintext;
            $text = $review->find('.review-text-full', 0);
            if (!$text) {
                $text = $review->find('.review-text-preview', 0);
            }
            $text = $text->plaintext;

            $result[] = [
                'Имя' => $name,
                'Дата' => numberMonthsDate($date),
                'Оценка' => $grade,
                'Текст' => $text,
            ];
        }
    }

    return $result;
}


$url = 'https://www.restoran.ru/msk/opinions/restaurants/sixty/';

$result = getReviews($url);

foreach ($result as $item) {
    echo $item['Имя'] . ' ' . $item['Дата'] . ' Оценка: ' . $item['Оценка'] . '<br>';
    echo $item['Текст'] . '<br>';
    echo '-------------------------<br>';
}