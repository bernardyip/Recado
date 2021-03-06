<?php

class HtmlHelper {
    
    public static function createTable($rows, $headers) {
        $html = "<table>";
        if (!is_null($headers) && sizeof($headers) > 0) {
            $html = $html . HtmlHelper::createTableHeaderRow($headers);
        }
        foreach ($rows as $row) {
            $html = $html . HtmlHelper::createTableRow($row);
        }
        $html = $html . "</table>";
        return $html;
    }
    
    public static function createTableHeaderRow($columns) {
        $html = "<tr>";
        foreach ($columns as $column) {
            $html = $html . "<th>" . $column . "</th>";
        }
        $html = $html . "</tr>";
        return $html;
    }
    
    public static function createTableRow($columns) {
        $html = "<tr>";
        foreach ($columns as $column) {
            $html = $html . "<td>" . $column . "</td>";
        }
        $html = $html . "</tr>";
        return $html;
    }
    
    public static function makeTextArea2($name, $value, $rows, $placeholder) {
        $html = "<textarea class=\"form-control\" name=\"$name\" placeholder=\"$placeholder\">$value";
        $html = $html . "</textarea>";
        return $html;
    }
    
    public static function makeInput2($type, $name, $value, $placeholder, $title, $autofocus = false) {
        $html = "<input class=\"form-control\" type=\"$type\" name=\"$name\" placeholder=\"$placeholder\" title=\"$title\" value=\"$value\"";
        if ($autofocus) {
            $html = $html . " autofocus ";
        }
        $html = $html . "/>";
        return $html;
    }
    
    public static function makeInputOnChange2($type, $name, $value, $placeholder, $title, $onChange, $autofocus = false) {
        $html = "<input class=\"form-control\" type=\"$type\" name=\"$name\" placeholder=\"$placeholder\" title=\"$title\" value=\"$value\" onchange=\"$onChange\"";
        if ($autofocus) {
            $html = $html . " autofocus ";
        }
        $html = $html . "/>";
        return $html;
    }
    
    public static function makeFileInput2($extension, $name, $value, $placeholder, $title, $autofocus = false) {
        $html = "<input class=\"form-control\" accept=\"$extension\" type=\"file\" name=\"$name\" placeholder=\"$placeholder\" title=\"$title\" value=\"$value\"";
        if ($autofocus) {
            $html = $html . " autofocus ";
        }
        $html = $html . "/>";
        return $html;
    }
    
    public static function makeInput($type, $name, $value, $placeholder, $title, $autofocus = false) {
        $html = "<input type=\"$type\" name=\"$name\" placeholder=\"$placeholder\" title=\"$title\" value=\"$value\"";
        if ($autofocus) {
            $html = $html . " autofocus ";
        }
        $html = $html . "/>";
        return $html;
    }
    
    public static function makeMoneyInput2($name, $value, $placeholder, $title, $autofocus = false) {
        $html = "<input class=\"form-control\" type=\"number\" min=\"0.01\" step=\"0.01\" name=\"$name\" placeholder=\"$placeholder\" title=\"$title\" value=\"$value\"";
        if ($autofocus) {
            $html = $html . " autofocus ";
        }
        $html = $html . "/>";
        return $html;
    }
    
    public static function makeMoneyInput3($name, $value, $placeholder, $title, $min, $autofocus = false) {
        $html = "<input class=\"form-control\" type=\"number\" min=\"$min\" step=\"0.01\" name=\"$name\" placeholder=\"$placeholder\" title=\"$title\" value=\"$value\"";
        if ($autofocus) {
            $html = $html . " autofocus ";
        }
        $html = $html . "/>";
        return $html;
    }
    
    public static function makeMoneyInput($name, $value, $placeholder, $title, $autofocus = false) {
        $html = "<input type=\"number\" min=\"0.01\" step=\"0.01\" name=\"$name\" placeholder=\"$placeholder\" title=\"$title\" value=\"$value\"";
        if ($autofocus) {
            $html = $html . " autofocus ";
        }
        $html = $html . "/>";
        return $html;
    }
    
    public static function makeCheckboxInput($name, $display, $category_id) {
        $html = "<input type='checkbox' value='" . $category_id. "' name='" . $name . "' checked /><span>&nbsp;" . $display. "</span>";
        return $html;
    }
}

?>