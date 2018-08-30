<?php

if (!function_exists('plural')) {
    function plural($word) {
        $uncountable = [
            'sheep',
            'fish',
            'deer',
            'series',
            'species',
            'money',
            'rice',
            'information',
            'equipment'
        ];

        $irregular = [
            'move'   => 'moves',
            'foot'   => 'feet',
            'goose'  => 'geese',
            'sex'    => 'sexes',
            'child'  => 'children',
            'man'    => 'men',
            'tooth'  => 'teeth',
            'person' => 'people',
            'valve'  => 'valves'
        ];

        $plural = [
            '/(quiz)$/i'               => "$1zes",
            '/^(ox)$/i'                => "$1en",
            '/([m|l])ouse$/i'          => "$1ice",
            '/(matr|vert|ind)ix|ex$/i' => "$1ices",
            '/(x|ch|ss|sh)$/i'         => "$1es",
            '/([^aeiouy]|qu)y$/i'      => "$1ies",
            '/(hive)$/i'               => "$1s",
            '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
            '/(shea|lea|loa|thie)f$/i' => "$1ves",
            '/sis$/i'                  => "ses",
            '/([ti])um$/i'             => "$1a",
            '/(tomat|potat|ech|her|vet)o$/i'=> "$1oes",
            '/(bu)s$/i'                => "$1ses",
            '/(alias)$/i'              => "$1es",
            '/(octop)us$/i'            => "$1i",
            '/(ax|test)is$/i'          => "$1es",
            '/(us)$/i'                 => "$1es",
            '/s$/i'                    => "s",
            '/$/'                      => "s"
        ];

        // save some time in the case that singular and plural are the same
        if ( in_array( strtolower( $word ), $uncountable ) ) {
            return $word;
        }


        // check for irregular singular forms
        foreach ( $irregular as $pattern => $result ) {
            $pattern = '/' . $pattern . '$/i';

            if ( preg_match( $pattern, $word ) ) {
                return preg_replace($pattern, $result, $word);
            }
        }

        // check for matches using regular expressions
        foreach ( $plural as $pattern => $result ) {
            if ( preg_match( $pattern, $word ) ) {
                return preg_replace($pattern, $result, $word);
            }
        }

        return $word;
    }
}