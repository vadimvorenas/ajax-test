<?php

class DB
{
    public static function init()
    {
        $db = new MongoDB\Driver\Manager("mongodb://localhost:27017");
        return $db;
    }

    public static function default_options_search ()
    {
        return [
            /* Only return the following fields in the matching documents */
            'projection' => [
            ],
            /* Return the documents in descending order of views */
            'sort' => [

            ],
        ];
    }
}