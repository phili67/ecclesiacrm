<?php
/*******************************************************************************
 *
 *  filename    : /Include/LabelFunctions.php
 *  website     : http://www.ecclesiacrm.com
 *
 *  Contributors:
 *  2006 Ed Davis
 *
 *
 *  Copyright 2006 Contributors
 *
 *
 ******************************************************************************/

// This file contains functions specifically related to address labels

function FontSelect($fieldname,$path='')
{
    $sFPDF_PATH = $path.'vendor/setasign/fpdf';

    $d = scandir($sFPDF_PATH.'/font/', SCANDIR_SORT_DESCENDING);
    
    $fontnames = [];
    $family = ' ';
    foreach ($d as $entry) {
        $len = strlen($entry);
        if ($len > 3) {
            $r = file_get_contents ($sFPDF_PATH.'/font/'.$entry);
            $res = explode('$name = \'', $r);
            $font = explode ("';",$res[1]);
            
            $font = $font[0];
            
            $font = str_replace ("-BoldOblique"," Bold Italic",$font);
            $font = str_replace ("-BoldItalic"," Bold Italic",$font);
            $font = str_replace ("-Bold"," Bold",$font);
            $font = str_replace ("-Oblique"," Italic",$font);
            $font = str_replace ("-Italic"," Italic",$font);
            $font = str_replace ("-Roman","",$font);
            $font = str_replace ("-"," ",$font);
            
            $fontnames[] = $font;
        }
    }

    sort($fontnames);

    echo '<tr>';
    echo '<td class="LabelColumn">'.gettext('Font').':</td>';
    echo '<td class="TextColumn">';
    echo "<select name=\"$fieldname\" class=\"form-control input-sm\">";
    foreach ($fontnames as $n) {
        $sel = '';
        if (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == $n) {
            $sel = ' selected';
        }
        echo '<option value="'.$n.'"'.$sel.'>'.gettext("$n").'</option>';
    }
    echo '</select>';
    echo '</td>';
    echo '</tr>';
}

function FontSizeSelect($fieldname,$message='')
{
    $sizes = [gettext('default'), 6, 7, 8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26];
    echo '<tr>';
    echo '<td class="LabelColumn"> '.gettext('Font Size').(!empty($message)?' '.$message:'').':</td>';
    echo '<td class="TextColumn">';
    echo "<select name=\"$fieldname\" class=\"form-control input-sm\">";
    foreach ($sizes as $s) {
        $sel = '';
        if (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == $s) {
            $sel = ' selected';
        }
        echo '<option value="'.$s.'"'.$sel.'>'.gettext("$s").'</option>';
    }
    echo '</select>';
    echo '</td>';
    echo '</tr>';
}

function LabelSelect($fieldname,$title='')
{
    $labels = [gettext('Tractor'), 'Badge', '5160', '5161', '5162', '5163', '5164', '8600', 'L7163'];
    
    if (empty($title)) {
      $title = gettext('Label Type');
    }
    
    echo '<tr>';
    echo '<td class="LabelColumn">'.$title.':</td>';
    echo '<td class="TextColumn">';
    echo "<select name=\"$fieldname\" class=\"form-control input-sm\">";
    foreach ($labels as $l) {
        $sel = '';
        if (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == $l) {
            $sel = ' selected';
        }
        echo '<option value="'.$l.'"'.$sel.'>'.gettext("$l").'</option>';
    }
    echo '</select>';
    echo '</td>';
    echo '</tr>';
}

function LabelGroupSelect($fieldname)
{
    echo '<tr><td class="LabelColumn">'.gettext('Label Grouping').'</td>';
    echo '<td class="TextColumn">';
    echo "<input name=\"$fieldname\" type=\"radio\" value=\"indiv\" ";

    if (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] != 'fam') {
        echo 'checked';
    }

    echo '>'.gettext('All Individuals').'<br>';
    echo "<input name=\"$fieldname\" type=\"radio\" value=\"fam\" ";

    if (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname] == 'fam') {
        echo 'checked';
    }

    echo '>'.gettext('Grouped by Family').'<br></td></tr>';
}

function ToParentsOfCheckBox($fieldname)
{
    echo '<tr><td class="LabelColumn">'.gettext('To the parents of').':</td>';
    echo '<td class="TextColumn">';
    echo "<input name=\"$fieldname\" type=\"checkbox\" ";
    echo 'id="ToParent" value="1" ';

    if (array_key_exists($fieldname, $_COOKIE) && $_COOKIE[$fieldname]) {
        echo 'checked';
    }

    echo '><br></td></tr>';
}

function StartRowStartColumn()
{
    echo '
	<tr>
	<td class="LabelColumn">'.gettext('Start Row').':
	</td>
	<td class="TextColumn">
	<input type="text" name="startrow" id="startrow" maxlength="2" size="3" value="1" class="form-control">
	</td>
	</tr>
	<tr>
	<td class="LabelColumn">'.gettext('Start Column').':
	</td>
	<td class="TextColumn">
	<input type="text" name="startcol" id="startcol" maxlength="2" size="3" value="1" class="form-control">
	</td>
	</tr>';
}

function IgnoreIncompleteAddresses()
{
    echo '
	<tr>
	<td class="LabelColumn">'.gettext('Ignore Incomplete<br>Addresses').':
	</td>
	<td class="TextColumn">
	<input type="checkbox" name="onlyfull" id="onlyfull" value="1" checked>
	</td>
	</tr>';
}

function LabelFileType()
{
    echo '
	<tr>
		<td class="LabelColumn">'.gettext('File Type').':
		</td>
		<td class="TextColumn">
			<select name="filetype" class="form-control input-sm">
				<option value="PDF">PDF</option>
				<option value="CSV">CSV</option>
			</select>
		</td>
	</tr>';
}
