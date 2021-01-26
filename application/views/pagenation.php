<?php
/*
 * Requires:
 * $number - Number of items to page
 * $offset - The current offset
 * $limit  - The limit used
 * $anchor - The link to use
 */

if (!isset($number) || !isset($offset) || !isset($limit))
    echo 'Paganation ERROR';
else if ($number < 0 || $offset < 0 || $limit < 0)
    echo 'Paganation ERROR';
else
{
    $current_page = ($offset > 0 ? $offset / $limit : 1);
    $max_page = (int)ceil(($number / $limit));

    for ($page = 1; $page <= $max_page; $page++)
    {
        echo anchor($anchor.( ($page - 1) * $limit), $page).' ';
    }
}
?>
