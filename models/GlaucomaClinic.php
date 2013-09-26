<?php

/**
 * Contains table-formatting rules for glaucoma clinic data.
 */
class GlaucomaClinic {

    /**
     * Enables custom formatting of table data.
     * 
     * @param string $columnName the column name being formatted.
     * 
     * @param mixed $data data passed in representing the column's value.
     * 
     * @return string formatted text, if the specified column had custom
     * formatting; null otherwise.
     */
    public static function formatData($columnName, $data) {
        $text = null;
        if (($columnName == 'IOP' || $columnName == 'CCT') && $data) {
            if ($data[0] && $data[1]) {
                $text = "RE: " . $data[1] . "<br>" . "LE: " . $data[0];
            }
        }
        if ($columnName == 'Medications' && $data) {
            for ($i=0; $i < 3; $i++) {
                if ($data[$i]) {
                    $text = $text . substr($data[$i], 0, 3) . "/";
                }
            }
            if ($text[strlen($text)-1] == "/") {
                $text = substr($text, 0, strlen($text)-1);
            }
            if ($data[0] || $data[1] || $data[2]) {
                $text = $text . "<br/>";
            }
            for ($i=3; $i < 6; $i++) {
                if ($data[$i]) {
                    $text = $text . substr($data[$i], 0, 3) . "/";
                }
            }
            if ($text[count($text)-1] == "/") {
                $text = substr($text, 0, count($text));
            }
            if ($text[strlen($text)-1] == "/") {
                $text = substr($text, 0, strlen($text)-1);
            }
        }
        return $text;
    }
}

?>
